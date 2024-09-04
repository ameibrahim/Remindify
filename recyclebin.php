<?php include 'auth.php'; 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reminder_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if the user is logged in


// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];



// Fetch user data for display
$sql = "SELECT first_name, username, email, profile_image FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = htmlspecialchars($row['first_name']); // Fetch the first name
    $username = htmlspecialchars($row['username']);
    $imageData = !empty($row['profile_image']) ? base64_encode($row['profile_image']) : null;
    $imageSrc = $imageData ? 'data:image/jpeg;base64,' . $imageData : 'default-profile.png'; // Default profile image if none is set
} else {
    echo "No user found.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin │ Remindify</title>
    <link rel="shortcut icon" type="x-icon" href="logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp"
    rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <aside>
            <div class="top">
                <div class="logo">
                    <img src="logo.png" alt="Remindify Logo">
                    <h2><span class="primary">Remind</span><span class="danger">Ify</span></h2>
                </div>
                <div class="close" id="close-btn">        
                    <span class="material-icons-sharp">close</span>
                </div>
            </div>

            <div class="sidebar">
                <a href="dashboard.php" >
                    <span class="material-icons-sharp">grid_view</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="AllReminders.php">
                    <span class="material-icons-sharp">today</span>
                    <h3>All Reminders</h3>
                    <!-- <span class="today-count">3</span> -->
                </a>
                <a href="#" class="active">
                <span class="material-icons-sharp">schedule_send</span>
                    <h3>Shared Calendar</h3>
                </a>
                <a href="calendar.php">
                    <span class="material-icons-sharp">calendar_month</span>
                    <h3>Calendar</h3>
                </a>
                <a href="balance.php">
                    <span class="material-icons-sharp">savings</span>
                    <h3>My Wallet</h3>
                </a>
                <a href="completed.php">
                    <span class="material-icons-sharp">assignment_turned_in</span>
                    <h3>Completed</h3>
                </a>
                <a href="#" class="active">
                    <span class="material-icons-sharp" class="active">delete</span>
                    <h3>Recycle Bin</h3>
                </a>
                <!-- <a href="settings.html">
                    <span class="material-icons-sharp">settings</span>
                    <h3>Settings</h3>
                </a> -->
                <div class="bottom-buttons">
                    <a href="accountsettings.php">
                        <span class="material-icons-sharp">account_circle</span>
                        <h3>Account Settings</h3>
                    </a>
                    <form action="logout.php" method="post" style="display: inline;">
    <button type="submit" class="red">
        <span class="material-icons-sharp">logout</span>
        <h3>Log Out</h3>
    </button>
</form>

            </div>
        </aside>

        <!-- ----------------end of aside ;))))) ---------------- -->

        <main>
            <h1>Recycle Bin</h1>

            <div class="date">
                <span id="current-date"></span>
            </div>

            <div class="recycle-bin">
                <div class="recycle-bin-options">
                    <button class="restore-btn">Restore Selected</button>
                    <button class="delete-btn">Delete Selected</button>
                    <button class="empty-bin-btn">Empty Bin</button>
                </div>
                <div class="recycle-bin-items">
                    <!-- Example of a deleted reminder -->
                    <div class="recycle-item">
                        <input type="checkbox" class="item-checkbox">
                        <span class="item-name">Deleted Reminder 1</span>
                        <span class="item-date">Deleted on: 2024-08-19</span>
                    </div>
                    <div class="recycle-item">
                        <input type="checkbox" class="item-checkbox">
                        <span class="item-name">Deleted Reminder 2</span>
                        <span class="item-date">Deleted on: 2024-08-18</span>
                    </div>
                    <!-- Additional items -->
                </div>
            </div>
        
            <!-- Modal for confirmation prompt -->
            <div id="confirmation-modal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <p>Are you sure you want to permanently delete the selected items?</p>
                    <button class="confirm-delete">Yes, Delete</button>
                    <button class="cancel-delete">Cancel</button>
                </div>
            </div>
        
            <!-- Settings for auto empty -->
            <div class="auto-empty-settings">
                <h2>Auto Empty Recycle Bin</h2>
                <form id="auto-empty-form">
                    <label for="empty-period">Auto Empty After:</label>
                    <select id="empty-period" name="empty-period">
                        <option value="7">1 Week</option>
                        <option value="30">1 Month</option>
                        <option value="90">3 Months</option>
                    </select>
                    <button type="submit">Save Settings</button>
                </form>
            </div>
        </main>
        <!-- ============================ END OF MAIN===================== -->
        <div class="right">
            <div class="top">
                <button id="menu-btn">
                    <span class="material-icons-sharp">menu</span>
                </button>
                <div class="theme-toggler">
                    <span class="material-icons-sharp active">light_mode</span>
                    <span class="material-icons-sharp">dark_mode</span>
                </div>
                <div class="profile">
                    <div class="info">
                        <!-- Dynamically display first name -->
                        <p>Hey, <b><?php echo $firstName; ?></b></p>
                        <small class="text-muted">Admin</small> <!-- This role can be dynamic if needed -->
                    </div>
                    <div class="profile-photo">
                        <!-- Dynamically display profile picture -->
                        <img src="<?php echo $imageSrc; ?>" alt="Profile Photo">
                    </div>
                </div>
            </div>
                <!-- === end of top === -->
        </div>
    </div>
    <script src="./dashboard.js"></script>
</body>
</html>