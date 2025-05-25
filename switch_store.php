<?php
session_start();
$stores = [
   'kape_milagrosa' => 'anak_ng_birhen',
   'anak_ng_birhen' => 'kape_milagrosa'
];

if (isset($_SESSION['store'])) {
   $_SESSION['store'] = $stores[$_SESSION['store']];
}
?>
