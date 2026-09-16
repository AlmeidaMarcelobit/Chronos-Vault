<?php
// config.php
date_default_timezone_set('America/Sao_Paulo'); // Definindo o fuso horário

// Outras configurações de banco de dados ou sistema podem vir aqui
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nome_do_banco');

// Incluir o arquivo de configuração em outros scripts
// require_once 'config.php';