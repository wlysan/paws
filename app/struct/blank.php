<?php
// app/struct/blank.php
get_std_controller($rota['route']);

// Verificar se o caminho da view existe antes de incluí-lo
$view_path = get_view($rota['route']);
if (!empty($view_path) && file_exists($view_path)) {
    include $view_path;
} else {
    echo "<div class='alert alert-danger'>View não encontrada: " . htmlspecialchars($rota['route']) . "</div>";
}
?>