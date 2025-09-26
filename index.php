<?php
@include 'config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home |
        <?php
        if (!empty($_SESSION['store_code'])) {
            echo $_SESSION['store_code'] === 'KM' ? 'Kape Milagrosa' : 'Anak ng Birhen';
        } else {
            echo 'Kape Milagrosa';
        }
        ?>
    </title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <?php include 'views/css/style.php'; ?>
</head>

<body class="min-h-screen font-sans text-gray-900">
    <?php include 'views/layouts/header.php'; ?>

    <main >
        <?php include 'views/kapemilagrosa/home.php'; ?>
    </main>
    <?php include 'views/layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
</body>

</html>
