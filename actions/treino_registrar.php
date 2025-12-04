<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = $_SESSION['user_id'];
    $treino_id = $_POST['treino_id'];
    $divisao_id = $_POST['divisao_id'];
    
    // Os dados vêm como arrays: exercicio[ex_id][serie_num][reps]
    $dados = $_POST['treino'] ?? [];

    try {
        $pdo->beginTransaction();

        // CORREÇÃO AQUI: Mudamos de reps_feitas para reps_realizadas
        $sql = "INSERT INTO treino_historico (aluno_id, treino_id, divisao_id, exercicio_id, serie_numero, reps_realizadas, carga_kg) 
                VALUES (:aid, :tid, :did, :eid, :snum, :reps, :carga)";
        $stmt = $pdo->prepare($sql);

        foreach ($dados as $ex_id => $series) {
            foreach ($series as $num_serie => $valores) {
                // Só salva se tiver preenchido pelo menos um dos dois
                if (!empty($valores['reps']) || !empty($valores['carga'])) {
                    $stmt->execute([
                        'aid' => $aluno_id,
                        'tid' => $treino_id,
                        'did' => $divisao_id,
                        'eid' => $ex_id,
                        'snum' => $num_serie,
                        'reps' => $valores['reps'],
                        'carga' => str_replace(',', '.', $valores['carga']) // Trata vírgula
                    ]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../usuario.php?msg=treino_concluido");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro ao salvar: " . $e->getMessage();
    }
}
?>