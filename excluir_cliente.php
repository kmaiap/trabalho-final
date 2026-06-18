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
        $stmt = $conexao->prepare("DELETE FROM clientes WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        die("Erro ao excluir cliente: " . $e->getMessage());
    }
}

header('Location: clientes.php');
exit();
