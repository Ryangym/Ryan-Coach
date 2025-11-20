<?php
session_start();
require_once 'db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpa os dados recebidos
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    // Verifica se o email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Email já cadastrado!'); window.location.href='login.html';</script>";
    } else {
        // Criptografa a senha (Segurança essencial)
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Insere no banco
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, 'aluno')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senhaHash])) {
            echo "<script>alert('Cadastro realizado com sucesso! Faça login.'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar.'); window.location.href='login.html';</script>";
        }
    }
}
?>