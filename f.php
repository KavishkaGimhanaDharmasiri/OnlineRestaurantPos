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

                // Generate PDF in a new tab
                generatePDF(billNo, invoiceData, totalAmount, cash, balance);

                // Clear the table and reset values
                tableBody.innerHTML = '';
                totalAmount = 0;
                document.getElementById("totalAmount").textContent = "Rs:0.00";
                document.getElementById("cash").value = '';
                document.getElementById("balance").textContent = "Rs:0.00";
                alert("Invoice processed successfully!");

                // Redirect the current tab to button4.php
                window.location.href = "button4.php";
            } else {
                alert('Error processing invoice');
            }
        }
    };
    xhr.send(JSON.stringify({ billNo: billNo, items: invoiceData, totalAmount: totalAmount }));
}

function generatePDF(billNo, invoiceData, totalAmount, cash, balance) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: [78, 297] // 78mm width, dynamic height
    });

    // Set font size and style
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");

    // Add logo 
    const logoBase64 = "data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAIAAACzY+a1AAAACXBIWXMAAA7zAAAO8wEcU5k6AAAAEXRFWHRUaXRsZQBQREYgQ3JlYXRvckFevCgAAAATdEVYdEF1dGhvcgBQREYgVG9vbHMgQUcbz3cwAAAALXpUWHREZXNjcmlwdGlvbgAACJnLKCkpsNLXLy8v1ytISdMtyc/PKdZLzs8FAG6fCPGXryy4AABJeElEQVQYGezBebDm11nY+e9zzvkt73b3e/v23upWa21Zu4TlIEOwYawAhgAZKMAFmWAClckClRDCkL3IkEmRmsxk/kgyhGEJAQoyIUMCDgSz2CBssC1ba0vqVqvV213fe9/lt5zzPHNv2waC+QNVT0/Jlf58uOWWW2655ZZbbrnllltuueWWW2655ZZbbrnllltuueWWW/6b5XmrySEVJbM5zmg1Aw/az8lLGgfJg+9AF9MCTfy3zvFWY4BChMh/xQyMP8y4BRxvRWa0kIzrzIEpUfl9jn1q3ILjrcYAhVaJ7DHAAUYyUGGfCeYQVeGWwFuNAmooewQMzANKNDBhnzmuMwHjv3GOtxoLwj4DBRQscF1yKGCgDmOPCrc43mrMAwoq7LMM84AJCgaY4/cZtzjeYoQAJCEJxp4AjutM2GeefYpxy57AW0zAJ7y5ZAYGeHAggMvQGnAe9WgChMTnGRFxzgH2WYBzTkTMTFV5kzxvMY5MaU2MPZYJucOgRaIl8rxH0kHIo466ed7EZHx+yPMcMDPAPgvI8xxQVbsOEBHnnJnxJyO8xeR0Wmpzyh7teLyQErUFITks9ESwCRCCm0Zt+DzjnBMRPiulxGfJdXYdf2Ket5gMUUnmweVo5kieKRhB0AJcx8WVAbG2Vk0IEeXzh4gAqmqfxWeJCNeZGW+G4y2nNQMEc4DQCohgrWKuk3VUm6986h2HlwggKJ8nvPfOOcDMuM45l2VZuM45Z58FhBD4E3O8BQlgmIA5ohOCAwG8tjHA3adXH3rggIeMwOeJlJKZee9DCM45ETGzlFK8TlXlOq6LMfIn5niLyQTnuc6BAg5w7PF5HomZI8/bJx5/m4eA4/OKmaWUVNWuU9Usy0II7rpwHW9S4C2mKInC1DKSIylggLEnNU2G88LK8qzQm+1ybdLweSLLsnQdkGXZ/Pz8ysrK7OzseDze2tpaW1ubTCYpJUBEzIw/scDN5jukTHA5tTFtPRbY5zKmRQfnmDhJVXCtFUj5VXdtnrj9K37oZz5UhTjx00IprLA423jTQBvrB1a4v/m9mSW99wgvv3QaIlwqe9M4IRgGyRMNdAYy8h2sJeF0kIOym+glNyaDRIhZoKdMG1fjcApZT8WRV6S2O2VRGUMN4wK8g4xp1rNQkm8Q6UbyyKTpNbEDO4GmgOBpEq3vp9xbdFiNTry23ZYjcKosTw2O37Fy8vhSv0uqp9XgjsH67qHNdv3FK2d/6VNcxTaNBp+CRwSMlNAk4EAg8kcEbjbNQGFqtIBTUmJfa72ZTrWzEVAzBEiJ3B1a5uEHD8//Ozkfaykwo23qyFCTo+xIrO8+dWSmLON0ePBgR17aJutZq1rRzxBj5VC5NaqubQIt3kgJzx6jaiHDNTL2CjXJE7MUbSSRUjGlDmBjpEPVw0x0WMEEybD52iWoSQ1a0VZM2VMrrUmWqbmdtmkMEkwKF5xqPZIpJZT0Dpan7jx86m2nZ1dn51fnzdlwuD19bVfyYmkw8/z48lxndvbQ7Jm5Ox9ceeCn/+6P80c5UPYICJ8rcLOZgynSKvvEkFgYGdKOq3UyS0qvlHqclsrOpNp87O7izMnyyCF3/kJrLoya2BWS1XiHzwrqL3jweIgjH8LxQ8sZF5t2nKGzjtTwRe9cuveBJ//p//FzWIDWSaMJcaVRmW9JODrBpgGndJMZ+RjUFKeFw2prEGjrLPUzksGuUPkyRp3FSkyI0VkKkAiKT6REjHEqAZeRHMktih+2Y83Un+wtn1k9fv+x43ccXpwb7K6tn/vEC//l3z678WpkGwzmBisrB099y+rc8aPVxjg3ee3Zc0R8kaeqEQScgaKKA8X4YwVuMqGFFiEJLiF0PN1EMqsJhoEyntqCp9+MHjjU/YI7VmN8/fDBGC5rQnBoQTMFyZiMjs/y4F39ZveF2f5tc10LaMuoC77lnQ+5v/e9f/Hv/OCPa+s72ey03RBDzFkrBC+SNJHwPZjiGwyJNCAgboor0G7ylSYz9ex4mELsQNSY6Ubw+EQyDJQ8kYFB68EDnoYS36dcD5uDo/6OJx46/PDpxTsPd+Y6Lz337H/82Z8f/e4Oa1ATGvIQqljoSK6tDa/9k5fS19R/6tEnbZSe/pkPEUlVI+b4LMMZn6b8cQI3WcY4CUnASLhA5khQS9faBKWnEtHoYdnzd77zyxbD2Wujq6vL0WXgEkrVIJJn9JxtfuWTh47ObaftDdoTEksPQegYt8/yD/7at43WXrv4yvmEMxWBTBGsQm2PEDJSmxzLLSOyMYmiJZIlcUZ0hB4DrJ0yTq72QhSQAbbLHmnxEKCliMyDQi1MBBxo3ekUTKsJk4NfX9734EMHD5/wRW+0tvlLP/nbl37lBXahoWh8jzLzoVZqIrRoYsTv/Nunpy+Oq2HFFCdkoYgpcZ2Cssexxxz7Iv+1wE0mggAK1oEsoTAxYqwh94yD83mP3R784Pe8/cyBHbvSzBws5vq+baEHUjBJuZslplXhqcdvl/FzvQ71uB2NCsds1w+J/KVv/cJsenW8vTPZpvQyTE1OF5sI5qmjskdFhHabnjHxkWB4MMBcgECW6EQwR+yiChWM82PQAW2ZQOMZOyGa4gpcbVEMG2RoO23r5buXvurLv/zVL3hFK+cHfvOFy//xh/9T80LKtUPtc0rFJpimmKgtRCRhMIYRn/yVTzlPrygnu1WkFRygqOH4NAOEP07gZhPMwAroQjKZRIkCKK4KM71FHV0+mPHPvvddj5xy0yvPStUrXBE0eCU24DISgWYWvv5LHr5rIXc7W2R5ZPDG2nakE+LwyDxL/WlXx0cGC9MdXJZISek4Wk97eCW7sNaaBgUk4nZ8omvUMAlIbAckT7els0OK1DjMkyID5V7yuwe9uxaPLLZlPWleT+1v7lx5htGWqDddKMtxVcVpy4O9e979xMnjhychX+yfXJif+fC/++Az/+qTTBgQfO0gn6AN4B0Ymkgpd+SOqboyL8bjKUItrWOPOvHJEjglGcKnGRifK3CTJUMNyCDhanxEsEgpeaEhjC7dt8Tf/itfdu8hHV99eaXfQ307tVh1B4F6GrB6oQxUw4dPzn/bVz8yFz+RxQHWcXMHXl07O6YZwJEVzpxezHaHdSyXe1wag1O0C3VGe9fJpbWNy+S9yXRCBmxKClP60dfkrYFEjdiIiNTYlD0WaOP9FH/+0Orhx7/4wUceXuzP77x+7ewLL8WP/trFjdGlY9ng2Gqe7N5OsfrInb0vuH2zjJPp5PBg9qm7v+KHfuAHn/mJT1JTBqajCDHKmNxjhoCBw7USkrmEl6xq227Wqds6WQLyLG/aBjCUfY59jn3C5wjcZOa8JYEWV+NbSmhBXUD6jJ84Gv76+995/8m4c/nsQqczGWlftho9SjtINQt5v262fdU+djs/8De+tF89V9a7pCXCwpWR+8S5a4QgkbvuOBQktqP1ubnF1QWeHbMnogUeeOdj9492ph9/fhsCxiAykmihxhvXTRwtE5yiEVzIenEnnqD8s3fd80Wrq7d9/9/j1CJ5Oxfl0Q/8xocvfCS7wslHD77tW/9M7iTfrop+91wz3F3fffjoHf/DO977C//4N3/nR59m2yFaGX7gUqU4kISAgUFCIBGA1hRk2raAsa+ODcJnGKDCHgUHGH+U4yZLqQMeVyMtAhW0WScf5NSPHuIH/sqXnplfH7/8oaXZvBnXoZhVNyz7+db6ZhdCs70C3/hFq//b3/+GWfdc314mDkk9/Mpzl9bGGRTRw9xgJjVWZjIopg/f081ADGiUqoDbDy5/6aP35IYnEjo5PPjAkWK+lRgXQJQ2h2AhTnPn8J1Y21Hqbz5y6q5jR277X76f5T59v2Xjqy98bHrt4j0H5o+VvPD0havNG883r10Mm89deHYukzv78+97x3tGb1z80P/zS9XGNgouw5ES+wQapMLX+BZRklC7OPHRwMDAAAEHAgICAg6EPQ4c6lE+h+Nm8w7XZjkYRfC0hU+la4ZPnM5/8G+99+T8Rjm5MD87Y2vDIu80MTbopN7Z2bo8B48f4ge+88nv+/avbDae7XGlkKvYNsHarPyt587uGgQCLM4vpzpkIdFefez+4wUEcUhj1P2M22Zn3/XwqcMdMhDrjuDeU4d/8u980194iIUJAyPvBmnpQjcvKHxPR0/1Vp6669R7/uHftAdPMu9f/PGf+uC3fM/T3/y9z/7PP7x8dfye0we6V3nlF3/z+GAxSjp68tj6hQtf8uCDc/gf/sl/9VvP/IqFSKH4BI4mELuh6vW0N0OnT1biPOAhh5J9Ygg4EPYJCJ9L+OMFbjZfk7RtKB06sYXQTXHrsVPl3/j2J1e6F3V4PrQt9CTrivjIeKZ3YFgNbzvKF77zni95+yO+2tD1F/pWhaTeFDehqDfi9KPPX6oVhABLs/OWVEjN5I3TB2+/c5nNtabNkkVW55mzqt9tv/hB3vgwVeu0nL36/IsPvPvoV3znV/ynez/5ff/6/OXNuEtvTCymoxXTp5aWvvKRR7/g+/4at69c8p32n//98z/2C2+/bKs2Q2q2nfvCldu+trz68z+xWd1xeXDXoYubm4/f/8CThx/7zWu/87vT18bR6EILSUFQn5n3iAeHVxw0oCgYJBBDlH3K7zM+w/h9BsIfw3GzSQ3MDgoiXbSMW2fm+b5ve/f9R+Lw4ieCGNkMqU85O56O+j3fTkIZ+JZvfOLL3rHarV7ujF5d6bWubmj6yoDMUfLy1Y1XLmI1JDow6HedJwhBpyt9/aJHjjhaH5LBiUOdGdvqTy++9x2nu4A2Ug1vG/SXvA3f+MRXvOPEj37HFx8BYTkNVrzpN81lX7W68tRf/os8/rY0P7/zO79+9d/81Ns2hqsmVFtY3W6Nj17R71h86KFtPvov/3Np/VazIGUkPXvu5ec33yCyL4IChk6NKjKtqMfUU2LNnuDVSe2kAomgmGJgYKBgYKBgfJqCQuKP4bjZWgbd3nhYl0IfZuCf/s2vPrNUTV978fiBI23lcT3KYne43ev1iCnQz1yd27Vq7aUFm87oeHrt1X5ZpDSIthTdoPHFR5+7vN1A6hHpOwLRaFQ181mH6aP3reZgGsU4fWxx3m30p288fOzgnQfwTFaFMyeW6snW4vJgeOXcmbtve+qRJyNjxpsPDORLz9z73r/+N3j08Trr66fOnvuuv31/FZazLBXTndl284DL58vlyN3j8L13PXDgWX7+f/rnJ3Vx57XhBz78wfG11o1LGkKV0UIiKAJR2pi1ja+bUDeujqQEnqxD1qWDgbHPHOYwhzksiDlw7DEMTDDBhM/luMlEu6Pd6CB3BPjH3/vIgeJiuXPxQDlTr1nWPZg6nUu7a9KV0OtWO0nyfDpZ9zpc7feZ4LKy0xNNlUq/ZalhbhxnPvKJq8oq9IEMhMqFadIW9TYZnjxc3nkCbaxbcPfpQ5ldIW3OIO96x32eFuO+O5b6sjvZGjbzhz8wHv7Ui7++wNq73fjrztxz59d9HX/2axgsFk+//PT7/9bjFzZspFvj6UaI9VKxmYbDtDViqGl4t7p/8tBD93+UX/3uf/nKB599+dlrK+nI0WtLuRVZzDquE8yJYgIOHDgQ8ODU0ERKkFAMMcQQ8DiP83iPOLzghQAOYZ+A8LkCN5nQKZw4tTY13/NXH33ikYP52ku9CDtS+MXpJFV5tXB43to43NgadBZsMp6ZzYfNbmaBqSNE0+3QP9BqSLFMVk1jef585VmBbcAp3mlRIiNIXm0yt8xD95/47fPnD6wcOHn8EPEc1K6qn3z8ifBzn+wVzPZS2e6Q9z8+5s//xM9ehffM85dXj37xl76n+M7vvBD65XNbZ//qD7ztpaszIa1LNn/wyM7OdtwczQTCTLY52u11O73p6O4t/ZF3vOsfv/rR/+vHPpj+y0cOzh8uNxuHM03CHqemWebblFAw9hn7RJNpIgrO8weEPQIOMHCgKDgMRNkjfC7Pm7QMU6B0OA8el+FyLJshC/jWO7qOTHDgIceooqvawPzJU8N65ZkXwoXN1V054udWiyAdYmc6DVtbWTXUzni9t1WmlTb20LlaQtVt2wzvemXy3eF66SWzmZ3Y/9nfOHtFr1blkJA93Or7v+Tx2a0qbz1uZtMt7+bLM4P82V+98P7Hxl995ihrFd3jO63P5wuqcw+cbb/qycfPHr7vn2/Zt//Qr66c4282h74yO/PA+7597vu+Y5yn5ec+9srXv+/+i+fqzuTVVTmxVo61udpn1PWFytxOXKpdr9FOEaSeHGzil+Rz3+C7f2YzPfjKxSc2tl+YmZnWI09S1CCa4UEFBXWYYB4cOMQhYqiBgWGKKqokJRnJSKCgYOwxMD5X4E2KXGcg7EtaoAECWUWCVGRdtsezMAAHEQMz1J89+8rZs6/Br8EAVhc4dIx77j92572HDx06MaDpNc18TPn4fN7r4rNGUxN86HWaFEajSWduPqoftTIWNzdDeY1yivbo5Yz13Gx/DJs0014sesww3/8f//u5B+4/vRWqtpyu9MKMpJkk3/PE8fOHBxt3HP5nv/jvfu7/PnfXNu8p52Zml49963tXv/ubmFS9F6988Nv/9h01bT+fxNGg8VU3IcxX2no1ZKdwPUcRacdtazLUmMSv9GYXF5dv1+PSK//1pcuXdzaNP0QEM/6AguP/O4E3aQIOUgSnQAl9aNBNaZkJMrLB9ngZjsEACpjN5xZ6vS7WNNOdNL3aVG8oQ/jEJr+xydYzF2LnwuEjPHXPsa8+fc+jSwc6c9eYTJtq4opu4Vw92klmRScfTUetFG4w6Pjs9G3uhWsqIOP2i79J9ECz0Q477U7ukxcJk/X5On/fux8f1xvaXF5YDeOdS23dDqgXZsvz73z86//RDz3/GovbfNPSwbcdumv23U/c/X3vo298YuPZP/fdT+w229X2+EAmPhtM4ma3HTTMV1IHhgU7hTTez08pLOR5NnJStVWZYmHWF5Ui9Gdn3EVa9iX2OeeUxE0jvFlCbjioAKFrZDAU6LNnZpcvcu7RQ0f7vaJf5seWDmTT3Zmi9KYxpXHhL6X2pe3x66NmYuWFK9tXRzutpNp2PazA7Qf67/3G2TOnT9+2sCRbm7p1bUai+Jo4YrZMqlPfa4qDv/fixkvnq+7cyfnlE6unPj6beXa3+9bO+CKXjGhxMiraacYUm+JsM2Vy/L4LNvjtsxd/8H99TobcJXzN/Q8P8v7xL3vyse//S2Sy8+u/+5vv/7vvmnTzVA/T5m63me3maXNr3Cv7Dd3WRee2CxlnzpnrN3Qa8SFMQqpIIubaVprULztf3ss/9uwnEySowQRfhFRH9pjjMxx7hH0WuTHCm5VRtHShgQoUzEEJBfMtf+HQyT87e6xTt4PbFpfuPDGqx4c2rjSwo1o77/pzre+NyBvfff6l19LIqvWdnctrF4eXX+bKOdIGrA84tcJ7HjzzVQ/f97b5zmB8yVeXyXeJ6zV17b2W81nnyLQedMrVqvbD8BGtmrnQzQmaaLQymoKqF8dZ5qkFm2H5nl98o/knv/T0r3xs+84xX7N8+MmVUzvO3v797z/0NV/knNv5+d94/gd/5PDahNFQma4uzW1cen3QzUGTU8MlceCS+Na5xjsxV1SpnxzOqq4b52p1s9JIL+UPMj578YJBCxFMkMxZq+wxJ+wz9jiEfRa5McKbFQiRDiSYOMhBoOVg5JsXV9+3etex7SY1o9l7j00Xs4+fe/HOS+d3i3yrLOrOTFEulNIblDNzc3P9owfxwub2pfOvX9jYeN3pp5rhi6ONp69stDEBC8Jjbzv4p5+49747Fxa6dccN+9lU6p3JcDu3oLX2itnp7qQzP2HS0u3TGtCq0sm1LNcndeiuFN3Dz7+49fRHLvyHD7yyDgvS//pT961kvdvuOPnAd30rjx2n0M2f+cAr/+iHD18aZR03mpHt8cYBCbOjptftDEc7nSyMcqbBgSuiE5PGu9a5joRyGqlj0/ebPibSAcvzrend1dbGZCTiatMkGOAcquwxBAcY14ljj0VujPAmeZyiBjjIoYDIwTHvLw9/88qdR9U3zSif7+zaeG06LOf7Jy69Tp4PfT72ebKgVczappNLDE3b1ZnbD/fvu4uye+785bPnLo3GzXAoL15544XR2mvYJZjC4hJvu33xyTMn7lnq3rPcXS1T4Sqrd6SfEWuGQ5qaIiO1dLskIZ9pUm8yf/JXX93+0Ivrv/iffrvd5kF4au74mbmlSwdO3P/UO49+xzcQGnZ3dn7kpz7+v//YfdkgtzQudZS3qnHW/IIU07Wtfrez61PraD17guIVcCpI8NYq0bIsm6rqoFsV7pmzL7xvVNVo8GGaIl6SGcY+Q9jjAOM6YZ8pN0Z4kwa4XZQMHATIoOauKT+w+Oi7lm7bcuMPbp69VG+myXgWf+zIoS90M0VEJo3XlJeZZqmNE9FpG8epm12W6qJU3YNHztz94GD2EGObvnrhpY21Z3aGz6fm+Wr8wvraeq0F9GERjgqnT7FyvFesFPO3rfhedrcuSZFlnc60qTpi02traWd69fLol3/r4seu8AaUlG8/dfeZmfJ0SO966Azf8V3cdhDXXP23P7fxs7/c/9jLy01b9MJEahVTiw0aut1qMl3QvJym1+fzTqRI0Zmpi4gGVTEaJ7V3nmJGy3qadhcHv1tO/8XHn/ll9ogPYRRbH1wSZzGyxxA+zQEGCPtMuTHCm9TFTZyRCao48GDcO3X/4PGvufrci/9597kPE7c9vcQc5PAkM194/I7HZudmtzeLersolaxtmqrMymoaow9tp7ej7aSKC/2ZowcO8vZ76fSom+fPX3z2/MUrmzs7u9PxaLQ5XN+hXYct2IJd2CpoHEcryJGyK1lOM5GdpgtLMA+n4cjynf0DR2dPHj39xD33/+n7ufcEoR+f/sQLP/nzu7/44dXN0bG5Gc3S5mSz530Z1Znbzd1O4Qzfq91MzYWlXnfczEzbrkUJbZRoElWoheQz3+ZZlUt3/tme/J9bL/2bK+sVGCgkMOEPGAKOfYozQNhnyo0R3izvEBMTn9RBC5mTRQ33Lx3Z2Fh/0XZ3OtABhQYi/aazSP0lCwe/6uTRO9tpf+taN7aFD1ElhEHQ0qLH+Zixa+2wGb84Pzm2euTu43fkh04yWGRU29lXn3n1lauxuaLVJa2uaL2Fbcd6u64mdTOzNq1cZ+j6Q7La2kERb5vxdwz8A4sHDhdzJ47cdffDj7t3PM7JBfzOxtaF1/7FT2/92u8tPHfpge6idPyF8aWhn64sLhRb436D4XbzsFN4JWTJFVG2CAPv57BuPaUew7TO0jiY83nXdduhxM78teWFH7109keuvnapJFQoGCRA+AOGgGOfggHCPuMGCW9W19FK2UrAHObBIQm3S0rsURGx3CNKMkywGXzMm9Ed8I3HDr136eDRnWk2mpD5iVpSl2nILVcvTfBtztb8pN6ZNFujTioPHji6cOe93HUHh5aZ7pyfDM+tXbl69epkc5iN6qKKRWQ+vjZ05Xo+u1nOtt1Bf2Hu8OHlA8sLx0+dPHzqDu44g8vYGfLRD7346x+48OzHjj+/NoPrqwuktmTStTFNU01mJC8jXkPrXe1864KKF6MzyqTjg4v5dNStJj5Y7FIF2kpDldOZvzA3+/Pj9R87/9IrSQmZxtZAwYQ/TAzHZygYIOwzbpDwZhXQUJhP7LEBdGEEw8wjljWyjE/oLhYhQ7QzO61GuDZoXDW+2Gdfe9vp+zud7mSXdupNg/PO8hQxV/i8sOmVkGch6zQiwxjXTUdlXvV7Jx64v7eytHrwEPPLqGdtxJV1NrfhQ3QXWDrOkbs4dDsLB+h3KTP6Hdp67dxrz/3O720//buzz7948vLaynhSdjrWzXcL20lT1djJi+AlampNxZxX58yBT+KiOBVZaora2prGqDOnwakTXEKjb7P+G7P9X5hu/diFs8/XFrLCt7TUCgYIf5gYDgQMFAwQ9hk3SHiTCvDQILEALNRuHirYLT1Omcq8sWeMKJLhhMoIDSTnyUzq5ij8udsOvnsw94DactVUu9uNWtafTeTjUbXqOo22dbC61CpPU9GUOcnyjY2hs7z03X5nfnXhUP/IMY7exsICd+wiXZiDWVrP1pBrF214+fmXn9kabm1ubLBbr2rnhHSWW6GKm0u79WRqZqHsmJNUNaXKIBQxau1pvGDOG0Fx5gAvaNOas6YXxoVMY9OZ6EyT+d7Ma7Pdnx6+/pMXX3slYeBTlhFqpoABAsI+Y48YDoR9CQwQ9hk3SHiTlqGCXYESBBpCJIHlgjMSRDBHEBx7iiZ59riKoLnQ97iYrzfvnSm/vDP7p/rzq4a200gSH2RPu5hok6stawmVae2aKkTph65onuiMzG+b28TtOGrvXpqV0nV71gttnjVaxHEet/vt+sEQly32LMNKtKhdvuuKqfcbK9cWK5kb4SJNHprM+1rL7bbvC/Xs5lQZ3rRsyVWdMi7qENUTpt1sM5OJ+a6VPd+/LPzM1Zd/euPKKw4Ep8GrMyRRGyDsE/YZGAIOhH0GCRD2GTfI8yZlyBgsgEECh3rwYJDA2JOZeTMVI7OkWE6TzEgY1EptIS9fG0+fHY/XfWnLK9lgNrXR100PvUaIXp0TSZbVVjZZX3odmWmnYuTJOc2c5JqXbS9vZ4r2uK2eIBxMcSltL8r2SjY+0kmHO2EQtWy9qyFKNGszKAkdfDNxO42fptznJqGKmvBlp4s6Z+IRMHWWnCImqIWRZdY6YpOylJWuPyT7FO2/X7/40+uXLgOFs9bM6HZ7VVuzTxBjj/D7BBx/wAABAeMGCW+SQzQYDhoKqAOUYDBhxtiToAMOhlAXYCDsiwwSczhHtkuYZr1pmqDVgyH7uoMHv3xu4fR0km9uXFqcy9Rn0bKWTEMmRaOMNNEroo8tNUwyHZdMSmtySX7jBK4mG2s2omzJfNW60TRz5fKEQe0K5+lY22t2etVOaKcwT79TFX5SR4sSfFFlvlItzc3UlCm23naLWGUpqJZRsTXtdmvzYZovyXzje7+6vf5vts7/Z9pNcJnTKI2ZCfuMzxAQ5dMMDAHPZxgkQEBAuUHCzdaFhiLSZd8UVwFOAYESSmUVHnHFlx2787HDp5auvFChY9e23pyjo67XpO5UQ5OChBRCFWTqpXEueW9OZqtKDIc6wxneVAwxrnMmGC6JqGA4E/rtopKU2NK00kIUp94RgmvbWhHLfOv91LRxInk4ecWo2/XZwcbRQ78n8T9cOPdrly5cRTUvTGsEUJQ80YECruXsM1AwvOJBQMAgQQITcA4HONrIjRFutgJaciWDBBUOD6IoGE5wSgeW4c4snOgtfO3J5ZnWVhq3XBOqpop1W7rYC8nURS1rLZN3ztfejbxOsCM1e5KQHEksOVRIjk8TE2eIOTGcOTFSHUTMB3GZOK8mGq1NFpvYAt5nmWTBnLTmkjhzSTQuz72Q8YGrb/zq5Usvq+7ixrhGIDMyECOqKHlDDhNQMAEBAQNDDA+OfQoJTMA5BKJyY4SbLYOEVwQijgwEDCJiag4yMPZFSuNPOZ46dfTL506euFJl6yNKl/q84SZ1KRnMtNafqE8WnZuUri18b1dMSEJyGh3REb2qICKAV+eVoM4r3pwYWlhKqdU2ojgzUduj2ut0QxLXCrWGFArJPQHjI4f42Na1D6xffHrMGiQI4Ag1pNIjLaYoKCjOMRuJ0EANeHBgYKAEw7MvQQITEFBukHCzeVDEMBwePOBQXJQMi2jKPYVDIlFBDk10BR5z7r9bveOx/oHFNvq6RpqdelcDLpfcOydilmKMqjrszgg4w5uK4U294RQvJub4NHN8ls+q1lmF1kGjQ8SX5jrJx/XJjO9mrkw+1CHfzcO2xp2m/onxq09v7n4KJh3IYZeuui7FhDY512jjYbYoqliPDUpmJ05xDdSAA8c+BcWjnj2qYJCEfcYNEm4270h49iWBwD4lTy5AQmrAOwSSE9xBGysozMOjM/0vPXDbIzJY3anmi25qhmtxd5y1WeH7zvcasrp9fmnGG0G1iBRJi6RFJJh5BFAhOqKT6IgOFTqTLc19KkIdXGsqUYpGum3oWZfILm67W1yayZ+zyW9eOfexjeEmbMJuB3JonJv6PiYQUdAB3NlZObqyemG0+fTGxbqA2rHPsU/Y5wBBwdgXDRD2CSg3SLjZXEDJUYEGNQ8OEl7JcIZrcYp4XIkrkAHVFnHHQQ/fsFTzZBaeWj11TzFYEMoAEl1TF3UzF8VbuFYC6g1vGtS8IaagQHJER3RER3Qk5xQWpxNw7FEkeTHnJBNXTlxoZgZvBD68dfWXL7/8kWjXHCmnqKgDZJAcrZuhMIs1bRTtGYfgz6zce3R++aVq89+//sylAB4UEkQXcB4RnEGCiCKGKCgCxj7lBgVuNhWPOXCoh8hnCDjU0OB8EifJwBzuChIJOKNOyXO1x8+18Zdff/FMwYMz848vHbqrHCySu3YyTE1QXRmBYIIJSYheo9foiJKSkAR1JEEFkwQ0WY+oeSsFPvOlhmwn8+u5/2S189Erz//W1sZZY8NRdcGzp24dTtCASjCriQ3JehlaT6fs6WdOxruzwc2Ku2xqwmeIOlPBOSwhxnUCAgLKPuPGBf7/IAYRooCAkSszUIDCRFNNqqGCFpq8iyZMSUJSvCbPVocPJz61tvUra1tn4KGF1TOLBw7OrHScs40NwJmxRxQUU6+GeS+qggkmKJgAbif0xDlXBBfC1OT16fjZ9cvP1zu/O52chw0h5eCgAQWDLCMpCmaCKGZe0YgjZWiL9txwZyTedZLOJ9qWT1MwSGiDJjBx4Pg0dZhiCBg3SrjpsuB9So2h0nPWaIicLHhk4dBCCl7chjWv1bsXq9GwtsYYhYIyo21JGrKiSKRmkkODJsDjEgNYhZPIatZ7cGlmtuwu9vsLWdFX6bbWiRRJXYwO2WNC8hLFkqDYuZnFSWquTcfnttZe3Lz6UlNdgi2YOhrPPgUlMx/AIeMQSComGXu0xcxHShDCmNOBd5+6Y5F8c3P73LUrg4MrHeroZNg2l8ajq1MdKiOhFXBQFFQ16rzzktRhHjclcmOEm81nOE+scJBBZC7ycL/z3pP39dZHcVK13Ww6X1615pX1y5eubb+MTJEmy6KDmLop65ByLGI7WF0aQdnTMNMyZxj0YFFYLovVrLOSdVayzqzkfR8yEy8C1ClO22YamzbFX6zGo9Rsab2GbsMYogcPBgYEiZpBAI8T2EEDLoeAGJKEqTQGudKHUxnvuP30vGXjze3drd0jx0/MVLuT2KQij4Petdg8t3bl3O7u0LHZgIcsUCeS5LgMcdgukRsTuNnEkMQehYgohRBM1q9czRrXVdy0Tm21krvV/kqYP/zJjfiR9RevtfUuMhabQJTgzAwaIAJKEHzaMdtJGjyWeEVx0zpM68B2CQEKCJCBgEEEY98roOwzQDw+4EANDAPMxLWmgEM9HqferDDn0QTRTNiXQRfmkW4Sr2oxee/LMmeU5otubHV6Zedkr3Pi0O3nJrsfX3vjnE4vt5hESs8kqRHIHYkb5rnZHKh6JQMHKApYe+TYUS3yppvXPa9lQMTVjRtNZxeWZi3X6W6DKrSe5C1mPjkHQvISRRoPAefwXttgEtSFFFxbZHWZj3O3m7stz0Zma8Fd87LmZcPJRsg2vLc8EDziCIX3heCsBXV4jykComROScmsRTUnGbmZwxJWQQpQYJEeHM+6R/tzmbkrw+3X6+GwF3yeNYhT6VnoTVMxagbmDy0sZrnfHo9GCs7wThOCOaRGuTGem85EySEDgUZoHduJc9vXnl2/9sz2tVfq4ZrXtshcnktRXJ1srKwu9bu93e2tGtSsCQ4UHOpyrIQMNTVVBSPvIh4cONQRlSgkwzzqsAAePJLhPM5n02jRUEHVTA3FOzxExVTMglgwFWWPOczhE58WxdXeyMGhjpA4UnSO9OazkL+6s/mJpn6F4fqovjQa70xrS3RcVriQ4Zxpp9f1QUbjSd3iAyZERfFK4sZ4brKQeZcsgEGLaACPekbK2LPruFjp+d3J5dF2nYn1ezMFLTHkodvtbG8PWyhwTYymCJqBYAlV1EQRwxRLWCSpaCqUjtJRy1Mq1WWKT0IyTRCNpAs0GSpIFMMbHpzhBE0Z2jEtjK6xksmplYU7V5fu7vY6sZk2aQpVMHIPDjOUPHE0HxzqzOVl+cpw/fm2XvdcHbVX2uaNtjrf7G5WO9LrSCc0qQnGwdn50nQ8mbQJheRNc0dUboznJjNvljCIkLzDe0JGckQf1KFqkKBOrO9OLqytPzbTieMxMc3MzSXs2nhSmSZHQgHFRDCSiZlXvGUgFkWTQwMqqJEMNUxRIxlJSIHkMY/WWATx5kVsT0qYoS0ORMXowhF4aOngQ/MH78lmHkqDhbzbBL3aVq2BOSwj0k2uj97WWVwp+5nLXt249rrWTYZKvzUbBTfM7XzU5yfDzWpjdXUlTJtFVy5IKePpWNsdI+ZQQG3cGM/NJuxRw4LDObygntZ18DNIiXOIoQYJWpjf2j3R6fXyUkW0W16phhdbTR3IBYepelOHGaaAMGgtMxz7WiEGYqANtAWtp3VEIYEaYIK1fZIjKiFZ16yE3EycS6g5Isw5ziyvPrxw+MiI4sL6oY220x9MZzpv6HSnbkjSS6FjtkA5QI53FxeznjPObV5aI04DtB2ckKtmKXYYZ4waNq9t3DW/WAyns+Sd0Fmb7qxhdQa5MeUGed4k6fmylVm6RhG9p5OAZces4gJ1ESj7hB55H+9A56JiRDJcFxNSncc4kGi0Yx93C6ldFn2ZRKIlg084PlalOb/4ZJw7eXWzLPzZZjppOgttMUgNM1SOeTihWWtadWma2Zqs9T55QZwkiiRlymmDxuBSNqNZaWpYW6Az9HbIIma0LtRZVrvQqBSWSqMVMuHR5d7b3cKRqW+Qa7PlheWqO6rvmXazzckVm0ax03n8ypXFr1iZeTyk+3Mtxlu7hf3OztYFlyklnV1p2plGpXaxEtf8v+3ByY+l13nY4d8ZvunONVf1xObU4iDLlChZUSQhhhPDcYwgWXjpff41J7sgQAAHiR07kQ3JEilKFAeRbDXZXV1z3brjN5xz3jfdSgIjQDaN3tBgPU8mUp3iPw7rrVtb+91i38QrG97tQuoXtDkx8nwcz0oVMYqpsfQtBT6lvMNBJ3QKvkeEpnUxlEJCeCoXHA5MSICCpXOAQTKSQdQQk+Epg13XL43GWWq7Sf+j5WKZ8pK8o64DJfzBi6+8WO6viKexRiFTYw2pw2me+xBTICUMoKjFCBoRsSAEIVjEgXNgUBAMouCVuxXfHB/cKIatsT++evTT9YnN4mY5LHwxje2DsHh1e/Pt/ds3TWG7tu063+vVRXnaK346Pb9SwXi0MxEHARXnnPVWXbK205bV1cvDkaw7u7n54Wy6sJ5kSJHn43hWjiwrmxTpe6SmiTuJW7BdOZsU0TwEn2KGFKQCXXhVweNELU/kFpNUwJIA40jeiS1IYpJ6TCI4naUw9rrX72VleRrkuIkLukTMle/tDb/Tu3PgJ6eyut/M6EETyhh7ihdCFHG4yqkknjA2oQEErLUaFAcOvMVaxBGNQRISDRvwrd7469VO7rJPwuwvLh5/mGIza159Yb9dt22MA7Ff37211ZlJUag1qci6vPikXr4f618uFmqs10xS5yFB5AlBTESSScF3szrdHfaqyGi8e1G3j1fLZDIk8nwcz8ralAQSGoh6M/L7473vHtzdG21ujkaDDsJaSALiTKspFqji1YMTY3BAchGjJEAd4gdYS2iNUFAFYmGCpV6tXt/ddE2gGH46my9MwMs+/NHBS7tnUtj8vYsvvrCt73TX8GYv/8Z4YzuzXds1SkINWKPGGjWCM6gpcLlqLhoSGIc6gumBg2gVpxPhB+ODvZgvrfz44vMHIVU9eoHbW/20XPfzcrc/3MRJ3dROH4ZlXRYXIf394eEv1qvGg83KmDKs4KJVHCg5oljNINc2aaXxxdGur9W58v7qosarRp6P51mphQj4wF3Lv73z+quplxbtWcFgND64UW0c6/vLqympBRxY8EbFJOWpCMl41IOKi9ZmoHSG5JSUyKBWqPii43G7einm+/3eyLsTZxBe2aj2WjdK7rxerdqZy/i25c2D7XuDnbzjqmteHI3fn59/NGtbiCA+4SwIahGb4SZIAdOAGnLoQ0QahQwf2Rlupnlzpu1n6/ZOxttbd/3WXK8W/by3XfXmF1chBrs9+snJg/ens61B5aN7JMyBDIIUaKSoCRRKooxUEEhLm5GEio9nzfe2fLWIN7JiA46l47l5npXNiLGXM2h5w1evaNlfh6uUisqTYuH0xa1R7ZvVojlNGEPTgGinHc7gMkSMugws9HCdILRKssgAUqQHi2DUqpY8urr42tbdEjar0rSLnvDm7k1/EWJRvn/6cAW7nj/d3u3bKls0su76vtjqj8cbxVY+/dnZdAnrBF5Qg9GkEiHxlEMiT4gBj4IglEXms2JdtA9ZLuDbw8kP2ZiiKrHnM5p1blnm8ml79jfz2YXni2W9AQmccSkqIWXYjkxtxxOJEfSgQ2japQXPDB6vly9Wm9TNnh1+aBYknpPnWQXFEFvu9sy93ji16/PMz0bZlKYIMrJxN7fF1taNXjdtbdT8k8XRaYpTJJoWqwgKQmZIFjIkEQrY7/V3Sl+pNqlfz89mnTjLVduJxUrcKLNiIXeG2b7rGR8+aec/4bSG17bHL9f+Is7n4PKyZ0yxjgc2s6P9ZdN8sqij0gZwiqSIsZhHDhScYKWLtk5YBIXEuKpS22jhzlaL5NkdDydX61VYDyfDtm5D6KpBf1HaXzz6/MLBgHbBKniPceKSRkc0+ABYBSbwCmz4QePc0nER26OuUThvGred5c0qrywJEs/J84wKbOe9DXE7K3cHg2D9fRPfOfn8crn8RpG/vjXuebOv5o4bpf5gJdlLZfXBxcl77exYRE3AKs42ihNKnog5HFTl7+7svtIbj6M8HG7cf2e6ippIqTTz0FRFOcCM4N5wU6Zz6Y0/WF08gj7c7U+4ahkPZhXTLmTNapt+6coB9t7erUV6sF4HEq3yW1GxTKCDBBacLBpyAccTo7Jyy3ZjUJSrdn/A5qBsTlb9ybCOXRvbflGu16usHPpIZgk1uKINJGxUxTmRuKAThphohJcqvtnf2xvfiGXeSvzNF49/3p2cITWhNnVWEOqGHBqek+UZOdQkGeTs9gY+SJ2bn6+Of7VatrCdlVu+MG1bJCnarr64NMQXbfX7t1767mhzS8gzwQlZTDZ0Rtd0Ed305ocvv/KKr8qTaX/VUTcDcodRWASVwidpK+IEhk3arAbnbfur+fkiI+8x6GTu3Wzc/6vLw3//8Pg/nBz/ZH56ZrFZMTL523de3oISigwMee4tgoNAphDAgyMYKMAgEvtqJhft96v9f7F7s7dujJdLCWFYrUv36dnj4WSi6/DW7o2bHaOaognOZGsQozinlrXBEDK47fiDl1+4O5yk1bpdXhWz2dsb298e3CigI65SnUxTVRnK87M8I4OoSKaM82G/HJ6vV59fzaJyz2Xf3r3LtE5PFH5ewKvbH/fWTVcXdft7mwdvWuPnioPCYoFkPB7ePNi/GdhuZXs0mIb65PykoY7QGTpPhzjVogtjGHmnSWZNPUewOGXDFk1vcBjj/SgnJQ8tP6sXv5ieLZKUye0G/1Z/XEFI4IkxFrB1ymsNb9TcEwZzdiwDhQ4cn15crE3asMXNy/Tawu22eI3Bu9PQHLl4aOUw1mV/cLcY/8nWi99ldIB4bX2RkRs04iyeijgM/OBgtN0mWdb5sF8MypI0brs9m1eQTOxcpy6ZFEk8P88zSx6smGTzpqweXT6aN2w7fnf3lr1YbhaD1Xh80ix+eXh0dPQwe+Pm5nC4GXWc0j/ZvTm7ePTeGrViIjlkkRd6/vXJ1uCitlEfhO7dxfGssVeE5AtsTLmNKRnEp1RBvygDzNdtUhBsonDe4FWsikWFjOOWYnEx7g3e6G+Wdf07Wzc+Ws2OgQIJ3Bxu/tl4f6C+DvV8kt9fX6Hm9OLy3WZ96TmBD1ju5tlwOOxndj2/NM6ONZ9pc0j4LLKcnb59eyPveLk3OTCDwfnjn3P1RTunyNCEgbLsh+71YfVaNXSnM1cdfLK4umqvvl6WuZidKu/PUQ1C655ISsfzszyjhFhj1kk/Xly9W08/XF4KvHywPSzLJqZo8lWjQWxV+svIB784fG91clWkVbO+WVU/uHN7R+hFBjCGMby1c2ujEdN0l9r+1cmj/9nFQ7o14B3gXQ5Yi0gHZIVPxtYhZniClUibYj4P21Ic+CENqMGac9L9kyNNqdfqdrR3e6VVEJzj5sGtnWUazprtaLfm4VvFxjfd+E/ufv07vbFv6Cr+pj37Czl9sFd9btvfrK/mpdmpXd9XR6H7DH6x7B5qnGYa0c08+8Htl970ww1wbUBBLNaPiN/Y2atmqxK39O5vD++/d365iK1o16vyEjIDGnNrTbRIxnPzPKOAOGUNP52efRCXF0G3c+5sbHWtOR+Ys5PHBy/c2R5sdeR/SPjo6Pj942kV0nc291Pb7Rv/GvwGliAwgVeKcTaf0y8+mZ98DIsCX9MAIkQ78aV3Tr1pNBiw3ofONFEUazARmUscNDoJZpdyqLMukNmso3tE/WhxueWHtutemOwMjx7OAsaYEKXdHYQuuCguNG7ZETTzvZEte8zmmbnftgt3+UUd88uL22XWL9m9UsbucNmsMs4DH52e3h1umEG/my62isGbW5vT8+VHKc6jBO9ZhV0GVRtLkXwwev/07BBKaDVFBKs5ZuB9rkZVU7RGSyXwfDzPKFm8oPipYdosHbzaH+9r1mXpx2cPZvBWXR8UvTzrHRT+3p1hc3784cX8xmR0M3fFOn5jY/d0evoYPHyr3BqtxXQ6H7kfP1ysShBWGQgEHardd4MM2zhdSGfAWhusriUFDNiWcJ66QT48kZjlxoBTVYktLOCXs8PbN17ZTH6nGOwb5h3WmvuHh3/uV5PAVscbO7cKY+xk+MHy/PPVlQW7VjGcaTo7vNiJvHpvL0ZS5o+b5dEaPBk8PLu4PJ+e3dh6czLxs8tbw/53+y+c3f8sQJn3F3U38r3S+4Hvf3Y5/XjRLn0VYp1VA11Ll6KBUVl5kZi0jUYpYMHzsTyrHAOlKTAZNtvNireznYNZWsyvPhA+h5+cPvrZ6ePVqFrGKLPFPzt4bQzvPHy08FIYd3u4mZdF6tGW5INeGYy37kE7/wKiULR0HgyFmj2yA9f3mBVhGlBARLDRZQGvztdw3C5irmvfslF1OZ2jQ6LlqsfHKX7UXMYq6+Ne6o9Hik1ctIu/67pfha4ufFlV2WDwedb+t+Wjd7TtMINoh4FqhUnc2MhvFhuTpb0cZr/pFtHjhRcZHOBnKn9/dPbr7nzmVk6a3TK70ytzsE0wavIq7/erYPT+dHWGJF/6LM/zQq1puhp0o+xn6kKX1mowBc/N8qwcDrwa1CJ+x/Tu6WhzkS6PTmvLKmPztXtXpf8vv/r5YTs3vWJzLt9+8bXDjou20SSV8cPdLcaOgllXV1lurH+0mjcZ0rFLgeGJAiZkE1eo6lrjWuggpWScNUWWcGRZC5fr1bSbr3PVraotiYbkIAfDZcFni7PGqUt6s79VgcMaXCqIA27ffcGqvapXP3rw0YcxxAlrdIjfwg+C3BsNv3Xj1fGK8VU6z9NxWuMZuOw7t1/741d/cFD2F8rf3Z8utV3X87RavXjz5qYrorQ9fH84WDX1ollrzgwh6auvvWmMs9bFGA1UeWGULoaahM14bpZnlehwLeqj9pr63kZ2OVq9s8XfIqruD8vb/2bd+9e9cab8p8dn/3n77qg93hhlDM1fnU5PhuPYrf6wql4/TtszbvZ3PzPZg97gbCF9pbTVjHxjPTJSzIuG0WpcpI22ml/lnxoOx+XDddxM9p7vesyx025j65355C/vXPW6xb96JN+fGyzirW/8aGXylsNGL+uuL9moyGrLchiXvXa05Adu785qeKblX1p+3CbfmfwKMg6H3YMsFvCnm7vfv4w7Z6ueL87cQXuVTWruhfB6Vo+W5wcb212f823+fLmeZpvbdXGrZWdcNISVXd5cfSpFPNzbfdDxKvxZxu/PlrXIZzdHHy2Pvon95rHdaG48kNGnrBk+4LlZnpWgpIQIeBi4vMSmFK6izjRJbpykShnAjPjh2efe+6xNLw03zSraLuRt2kh+OyeDwrkhbs/mVYgItdQtsSUhoWp5dWM/T1I3zbppkjKbNePNjdC0273+HtBC21iTmoumyPIQ42gw4rcEE1GFDlZdI2jh/NCBgLALe6OhGLnolg+mx+qIlmjAwJpbhu9tTKpO16lrBn5Wsa4XNUvAO2yM48iuWBoIhEUK9donmRi/qa4PuRo/6Jtk6s9P9uCPxzffntzai24v63fns2Wdtm/cXJTmmObKaQQCz8/yrBJPBEQIGYxxZdSurVtHyDCFpVn1m7DXLwUedee1jUUX71YbDi6n54Wz/S680BtYmK2vbFMPV11fQhDIaE2sqXPkjVH5cjW0IZoiO1tcJRC4nM8Suu2q3xuNDxJuvVLXrU9x4tuUqt4ABQVnIyRDC9N6GaFvsx1b5gESN2G3KKOLp7J4uJ7jwatYiAwTb2Xj72+8MEouZPai1LO+dK7tEBxV3zhN/RD3W92NEDBClOAwI5tvm7wHmYbL9Tp32Z3+1rd62y/2JnbdmBQzkaMHD/uQjwfTnLPKfN7MO0B5fpZnZAQDiYRLDgrIQ5K6VgFLWVm6Jm/b3aLKoG2W59oiOjIZ8OH8QvoZdbc3ngQ405UtbE/iZlaohx5UCSMTeHv7YLhsQwhd5Q9DLWDhw88f+FFf6/qt0d4/LQdbkMe6hK6JUpRB8YAiSAdiiXBar1qrPZPvu96gg8g2ZBqjSxdx0TmwPCWg3Or3X662B0stXbHI5MK160y6vKmhAzvwwUcx3VDlDvTWZBCdrFxMBu89ECCoSUGc2OHWxoWPi5v984Py708+O1NevrEvqyYZu3L8ZnGVeMLy3DzPyCkCWMGqJp7wmAFuqJjAxPsykwzG3u/Aes1ybHsY59wS5nDsu31NlS9MwWNZrCqZtN3OuO+u2pBwaxnCm8PiBbyrV2k4+ODq5BIEApwjFzYNYTPy7cmOrt2vmtmNzsRkZDKYX57kxkVVjGIRS0xchro2sqlmz1YDmCu9nm1CLTnLdpk5QktfCNAZxLr+cNKswoJ4f3GxtTHaJIu28eATZe7WWVoU4nB3dXQ0n/fAZH6e1Nowz7SBLqNtknXFKtWNS+9dPV4V/YvF8mIavzcwW0U/W0kc5Mcnj2tQIFoQno/nGSWDVVDBEgxLo9Fl277/KpctTIIkQ+vNqOp/Z6d7JaSsrEwySd05UHK/nt3Ix0Wy3nG0DidxsWd1p+y90lwulRxuTux3tg+yRe175VnP/ejBWc1TCkv46wcf/8s7r8jFetCmb23sbrvJ7aVps2JudNo2TixGMYK3qCSYwcroJKYNWxT81qgIRnOjGlIhZMIAGkjWfbGYnx7g+9mvp48/vpr/cDQcJb9b6T1IOfuujM4sSpNwN93wm8vFKK92qqFEuXR6HNaNB8dvmvRqllejbDq/PF2FTz67qi1f3+aNvZft43qzt3HYxl8vzyPgPWJ4bp5npAZRUJ5YKkfSzXIz9NUPx/uz1I6iXToWuYr3b23eKmvTSKvJX17MOoiGi7r21W4/yE6WnzTd48X0673t3az/z3dvdCvdaF0axQN8JC1K/+707AjEG5ucURY2vrtcvayt77ki6Djq13qDQrJLGz84P7nsmoDgHAZQEtayEuZWd6L2XOYgWuZWjXcVbmisEfFgIeMJW6PvXBx/gbk/O5/BfNUUfvBCNL2NLTVxoMMUdZE7Y+xmLPY37/aNz0M59XolzdFiEQUSj+CTi4vXq82t1v/R3u034rwmvHZwa6+2Fr9Qfnl6OIW1sxiLGp6b51llXlvJkZhMNHqU4jHcNPm98cFstfRks0pneYqxe7HLJpfxaGDF548vDn1GjIhkybjclkPbs9Ldv7w4y0aDpvvacGKi7Oe9h/ZyNZ37jY37sfkfR+frHIKWahVtbdb68JcPP8sPXvydg514MW2Pz9vJjQtv3j87miEKJCWzpOgEa2lg5U2dMM4r4Liq26yXDUO+6XoSlvTNaqUGmxLkvQ8uTiriGjLPUd0sJm4wrTfsyDi7ajh0bTN0hXXD2u65LG+5mod66BbOTOsEOMtRxvvT0zdGB3tdvk/vd6QMpV09bufr9fYLd989P/5RnM0sLUJS1PLcHM/KZSRTYVUkGdrQ9rPe7clefTkd9gZzq79cnP1ydRlctmX7+7b6vDI//eI39+nOrD5xMyvu9Ddrl/1iOX0Ym5VoZdLeeOyDyVrrNV+VK/rDT9fzvz58dGTAYlyVJ6eQDDidSVqtVxpjWbi8yN47u3r34uhB7BqwGEFBLOSKUTp46cbNnbxft+Fny5NFxa6xr+h4t81Tr/jJ8nLVoxWiOMWBQw2YBumAFMqiuOurFJLarO5Xn4blu8cPV1Hu7t2Miy6vhosy/2B59e7x43NiAoRQEkWqRl7sbU7Em1UT2laqMu5v/ujsi785P3oMtQPrTTIFJpF4Pp5nZTxEiA6MMovy8eIqD+aV4WiW2gezy3euzh9BMa0vOPs1vEM6gxmZGE9Mp6v6vWquVfWgXVOYRdIfLeZt6r422R+PJ4fJrOr14fnJ++vmEMgtec/VKkSDAQuK4aNVs1wevQ8FLLEPkdqB4kQtJCXDOJQnMs66+nGyy3pRA47HM/Gjvk67zXEx8awUeqRaCSDG4gwmN7Yz7eeR/7482S63+sNxyLJPlxfvXB4dBzbai8WyfaHcjM3scd181s3O6DoM3iiC47zlb2cnKYWvb+6NRv2lhgsf3zn69WfN+tigFnA+GY86pOV5GZ5VMaJte7QWGkj4CruN2bHGSnuJHsPKgTIRNuCBQR0UQ6Ip2uUI6UOb+SMRBgVaU7MZuAnk1VmQu6Y9Fi5hAepLEl41RwRanGYOE9HgVIaRHASmkDIwxkUV4YkSMmihNYyce1mqIKv76HpMPuPf7X7th93wxNT/sTj9r/UZBqJl7ZDCg0cdrOwapzjebPCWpbWHURoDhh4UQt/RYuaJFm+wziIEQSg8XUQYGLYKSktMLCxnATU8lUwu3mgCEkSE5+N4ViZHgkUsGIi4gF0SLjScwwxab7To4V1DvLKQgzckaM0mxpEuYaqC84jFeoiSmMEjjctKZ53OYQ0u60nEoZ7Uc3nQYLCCRS3GqE2NZeVAXOcMzmDQyBMGckyODWhytElVQ4cuIfVtEs3mze/dvreaTf1m/3G6mq+EqN5kYJLyhCFaJWVCgbQ8Vs6sxgyc99bYqB1MLWtHKkpMbpPzGq0mlCoChtK0TmfCRWSaWAEZv+WtZEZSgmBEciXxnBzPSgsIIAoCgsM49aRKgiUZ1DjEI+AiOURQSL4njDEgC1Qy0JLO0SWLeCWAlIbRsEttBNSqZBZnEUsnGgwqIOpQCxajWMETo+UpIYEBgzF4NQntQKsSkcr3g3TBmIRiXRPa24Nxz2iRWaPxarY2kVxSUiMOURzqSQHFkUfWHkpPMSSYrIs5ag2dgxzIaU0OOSlDcshwiiYRFZylZ6nACDE4xJOcigioTeRQQMdzcjwjg4NOHckgPOFwOV5JEQUL6pECMZhEpr5GHESXYQ1hRao9T5jox/gSdRIEBBSI4BIKkqFOUZwajY6nEirGYHOrXgGn5ELrwCCCggcPglGeCFgMYJKgKhEEiAaj8/XshYPdsltPrJt4Zd0KBFJnoXQmSoGgmiwuEgC1BEtnCtQhAfBIgA6HNzyhhqSgFAlEsao+kSWcYNUasqQZWDAYxQqGpyLPyfGMCpyQ8ODAAA4cKbgkDsSCLUiFU80lZp1kEBNYm7ANsXFKBZkznRtgSkiEZDAVyWYEA4lEJrnisKquFTTjqc6AtajLVDNVjUGjYjOrpkCNUXFgeErwLotGcQ5ISS0md3nC5MlI4U7bdtLjwNjNKNtFbyC2r34p3dQIuZcoFeKghdLSOSBDM9TnzgcTWlQFL2Q4wUfvY546jcHQqouoOqtGEkSIWEMR8ZCBNWARq8klbER4Xo5nlGETggfLU8YhzmksEacEAXGoL9AhoYc4Sw141KJeKZQckhqpeoJDAnFtSAoRxJElE/BkCUtmsAE0Uwx0FhykLMNViEUxktRa1QxVNBmwYLBKElHrcAbnSOpAVY2mEW4hIQ1YXSzfGg+H61BhB73BrZsvXHbxYWrECFEK1EIDIogB4zEZokHaaCMFKIXg8B0en/ABJxiwBQ6ckCs5aknGBZyoVSwYQ8pJOdKDHqx5XoavmhyyjBAI3Cqq7995+ZViw85Wk8nkl48e/OTy/iOYG8jAGJ5olS83x1eOJQnWIhpDXF6cSxt6w94itY8W0+N2NYdgeUpALKp8uRm+YnJsQhJkhTWdlMoYbvTG1trz1fxE0xqixaixqg7bIXy5Gb5iNvAt0iHRkOXWiJiABwsddKAGDF7IsQV2SuTLzfMVY7E5JqCgIQkGCoIgAgYsGEMA1PGPg+crpkUDKWKwgPJEQjwoTxkQBRK2A8XwpWf4ijF4BYyQeyy0HU8oTxmeMoBFjVVnIGnHl5vhq8Z7rEUSqoigZAajGGMEjRhFsRZjwPBEjHy5Wb5qLKiAIhjFg1UsGFWrOFWjIIBiwPLlZ/nKERAEFIe1PKWgPGXBYVEQQED40nN81RiDgODBIkCCAImnLBjUYET5P0T5cjN81RhQcrCgECEZcDyVcIoHAwIdYED5krN89TgwYMGAABYsWLAIGLBgwPGPg+WrxpD4B5b/h+UfJMDw5Wf5qnFgSTxlwQAKCgqKActTCbDg+PKzfNVYsCgI/5eCgoLyvwkoYMFy7dq1a9euXbt27dq1a9euXbt27dq1a9euXbt27dq1a9euXbt27dq1a9euXfv/+1/XAXuVQnPmwQAAAABJRU5ErkJggg=="; // Replace with your Base64 logo
    doc.addImage(logoBase64, 'PNG', 5, 5, 15, 15); // Adjust position and size as needed

    // Add header
    doc.text("Katagesma", 25, 10); // Adjust X position to align with logo
    doc.text("Hanwella", 5, 20);
    doc.text(" 070 581 1581", 5, 25);
    doc.setLineWidth(0.5);
    doc.line(5, 27, 73, 27); // Horizontal line

    // Add invoice details
    doc.text(`Bill No: ${billNo}`, 5, 32);
    doc.text(`Date: ${new Date().toLocaleDateString()}`, 5, 37);
    doc.line(5, 39, 73, 39); // Horizontal line

    // Add table headers
    doc.text("Item", 5, 44);
    doc.text("Qty", 45, 44);
    doc.text("Price", 55, 44);
    doc.text("Total", 65, 44);

    // Add invoice items
    let y = 49; // Starting Y position for items
    invoiceData.forEach(item => {
        doc.text(item.name, 5, y);
        doc.text(item.quantity.toString(), 45, y);
        doc.text(`Rs: ${item.price.toFixed(2)}`, 55, y);
        doc.text(`Rs: ${(item.price * item.quantity).toFixed(2)}`, 65, y);
        y += 5; // Increment Y position for the next item
    });

    // Add total amount, cash, and balance
    doc.line(5, y, 73, y); // Horizontal line
    y += 5;
    doc.text(`Total Amount: Rs: ${totalAmount.toFixed(2)}`, 5, y);
    y += 5;
    doc.text(`Cash: Rs: ${cash.toFixed(2)}`, 5, y);
    y += 5;
    doc.text(`Balance: Rs: ${balance.toFixed(2)}`, 5, y);
    y += 5;
    doc.line(5, y, 73, y); // Horizontal line

    // Add footer
    y += 5;
    doc.text("Thank you for visiting!", 5, y);
    y += 5;
    doc.text("Visit us again!", 5, y);

    // Save the PDF and open it in a new tab
    const pdfBlob = doc.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, '_blank');
}
    </script>
</body>
</html>
