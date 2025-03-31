<?php
// No início do arquivo index.php
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    include 'debug_mode.php';
}

include 'config/config.php';
?>