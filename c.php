<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php
    include 'connection.php'; // Include the database connection

    // Fetch the last BillNo from the invoice table
    $lastBillNoResult = $conn->query("SELECT MAX(BillNo) AS lastBillNo FROM invoice");
    $lastBillNo = $lastBillNoResult->fetch_assoc()['lastBillNo'] + 1;

    // Fetch products from the database
    $sql = "SELECT name, price, type FROM products ORDER BY type = 'food' DESC";
    $result = $conn->query($sql);
    ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">Product List</h1>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-2/3">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6" id="productContainer">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="product-card bg-white rounded-lg shadow-md p-6 flex flex-col justify-between">';
                            echo '<h3 class="text-xl font-semibold mb-2">' . htmlspecialchars($row['name']) . '</h3>';
                            echo '<p class="text-gray-600 mb-2">Price: $' . htmlspecialchars(number_format($row['price'], 2)) . '</p>';
                            echo '<p class="text-gray-600 mb-4">Type: ' . htmlspecialchars(ucfirst($row['type'])) . '</p>';
                            echo '<button onclick="addToTable(\'' . htmlspecialchars($row['name']) . '\', ' . htmlspecialchars($row['price']) . ')" class="bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 transition duration-300 ease-in-out">Add to Invoice</button>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p class='text-center text-gray-600'>No products found.</p>";
                    }

                    // Close connection for product fetching
                    $conn->close();
                    ?>
                </div>
            </div>

            <div class="lg:w-1/3">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-semibold mb-4">Invoice</h2>
                    <div class="mb-4">
                        <span class="font-semibold">Bill No:</span>
                        <span id="billNo" class="ml-2"><?php echo $lastBillNo; ?></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full mb-4">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Product</th>
                                    <th class="text-right py-2">Price</th>
                                </tr>
                            </thead>
                            <tbody id="productTable">
                                <!-- Invoice items will be added here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-semibold">Total Amount:</span>
                        <span id="totalAmount" class="text-lg font-bold">$0.00</span>
                    </div>
                    <div class="mb-4">
                        <label for="cash" class="block mb-2">Cash Amount:</label>
                        <input type="number" id="cash" placeholder="Enter cash amount" class="w-full px-3 py-2 border rounded-md" oninput="calculateBalance()">
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <span class="font-semibold">Balance:</span>
                        <span id="balance" class="text-lg font-bold">$0.00</span>
                    </div>
                    <button onclick="processInvoice()" class="w-full bg-blue-500 text-white py-3 px-4 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">Process Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let totalAmount = 0;

        function addToTable(name, price) {
            const tableBody = document.getElementById("productTable");
            const newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td class="py-2">${name}</td>
                <td class="text-right py-2">$${parseFloat(price).toFixed(2)}</td>
            `;
            tableBody.insertBefore(newRow, tableBody.firstChild);

            totalAmount += parseFloat(price);
            document.getElementById("totalAmount").textContent = "$" + totalAmount.toFixed(2);
            calculateBalance();
        }

        function calculateBalance() {
            const cash = parseFloat(document.getElementById("cash").value) || 0;
            const balance = cash - totalAmount;
            document.getElementById("balance").textContent = "$" + balance.toFixed(2);
        }

        function processInvoice() {
            const tableBody = document.getElementById("productTable");
            const rows = Array.from(tableBody.rows);
            const invoiceData = rows.map(row => {
                return {
                    name: row.cells[0].textContent,
                    price: parseFloat(row.cells[1].textContent.replace('$', ''))
                };
            });

            // Insert data into the invoice table via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "insert_invoice.php", true);
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log(xhr.responseText);
                    // Clear table and reset totals
                    tableBody.innerHTML = '';
                    totalAmount = 0;
                    document.getElementById("totalAmount").textContent = "$0.00";
                    document.getElementById("cash").value = '';
                    document.getElementById("balance").textContent = "$0.00";
                    alert("Invoice processed successfully!");
                }
            };
            xhr.send(JSON.stringify({ billNo: document.getElementById("billNo").textContent, items: invoiceData, totalAmount: totalAmount }));
        }
    </script>
</body>
</html>