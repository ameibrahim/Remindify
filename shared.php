<?php
include 'auth.php';
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "remindify";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user data for display
$sql = "SELECT first_name, username, email, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = htmlspecialchars($row['first_name']);
    $username = htmlspecialchars($row['username']);
    $imageData = !empty($row['profile_image']) ? base64_encode($row['profile_image']) : null;
    $imageSrc = $imageData ? 'data:image/jpeg;base64,' . $imageData : 'default-profile.png';
} else {
    echo "No user found.";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Calendar │ Remindify</title>
    <link rel="shortcut icon" type="x-icon" href="logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp"
    rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="shared.css">

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
                <!-- <a href="completed.php">
                    <span class="material-icons-sharp">assignment_turned_in</span>
                    <h3>Completed</h3>
                </a> -->
                <a href="recyclebin.php">
                    <span class="material-icons-sharp" class="active">delete</span>
                    <h3>Recycle Bin</h3>
                </a>
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
            <h1>Shared Calendar</h1>

            <div class="date">
                <span id="current-date"></span>
            </div>
<div class="wrapper">


            <div class="groups-container">
    <div class="groups-list">
        <?php
        // Fetch all groups
        $sql = "SELECT * FROM shared_groups WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id); // Bind user_id parameter
$stmt->execute();
$result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $group_id = htmlspecialchars($row['group_id']);
                $group_name = htmlspecialchars($row['group_name']);
                echo "
                <div class='group-item' data-group-id='$group_id'>
                    <header>
                        <h1>$group_name</h1>
                    </header>

                    <section class='reminder'>
                        <h2>Upcoming Event</h2>
                    </section>

                    <footer>
                        <div class='dropdown'>
                            <button class='dropbtn'>⋮</button>
                            <div class='dropdown-content'>
                                <button class='add-reminder-btn' data-group-id='$group_id'>Add Reminder</button>
                                <button class='view-participants-btn' data-group-id='$group_id'>View Participants</button>
                                <button class='details-btn' data-group-id='$group_id'>Reminder Details</button>
                                <button class='edit-btn' data-group-id='$group_id'>Edit</button>
                                <button class='delete-btn' data-group-id='$group_id'>Delete</button>
                            </div>
                        </div>
                    </footer>
                </div>

                <!-- Modal for Editing Group -->
                <div id='myModal-$group_id' class='modal'>
                    <div class='plan'>
                        <div class='inner'>
                            <h2 class='title'>Edit Group</h2>
                            <ul class='features'>
                                <li><span class='icon'>✓</span>Change group name</li>
                                <li><span class='icon'>✓</span>Manage reminders</li>
                            </ul>
                            <button>Save Changes</button>
                        </div>
                    </div>
                </div>";
            }
        } else {
            echo "<p>No groups available.</p>";
        }
        ?>
    </div>
</div>
</div>
    <!-- Modals -->
    <div id="modal-container">
        <!-- Add Reminder Modal -->
<!-- Add Reminder Modal -->
<div id="add-reminder-modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span> <!-- Close Button -->
        <h2>Add Reminder</h2>
        
        <!-- Add Reminder Form -->
        <form id="add-reminder-form" action="add_group_reminder.php" method="POST">
            <div class="form-group">
                <input type="hidden" id="add-group-id" name="group-id"> <!-- Hidden Group ID -->

                <label for="reminder-title">Reminder Title</label>
                <input type="text" id="reminder-title" name="reminder-title" placeholder="Enter title" required>
            </div>

            <div class="form-group">
                <label for="reminder-date">Reminder Date</label>
                <input type="date" id="reminder-date" name="reminder-date" required>
            </div>

            <div class="form-group">
                <label for="reminder-time">Reminder Time</label>
                <input type="time" id="reminder-time" name="reminder-time" required>
            </div>

            <div class="form-group">
                <label for="reminder-description">Description</label>
                <input type="text" id="reminder-description" name="reminder-description" placeholder="Enter description" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">Add Reminder</button>
        </form>
    </div>
</div>


        <!-- Details Modal -->
        <div id="details-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('details-modal').style.display='none'">&times;</span>
        <h2>Reminder Details</h2>
        <button id="toggle-button">Show All</button>
        <table id="reminder-table" style="width: 100%; display: none;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="activity-list">
                <!-- Dynamic rows will be inserted here -->
            </tbody>
        </table>
    </div>
</div>

        <!-- View Participants Modal -->
        <div id="view-participants-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Participants</h2>
                <ul id="participants-list">
                    <!-- Participants will be dynamically loaded here -->
                </ul>
            </div>
        </div>

        <!-- Edit Group Modal -->
        <div id="edit-group-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Group</h2>
                <form id="edit-group-form">
                    <input type="hidden" id="edit-group-id">
                    <label for="edit-group-name">Group Name:</label>
                    <input type="text" id="edit-group-name" required>
                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

        </main>

<!-- Modal Container -->
<div id="notification-modal" class="modal">
    <article class="plan card">
        <div class="inner">
            <!-- Notification Title and Message -->
            <h2 class="title" id="notification-title">New Event Notification</h2>
            <p class="info" id="notification-message">You have been invited to a group event.</p>

            <!-- Group Participants -->
            <ul class="features" id="participants-list">
                <li>
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z" fill="currentColor" />
                        </svg>
                    </span>
                    <span>Participant Name</span>
                </li>
                <!-- Add more participants dynamically -->
            </ul>

            <!-- Action Buttons -->
            <button id="view-details-btn" class="button">View Details</button>
            <button id="close-btn" class="button">Close</button>
        </div>
    </article>
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
     <script>
    document.addEventListener('DOMContentLoaded', () => {
    // Handle dropdown toggle
    document.querySelectorAll('.dropbtn').forEach(button => {
        button.addEventListener('click', () => {
            button.nextElementSibling.classList.toggle('show');
        });
    });

    // Function to open modals
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'block';

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        };
    }

    // Function to close modals
    function closeModal(modal) {
        modal.style.display = 'none';
    }

    // Handle Add Reminder
    document.querySelectorAll('.add-reminder-btn').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.dataset.groupId;
            document.getElementById('add-group-id').value = groupId;
            openModal('add-reminder-modal');
        });
    });

    // Handle View Participants
    document.querySelectorAll('.view-participants-btn').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.dataset.groupId;
            fetchParticipants(groupId);
            openModal('view-participants-modal');
        });
    });

    document.querySelectorAll('.details-btn').forEach(button => {
    button.addEventListener('click', function() {
        const groupId = this.dataset.groupId;
         // Fetch reminder details based on the group ID
         fetchReminderDetails(groupId);

// Open the Reminder Details modal
openModal('details-modal');
});
});

    // Handle Edit Group
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.dataset.groupId;
            document.getElementById('edit-group-id').value = groupId;
            openModal('edit-group-modal');
        });
    });

    // Close modals on 'x' click
    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', function() {
            closeModal(this.closest('.modal'));
        });
    });

    // Fetch Participants
    function fetchParticipants(groupId) {
        fetch(`fetch_participants.php?group_id=${groupId}`)
            .then(response => response.json())
            .then(data => {
                const participantsList = document.getElementById('participants-list');
                participantsList.innerHTML = '';
                data.forEach(participant => {
                    const li = document.createElement('li');
                    li.textContent = participant.username;
                    participantsList.appendChild(li);
                });
            })
            .catch(error => console.error('Error fetching participants:', error));
    }
    document.addEventListener('DOMContentLoaded', () => {


    // Handle Add Reminder Form Submission
    document.getElementById('add-reminder-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent normal form submission

    // Get form values
    const groupId = document.getElementById('add-group-id').value;
    const title = document.getElementById('reminder-title').value;
    const reminderDate = document.getElementById('reminder-date').value;
    const reminderTime = document.getElementById('reminder-time').value;
    const description = document.getElementById('reminder-description').value;

    // Submit form data using fetch
    fetch('add_group_reminder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'group-id': groupId,         // This must match your form input names
            'reminder-title': title,      // Name in form = Name in body
            'reminder-date': reminderDate,
            'reminder-time': reminderTime,
            'reminder-description': description,
        }),
    })
    .then(response => response.json()) // Expecting a JSON response
    .then(data => {
        if (data.success) {
            console.log('Reminder added successfully');
            // Optionally, update the UI or handle success here
        } else {
            console.error('Error adding reminder:', data.message);
            // Optionally, show an error message
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Optionally, show an error message
    });
});
// Reminder Details
// Function to fetch reminder details
function fetchReminderDetails(groupId) {
    fetch(`get_group_details.php?group_id=${groupId}`)
        .then(response => response.json())
        .then(data => {
            // Populate the modal fields with the fetched data
            document.getElementById('reminder-title').value = data.title;
            document.getElementById('reminder-date').value = data.reminder_date;
            document.getElementById('reminder-time').value = data.reminder_time;
            document.getElementById('reminder-description').value = data.description;
            // Set the hidden group ID field
            document.getElementById('add-group-id').value = groupId;
        })
        .catch(error => {
            console.error('Error fetching reminder details:', error);
        });
}

    // Function to show a popup message
    function showPopupMessage(message) {
        const popup = document.createElement('div');
        popup.className = 'popup-message';
        popup.innerText = message;
        document.body.appendChild(popup);

        setTimeout(() => {
            popup.remove();  // Remove popup after 2 seconds
        }, 2000);
    }
});

// Delete Group Function
function deleteGroup(groupId) {
    fetch('delete_group.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ group_id: groupId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Group deleted successfully!');
            location.reload();  // Refresh the page to reflect changes
        } else {
            alert('Error deleting group: ' + data.message);
        }
    })
    .catch(error => console.error('Error deleting group:', error));
}

// Handle Delete Group Button Clicks
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const groupId = this.dataset.groupId;
        if (confirm('Are you sure you want to delete this group?')) {
            deleteGroup(groupId);
        }
    });
});

    // Send Desktop Notification
    function sendDesktopNotification(groupId, description) {
        fetch('notify_participants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                group_id: groupId,
                description: description,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error sending notifications: ' + data.message);
            }
        })
        .catch(error => console.error('Error sending notifications:', error));
    }
});

document.getElementById('edit-group-form').addEventListener('submit', function(event) {
    event.preventDefault();

    // Get form values
    var groupId = document.getElementById('edit-group-id').value;
    var groupName = document.getElementById('edit-group-name').value;

    // Create form data
    var formData = new FormData();
    formData.append('group_id', groupId);
    formData.append('group_name', groupName);

    // Send form data via AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_group.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Group updated successfully!');
            // Optionally refresh the page or close the modal
        } else {
            alert('Error updating group.');
        }
    };
    xhr.send(formData);
});

    </script>
</body>
</html>