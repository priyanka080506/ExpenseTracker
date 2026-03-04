<?php
// 1. Connect to database
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Get data from form
$user_id = 1; // default user
$category_id = $_POST['category_id'];
$payment_id = $_POST['payment_id'];
$amount = $_POST['amount'];
$date = $_POST['date'];

// 3. Insert into expenses table
$sql = "INSERT INTO expenses (user_id, category_id, payment_id, amount, date)
        VALUES ($user_id, $category_id, $payment_id, $amount, '$date')";

if ($conn->query($sql) === TRUE) {
    // 4. Redirect back to main page
    header("Location: index.php");
    exit;
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
