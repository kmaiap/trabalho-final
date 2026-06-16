<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_cliente']);
    $telefone = trim($_POST['telefone']);
    $servico = $_POST['servico'];
    $profissional = $_POST['profissional'];
    $data_hora = trim($_POST['data_hora']);

    if (strpos($data_hora, 'T') !== false) {
        $data_hora = str_replace('T', ' ', $data_hora);
    }

    if (!empty($nome) && !empty($telefone) && !empty($servico) && !empty($profissional) && !empty($data_hora)) {
        try {
            $sql = "INSERT INTO agendamentos (nome_cliente, telefone, servico, profissional, data_hora) 
                    VALUES (:nome, :telefone, :servico, :profissional, :data_hora)";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':servico' => $servico,
                ':profissional' => $profissional,
                ':data_hora' => $data_hora
            ]);
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $mensagem = "Erro ao agendar: " . $e->getMessage();
        }
    } else {
        $mensagem = "Por favor, preencha todos os campos obrigatórios.";
    }
}

// Carregar Serviços cadastrados no Banco de dados para o Select
try {
    $stmt_s = $conexao->query("SELECT nome_servico AS nome, preco FROM servicos ORDER BY nome ASC");
    $servicos_select = $stmt_s->fetchAll();
} catch (PDOException $e) {
    $servicos_select = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lumiére - Novo Agendamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #D4AF37; --tertiary: #FAF9F6; --neutral: #4A4543; --sidebar-bg: #2D2826; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--tertiary); color: var(--neutral); }
        h1, h2, .lumiere-logo { font-family: 'Playfair Display', serif; }
        
        .sidebar { background-color: var(--sidebar-bg); min-width: 260px; height: 100vh; position: fixed; padding: 2rem 0; }
        .sidebar .logo-area { padding: 0 2rem; color: var(--primary); }
        .sidebar .nav-link { color: #FAF9F6; opacity: 0.7; padding: 0.8rem 2rem; display: flex; align-items: center; text-decoration: none; }
        .sidebar .nav-link i { margin-right: 15px; }
        .sidebar .nav-link.active { opacity: 1; color: var(--primary); background: rgba(212, 175, 55, 0.05); border-right: 4px solid var(--primary); }
        .sidebar .sair { position: absolute; bottom: 2rem; width: 100%; }

        .main-content { margin-left: 260px; padding: 3rem; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-card { background: white; border-radius: 24px; padding: 3rem; width: 100%; max-width: 680px; box-shadow: 0 10px 40px rgba(0,0,0,0.02); }
        
        .back-link { text-decoration: none; color: var(--neutral); font-size: 0.8rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; display: inline-flex; align-items: center; margin-bottom: 2rem; }
        .back-link i { margin-right: 8px; }

        .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #8A8583; }
        .form-control, .form-select { background: #FAF9F6; border: 1px solid #EAE5DF; padding: 12px 18px; border-radius: 10px; font-size: 0.95rem; color: var(--neutral); }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: none; background: white; }
        
        .btn-submit { background: var(--primary); color: white; width: 100%; padding: 14px; border: none; border-radius: 50px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-top: 1.5rem; transition: 0.3s; }
        .btn-submit:hover { background: #BFA030; }
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
            <a href="cadastrar.php" class="nav-link active"><i class="bi bi-calendar3"></i> Agendamentos</a>
            <a href="servicos.php" class="nav-link"><i class="bi bi-scissors"></i> Serviços</a>
            <a href="clientes.php" class="nav-link"><i class="bi bi-people-fill"></i> Clientes</a>
        </div>
        <div class="sair">
            <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Sair</a>
        </div>
    </div>

    <div class="main-content">
        <div class="form-card">
            <a href="index.php" class="back-link"><i class="bi bi-arrow-left"></i> Voltar para listagem</a>
            
            <div class="text-center mb-4">
                <h2 class="h1 text-dark mb-2">Reserve um Momento</h2>
                <p class="text-muted small">Preencha os detalhes para agendar uma nova experiência de beleza.</p>
            </div>

            <?php if(!empty($mensagem)): ?>
                <div class="alert alert-info text-center p-2 small"><?= $mensagem ?></div>
            <?php endif; ?>

            <form method="POST" action="cadastrar.php">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Nome da Cliente</label>
                        <input type="text" name="nome_cliente" class="form-control" placeholder="Digite o nome completo" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Serviço</label>
                        <select name="servico" class="form-select" required>
                            <option value="" disabled selected>Selecione um serviço</option>
                            <?php if(!empty($servicos_select)): ?>
                                <?php foreach($servicos_select as $s_opt): ?>
                                    <option value="<?= htmlspecialchars($s_opt['nome']) ?>"><?= htmlspecialchars($s_opt['nome']) ?> (R$ <?= number_format($s_opt['preco'],2,',','.') ?>)</option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Corte e Escova">Corte e Escova (R$ 150,00)</option>
                                <option value="Unha em Gel">Unha em Gel (R$ 120,00)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profissional</label>
                        <select name="profissional" class="form-select" required>
                            <option value="" disabled selected>Selecione o profissional</option>
                            <option value="Alice Silva">Alice Silva</option>
                            <option value="Mariana Costa">Mariana Costa</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Data e Horário</label>
                        <input type="datetime-local" name="data_hora" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Confirmar Agendamento</button>
            </form>
        </div>
    </div>
</body>
</html>