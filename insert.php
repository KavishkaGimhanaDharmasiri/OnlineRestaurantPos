<?php
// Database configuration
include 'connection.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $product_price = (float) $_POST['product_price'];
    $product_type = $conn->real_escape_string($_POST['product_type']);

    // Prepare SQL statement
    $sql = "INSERT INTO products (name, price, type) VALUES ('$product_name', '$product_price', '$product_type')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>New product added successfully!</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
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
        input[type="number"] {
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
    <form action="" method="post">
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
        
        <input type="submit" value="Add Product">
    </form>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
