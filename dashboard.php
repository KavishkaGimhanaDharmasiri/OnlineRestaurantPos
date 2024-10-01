<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles can go here */
    </style>
</head>
<body class="bg-gray-100 flex">
    <!-- Sidebar -->
    <aside class="bg-white shadow-lg w-64 h-screen hidden md:block">
        <div class="p-4">
            <h1 class="text-2xl font-bold text-center">Dashboard</h1>
            <nav class="mt-6">
                <ul>
                    <li>
                        <a href="insert.php" class="block py-2 px-4 hover:bg-gray-200">Insert Foods/Beverage</a>
                    </li>
                    <li>
                        <a href="f.php" class="block py-2 px-4 hover:bg-gray-200">Bill</a>
                    </li>
                    <li>
                        <a href="summery.php" class="block py-2 px-4 hover:bg-gray-200">Summary</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-4 hover:bg-gray-200">Open Float</a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold">Welcome to the Dashboard</h2>
            <button id="sidebarToggle" class="md:hidden p-2 bg-blue-500 text-white rounded-md">Toggle Menu</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Cards for each section -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold">Insert Foods/Beverage</h3>
                <p class="mt-2 text-gray-600">Add new food or beverage items to the inventory.</p>
                <a href="insert.php" class="mt-4 inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Go</a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold">Bill</h3>
                <p class="mt-2 text-gray-600">Create and manage customer bills.</p>
                <a href="f.php" class="mt-4 inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Go</a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold">Summary</h3>
                <p class="mt-2 text-gray-600">View sales and transaction summaries.</p>
                <a href="summery.php" class="mt-4 inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Go</a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold">Open Float</h3>
                <p class="mt-2 text-gray-600">Manage the cash float for transactions.</p>
                <a href="#" class="mt-4 inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Open</a>
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
