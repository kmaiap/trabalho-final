<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#FAF9F6; color:#4A4543; font-family:Arial, sans-serif;">
    <div class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title">Relatórios</h1>
                <p class="card-text">Página de relatórios ainda em desenvolvimento. Retorne ao painel para ver as informações principais.</p>
                <a href="index.php" class="btn btn-primary">Voltar ao Painel</a>
            </div>
        </div>
    </div>
</body>
</html>
