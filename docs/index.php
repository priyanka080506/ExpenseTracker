<?php
// =====================
// DATABASE CONNECTION
// =====================
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =====================
// MONTHLY TOTAL
// =====================
$currentMonth = date('m');
$currentYear  = date('Y');

$totalQuery = $conn->query("
    SELECT SUM(amount) AS total
    FROM expenses
    WHERE MONTH(date) = $currentMonth
      AND YEAR(date) = $currentYear
");

$totalRow    = $totalQuery->fetch_assoc();
$totalAmount = $totalRow['total'] ?? 0;

// =====================
// MONTHLY GRAPH (YEAR + MONTH)
// =====================
$chartQuery = $conn->query("
    SELECT 
        YEAR(date) AS year,
        MONTH(date) AS month,
        SUM(amount) AS total
    FROM expenses
    GROUP BY YEAR(date), MONTH(date)
    ORDER BY YEAR(date), MONTH(date)
");

$months = [];
$totals = [];

while ($row = $chartQuery->fetch_assoc()) {
    $months[] = date("M Y", mktime(0, 0, 0, $row['month'], 1, $row['year']));
    $totals[] = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Expense Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Student Expense Tracker</h1>

    <!-- ADD EXPENSE -->
    <div class="card">
        <h2>Add Expense</h2>
        <form method="POST" action="add_expense.php">

            <label>Category</label>
            <select name="category_id" required>
                <option value="1">Food</option>
                <option value="2">Travel</option>
                <option value="3">Shopping</option>
                <option value="4">College Fees</option>
                <option value="5">Entertainment</option>
                <option value="6">Other</option>
            </select>

            <label>Payment Method</label>
            <select name="payment_id" required>
                <option value="1">Cash</option>
                <option value="2">UPI</option>
                <option value="3">Debit Card</option>
                <option value="4">Credit Card</option>
                <option value="5">Wallet</option>
            </select>

            <label>Amount (₹)</label>
            <input type="number" name="amount" step="0.01" required>

            <label>Date</label>
            <input type="date" name="date" required>

            <button type="submit">Add Expense</button>
        </form>
    </div>

    <!-- MONTHLY SUMMARY -->
    <div class="card">
        <h2>Monthly Summary</h2>
        <p>Total Expense This Month:</p>
        <h3>₹ <?php echo number_format($totalAmount, 2); ?></h3>
    </div>

    <!-- EXPENSE LIST -->
    <div class="card">
        <h2>Expense List</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Payment</th>
                <th>Amount (₹)</th>
                <th>Date</th>
                <th>Action</th>
            </tr>

            <?php
            $sql = "
                SELECT 
                    e.id,
                    c.category_name,
                    p.payment_name,
                    e.amount,
                    e.date
                FROM expenses e
                JOIN categories c ON e.category_id = c.category_id
                JOIN payment_methods p ON e.payment_id = p.payment_id
                ORDER BY e.id DESC
            ";

            $result = $conn->query($sql);
            $sn = 1;

            while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo $sn; ?></td>
                <td><?php echo $row['category_name']; ?></td>
                <td><?php echo $row['payment_name']; ?></td>
                <td>₹<?php echo $row['amount']; ?></td>
                <td><?php echo $row['date']; ?></td>
                <td>
                    <a class="delete-btn"
                       href="delete.php?id=<?php echo $row['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this expense?');">
                       Delete
                    </a>
                </td>
            </tr>
            <?php
                $sn++;
            }
            ?>
        </table>
    </div>

    <!-- GRAPH -->
    <div class="card">
        <h2>Monthly Expense Graph</h2>
        <canvas id="expenseChart"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('expenseChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Monthly Expense (₹)',
            data: <?php echo json_encode($totals); ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
