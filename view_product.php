<?php
// Include the database connection
include 'connection.php';

// Fetch all products
$query = "SELECT * FROM products";
$result = $conn->query($query);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Fetch all rows as an associative array
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .table-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="table-container">
            <h2 class="text-center mb-4">Product List</h2>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0) : ?>
                        <?php foreach ($products as $product) : ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo ucfirst($product['type']); ?></td>
                                <td>
                                    <?php if ($product['image_path'] && file_exists($product['image_path'])) : ?>
                                        <img src="<?php echo $product['image_path']; ?>" alt="Product Image" class="product-image">
                                    <?php else : ?>
                                        No image
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <!-- Edit Button -->
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <!-- Delete Button (Optional) -->
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
