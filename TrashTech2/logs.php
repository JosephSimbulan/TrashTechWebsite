<?php
// logs.php
include 'db_connection.php';
include 'header.php';
include 'sidebar.php';

// Check if the session is already started, if not, start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the logged-in user's company name
$company_name = $_SESSION['company_name'];

// Fetch logs from weight tables
$sql = "(
            SELECT 'Paper' AS Material_category, timestamp, weight
            FROM paper_weight
            WHERE company_name = '$company_name'
        )
        UNION ALL
        (
            SELECT 'Plastic' AS Material_category, timestamp, weight
            FROM plastic_weight
            WHERE company_name = '$company_name'
        )
        UNION ALL
        (
            SELECT 'Metal' AS Material_category, timestamp, weight
            FROM metal_weight
            WHERE company_name = '$company_name'
        )
        UNION ALL
        (
            SELECT 'Glass' AS Material_category, timestamp, weight
            FROM glass_weight
            WHERE company_name = '$company_name'
        )
        ORDER BY timestamp DESC";

$result = $conn->query($sql);

$page_title = "Logs Page";

?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding-top: 60px; /* Adjust for header height */
        display: flex;
    }
    /* Sidebar styling */
    .sidebar {
        width: 220px; /* Sidebar width */
        background-color: #343a40;
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        padding: 20px;
        overflow-y: auto;
        z-index: 1000; /* Ensure sidebar is on top of the content */
    }
    /* Header styling */
    header {
        background-color: #f8f9fa;
        padding: 20px;
        text-align: center;
        position: fixed;
        top: 0;
        left: 220px; /* Move header to the right of sidebar */
        width: calc(100% - 220px); /* Adjust width to match sidebar */
        z-index: 999; /* Ensure header is above the content */
        box-sizing: border-box;
    }
    /* Content section (where the logs are displayed) */
    #content {
        margin-left: 280px; /* Shift content more to the right */
        padding: 20px;
        width: calc(100% - 280px); /* Adjust content width */
        background: linear-gradient(135deg, #D187F5, #FFFFFF); /* Gradient background */
        height: calc(100vh - 80px); /* Fill remaining height after header */
        overflow-y: auto; /* Allow scrolling */
        box-sizing: border-box;
    }
    h1 {
        margin-top: 0;
        margin-left: 20px; /* Align h1 beside the sidebar */
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 18px;
        text-align: left;
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #f2f2f2;
    }
    tr:hover {
        background-color: #f5f5f5;
    }
</style>

<!-- Content Section -->
<div id="content">
    <h1>Logs</h1>
    <table>
        <thead>
            <tr>
                <th>Material Category</th>
                <th>Date</th>
                <th>Weight (kg)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Format timestamp to a readable date
                    $formatted_date = date("Y-m-d H:i:s", strtotime($row['timestamp']));
                    echo "<tr>
                            <td>{$row['Material_category']}</td>
                            <td>{$formatted_date}</td>
                            <td>{$row['weight']} kg</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No logs found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
