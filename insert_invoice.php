<?php
include 'connection.php'; // Include the database connection

// Get the JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO invoice (Items, QTY, Amount, Date) VALUES (?, ?, ?, NOW())");

// Insert each item into the invoice
foreach ($data['items'] as $item) {
    $itemName = $item['name'];
    $itemPrice = $item['price'];
    $itemQty = 1; // Assuming quantity is 1 for each item

    // Bind parameters
    $stmt->bind_param("sid", $itemName, $itemQty, $itemPrice);
    $stmt->execute();
}

// Close statement and connection
$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "message" => "Invoice recorded successfully."]);
