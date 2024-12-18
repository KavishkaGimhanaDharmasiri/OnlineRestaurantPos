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

        <!-- Main Content -->
        <main class="flex-1 p-8 bg-gray-100">
            <header class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800">Welcome, Admin</h2>
                    <p class="text-gray-500">Dashboard Overview</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-bell mr-2"></i>Notifications
                    </button>
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <img src="profile.jpg" alt="Profile" class="w-full h-full object-cover">
                    </div>
                </div>
            </header>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                    <div class="flex justify-between items-center mb-4">
                        <i class="fas fa-utensils text-blue-500 text-3xl"></i>
                        <span class="text-green-500 font-bold">+12% ↑</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Food Items</h3>
                    <p class="text-gray-500">Total 50 Items</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                    <div class="flex justify-between items-center mb-4">
                        <i class="fas fa-coffee text-green-500 text-3xl"></i>
                        <span class="text-red-500 font-bold">-5% ↓</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Beverage Sales</h3>
                    <p class="text-gray-500">Total 75 Items</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                    <div class="flex justify-between items-center mb-4">
                        <i class="fas fa-dollar-sign text-purple-500 text-3xl"></i>
                        <span class="text-green-500 font-bold">+8% ↑</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Total Revenue</h3>
                    <p class="text-gray-500">$15,240</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg card-hover">
                    <div class="flex justify-between items-center mb-4">
                        <i class="fas fa-users text-indigo-500 text-3xl"></i>
                        <span class="text-blue-500 font-bold">Active</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Customers</h3>
                    <p class="text-gray-500">120 This Month</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Optional: Add any interactive JavaScript here
    </script>
</body>
</html>