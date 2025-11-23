<?php
session_start();
require_once '../config/db_connect.php';

// Segurança Máxima: Só Admin entra aqui
if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'admin') {
    die("Acesso negado.");
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

try {
    // --- PROMOVER A ADMIN (NOVO) ---
    if ($acao === 'promover') {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            // Muda o nível para 'admin'
            $stmt = $pdo->prepare("UPDATE usuarios SET nivel = 'admin' WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        // Redireciona avisando
        header("Location: ../admin.php?msg=promovido");
        exit;
    }

    // --- EXCLUIR ALUNO ---
    if ($acao === 'excluir') {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND nivel = 'aluno'");
            $stmt->execute(['id' => $id]);
        }
        header("Location: ../admin.php?msg=excluido");
        exit;
    }

    // --- EDITAR ALUNO ---
    if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
        $nova_senha = $_POST['nova_senha'];

        $sql_senha = "";
        $params = ['nome' => $nome, 'email' => $email, 'telefone' => $telefone, 'id' => $id];

        if (!empty($nova_senha)) {
            $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql_senha = ", senha = :senha";
            $params['senha'] = $senhaHash;
        }

        $sql = "UPDATE usuarios SET nome = :nome, email = :email, telefone = :telefone $sql_senha WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("Location: ../admin.php?msg=atualizado");
        exit;
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>