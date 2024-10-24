<?php
// faq.php
include 'db_connection.php';
include 'header.php';  // Include the header.php to maintain the header layout
include 'sidebar.php'; // Include sidebar.php to ensure the sidebar is rendered
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM faqs";
$result = $conn->query($sql);

$page_title = "FAQ Page";

$page_content = '
    <h1>FAQs</h1>
    <div class="faq-container">';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $page_content .= '
            <div class="faq-item">
                <div class="faq-question">
                    <strong>' . $row['question'] . '</strong>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">' . $row['answer'] . '</div>
            </div>';
    }
} else {
    $page_content .= '<p>No FAQs found.</p>';
}

$page_content .= '
    </div>';

?>

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
        margin-left: 20px; /* Align h1 beside the sidebar */
    }
    .faq-container {
        margin-top: 20px;
    }
    .faq-item {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .faq-question {
        display: flex;
        justify-content: space-between;
        cursor: pointer;
        font-size: 18px;
    }
    .faq-answer {
        margin-top: 10px;
        display: none;
    }
</style>

<!-- Link the external JavaScript file -->
<script src="faq.js"></script>
