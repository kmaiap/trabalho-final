<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';

$mensagem = "";
$cliente = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $conexao->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erro ao buscar cliente: " . $e->getMessage());
    }
}

if (!$cliente) {
    header("Location: clientes.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $telefone_digits = preg_replace('/\D/', '', $telefone);
    $nome_valido = preg_match('/^[a-záàâãéèêíïóôõöúçñA-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\\s]+$/', $nome) && !empty(trim($nome));

    if (empty($nome) || empty($telefone)) {
        $mensagem = "<div class='alert alert-light border border-secondary text-dark text-center small'>Nome e telefone são obrigatórios.</div>";
    } elseif (!$nome_valido) {
        $mensagem = "<div class='alert alert-light border border-secondary text-dark text-center small'>O nome deve conter apenas letras e espaços.</div>";
    } elseif (strlen($telefone_digits) < 10 || strlen($telefone_digits) > 11) {
        $mensagem = "<div class='alert alert-light border border-secondary text-dark text-center small'>O telefone deve conter 10 ou 11 dígitos.</div>";
    } else {
        // Verificar duplicatas de telefone (exceto o cliente atual)
        try {
            $stmt_check = $conexao->prepare("SELECT COUNT(*) as count FROM clientes WHERE telefone = :telefone AND id != :id");
            $stmt_check->execute([':telefone' => $telefone, ':id' => $id]);
            $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                $mensagem = "<div class='alert alert-light border border-secondary text-dark text-center small'>Já existe outro cliente cadastrado com este telefone.</div>";
            } else {
                // Verificar duplicata de email se fornecido (exceto o cliente atual)
                if (!empty($email)) {
                    $stmt_check_email = $conexao->prepare("SELECT COUNT(*) as count FROM clientes WHERE email = :email AND id != :id");
                    $stmt_check_email->execute([':email' => $email, ':id' => $id]);
                    $result_email = $stmt_check_email->fetch(PDO::FETCH_ASSOC);
                    if ($result_email['count'] > 0) {
                        $mensagem = "<div class='alert alert-light border border-secondary text-dark text-center small'>Já existe outro cliente cadastrado com este email.</div>";
                    }
                }
                // Se passou pelas validações, atualiza
                if (empty($mensagem)) {
                    try {
                        $stmt = $conexao->prepare("UPDATE clientes SET nome = :nome, telefone = :telefone, email = :email WHERE id = :id");
                        $stmt->execute([
                            ':nome' => $nome,
                            ':telefone' => $telefone,
                            ':email' => !empty($email) ? $email : null,
                            ':id' => $id
                        ]);
                        header('Location: clientes.php');
                        exit();
                    } catch (PDOException $e) {
                        $mensagem = "<div class='alert alert-danger text-center small'>Erro ao atualizar cliente: " . $e->getMessage() . "</div>";
                    }
                }
            }
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger text-center small'>Erro ao verificar dados: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #D4AF37; --tertiary: #FAF9F6; --neutral: #4A4543; --sidebar-bg: #2D2826; }
        body { margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--tertiary); color: var(--neutral); }
        .sidebar { background: var(--sidebar-bg); min-width: 260px; height: 100vh; position: fixed; padding: 2rem 0; }
        .sidebar .logo-area { padding: 0 2rem; color: var(--primary); }
        .sidebar .nav-link { color: #FAF9F6; opacity: 0.7; padding: 0.8rem 2rem; display: flex; align-items: center; text-decoration: none; transition: 0.2s; }
        .sidebar .nav-link i { margin-right: 15px; }
        .sidebar .nav-link.active { opacity: 1; color: var(--primary); background: rgba(212,175,55,0.08); border-right: 4px solid var(--primary); }
        .sidebar .sair { position: absolute; bottom: 2rem; width: 100%; padding: 0 2rem; }
        .sidebar .sair a { color: #FAF9F6; display: flex; align-items: center; text-decoration: none; }
        .sidebar .sair a:hover { color: #dc2626; }
        .main-content { margin-left: 260px; padding: 3rem; min-height: 100vh; }
        .card-custom { background: white; border-radius: 24px; padding: 2.5rem; box-shadow: 0 12px 40px rgba(0,0,0,0.05); }
        .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #8A8583; margin-bottom: 0.6rem; }
        .form-control { width: 100%; background: #FAF9F6; border: 1px solid #EAE5DF; border-radius: 14px; padding: 14px 16px; }
        .form-control:focus { border-color: var(--primary); box-shadow: none; background: white; }
        .btn-submit { width: 100%; padding: 14px 24px; border: none; border-radius: 50px; background: var(--primary); color: white; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; transition: 0.3s; }
        .btn-submit:hover { background: #BFA030; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--neutral); text-decoration: none; font-weight: 600; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <h2 class="lumiere-logo mb-0">Lumiére</h2>
            <small style="letter-spacing: 2px; font-size: 0.7rem; opacity: 0.7;">GESTÃO INTERNA</small>
        </div>
        <div class="mt-4">
            <a href="index.php" class="nav-link"><i class="bi bi-grid-fill"></i> Dashboard</a>
            <a href="cadastrar.php" class="nav-link"><i class="bi bi-calendar3"></i> Agendamentos</a>
            <a href="servicos.php" class="nav-link"><i class="bi bi-scissors"></i> Serviços</a>
            <a href="clientes.php" class="nav-link active"><i class="bi bi-people-fill"></i> Clientes</a>
        </div>
        <div class="sair">
            <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Sair</a>
        </div>
    </div>
    <div class="main-content">
        <div class="card-custom mx-auto" style="max-width: 640px;">
            <a href="clientes.php" class="back-link"><i class="bi bi-arrow-left"></i> Voltar para Clientes</a>
            <h2 class="mb-2">Editar Cliente</h2>
            <p class="text-muted small mb-4">Ajuste informações de contato da cliente.</p>
            <?= $mensagem ?>
            <form method="POST" action="editar_cliente.php?id=<?= $cliente['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($cliente['nome']) ?>" placeholder="Nome completo" pattern="[a-záàâãéèêíïóôõöúçñA-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\\s]+" title="O nome deve conter apenas letras e espaços" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone *</label>
                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($cliente['telefone']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email']) ?>">
                </div>
                <button type="submit" class="btn-submit">Salvar Alterações</button>
            </form>
        </div>
    </div>
</body>
</html>