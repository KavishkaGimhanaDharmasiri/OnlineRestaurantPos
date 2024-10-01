<?php
include 'connection.php'; // Include the database connection

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Prepare and bind the statement
$stmt = $conn->prepare("INSERT INTO invoice (BillNo, Items, QTY, Amount, Date) VALUES (?, ?, ?, ?, NOW())");

$billNo = $data['billNo']; // Get bill number
$items = $data['items']; // Get items array
$totalAmount = $data['totalAmount']; // Get total amount

foreach ($items as $item) {
    $itemName = $item['name'];
    $itemPrice = $item['price'];
    $itemQty = 1; // Assuming quantity is 1 for now

    // Bind and execute the statement
    $stmt->bind_param("ssid", $billNo, $itemName, $itemQty, $itemPrice);
    $stmt->execute();
}

// Close statement and connection
$stmt->close();
$conn->close();

echo "Invoice processed successfully!";
?>
