<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oren Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom Oren-inspired color palette */
        body {
            background-color: #F4E4D0; /* Light sandy background */
        }
        .sidebar-bg {
            background-color: #8B4513; /* Saddle Brown for sidebar */
            color: #FFF8DC; /* Cornsilk for text */
        }
        .card-bg {
            background-color: #D2B48C; /* Tan color for cards */
            box-shadow: 0 4px 6px rgba(139, 69, 19, 0.1);
        }
        .btn-oren {
            background-color: #A0522D; /* Sienna for buttons */
            color: #FFFAF0; /* Floral white for text */
        }
        .btn-oren:hover {
            background-color: #6B3E23; /* Darker sienna on hover */
        }
    </style>
</head>
<body class="flex">
    <!-- Sidebar -->
    <aside class="sidebar-bg w-64 h-screen hidden md:block shadow-xl">
        <div class="p-4">
            <h1 class="text-2xl font-bold text-center text-white">Oren Dashboard</h1>
            <nav class="mt-6">
                <ul>
                    <li>
                        <a href="insert.php" class="block py-2 px-4 hover:bg-brown-700 text-white">Insert Foods/Beverage</a>
                    </li>
                    <li>
                        <a href="f.php" class="block py-2 px-4 hover:bg-brown-700 text-white">Bill</a>
                    </li>
                    <li>
                        <a href="summery.php" class="block py-2 px-4 hover:bg-brown-700 text-white">Summary</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-4 hover:bg-brown-700 text-white">Open Float</a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold text-brown-800">Welcome to the Oren Dashboard</h2>
            <button id="sidebarToggle" class="md:hidden p-2 btn-oren text-white rounded-md">Toggle Menu</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Cards for each section -->
            <div class="card-bg rounded-lg p-6 transform transition duration-300 hover:scale-105">
                <h3 class="text-xl font-bold text-brown-900">Insert Foods/Beverage</h3>
                <p class="mt-2 text-brown-700">Add new food or beverage items to the inventory.</p>
                <a href="insert.php" class="mt-4 inline-block btn-oren text-white py-2 px-4 rounded-md hover:shadow-lg">Go</a>
            </div>
            <div class="card-bg rounded-lg p-6 transform transition duration-300 hover:scale-105">
                <h3 class="text-xl font-bold text-brown-900">Bill</h3>
                <p class="mt-2 text-brown-700">Create and manage customer bills.</p>
                <a href="f.php" class="mt-4 inline-block btn-oren text-white py-2 px-4 rounded-md hover:shadow-lg">Go</a>
            </div>
            <div class="card-bg rounded-lg p-6 transform transition duration-300 hover:scale-105">
                <h3 class="text-xl font-bold text-brown-900">Summary</h3>
                <p class="mt-2 text-brown-700">View sales and transaction summaries.</p>
                <a href="NewSummery.php" class="mt-4 inline-block btn-oren text-white py-2 px-4 rounded-md hover:shadow-lg">Go</a>
            </div>
            <div class="card-bg rounded-lg p-6 transform transition duration-300 hover:scale-105">
                <h3 class="text-xl font-bold text-brown-900">Open Float</h3>
                <p class="mt-2 text-brown-700">Manage the cash float for transactions.</p>
                <a href="#" class="mt-4 inline-block btn-oren text-white py-2 px-4 rounded-md hover:shadow-lg">Open</a>
            </div>
        </div>
    </main>

    <script>
        // Toggle sidebar for mobile
        document.getElementById('sidebarToggle').addEventListener('click', () => {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
        });
    </script>
</body>
</html>