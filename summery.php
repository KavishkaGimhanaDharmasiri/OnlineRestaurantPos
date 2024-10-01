<?php
include 'connection.php'; // Include the database connection

// Get the current date
date_default_timezone_set('Asia/Colombo'); // Set to Colombo's time zone
$currentDate = date('Y-m-d');

// Query to get the total amount for the current date
$totalAmountQuery = "SELECT SUM(Amount) AS totalAmount FROM invoice WHERE Date = ?";
$stmt = $conn->prepare($totalAmountQuery);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$stmt->bind_result($totalAmount);
$stmt->fetch();
$stmt->close();

// Query to get the total amount product-wise for the current date
$productWiseQuery = "SELECT Items, SUM(Amount) AS totalAmount FROM invoice WHERE Date = ? GROUP BY Items";
$stmt = $conn->prepare($productWiseQuery);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$productWiseResult = $stmt->get_result();
$stmt->close();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Invoice Summary for <?php echo htmlspecialchars($currentDate); ?></h1>

    <h2>Total Amount: $<?php echo htmlspecialchars(number_format($totalAmount, 2)); ?></h2>

    <h3>Product Wise Total Amount</h3>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($productWiseResult->num_rows > 0) {
                while ($row = $productWiseResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['Items']) . '</td>';
                    echo '<td>$' . htmlspecialchars(number_format($row['totalAmount'], 2)) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="2">No data found for today.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</body>
</html>
