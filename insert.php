<?php
// Database configuration
include 'connection.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $product_price = (float) $_POST['product_price'];
    $product_type = $conn->real_escape_string($_POST['product_type']);
    
    // Handle file upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $file_name = basename($_FILES['product_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Set allowed file extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Validate file type
        if (in_array($file_ext, $allowed_extensions)) {
            // Define file upload directory
            $upload_dir = 'uploads/';
            // Create the upload directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Create a unique file name
            $unique_file_name = uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $unique_file_name;

            // Move the uploaded file to the server
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Prepare SQL statement to insert product details and image path
                $sql = "INSERT INTO products (name, price, type, image_path) VALUES ('$product_name', '$product_price', '$product_type', '$file_path')";
                
                if ($conn->query($sql) === TRUE) {
                    // Redirect to button.php after successful insertion
                    header("Location: button.php");
                    exit(); // Ensure no further code is executed after redirection
                } else {
                    echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
                }
            } else {
                echo "<p>Failed to upload image.</p>";
            }
        } else {
            echo "<p>Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.</p>";
        }
    } else {
        echo "<p>No image uploaded or there was an error with the file upload.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="radio"] {
            margin-right: 5px;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .radio-group {
            margin-bottom: 15px;
        }
        .radio-option {
            margin-bottom: 5px;
        }
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <h1>Add Product</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" required>
        
        <label for="product_price">Product Price:</label>
        <input type="number" id="product_price" name="product_price" required step="0.01">
        
        <label>Type:</label>
        <div class="radio-group">
            <div class="radio-option">
                <input type="radio" id="food" name="product_type" value="food" required>
                <label for="food">Food</label>
            </div>
            <div class="radio-option">
                <input type="radio" id="beverage" name="product_type" value="beverage" required>
                <label for="beverage">Beverage</label>
            </div>
        </div>
        
        <label for="product_image">Product Image:</label>
        <input type="file" id="product_image" name="product_image" required>
        
        <input type="submit" value="Add Product">
    </form>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
