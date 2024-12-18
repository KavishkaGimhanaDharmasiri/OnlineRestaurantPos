<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3B82F6;
            --secondary-color: #10B981;
        }
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Modern Sidebar -->
        <aside class="w-64 bg-white shadow-xl relative">
            <div class="p-6 gradient-bg text-white">
                <div class="flex items-center">
                    <img src="logo.jpg" alt="Logo" class="w-12 h-12 rounded-full mr-3">
                    <h1 class="text-2xl font-bold">Dashboard</h1>
                </div>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="insert.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-plus-circle mr-3"></i>
                            Insert Foods/Beverage
                        </a>
                    </li>
                    <li>
                        <a href="f.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-receipt mr-3"></i>
                            Bill
                        </a>
                    </li>
                    <li>
                        <a href="summery.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-chart-pie mr-3"></i>
                            Summary
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-cash-register mr-3"></i>
                            Open Float
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>




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
