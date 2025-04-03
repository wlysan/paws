<?php
/**
 * Arquivo de autoload do plugin de produtos
 * Carrega as dependências necessárias para o funcionamento do plugin
 */

// Inclui o arquivo de rotas do plugin
include_once "plugin_routes.php";

// Inclui os arquivos de helpers
include_once "helpers/category_helpers.php";
include_once "helpers/product_helpers.php";
include_once "helpers/admin_helpers.php";  // Adicionada esta linha
include_once "helpers/debug_helpers.php";  // Adicionada esta linha

// Inclui os arquivos de hooks
include_once "hooks/admin_hooks.php";
include_once "hooks/menu_hooks.php";