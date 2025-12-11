<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] !== 'admin') { die("Acesso negado."); }

$acao = $_REQUEST['acao'] ?? '';
$aluno_id = $_REQUEST['aluno_id'] ?? 0;

try {
    // 1. CRIAR DIETA (CABEÇALHO)
    if ($acao === 'criar_dieta') {
        $titulo = $_POST['titulo'];
        $objetivo = $_POST['objetivo'];

        // Desativa dietas anteriores (opcional, mas bom pra organização)
        $pdo->prepare("UPDATE dietas SET ativo = 0 WHERE aluno_id = ?")->execute([$aluno_id]);

        $stmt = $pdo->prepare("INSERT INTO dietas (aluno_id, titulo, objetivo, ativo) VALUES (?, ?, ?, 1)");
        $stmt->execute([$aluno_id, $titulo, $objetivo]);
    }

    // 2. EXCLUIR DIETA INTEIRA
    elseif ($acao === 'excluir_dieta') {
        $id = $_GET['id'];
        $pdo->prepare("DELETE FROM dietas WHERE id = ?")->execute([$id]);
    }

    // 3. ADICIONAR REFEIÇÃO (BLOCO DE HORÁRIO)
    elseif ($acao === 'add_refeicao') {
        $dieta_id = $_POST['dieta_id'];
        $nome = $_POST['nome'];
        $horario = $_POST['horario'];
        $ordem = $_POST['ordem'];

        $stmt = $pdo->prepare("INSERT INTO refeicoes (dieta_id, nome, horario, ordem) VALUES (?, ?, ?, ?)");
        $stmt->execute([$dieta_id, $nome, $horario, $ordem]);
    }

    // 4. EXCLUIR REFEIÇÃO
    elseif ($acao === 'excluir_refeicao') {
        $id = $_GET['id'];
        $pdo->prepare("DELETE FROM refeicoes WHERE id = ?")->execute([$id]);
    }

    // 5. ADICIONAR ITEM (ALIMENTO)
    elseif ($acao === 'add_item') {
        $refeicao_id = $_POST['refeicao_id'];
        $opcao = $_POST['opcao_numero'];
        $desc = $_POST['descricao'];
        $obs = $_POST['observacao'];

        $stmt = $pdo->prepare("INSERT INTO itens_dieta (refeicao_id, opcao_numero, descricao, observacao) VALUES (?, ?, ?, ?)");
        $stmt->execute([$refeicao_id, $opcao, $desc, $obs]);
    }

    // 6. EXCLUIR ITEM
    elseif ($acao === 'excluir_item') {
        $id = $_GET['id'];
        $pdo->prepare("DELETE FROM itens_dieta WHERE id = ?")->execute([$id]);
    }

    // Redireciona de volta para o editor
    header("Location: ../admin.php?pagina=dieta_editor&id=$aluno_id&msg=sucesso");
    exit;

} catch (PDOException $e) {
    echo "Erro ao salvar: " . $e->getMessage();
    exit;
}
?>