<?php
// Include the database connection file
include 'connection.php';

// Set the default timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Get the current date (without the time part)
$current_date = date('Y-m-d');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the price from the POST data
    $price = $_POST['price'];

    // Validate price (ensure it's a valid float)
    if (is_numeric($price)) {
        // First, check if an entry already exists for today's date
        $checkQuery = "SELECT id FROM open_floats WHERE DATE(date) = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $current_date);
        $stmt->execute();
        $stmt->store_result();

        // If a record exists for today, prevent the insertion
        if ($stmt->num_rows > 0) {
            $error_message = "Entry for today already exists. Only one entry per day is allowed.";
        } else {
            // Otherwise, insert the new record
            $stmt = $conn->prepare("INSERT INTO open_floats (price) VALUES (?)");
            $stmt->bind_param("d", $price);

            if ($stmt->execute()) {
                $success_message = "New record created successfully for " . $current_date . ".";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }

        // Close the statement
        $stmt->close();
    } else {
        $error_message = "Please enter a valid price.";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Float - Dashboard</title>
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
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar (same as previous dashboard) -->
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
                        <a href="NewSummery.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-chart-pie mr-3"></i>
                            Summary
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-3 text-gray-700 bg-blue-50 rounded-lg">
                            <i class="fas fa-cash-register mr-3"></i>
                            Open Float
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 bg-gray-100">
            <header class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800">Open Float</h2>
                    <p class="text-gray-500">Manage Daily Cash Float</p>
                </div>
            </header>

            <!-- Open Float Form -->
            <div class="max-w-md mx-auto bg-white shadow-lg rounded-xl p-8">
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>

                <form action="insert_open_float.php" method="post" class="space-y-4">
                    <div>
                        <label for="price" class="block text-gray-700 font-bold mb-2">
                            <i class="fas fa-dollar-sign mr-2 text-blue-500"></i>
                            Open Float Amount
                        </label>
                        <input 
                            type="number" 
                            name="price" 
                            id="price" 
                            step="0.01" 
                            min="0" 
                            required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter opening float amount"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-green-500"></i>
                            Date
                        </label>
                        <input 
                            type="text" 
                            value="<?php echo $current_date; ?>" 
                            disabled 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-300 flex items-center justify-center"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Submit Open Float
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>