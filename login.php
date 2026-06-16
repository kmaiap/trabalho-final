<?php
require_once 'conexao.php';
session_start();

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_usuario']);
    $senha = trim($_POST['senha_usuario']);

    if (!empty($nome) && !empty($senha)) {
        try {
            $sql = "SELECT id, nome, senha FROM usuarios WHERE nome = :nome";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->execute();
            $usuario = $stmt->fetch();

            // verifica se o usuário existe e se a senha está correta
            if ($usuario && (password_verify($senha, $usuario['senha']) || $senha === $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                
                header("Location: index.php");
                exit();
            } else {
                $mensagem = "<div class='alert alert-danger'>Nome de usuário ou senha incorretos.</div>";
            }
        } catch (PDOException $erro) {
            $mensagem = "<div class='alert alert-danger'>Erro interno: " . $erro->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --cor-primaria: #D4AF37; --cor-fundo: #FAF9F6; --cor-neutra: #4A4543; --font-headline: 'Playfair Display', serif; --font-body: 'Plus Jakarta Sans', sans-serif; }
        body { font-family: var(--font-body); background-color: var(--cor-fundo); color: var(--cor-neutra); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { background-color: #FFFFFF; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); width: 100%; max-width: 450px; padding: 3rem; }
        .lumiere-logo { font-family: var(--font-headline); font-size: 2.2rem; color: var(--cor-primaria); text-align: center; margin-bottom: 0.2rem; }
        .lumiere-subtitle { font-size: 0.75rem; text-align: center; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2rem; opacity: 0.7; }
        .form-label-lumiere { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .form-control-lumiere { background-color: var(--cor-fundo); border: 1px solid #E5E1DA; border-radius: 8px; padding: 12px 15px; font-size: 0.9rem; }
        .form-control-lumiere:focus { border-color: var(--cor-primaria); box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.1); }
        .btn-login { background-color: var(--cor-primaria); color: white; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; border-radius: 50px; padding: 12px; border: none; margin-top: 1.5rem; transition: all 0.3s; }
        .btn-login:hover { background-color: #C09E32; }
    </style>
</head>
<body>

    <div class="card-login">
        <h1 class="lumiere-logo">Lumiére</h1>
        <p class="lumiere-subtitle">Gestão Interna • Login</p>

        <?= $mensagem ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="nome_usuario" class="form-label-lumiere">Usuário</label>
                <input type="text" class="form-control form-control-lumiere" id="nome_usuario" name="nome_usuario" required placeholder="Seu nome cadastrado">
            </div>

            <div class="mb-4">
                <label for="senha_usuario" class="form-label-lumiere">Senha</label>
                <input type="password" class="form-control form-control-lumiere" id="senha_usuario" name="senha_usuario" required placeholder="Sua senha de acesso">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-login">Entrar no Sistema</button>
            </div>
            <p class="text-center mt-3 mb-0" style="font-size: 0.8rem;">Não tem conta? <a href="cadastrar_usuario.php" style="color: var(--cor-primaria);">Cadastre-se</a></p>
        </form>
    </div>

</body>
</html>