<?php
require_once 'enums/OrderStatusEnum.php';
use Enums\OrderStatusEnum;



if (!isset($conn)) {
    die("Database connection not established.");
}

$pendingStatus = OrderStatusEnum::Pending;

$query = $conn->prepare("SELECT COUNT(*) AS pending_count FROM orders WHERE status = ?");
$query->execute([$pendingStatus->value]);
$result = $query->fetch(PDO::FETCH_ASSOC);

$pending_count = $result['pending_count'] ?? 0;

if ($pending_count > 0): ?>
        let audioAllowed = false;

        function enableAudio() {
            audioAllowed = true;
            document.removeEventListener('click', enableAudio);
            document.removeEventListener('mousemove', enableAudio);
            document.removeEventListener('keydown', enableAudio);
        }

        document.addEventListener('click', enableAudio);
        document.addEventListener('mousemove', enableAudio);
        document.addEventListener('keydown', enableAudio);

        function sweetAlert(message) {
            const audio = new Audio('./audio/alert.mp3');
            audio.loop = true;
            audio.volume = 0.5; // Set the volume (0.0 to 1.0)

            const playAudio = () => {
                if (audioAllowed) {
                    audio.play().catch(err => console.log('Autoplay blocked:', err));
                }
            };

            const stopAudio = () => {
                audio.pause();
                audio.currentTime = 0;
                <?php
                if ($_SESSION['cashier_id']) {
                    echo "window.location.href = 'cashier_orders.php'";
                }
                if ($_SESSION['admin_id']) {
                    echo "window.location.href = 'admin_orders.php'";

                }
                ?>
            };

            Swal.fire({
                title: 'New Orders!',
                text: message,
                icon: 'info',
                confirmButtonText: 'OK',
                didOpen: playAudio,
                willClose: stopAudio
            });
        }

        setTimeout(() => {
            sweetAlert('There are <?= $pending_count ?> pending orders!');
        }, 2000);

<?php endif; ?>