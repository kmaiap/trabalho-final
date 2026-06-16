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
        $sql = "DELETE FROM agendamentos WHERE id = :id";
        
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        // exclui
        $stmt->execute();
        
    } catch (PDOException $erro) {
        die("Erro ao tentar desmarcar horário: " . $erro->getMessage());
    }
}

// redireciona pro painel (index.php)
header("Location: index.php");
exit();
?>