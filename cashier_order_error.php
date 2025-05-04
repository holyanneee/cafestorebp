<?php
// You can include any necessary logic here to check if the order was successfully placed.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .button {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #E74C3C;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
            display: inline-block;
        }
        .button:hover {
            background-color:rgb(223, 131, 121);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>ORDER ERROR</h1>
    <p>Cannot complete order, please wait for the Barista to finish the Order.</p>
    <a href="cashier_orders.php" class="button">Go Back</a>
</div>

</body>
</html>
