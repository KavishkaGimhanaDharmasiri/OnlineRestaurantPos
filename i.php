<?php
// Database connection
include 'connection.php';

// Handle AJAX request for processing invoice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'processInvoice') {
    $data = json_decode(file_get_contents('php://input'), true);

    $billNo = $data['billNo'];
    $items = $data['items'];
    $totalAmount = $data['totalAmount'];

    // Insert the invoice into the database
    $stmt = $conn->prepare("INSERT INTO invoice (BillNo, TotalAmount) VALUES (?, ?)");
    if (!$stmt) {
        die(json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]));
    }
    $stmt->bind_param("id", $billNo, $totalAmount);

    if ($stmt->execute()) {
        // Insert invoice items
        foreach ($items as $item) {
            $stmt = $conn->prepare("INSERT INTO invoice_items (BillNo, ProductName, Price) VALUES (?, ?, ?)");
            if (!$stmt) {
                die(json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]));
            }
            $stmt->bind_param("isd", $billNo, $item['name'], $item['price']);
            $stmt->execute();
        }
        echo json_encode(['status' => 'success', 'message' => 'Invoice processed successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Error processing invoice: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Fetch the last BillNo
$lastBillNoResult = $conn->query("SELECT MAX(BillNo) AS lastBillNo FROM invoice");
$lastBillNo = $lastBillNoResult->fetch_assoc()['lastBillNo'] + 1;

// Fetch products
$sql = "SELECT name, price, type FROM products ORDER BY type = 'food' DESC";
$result = $conn->query($sql);
?>

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

        function addToTable(name, price) {
            const tableBody = document.getElementById("productTable");
            const newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td class="py-2">${name}</td>
                <td class="text-right py-2">Rs: ${parseFloat(price).toFixed(2)}</td>
                <td class="text-center py-2">
                    <button onclick="removeFromTable(this, ${parseFloat(price)})" class="bg-red-500 text-white py-1 px-2 rounded-md hover:bg-red-600 transition duration-300 ease-in-out">Remove</button>
                </td>
            `;
            tableBody.insertBefore(newRow, tableBody.firstChild);

            totalAmount += parseFloat(price);
            document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
            calculateBalance();
        }

        function removeFromTable(button, price) {
            const row = button.closest('tr');
            row.remove();
            totalAmount -= price;
            document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
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
                price: parseFloat(row.cells[1].textContent.replace('Rs:', ''))
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
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    console.log('Response Status: ' + xhr.status); // Check status code
                    console.log('Response Text: ' + xhr.responseText); // Check the response text
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            tableBody.innerHTML = '';
                            totalAmount = 0;
                            document.getElementById("totalAmount").textContent = "Rs:0.00";
                            document.getElementById("cash").value = '';
                            document.getElementById("balance").textContent = "Rs:0.00";
                            alert(response.message);

                            // Redirect to button3.php after successful processing
                            window.location.href = "button3.php"; // Redirect to button3.php
                        } else {
                            alert(response.message);
                        }
                    } else {
                        alert('Error processing invoice');
                    }
                }
            };
            xhr.send(JSON.stringify({ action: 'processInvoice', billNo: billNo, items: invoiceData, totalAmount: totalAmount }));
        }
    </script>
</body>
</html>