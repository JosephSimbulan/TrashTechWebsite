<?php
include 'header.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connection.php'; // Ensure the database connection is included


// Ensure session_start() is only called once
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Retrieve company_name based on the username
$query = "SELECT company_name FROM users WHERE username = ? LIMIT 1"; // Use ? for mysqli
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();

// Check if user information is retrieved successfully
if ($user_info) {
    $_SESSION['company_name'] = $user_info['company_name']; // Store company_name in session
} else {
    echo "User not found.";
    exit();
}

// Function to fetch users based on company_name
function fetchUsers($conn, $company_name) {
    $query = "SELECT * FROM users WHERE company_name = ?"; // Use ? for mysqli
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $company_name);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Initialize error messages array
$errors = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $userIds = $_POST['id'] ?? []; // Get array of user IDs
        $index = array_search($_POST['user_id'], $userIds); // Get the index of the user being updated

        if ($index !== false) {
            // Get the data for the user being updated
            $username = $_POST['username'][$index];
            $email = $_POST['email'][$index];
            $company_name = $_POST['company_name'][$index];
            $company_address = $_POST['company_address'][$index];
            $role = $_POST['role'][$index];

            // Basic validation
            if (empty($username)) {
                $errors['username'][$index] = "Username is required.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'][$index] = "Invalid email format.";
            }
            if ($action === 'create' || $action === 'update') {
                if ($role != 'admin' && (strlen($_POST['password'][$index]) < 8 || strlen($_POST['password'][$index]) > 16 || 
                    !preg_match('/[A-Z]/', $_POST['password'][$index]) || 
                    !preg_match('/[a-z]/', $_POST['password'][$index]) || 
                    !preg_match('/[0-9]/', $_POST['password'][$index]))) {
                    $errors['password'][$index] = "Password does not meet the requirements.";
                }
            }

            // Only proceed if there are no errors
            if (empty($errors)) {
                if ($action === 'create') {
                    $password = ($role == 'admin') ? 'admin' : $_POST['password'][$index];
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user into the database
                    $sql = "INSERT INTO users (username, email, password, company_name, company_address, role) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $company_name, $company_address, $role);
                    $stmt->execute();

                    echo "New user created successfully";
                } elseif ($action === 'update') {
                    $hashed_password = ($role == 'admin') ? password_hash('admin', PASSWORD_DEFAULT) : null;

                    $sql = "UPDATE users SET username=?, email=?, company_name=?, company_address=?, role=?" . 
                            ($hashed_password ? ", password=?" : "") . " WHERE id=?";
                    
                    $stmt = $conn->prepare($sql);
                    
                    if ($hashed_password) {
                        $stmt->bind_param("ssssssi", $username, $email, $company_name, $company_address, $role, $hashed_password, $userIds[$index]);
                    } else {
                        $stmt->bind_param("sssss", $username, $email, $company_name, $company_address, $role, $userIds[$index]);
                    }
                    
                    $stmt->execute();
                    echo "User updated successfully";
                } elseif ($action === 'delete') {
                    $sql = "DELETE FROM users WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $userIds[$index]);
                    $stmt->execute();

                    echo "User deleted successfully";
                }
            }
        }
    }

    // Add new user
    if (isset($_POST['create_user'])) {
        $new_username = $_POST['new_username'];
        $new_email = $_POST['new_email'];
        $new_password = $_POST['new_password'];
        $new_role = $_POST['new_role'];

        // Validate new user data
        if (empty($new_username)) {
            $errors['new_username'] = "Username is required.";
        }
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors['new_email'] = "Invalid email format.";
        }
        if (strlen($new_password) < 8 || strlen($new_password) > 16 || 
            !preg_match('/[A-Z]/', $new_password) || 
            !preg_match('/[a-z]/', $new_password) || 
            !preg_match('/[0-9]/', $new_password)) {
            $errors['new_password'] = "Password must be between 8-16 characters and include an uppercase letter, a lowercase letter, and a number.";
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Insert new user into the database
            $sql = "INSERT INTO users (username, email, password, company_name, role) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $new_username, $new_email, $hashed_password, $_SESSION['company_name'], $new_role);
            $stmt->execute();
            echo "New user created successfully.";
        }
    }
}

// Fetch users for the logged-in admin's company
$users = fetchUsers($conn, $_SESSION['company_name']);

$page_title = "Manage Users";
$page_content = '
    <h1>Manage Users</h1>
    <div class="form-container">
        <form method="POST" action="manage_users.php">
            <h2>Create New User</h2>
            <label for="new_username">Username</label>
            <input type="text" name="new_username" value="" placeholder="Enter username">
            <div class="error">' . ($errors['new_username'] ?? '') . '</div>
            
            <label for="new_email">Email</label>
            <input type="email" name="new_email" value="" placeholder="Enter email">
            <div class="error">' . ($errors['new_email'] ?? '') . '</div>
            
            <label for="new_password">Password</label>
            <input type="password" name="new_password" value="" placeholder="Enter password">
            <div class="error">' . ($errors['new_password'] ?? '') . '</div>
            
            <label for="new_role">Role</label>
            <select name="new_role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            
            <button type="submit" name="create_user">Create User</button>
        </form>
    </div>';

$page_content .= '
    <h2>Existing Users</h2>
    <form id="userForm" method="POST" action="manage_users.php">
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Company Name</th>
                <th>Company Address</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>';

foreach ($users as $index => $row) {
    $page_content .= '
        <tr>
            <td>
                <input type="text" name="username[]" value="' . htmlspecialchars($row['username']) . '" class="input-field">
                <div class="error">' . ($errors['username'][$index] ?? '') . '</div>
            </td>
            <td>
                <input type="email" name="email[]" value="' . htmlspecialchars($row['email']) . '" class="input-field">
                <div class="error">' . ($errors['email'][$index] ?? '') . '</div>
            </td>
            <td>
                <input type="text" name="company_name[]" value="' . htmlspecialchars($row['company_name']) . '" class="input-field">
            </td>
            <td>
                <input type="text" name="company_address[]" value="' . htmlspecialchars($row['company_address']) . '" class="input-field">
            </td>
            <td>
                <select name="role[]">
                    <option value="user"' . ($row['role'] == 'user' ? ' selected' : '') . '>User</option>
                    <option value="admin"' . ($row['role'] == 'admin' ? ' selected' : '') . '>Admin</option>
                </select>
            </td>
            <td>
                <input type="hidden" name="id[]" value="' . $row['id'] . '">
                <button type="submit" name="action" value="update">Update</button>
                <button type="submit" name="action" value="delete">Delete</button>
            </td>
        </tr>';
}

$page_content .= '</table></form>';

include 'template.php'; // Include the general template that includes header, sidebar, and other components
?>
