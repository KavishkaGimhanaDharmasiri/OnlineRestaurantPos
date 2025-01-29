<?php
// Include the database connection
include 'connection.php';

// Check if ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Delete the product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id);

    if ($stmt->execute()) {
        header("Location: products.php?message=Product+deleted+successfully");
    } else {
        header("Location: products.php?message=Error+deleting+product");
    }
} else {
    header("Location: products.php?message=Product+ID+not+provided");
}
?>