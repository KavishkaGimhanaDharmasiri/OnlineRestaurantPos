<?php
// Include the database connection
include 'connection.php';

// Check if product ID is passed in URL
if (isset($_GET['id'])) {
    $product_id = (int) $_GET['id'];

    // Fetch the product details
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
}

// Handle form submission to update the product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $product_price = (float) $_POST['product_price'];
    $product_type = $conn->real_escape_string($_POST['product_type']);
    $image_path = $product['image_path']; // Keep the current image if no new image is uploaded

    // Handle image upload if a new image is provided
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $file_name = basename($_FILES['product_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Set allowed file extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_extensions)) {
            // Define the upload directory
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique file name
            $unique_file_name = uniqid() . '.' . $file_ext;
            $new_image_path = $upload_dir . $unique_file_name;

            // Move the uploaded file
            if (move_uploaded_file($file_tmp, $new_image_path)) {
                $image_path = $new_image_path; // Update image path
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    // Update product in the database
    $update_query = "UPDATE products SET name = '$product_name', price = $product_price, type = '$product_type', image_path = '$image_path' WHERE id = $product_id";

    if ($conn->query($update_query) === TRUE) {
        // Redirect to the product list page
        header("Location: view_product.php");
        exit();
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>
        <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $product['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="product_price">Product Price</label>
                <input type="number" class="form-control" id="product_price" name="product_price" value="<?php echo $product['price']; ?>" required step="0.01">
            </div>
            <div class="form-group">
                <label for="product_type">Product Type</label>
                <select class="form-control" id="product_type" name="product_type" required>
                    <option value="food" <?php echo ($product['type'] == 'food') ? 'selected' : ''; ?>>Food</option>
                    <option value="beverage" <?php echo ($product['type'] == 'beverage') ? 'selected' : ''; ?>>Beverage</option>
                </select>
            </div>
            <div class="form-group">
                <label for="product_image">Product Image</label>
                <input type="file" class="form-control-file" id="product_image" name="product_image">
                <?php if ($product['image_path']) : ?>
                    <p>Current image: <img src="<?php echo $product['image_path']; ?>" alt="Product Image" class="product-image"></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
