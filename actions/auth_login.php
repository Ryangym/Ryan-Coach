<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    
    // Recebe a etiqueta do formulário ('admin' ou 'aluno')
    // Se alguém tentar burlar removendo o input, definimos um padrão vazio
    $tipo_login_esperado = $_POST['tipo_login'] ?? ''; 

    // Busca o usuário pelo email
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 1. Verifica se usuário existe e senha bate
    if ($user && password_verify($senha, $user['senha'])) {
        
        // 2. VERIFICAÇÃO DE SEGURANÇA DE NÍVEL
        
        // Cenário A: Tentando logar no Admin, mas é Aluno
        if ($tipo_login_esperado === 'admin' && $user['nivel'] !== 'admin') {
            echo "<script>
                alert('Acesso Negado! Alunos não têm permissão nesta área.');
                window.location.href = '../login.php'; // Manda de volta pro login de aluno
            </script>";
            exit;
        }

        // Cenário B: Tentando logar no Aluno, mas é Admin (Opcional, mas você pediu separação)
        if ($tipo_login_esperado === 'aluno' && $user['nivel'] === 'admin') {
             echo "<script>
                alert('Administradores devem usar o Painel Administrativo.');
                window.location.href = '../loginAdmin.php'; // Manda pro login certo
            </script>";
            exit;
        }

        // Se passou por tudo, libera o acesso
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_nivel'] = $user['nivel'];

        // Redireciona para a página correta
        if ($user['nivel'] === 'admin') {
            header("Location: ../admin.php");
        } else {
            header("Location: ../usuario.php");
        }
        exit;

    } else {
        // Senha ou email errados
        echo "<script>alert('Email ou senha incorretos!'); window.history.back();</script>";
    }
}
?>