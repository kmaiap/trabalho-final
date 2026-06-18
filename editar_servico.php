<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';

$mensagem = "";
$servico = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $conexao->prepare("SELECT * FROM servicos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erro ao buscar serviço: " . $e->getMessage());
    }
}

if (!$servico) {
    header("Location: servicos.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $preco_raw = trim($_POST['preco']);
    $duracao = intval($_POST['duracao']);
    $preco_limpo = str_replace(['R$', ' '], '', $preco_raw);
    $preco = str_replace(',', '.', $preco_limpo);

    if (empty($nome) || empty($preco) || $duracao <= 0) {
        $mensagem = "<div class='alert alert-warning text-center small'>Preencha todos os campos corretamente.</div>";
    } elseif (!is_numeric($preco) || floatval($preco) <= 0) {
        $mensagem = "<div class='alert alert-warning text-center small'>Informe um preço válido.</div>";
    } else {
        try {
            $sql = "UPDATE servicos SET nome = :nome, preco = :preco, duracao = :duracao WHERE id = :id";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':preco' => number_format((float)$preco, 2, '.', ''),
                ':duracao' => $duracao,
                ':id' => $id
            ]);
            header('Location: servicos.php');
            exit();
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger text-center small'>Erro ao atualizar serviço: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço - Lumiére</title>
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
            <a href="servicos.php" class="nav-link active"><i class="bi bi-scissors"></i> Serviços</a>
            <a href="clientes.php" class="nav-link"><i class="bi bi-people-fill"></i> Clientes</a>
        </div>
        <div class="sair">
            <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Sair</a>
        </div>
    </div>
    <div class="main-content">
        <div class="card-custom mx-auto" style="max-width: 640px;">
            <a href="servicos.php" class="back-link"><i class="bi bi-arrow-left"></i> Voltar para Serviços</a>
            <h2 class="mb-2">Editar Serviço</h2>
            <p class="text-muted small mb-4">Ajuste o nome, preço ou duração do procedimento.</p>
            <?= $mensagem ?>
            <form method="POST" action="editar_servico.php?id=<?= $servico['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Nome do Serviço *</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($servico['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Preço (R$) *</label>
                    <input type="text" name="preco" class="form-control" value="<?= number_format($servico['preco'], 2, ',', '.') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Duração (Minutos) *</label>
                    <input type="number" name="duracao" class="form-control" value="<?= htmlspecialchars($servico['duracao']) ?>" min="1" required>
                </div>
                <button type="submit" class="btn-submit">Salvar Alterações</button>
            </form>
        </div>
    </div>
</body>
</html>
