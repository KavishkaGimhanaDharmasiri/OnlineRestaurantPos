<?php
require_once('tcpdf/tcpdf.php'); // Include the TCPDF library from the 'tcpdf' folder

// Fetch last BillNo from the database (optional)
include 'connection.php';
$lastBillNoResult = $conn->query("SELECT MAX(BillNo) AS lastBillNo FROM invoice");
$lastBillNo = $lastBillNoResult->fetch_assoc()['lastBillNo'] + 1;

// Fetch products from the database
$sql = "SELECT name, price, type FROM products ORDER BY type = 'food' DESC";
$result = $conn->query($sql);

// Close the connection after fetching data
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
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
                            echo '<div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between">';
                            echo '<h3 class="text-xl font-semibold mb-2">' . htmlspecialchars($row['name']) . '</h3>';
                            echo '<p class="text-gray-600 mb-2">Price: Rs:-' . htmlspecialchars(number_format($row['price'], 2)) . '</p>';
                            echo '<button onclick="addToTable(\'' . htmlspecialchars($row['name']) . '\', ' . htmlspecialchars($row['price']) . ')" class="bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 transition duration-300 ease-in-out">Add to Invoice</button>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p class='text-center text-gray-600'>No products found.</p>";
                    }
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
                                    <th class="text-left py-2">Item No</th>
                                    <th class="text-left py-2">Product</th>
                                    <th class="text-right py-2">Price</th>
                                    <th class="text-center py-2">Qty</th>
                                    <th class="text-right py-2">Amount</th>
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
        let itemCounter = 1;

        function addToTable(name, price) {
            const qty = 1;
            const amount = price * qty;
            const tableBody = document.getElementById("productTable");
            const newRow = document.createElement("tr");

            newRow.innerHTML = `
                <td class="py-2">${itemCounter}</td>
                <td class="py-2">${name}</td>
                <td class="text-right py-2">Rs: ${price.toFixed(2)}</td>
                <td class="text-center py-2">
                    <input type="number" value="${qty}" min="1" class="qtyInput w-16 text-center" onchange="updateAmount(this, ${price}, ${itemCounter})">
                </td>
                <td class="text-right py-2" id="amount-${itemCounter}">Rs: ${amount.toFixed(2)}</td>
                <td class="text-center py-2">
                    <button onclick="removeFromTable(this, ${amount})" class="bg-red-500 text-white py-1 px-2 rounded-md hover:bg-red-600 transition duration-300 ease-in-out">Remove</button>
                </td>
            `;
            tableBody.appendChild(newRow);

            totalAmount += amount;
            document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
            itemCounter++;
            calculateBalance();
        }

        function updateAmount(input, price, itemNo) {
            const qty = parseInt(input.value);
            const amount = qty * price;
            document.getElementById(`amount-${itemNo}`).textContent = `Rs: ${amount.toFixed(2)}`;
            updateTotalAmount();
        }

        function updateTotalAmount() {
            totalAmount = 0;
            const rows = document.getElementById("productTable").rows;
            for (let row of rows) {
                const amount = parseFloat(row.cells[4].textContent.replace('Rs: ', ''));
                totalAmount += amount;
            }
            document.getElementById("totalAmount").textContent = "Rs:" + totalAmount.toFixed(2);
            calculateBalance();
        }

        function removeFromTable(button, price) {
            const row = button.closest('tr');
            row.remove();
            totalAmount -= price;
            updateTotalAmount();
        }

        function calculateBalance() {
            const cash = parseFloat(document.getElementById("cash").value) || 0;
            const balance = cash - totalAmount;
            document.getElementById("balance").textContent = "Rs:" + balance.toFixed(2);
        }

        function processInvoice() {
            const tableBody = document.getElementById("productTable");
            const rows = Array.from(tableBody.rows);
            const invoiceData = rows.map((row, index) => ({
                itemNo: index + 1,
                name: row.cells[1].textContent,
                qty: parseInt(row.cells[3].children[0].value),
                price: parseFloat(row.cells[2].textContent.replace('Rs: ', '')),
                amount: parseFloat(row.cells[4].textContent.replace('Rs: ', ''))
            }));

            const totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace('Rs:', ''));
            const cash = parseFloat(document.getElementById("cash").value) || 0;
            const billNo = document.getElementById("billNo").textContent;
            const date = new Date().toLocaleDateString();
            const time = new Date().toLocaleTimeString();

            const balance = cash - totalAmount;
            if (balance < 0) {
                alert("Check the entered amount: balance cannot be negative.");
                return;
            }

            // TCPDF part starts here
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);

            // Add header
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->Cell(0, 10, 'Katagasma Hanwella', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, "Bill No: $billNo", 0, 1, 'C');
            $pdf->Cell(0, 10, "Date: $date", 0, 1, 'C');
            $pdf->Cell(0, 10, "Time: $time", 0, 1, 'C');
            $pdf->Ln(10);

            // Table headers
            $pdf->Cell(10, 10, 'Item No', 1);
            $pdf->Cell(60, 10, 'Product', 1);
            $pdf->Cell(30, 10, 'Price', 1);
            $pdf->Cell(20, 10, 'Qty', 1);
            $pdf->Cell(30, 10, 'Amount', 1);
            $pdf->Ln();

            // Table rows
            foreach ($invoiceData as $item) {
                $pdf->Cell(10, 10, $item['itemNo'], 1);
                $pdf->Cell(60, 10, $item['name'], 1);
                $pdf->Cell(30, 10, 'Rs: ' . number_format($item['price'], 2), 1);
                $pdf->Cell(20, 10, $item['qty'], 1);
                $pdf->Cell(30, 10, 'Rs: ' . number_format($item['amount'], 2), 1);
                $pdf->Ln();
            }

            // Add totals
            $pdf->Cell(120, 10, 'Total Amount: Rs: ' . number_format($totalAmount, 2), 1);
            $pdf->Cell(30, 10, 'Cash: Rs: ' . number_format($cash, 2), 1);
            $pdf->Cell(30, 10, 'Balance: Rs: ' . number_format($balance, 2), 1);

            // Output the PDF
            $pdf->Output('invoice.pdf', 'I'); // I stands for inline (opens in browser)

        }
    </script>
</body>
</html>
