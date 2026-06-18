<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $conexao->prepare("DELETE FROM servicos WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        die("Erro ao excluir serviço: " . $e->getMessage());
    }
}

header('Location: servicos.php');
exit();
