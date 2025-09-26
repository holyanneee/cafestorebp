<?php
session_start();
if (isset($_GET['store'])) {
   $store = $_GET['store'];
   if (in_array($store, ['KM', 'ANB'])) {
      $_SESSION['store_code'] = $store;
   }
}
?>