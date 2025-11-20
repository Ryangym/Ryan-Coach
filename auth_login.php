<?php
session_start();
require_once 'db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    // Busca o usuário pelo email
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se usuário existe e se a senha bate com o hash
    if ($user && password_verify($senha, $user['senha'])) {
        
        // Salva dados na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_nivel'] = $user['nivel'];

        // Redirecionamento inteligente
        if ($user['nivel'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: usuario.php");
        }
        exit;
    } else {
        echo "<script>alert('Email ou senha incorretos!'); window.location.href='login.html';</script>";
    }
}
?>