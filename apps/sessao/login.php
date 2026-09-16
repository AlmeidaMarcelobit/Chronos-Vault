<?php
session_start();

// Credenciais fixas (exemplo simples)
$validUsername = "Admin";
$validPassword = "n/m=mRi0TJF5";

// Verifica dados enviados
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $validUsername && $password === $validPassword) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;

        header("Location: ../../pages/includes/home.php");
        exit();
    } else {
        echo "<p style='color:red;'>Usuário ou senha incorretos.</p>";
        echo "<a href='login.html'>Voltar</a>";
    }
} else {
    header("Location: apps/sessao/login.html");
    exit();
}
