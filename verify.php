<?php
require 'services/mail.php';

session_start();
$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

// get email from url parameter if available
$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
$verification_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // get the expected code from session
    $code = $_SESSION['verification_code'] ?? '';

    // get user via email
    $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select->execute([$email]);
    $user = $select->fetch(PDO::FETCH_ASSOC);

    // check if the email already verified
    if ($user && $user['email_verified_at']) {
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'Your account is already verified. Please log in.'];
        header('Location: login.php');
        exit;
    }

    // check if code is expired (15 minutes)
    $isVerificationCodeExpired = $user && isset($user['code_generated_at']) && (time() - strtotime($user['created_at']) > 15 * 60);

    if($code === '' || $isVerificationCodeExpired) {
        // generate a new code and send email again
        $new_code = bin2hex(random_bytes(6));
        $_SESSION['verification_code'] = $new_code;

        $message = sendVerificationEmail($email, $new_code);

        $_SESSION['alert'] = ['type' => $message['status'] === 'success' ? 'success' : 'error', 'message' => $message['message'] . ' A new verification code has been sent to your email.'];
        header('Location: verify.php' . ($email ? '?email=' . urlencode($email) : ''));
        exit;
    }

    if ($code === $code) {
        // mark user as verified in database
        markUserAsVerified($email);

        unset($_SESSION['verification_code']);

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Your account has been verified! You can now log in.'];
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid verification code. Please try again.'];
        header('Location: verify.php' . ($email ? '?email=' . urlencode($email) : ''));
        exit;
    }
}

if(isset($_GET['resend']) && $_GET['resend'] == '1' && $email) {
    // generate a new code and send email again
    $new_code = bin2hex(random_bytes(6));
    $_SESSION['verification_code'] = $new_code;

    $message = sendVerificationEmail($email, $new_code);

    $_SESSION['alert'] = ['type' => $message['status'] === 'success' ? 'success' : 'error', 'message' => $message['message'] . ' A new verification code has been sent to your email.'];
    header('Location: verify.php' . ($email ? '?email=' . urlencode($email) : ''));
    exit;
}


?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-white">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify | Kape Milagrosa
    </title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="h-full">

    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <!-- <img src="images/kape_milag.jpg" alt="kape_milagrosa_logo" class="mx-auto h-20 w-auto rounded-full" /> -->
            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">
                Verify your account
            </h2>
            <p class="mt-2 text-center text-sm/6 text-gray-600">
                Please enter the verification code sent to your email<?= $email ? " <strong>($email)</strong>" : "" ?>.
            </p>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="submit" value="1">
                <div>
                    <!-- <label for="code" class="block text-sm/6 font-medium text-gray-900">Code</label> -->
                    <div class="mt-2">
                        <input id="code" type="text" name="code" required autocomplete="code" value="<?= htmlspecialchars($_SESSION['verification_code'] ?? '', ENT_QUOTES) ?>"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    </div>
                </div>


                <div class="flex flex-col gap-3 mt-6">
                    <button type="submit"
                        class="flex w-full justify-center rounded-md bg-color px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs  focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Verify
                    </button>
                    <a href="verify.php?email=<?= urlencode($email) ?>&resend=1"
                        class="text-sm/6 text-center text-indigo-600 hover:underline">
                        Resend Verification Code
                    </a>
                </div>
            </form>


        </div>
    </div>

    <!-- sweet alert -->
    <script src=" https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <?php if (!empty($alert)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: '<?= $alert['type'] ?>',
                    title: '<?= $alert['message'] ?>',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        </script>
    <?php endif; ?>

</body>

</html>