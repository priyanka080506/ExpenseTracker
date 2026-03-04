 <?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "expense_tracker");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get id from URL
$id = $_GET['id'];

// Delete record
$sql = "DELETE FROM expenses WHERE id = $id";

$conn->query($sql);
$conn->close();

// Go back to main page
header("Location: index.php");
exit;
?>

