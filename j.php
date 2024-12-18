<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/snowfall/1.0.0/snowfall.min.js"></script>
    <script>
        // This will start the snowfall effect on the page
        document.addEventListener("DOMContentLoaded", function () {
            snowfall.start();
        });
    </script>
    <div id="snowstorm"></div>

    <style>
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        @keyframes snowfall {
            0% { transform: translateY(-100px); }
            100% { transform: translateY(100vh); }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php
    include 'connection.php';

    // Fetch the last BillNo
    $lastBillNoResult = $conn->query("SELECT MAX(BillNo) AS lastBillNo FROM invoice");
    $lastBillNo = $lastBillNoResult->fetch_assoc()['lastBillNo'] + 1;

    // Fetch products
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
                            echo '<p class="text-gray-600 mb-2">Price: Rs:-' . htmlspecialchars(number_format($row['price'], 2)) . '</p>';
                            echo '<button onclick="addToTable(\'' . htmlspecialchars($row['name']) . '\', ' . htmlspecialchars($row['price']) . ')" class="bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 transition duration-300 ease-in-out">Add to Invoice</button>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p class='text-center text-gray-600'>No products found.</p>";
                    }
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
                                    <th class="text-center py-2">Quantity</th>
                                    <th class="text-center py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTable"></tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-semibold">Total Amount:</span>
                        <span id="totalAmount" class="text-lg font-bold">Rs:0.00</span>
                    </div>
                    <div class="mb-4">
                        <label for="cash" class="block mb-2">Cash Amount:</label>
                        <input type="number" id="cash" placeholder="Enter cash amount" class="w-full px-3 py-2 border rounded-md" oninput="calculateBalance()">
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <span class="font-semibold">Balance:</span>
                        <span id="balance" class="text-lg font-bold">Rs:0.00</span>
                    </div>
                    <button onclick="processInvoice()" class="w-full bg-blue-500 text-white py-3 px-4 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">Process Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let totalAmount = 0;
        let products = {};  // This object will track the quantity of each product

        function addToTable(name, price) {
            const tableBody = document.getElementById("productTable");

            // Check if the product already exists in the products object
            if (products[name]) {
                // Update the quantity of the product
                products[name].quantity += 1;

                // Update the row in the table
                const row = document.getElementById(name);
                row.querySelector('.quantity').textContent = products[name].quantity;

                // Update the total amount
                totalAmount += parseFloat(price);
                document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
            } else {
                // Add new product to the table
                const newRow = document.createElement("tr");
                newRow.id = name;
                newRow.innerHTML = `
                    <td class="py-2">${name}</td>
                    <td class="text-right py-2">Rs: ${parseFloat(price).toFixed(2)}</td>
                    <td class="text-center py-2 quantity">1</td>
                    <td class="text-center py-2">
                        <button onclick="removeFromTable(this, ${parseFloat(price)}, '${name}')" class="bg-red-500 text-white py-1 px-2 rounded-md hover:bg-red-600 transition duration-300 ease-in-out">Remove</button>
                    </td>
                `;
                tableBody.insertBefore(newRow, tableBody.firstChild);

                // Initialize the product object for the new product
                products[name] = {
                    quantity: 1,
                    price: price
                };

                // Update the total amount
                totalAmount += parseFloat(price);
                document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
            }

            // Recalculate balance
            calculateBalance();
        }

        function removeFromTable(button, price, productName) {
            const row = button.closest('tr');
            const quantity = products[productName].quantity;

            if (quantity > 1) {
                // If quantity is greater than 1, just decrease the quantity
                products[productName].quantity -= 1;
                row.querySelector('.quantity').textContent = products[productName].quantity;
            } else {
                // If quantity is 1, remove the row completely
                row.remove();
                delete products[productName];  // Remove the product from the object
            }

            // Update the total amount
            totalAmount -= price;
            document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
            
            // Recalculate balance
            calculateBalance();
        }

        function calculateBalance() {
            const cash = parseFloat(document.getElementById("cash").value) || 0;
            const balance = cash - totalAmount;
            document.getElementById("balance").textContent = "Rs:" + balance.toFixed(2);
        }

        function processInvoice() {
            const tableBody = document.getElementById("productTable");
            const rows = Array.from(tableBody.rows);
            const invoiceData = rows.map(row => ({
                name: row.cells[0].textContent,
                price: parseFloat(row.cells[1].textContent.replace('Rs:', '')),
                quantity: parseInt(row.cells[2].textContent)
            }));

            const totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace('Rs:', ''));
            const cash = parseFloat(document.getElementById("cash").value) || 0;
            const billNo = document.getElementById("billNo").textContent;

            const balance = cash - totalAmount;
            if (balance < 0) {
                alert("Check the entered amount: balance cannot be negative.");
                return;
            }

            // AJAX call to process the invoice
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "insert_invoice2.php", true);
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    console.log('Response Status: ' + xhr.status); // Check status code
                    console.log('Response Text: ' + xhr.responseText); // Check the response text
                    if (xhr.status === 200) {
                        console.log(xhr.responseText); // Log the response if successful
                        tableBody.innerHTML = '';
                        totalAmount = 0;
                        document.getElementById("totalAmount").textContent = "Rs:0.00";
                        document.getElementById("cash").value = '';
                        document.getElementById("balance").textContent = "Rs:0.00";
                        alert("Invoice processed successfully!");

                        // Redirect to the dashboard page after processing the invoice
                        window.location.replace("dashboard.php"); // Force redirection
                    } else {
                        alert('Error processing invoice');
                    }
                }
            };
            xhr.send(JSON.stringify({ billNo: billNo, items: invoiceData, totalAmount: totalAmount }));

                // Redirect to generate PDF
    const url = `generate_pdf.php?billNo=${billNo}&data=${encodeURIComponent(JSON.stringify(invoiceData))}&totalAmount=${totalAmount}&cash=${cash}&balance=${balance}`;
    window.location.href = url;
        }
    </script>
</body>
</html>
