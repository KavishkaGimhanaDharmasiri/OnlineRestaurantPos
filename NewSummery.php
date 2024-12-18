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

// Prepare product-wise data for chart
$productLabels = [];
$productAmounts = [];
while ($row = $productWiseResult->fetch_assoc()) {
    $productLabels[] = $row['Items'];
    $productAmounts[] = $row['totalAmount'];
}
$stmt->close();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <!-- Sidebar -->
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
                        <a href="#" class="flex items-center p-3 text-gray-700 bg-blue-50 rounded-lg">
                            <i class="fas fa-chart-pie mr-3"></i>
                            Summary
                        </a>
                    </li>
                    <li>
                        <a href="insert_open_float.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors">
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
                    <h2 class="text-4xl font-bold text-gray-800">Daily Summary</h2>
                    <p class="text-gray-500">Sales Overview for <?php echo htmlspecialchars($currentDate); ?></p>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Total Sales Card -->
                <div class="bg-white shadow-lg rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Total Sales</h3>
                        <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-blue-600">
                        Rs. <?php echo htmlspecialchars(number_format($totalAmount, 2)); ?>
                    </div>
                    <p class="text-gray-500 mt-2">Total sales for today</p>
                </div>

                <!-- Product Breakdown Card -->
                <div class="bg-white shadow-lg rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Product Breakdown</h3>
                        <i class="fas fa-chart-bar text-purple-500 text-2xl"></i>
                    </div>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left">Product</th>
                                <th class="py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $productWiseResult->data_seek(0); // Reset result pointer
                            while ($row = $productWiseResult->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td class="py-2">' . htmlspecialchars($row['Items']) . '</td>';
                                echo '<td class="py-2 text-right text-blue-600">Rs. ' . htmlspecialchars(number_format($row['totalAmount'], 2)) . '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sales Chart -->
                <div class="bg-white shadow-lg rounded-xl p-6 col-span-full">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Product Sales Distribution</h3>
                        <i class="fas fa-chart-pie text-blue-500 text-2xl"></i>
                    </div>
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($productLabels); ?>,
            datasets: [{
                label: 'Product Sales',
                data: <?php echo json_encode($productAmounts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Product Sales Distribution'
                }
            }
        }
    });
</script>