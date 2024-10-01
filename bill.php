<?php
include 'connection.php'; // Include the database connection

// Fetch the last BillNo from the invoice table
$lastBillNoResult = $conn->query("SELECT MAX(BillNo) AS lastBillNo FROM invoice");
$lastBillNo = $lastBillNoResult->fetch_assoc()['lastBillNo'] + 1;

// Fetch products from the database
$sql = "SELECT name, price, type FROM products ORDER BY type = 'food' DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            padding: 20px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-right: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 20px;
            width: 200px;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            cursor: pointer;
        }
        .card h3 {
            margin: 0;
            font-size: 1.5em;
        }
        .card p {
            margin: 5px 0;
        }
        table {
            border-collapse: collapse;
            width: 300px;
            margin-left: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        #totalAmount, #cashInput, #balance {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Product List</h1>

    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="card" onclick="addToTable(\'' . htmlspecialchars($row['name']) . '\', ' . htmlspecialchars($row['price']) . ')">';
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<p>Price: $' . htmlspecialchars(number_format($row['price'], 2)) . '</p>';
                echo '<p>Type: ' . htmlspecialchars(ucfirst($row['type'])) . '</p>';
                echo '</div>';
            }
        } else {
            echo "<p>No products found.</p>";
        }

        // Close connection for product fetching
        $conn->close();
        ?>
    </div>

    <h2>Bill No: <span id="billNo"><?php echo $lastBillNo; ?></span></h2>
    <table id="productTable">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be added here -->
        </tbody>
    </table>

    <div id="totalAmount">Total Amount: $0.00</div>
    <div id="cashInput">
        Cash Amount: <input type="number" id="cash" placeholder="Enter cash amount" oninput="calculateBalance()">
    </div>
    <div id="balance">Balance: $0.00</div>
    <button onclick="processInvoice()">Cash</button>

    <script>
        let totalAmount = 0;

        function addToTable(name, price) {
            const tableBody = document.querySelector("#productTable tbody");
            const newRow = document.createElement("tr");

            const nameCell = document.createElement("td");
            nameCell.textContent = name;

            const priceCell = document.createElement("td");
            priceCell.textContent = "$" + price.toFixed(2);

            newRow.appendChild(nameCell);
            newRow.appendChild(priceCell);
            tableBody.insertBefore(newRow, tableBody.firstChild); // Add to the top of the table

            totalAmount += price;
            document.getElementById("totalAmount").textContent = "Total Amount: $" + totalAmount.toFixed(2);
        }

        function calculateBalance() {
            const cash = parseFloat(document.getElementById("cash").value) || 0;
            const balance = cash - totalAmount;
            document.getElementById("balance").textContent = "Balance: $" + balance.toFixed(2);
        }

        function processInvoice() {
            const tableBody = document.querySelector("#productTable tbody");
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
                    document.getElementById("totalAmount").textContent = "Total Amount: $0.00";
                    document.getElementById("cash").value = '';
                    document.getElementById("balance").textContent = "Balance: $0.00";
                }
            };
            xhr.send(JSON.stringify({ billNo: document.getElementById("billNo").textContent, items: invoiceData, totalAmount: totalAmount }));
        }
    </script>
</body>
</html>
