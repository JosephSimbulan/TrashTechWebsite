<?php
// bin_levels.php
// Access this page at: http://localhost/TrashTech2/bin_levels.php
//file ni Jed to
include 'db_connection.php';
include 'header.php';

// Check if the session is already started, if not, start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Check if user ID is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "User ID is not set in the session.";
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM bin_levels WHERE user_id='$user_id'";
$result = $conn->query($sql);

// Fetch the most recent distance for the user
$sql_distance = "SELECT distance FROM bin_levels WHERE user_id='$user_id' ORDER BY timestamp DESC LIMIT 1";
$result_distance = $conn->query($sql_distance);
$current_distance = 0;
$current_color = '';

// Determine the color based on the distance
if ($row_distance = $result_distance->fetch_assoc()) {
    $current_distance = $row_distance['distance'];

    // Color determination logic based on Arduino code
    if ($current_distance >= 20) {
        $current_color = 'green';
    } elseif ($current_distance >= 14) {
        $current_color = 'yellow';
    } elseif ($current_distance >= 6) {
        $current_color = 'orange';
    } else {
        $current_color = 'red';
    }
}

$page_title = "Bin Level Page";

// Start output buffering to capture table content
ob_start();
?>
<!-- Gradient Background and Title Placement -->
<h1>Bin Levels</h1>

<!-- Trash Bin Animation - 4 Designs -->
<div class="trash-bin-container">
    <div class="container trash-style-1">
        <div class="trashLid"></div>
        <div class="trash" id="trash1">
            <div class="strap"></div>
            <div class="strap"></div>
        </div>
        <label for="trash1">Paper (Bin 1)</label> <!-- Label for Trash Bin 1 -->
    </div>

    <div class="container trash-style-2">
        <div class="trashLid"></div>
        <div class="trash" id="trash2">
            <div class="strap"></div>
            <div class="strap"></div>
        </div>
        <label for="trash2">Plastic (Bin 2)</label> <!-- Label for Trash Bin 2 -->
    </div>

    <div class="container trash-style-3">
        <div class="trashLid"></div>
        <div class="trash" id="trash3">
            <div class="strap"></div>
            <div class="strap"></div>
        </div>
        <label for="trash3">Metal (Bin 3)</label> <!-- Label for Trash Bin 3 -->
    </div>

    <div class="container trash-style-4">
        <div class="trashLid"></div>
        <div class="trash" id="trash4">
            <div class="strap"></div>
            <div class="strap"></div>
        </div>
        <label for="trash4">Glass (Bin 4)</label> <!-- Label for Trash Bin 4 -->
    </div>
</div>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding-top: 60px; /* Adjust this value to match the header height */
        display: flex;
    }

    #content {
        margin-left: 280px; /* Shift content more to the right */
        padding: 20px;
        width: calc(100% - 280px); /* Adjust content width */
        background: linear-gradient(135deg, #D187F5, #FFFFFF); /* Gradient background */
    }

    h1 {
        margin-top: 0;
    }

    /* Flexbox layout for trash bins */
    .trash-bin-container {
        display: flex; /* Use flexbox */
        justify-content: space-around; /* Space bins evenly */
        align-items: flex-start; /* Align bins to the top */
        margin-top: 20px;
        flex-wrap: wrap; /* Allow wrapping if the screen is too small */
    }

    .container {
        padding: 80px; /* Increase padding for larger bins */
        margin: 10px;
        width: 120px; /* Increase width for the bins */
        background-color: transparent; /* Set background to transparent */
        border: none; /* Remove border */
    }

    label {
        position: relative;
        overflow: hidden;
        text-align: center; /* Center the text */
        margin-top: 10px; /* Add margin for spacing */
    }

    /* Shared trash bin styles */
    .trash {
        width: 90px; /* Increase width for the trash */
        height: 130px; /* Increase height for the trash */
        border: 3px solid black;
        border-radius: 8px;
        position: relative;
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        background: transparent; /* Set background to transparent */
        cursor: pointer;
        transition: background-color 0.5s; /* Add transition for color change */
    }

    .trash .strap {
        width: 15px; /* Increase strap width */
        height: 95%; /* Increase strap height */
        background-color: black; /* Strap color */
        border-radius: 50px;
        position: relative;
        overflow: hidden;
    }

    .trash .strap::after {
        content: "";
        display: block;
        position: absolute;
        bottom: 0;
        height: 0%;
        background-color: rgb(77, 217, 77);
        width: 100%;
    }

    .trashLid {
        width: 90px; /* Increase lid width */
        height: 8px; /* Increase lid height */
        background-color: black;
        display: block;
        margin-bottom: 2px;
        border-radius: 50px;
        position: relative;
        transition: 0.5s cubic-bezier(0.05, 0.61, 0.41, 0.95);
        cursor: pointer;
    }
</style>

<script>
    function updateTrashBinColor(color) {
        const trashBins = document.querySelectorAll('.trash');
        trashBins.forEach((trash) => {
            trash.style.backgroundColor = color; // Update based on color passed
        });
    }

    // Use the current color fetched from PHP
    updateTrashBinColor('<?php echo $current_color; ?>');
</script>

<?php
// Get the content from the buffer and store it
$page_content = ob_get_clean();

// Include template.php which will use $page_title and $page_content
include 'template.php';
?>