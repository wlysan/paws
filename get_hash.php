<?php

// Adicione este código em um arquivo PHP temporário para gerar o hash
$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;

?>