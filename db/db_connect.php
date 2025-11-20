<?php
// config/db_connect.php

$host = 'localhost';
$dbname = 'ryan_coach_db';
$username = 'root';
$password = 'vertrigo';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configura para lançar erros em caso de problemas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>