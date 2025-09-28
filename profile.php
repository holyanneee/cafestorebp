<?php
// profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

@include 'config.php';
session_start();

if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Flash alert (from previous POST + redirect)
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

// Helper to set flash and redirect back to this page
function set_flash_and_redirect(array $alert)
{
    $_SESSION['alert'] = $alert;
    // change this if the file name is different
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch fresh user
$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_user->execute([$user_id]);
$user = $select_user->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------- PROFILE UPDATE ----------
    if (isset($_POST['update_profile'])) {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        // basic validation
        if ($name === '' || $email === '') {
            set_flash_and_redirect(['type' => 'error', 'message' => 'Name and email are required.']);
        }

        // Avatar handling - input name "avatar"
        $avatarName = $user['image'] ?? null; // keep existing if any
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowed, true)) {
                set_flash_and_redirect(['type' => 'error', 'message' => 'Invalid avatar type. Allowed: ' . implode(', ', $allowed)]);
            }

            $uploadDir = __DIR__ . '/uploaded_files/';
            if (!is_dir($uploadDir))
                @mkdir($uploadDir, 0755, true);

            $newFileName = md5((string) time() . $fileName) . '.' . $fileExtension;
            $dest = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $dest)) {
                set_flash_and_redirect(['type' => 'error', 'message' => 'Failed to move uploaded avatar.']);
            }

            // delete old avatar if exists
            if (!empty($avatarName) && file_exists($uploadDir . $avatarName)) {
                @unlink($uploadDir . $avatarName);
            }

            $avatarName = $newFileName;
        }

        // Persist user update
        $update = $conn->prepare("UPDATE `users` SET name = ?, email = ?, image = ? WHERE id = ?");
        $update->execute([$name, $email, $avatarName, $user_id]);

        set_flash_and_redirect(['type' => 'success', 'message' => 'Profile updated successfully!']);
    }

    // ---------- PASSWORD UPDATE ----------
    if (isset($_POST['update_password'])) {
        // Re-fetch user to ensure latest data
        $select_user->execute([$user_id]);
        $user = $select_user->fetch(PDO::FETCH_ASSOC);

        // $oldPass     = $_POST['old_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        // Trim to be safe
        // $oldPass = trim((string)$oldPass);
        $newPass = trim((string) $newPass);
        $confirmPass = trim((string) $confirmPass);

        if ($newPass === '' || $confirmPass === '') {
            set_flash_and_redirect(['type' => 'error', 'message' => 'All password fields are required.']);
        }

        if ($newPass !== $confirmPass) {
            set_flash_and_redirect(['type' => 'error', 'message' => 'New passwords do not match.']);
        }

        // Minimum password length (example: 8)
        if (strlen($newPass) < 8) {
            set_flash_and_redirect(['type' => 'error', 'message' => 'New password must be at least 8 characters.']);
        }

        // Ensure password column exists and isn't empty
        if (empty($user['password'])) {
            set_flash_and_redirect(['type' => 'error', 'message' => 'No existing password on file — contact admin.']);
        }

        // if (!password_verify($oldPass, $user['password'])) {
        //     set_flash_and_redirect(['type' => 'error', 'message' => 'Old password is incorrect.']);
        // }

        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user_id]);

        set_flash_and_redirect(['type' => 'success', 'message' => 'Password updated successfully!']);
    }
}

// Re-fetch user for display
$select_user->execute([$user_id]);
$user = $select_user->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile
    </title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="min-h-screen font-sans text-gray-900">

    <main>
        <section>
            <div class="flex items-center justify-center p-6">
                <div class="w-full max-w-3xl space-y-8">
                    <!-- Profile Section -->
                    <div class="bg-gray-100 rounded-xl p-6 shadow mt-10">
                        <h2 class="text-xl font-bold mb-6">Profile Information</h2>
                        <form class="space-y-6" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm/6 font-medium text-gray-900">Name</label>
                                    <div class="mt-2">
                                        <div
                                            class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input id="name" type="text" name="name" placeholder="janesmith"
                                                value="<?= htmlspecialchars($user['name']) ?>"
                                                class="block min-w-0 grow bg-white py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email</label>
                                    <div class="mt-2">
                                        <div
                                            class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input id="email" type="email" name="email"
                                                placeholder="janesmith@email.com"
                                                value="<?= htmlspecialchars($user['email']) ?>"
                                                class="block min-w-0 grow bg-white py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Avatar Upload -->
                            <div>
                                <label for="cover-photo" class="block text-sm/6 font-medium text-gray-900">
                                    Avatar
                                </label>
                                <div
                                    class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                    <div class="text-center">
                                        <svg viewBox="0 0 24 24" fill="currentColor" data-slot="icon" aria-hidden="true"
                                            class="mx-auto size-12 text-gray-300">
                                            <path
                                                d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z"
                                                clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm/6 text-gray-600">
                                            <label for="file-upload"
                                                class="relative cursor-pointer rounded-md bg-transparent font-semibold text-color focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-indigo-600 hover:text-color">
                                                <span>Upload a file</span>
                                                <input id="file-upload" type="file" name="file-upload"
                                                    class="sr-only" />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs/5 text-gray-600">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Actions -->
                            <div class="flex justify-end space-x-3">
                                <button type="submit"
                                    class="px-6 py-2 rounded-md bg-color bg-hover-color text-white">Save</button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Section -->
                    <div class="bg-gray-100 rounded-xl p-6 shadow">
                        <h2 class="text-xl font-bold mb-6">Change Password</h2>
                        <form class="space-y-6" method="POST">
                            <input type="hidden" name="update_password" value="1">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Old Password -->
                                <!-- <div>
                                    <label for="old_password" class="block text-sm/6 font-medium text-gray-900">Old
                                        Password</label>
                                    <div class="mt-2">
                                        <div
                                            class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input id="old_password" type="password" name="old_password"
                                                class="block min-w-0 grow bg-white py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                </div> -->

                                <!-- New Password -->
                                <div>
                                    <label for="new_password" class="block text-sm/6 font-medium text-gray-900">New
                                        Password</label>
                                    <div class="mt-2">
                                        <div
                                            class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input id="new_password" type="password" name="new_password"
                                                class="block min-w-0 grow bg-white py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium mb-2">Confirm
                                        Password</label>
                                    <div class="mt-2">
                                        <div
                                            class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input id="confirm_password" type="password" name="confirm_password"
                                                class="block min-w-0 grow bg-white py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-3">
                                <a href="index.php"
                                    class="px-6 py-2 rounded-md bg-gray-700 hover:bg-gray-600 text-gray-300">
                                    Back
                                </a>
                                <button type="submit"
                                    class="px-6 py-2 rounded-md bg-color bg-hover-color text-white">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </section>
    </main>
    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
</body>

</html>