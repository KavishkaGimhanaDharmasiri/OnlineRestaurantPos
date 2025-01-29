<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yes/No Buttons with Links</title>
    <style>
        /* Style for the modal overlay */
        .modal-overlay {
            display: flex; /* Always visible */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            justify-content: center;
            align-items: center;
        }

        /* Style for the modal box */
        .modal-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        /* Style for the buttons */
        .modal-box button {
            margin: 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-box button.yes {
            background-color: #4CAF50;
            color: white;
        }

        .modal-box button.no {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Modal Overlay -->
    <div id="modalOverlay" class="modal-overlay">
        <div class="modal-box">
            <h2> Product Added Successfully,
                <br>Do you want to add another product?</h2>
            <!-- Yes button with link -->
            <button class="yes" id="yesButton" onclick="window.location.href='insert.php'">Yes</button>
            <!-- No button with link -->
            <button class="no" id="noButton" onclick="window.location.href='dashboard.php'">No</button>
        </div>
    </div>
</body>
</html>