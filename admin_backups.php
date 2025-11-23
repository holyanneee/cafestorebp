<?php
include 'config.php';
include 'services/export.php';

session_start();
$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

$backups = [];

$selectBackups = $conn->prepare("SELECT * FROM `backups` ORDER BY created_at DESC");
$selectBackups->execute();
$backups = $selectBackups->fetchAll(PDO::FETCH_ASSOC);

// Handle backup creation
if (isset($_GET['backup'])) {
    // Check if a backup already exists for today
    $checkBackup = $conn->prepare("
    SELECT COUNT(*) 
    FROM backups 
    WHERE created_at >= CURDATE()
");

    $checkBackup->execute();
    $backupExists = $checkBackup->fetchColumn();

    if ($backupExists > 0) {
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'A backup for today already exists.'];
        header('Location: admin_backups.php');
        exit;
    }

    // Generate a unique filename
    $filename = 'backup_' . date('Ymd_His') . '.sql';
    $filepath = 'backups/' . $filename;

    // Create backup directory if it doesn't exist
    if (!is_dir('backups')) {
        mkdir('backups', 0777, true);
    }

    // Perform the database backup
    $isBackupSuccessful = backupDatabase($conn, $dbname, $filepath);

    if ($isBackupSuccessful) {
        // Get the file size in MB
        $filesize = filesize($filepath) / (1024 * 1024); // Convert bytes to MB
        $filesize = round($filesize, 2); // Round to 2 decimal places

        // Insert backup record into the database
        $insertBackup = $conn->prepare("INSERT INTO `backups` (file_name, file_path, size_mb, created_at) VALUES (:file_name, :file_path, :size_mb, NOW())");
        $insertBackup->bindParam(':file_name', $filename);
        $insertBackup->bindParam(':file_path', $filepath);
        $insertBackup->bindParam(':size_mb', $filesize);
        $insertBackup->execute();

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Database backup created successfully.'];
        // Redirect to the backups page to refresh the list
        header('Location: admin_backups.php');
        exit;
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Failed to create database backup.'];
        header('Location: admin_backups.php');
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Backups</title>
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        /* [Previous CSS styles remain exactly the same] */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            font-size: 14px;
        }

        .admin-container {
            margin-left: 220px;
            padding: 20px;
            margin-top: 80px;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            border: none;
            margin-top: 50px;
        }

        .table-responsive {
            border-radius: 6px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
            table-layout: fixed;
        }

        .table thead th {
            background-color: #4a6fdc;
            color: white;
            font-weight: 600;
            padding: 10px 8px;
            font-size: 12px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 8px;
            vertical-align: middle;
            font-size: 12px;
            word-wrap: break-word;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .status-completed {
            background-color: #e6f7e6;
            color: #27ae60;
        }

        .status-pending {
            background-color: #fff7e6;
            color: #e67e22;
        }

        .action-btn {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 4px;
            display: inline-block;
            text-align: center;
            min-width: 60px;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-view {
            background-color: #e3f2fd;
            color: #1976d2;
            border-color: #bbdefb;
        }

        .btn-update {
            background-color: #e8f5e9;
            color: #388e3c;
            border-color: #c8e6c9;
        }

        .btn-delete {
            background-color: #ffebee;
            color: #d32f2f;
            border-color: #ffcdd2;
        }

        .filter-section {
            background-color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-end;
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pagination-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 10px;
            text-align: right;
        }

        .modal-content {
            font-size: 13px;
        }

        .modal-header {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #4a6fdc;
            color: white;
        }

        .btn-close-white {
            filter: invert(1);
        }

        .order-details strong {
            width: 120px;
            display: inline-block;
            font-size: 13px;
        }

        .empty-orders {
            padding: 20px;
            font-size: 14px;
            text-align: center;
            color: #666;
        }

        .form-control,
        .form-select {
            font-size: 12px;
            padding: 5px 8px;
            height: 32px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
    </style>

    <style>
        .text-gray-300 {
            color: #dddfeb !important
        }

        .text-gray-800 {
            color: #5a5c69 !important
        }

        .card {
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            min-width: 0 !important;
            word-wrap: break-word !important;
            background-color: #fff !important;
            background-clip: border-box !important;
            border: 1px solid #e3e6f0 !important;
            border-radius: .35rem !important;
            margin-top: 0 !important;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -.75rem;
            margin-left: -.75rem;
        }
    </style>
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column admin-container">

        <!-- Main Content -->
        <div id="content">
            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row mb-5 align-items-center">
                    <div class="col">
                        <h1 class="h2 mb-0 text-gray-800">Backups</h1>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="flex-shrink-0">
                                <a href="admin_backups.php?backup=1" class="btn btn-sm btn-primary">
                                    <i class="fas fa-database fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Create New Backup
                                </a>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Content Row -->

                <div class="row">
                    <!-- Orders -->
                    <div class="col">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h5 class="m-0 font-weight-bold text-primary">Backups</h5>

                            </div>
                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>

                                                <th>File Name</th>
                                                <th>Size</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($backups)): ?>
                                                <?php foreach ($backups as $backup): ?>
                                                    <tr>

                                                        <td>
                                                            <a href="backups/<?= $backup['file_name'] ?>" download>
                                                                <?= $backup['file_name'] ?>
                                                            </a>
                                                        </td>
                                                        <td><?= $backup['size_mb'] ?> MB</td>
                                                        <td><?= date('Y-m-d H:i:s', strtotime($backup['created_at'])) ?></td>

                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">No backups found.</td>
                                                </tr>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
        </div>


        <!-- </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // check if the filters are changed
        document.addEventListener('DOMContentLoaded', () => {
            const filterForm = document.getElementById('filters');

            const typeSelect = document.getElementById('type');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            const initialType = typeSelect.value;
            const initialStartDate = startDate.value;
            const initialEndDate = endDate.value;

            function checkFilters() {
                if (typeSelect.value !== initialType || startDate.value !== initialStartDate || endDate.value !== initialEndDate) {
                    // Submit the form
                    filterForm.submit();
                }
            }

            typeSelect.addEventListener('change', checkFilters);
            startDate.addEventListener('change', checkFilters);
            endDate.addEventListener('change', checkFilters);
        });
    </script> -->
        <script src=" https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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