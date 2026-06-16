<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';
$page = 'servicos';
$mensagem = "";

// 1. PROCESSA O CADASTRO DE UM NOVO SERVIÇO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_servico'])) {
    $nome = trim($_POST['nome']);
    
    // Tratamento do preço: remove "R$", espaços e troca a vírgula por ponto para o banco aceitar (formatos como 150,00 viram 150.00)
    $preco_limpo = str_replace(['R$', ' '], '', $_POST['preco']);
    $preco = str_replace(',', '.', $preco_limpo);
    
    $duracao = intval($_POST['duracao']);

    if (!empty($nome) && !empty($preco) && $duracao > 0) {
        try {
            // Insere no banco de dados
            $sql = "INSERT INTO servicos (nome, preco, duracao) VALUES (:nome, :preco, :duracao)";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                ':nome'    => $nome, 
                ':preco'   => $preco, 
                ':duracao' => $duracao
            ]);
            $mensagem = "<div class='alert alert-success p-2 small text-center'>Serviço cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger p-2 small text-center'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-warning p-2 small text-center'>Por favor, preencha todos os campos corretamente.</div>";
    }
}

// 2. BUSCA TODOS OS SERVIÇOS DO BANCO PARA LISTAR NA TABELA
try {
    $stmt = $conexao->query("SELECT * FROM servicos ORDER BY nome ASC");
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar serviços: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #D4AF37; 
            --tertiary: #FAF9F6; 
            --neutral: #4A4543; 
            --sidebar-bg: #2D2826; 
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--tertiary); color: var(--neutral); }
        h3, .lumiere-logo { font-family: 'Playfair Display', serif; }

        /* Sidebar */
        .sidebar { background-color: var(--sidebar-bg); min-width: 260px; height: 100vh; position: fixed; padding: 2rem 0; z-index: 100; }
        .sidebar .logo-area { padding: 0 2rem; color: var(--primary); }
        .sidebar .nav-link { color: #FAF9F6; opacity: 0.7; padding: 0.8rem 2rem; display: flex; align-items: center; text-decoration: none; }
        .sidebar .nav-link i { margin-right: 15px; }
        .sidebar .nav-link.active { opacity: 1; color: var(--primary); background: rgba(212, 175, 55, 0.05); border-right: 4px solid var(--primary); }
        .sidebar .sair { position: absolute; bottom: 2rem; width: 100%; }

        /* Conteúdo */
        .main-content { margin-left: 260px; padding: 3rem; min-height: 100vh; }
        .card-custom { background: white; border-radius: 20px; padding: 2rem; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.02); }
        
        .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #8A8583; letter-spacing: 0.5px; }
        .form-control { background: #FAF9F6; border: 1px solid #EAE5DF; padding: 10px 15px; border-radius: 10px; }
        .form-control:focus { border-color: var(--primary); box-shadow: none; background: white; }
        
        .btn-submit { background: var(--primary); color: white; border: none; border-radius: 50px; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; padding: 12px 24px; transition: 0.3s; width: 100%; }
        .btn-submit:hover { background: #BFA030; }

        .lumiere-table thead th { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #8A8583; border-bottom: 2px solid #FAF9F6; padding: 12px; }
        .lumiere-table tbody td { padding: 14px 12px; vertical-align: middle; border-bottom: 1px solid #FAF9F6; font-size: 0.9rem; }
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
        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="card card-custom shadow-sm">
                    <h3 class="mb-3 text-dark" style="font-size:1.4rem;"><i class="bi bi-plus-circle text-warning me-2"></i>Novo Serviço</h3>
                    <p class="text-muted small mb-4">Insira os dados abaixo para catalogar um serviço.</p>
                    
                    <?= $mensagem ?>
                    
                    <form method="POST" action="servicos.php">
                        <input type="hidden" name="cadastrar_servico" value="1">
                        <div class="mb-3">
                            <label class="form-label">Nome do Serviço *</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Mechas Criativas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preço (R$) *</label>
                            <input type="text" name="preco" class="form-control" placeholder="0,00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duração (Minutos) *</label>
                            <input type="number" name="duracao" class="form-control" placeholder="Ex: 60" required>
                        </div>
                        <button type="submit" class="btn-submit mt-2">Salvar Serviço</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom shadow-sm">
                    <h3 class="mb-4 text-dark" style="font-size:1.4rem;"><i class="bi bi-scissors text-warning me-2"></i>Procedimentos Ativos</h3>
                    
                    <div class="table-responsive">
                        <table class="table lumiere-table table-borderless table-hover">
                            <thead>
                                <tr>
                                    <th>Nome do Procedimento</th>
                                    <th>Preço Base</th>
                                    <th>Tempo Estimado</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($servicos) > 0): ?>
                                    <?php foreach ($servicos as $serv): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($serv['nome']) ?></td>
                                            <td class="text-dark fw-bold">R$ <?= number_format($serv['preco'], 2, ',', '.') ?></td>
                                            <td class="text-muted"><i class="bi bi-clock me-1"></i> <?= htmlspecialchars($serv['duracao']) ?> min</td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size: 0.75rem;">Ativo</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">Nenhum serviço cadastrado ainda.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>