<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';
$page = 'clientes';
$mensagem = "";

// 1. PROCESSA O CADASTRO DE UMA NOVA CLIENTE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_cliente'])) {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $telefone_digits = preg_replace('/\D/', '', $telefone);
    $nome_valido = preg_match('/^[a-záàâãéèêíïóôõöúçñA-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s]+$/', $nome) && !empty(trim($nome));

    if (empty($nome) || empty($telefone)) {
        $mensagem = "<div class='alert alert-light border border-secondary text-dark p-2 small text-center'>Nome e Telefone são obrigatórios.</div>";
    } elseif (!$nome_valido) {
        $mensagem = "<div class='alert alert-light border border-secondary text-dark p-2 small text-center'>O nome deve conter apenas letras e espaços.</div>";
    } elseif (strlen($telefone_digits) < 10 || strlen($telefone_digits) > 11) {
        $mensagem = "<div class='alert alert-light border border-secondary text-dark p-2 small text-center'>O telefone deve conter 10 ou 11 dígitos.</div>";
    } else {
        // Verificar duplicatas de telefone
        try {
            $stmt_check = $conexao->prepare("SELECT COUNT(*) as count FROM clientes WHERE telefone = :telefone");
            $stmt_check->execute([':telefone' => $telefone]);
            $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                $mensagem = "<div class='alert alert-light border border-secondary text-dark p-2 small text-center'>Já existe um cliente cadastrado com este telefone.</div>";
            } else {
                // Verificar duplicata de email se fornecido
                if (!empty($email)) {
                    $stmt_check_email = $conexao->prepare("SELECT COUNT(*) as count FROM clientes WHERE email = :email");
                    $stmt_check_email->execute([':email' => $email]);
                    $result_email = $stmt_check_email->fetch(PDO::FETCH_ASSOC);
                    if ($result_email['count'] > 0) {
                        $mensagem = "<div class='alert alert-light border border-secondary text-dark p-2 small text-center'>Já existe um cliente cadastrado com este email.</div>";
                    }
                }
                // Se passou pelas validações, insere
                if (empty($mensagem)) {
                    try {
                        $sql = "INSERT INTO clientes (nome, telefone, email) VALUES (:nome, :telefone, :email)";
                        $stmt = $conexao->prepare($sql);
                        $stmt->execute([
                            ':nome' => $nome,
                            ':telefone' => $telefone,
                            ':email' => !empty($email) ? $email : null
                        ]);
                        $mensagem = "<div class='alert alert-success p-2 small text-center'>Cliente cadastrada com sucesso!</div>";
                    } catch (PDOException $e) {
                        $mensagem = "<div class='alert alert-danger p-2 small text-center'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
                    }
                }
            }
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger p-2 small text-center'>Erro ao verificar dados: " . $e->getMessage() . "</div>";
        }
    }
}

// 2. BUSCA TODAS AS CLIENTES DO BANCO PARA EXIBIR NA TABELA
try {
    $stmt = $conexao->query("SELECT * FROM clientes ORDER BY nome ASC");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar clientes: " . $e->getMessage());
}

// Função para gerar as iniciais no círculo colorido (Avatar)
function obterIniciaisClientes($nome) {
    $palavras = explode(" ", trim($nome));
    $iniciais = "";
    foreach ($palavras as $palavra) {
        if (!empty($palavra)) { 
            $iniciais .= strtoupper(substr($palavra, 0, 1)); 
        }
        if (strlen($iniciais) >= 2) break;
    }
    return !empty($iniciais) ? $iniciais : "??";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #D4AF37; 
            --tertiary: #FAF9F6; 
            --neutral: #4A4543; 
            --sidebar-bg: #2D2826; 
            --avatar-bg: #F8E1E7; 
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
        .lumiere-table .client-avatar { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; background-color: var(--avatar-bg); color: #7B3F4A; font-weight: 700; font-size: 0.8rem; margin-right: 12px; }
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
        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="card card-custom shadow-sm">
                    <h3 class="mb-3 text-dark" style="font-size:1.4rem;"><i class="bi bi-person-plus text-warning me-2"></i>Nova Cliente</h3>
                    <p class="text-muted small mb-4">Cadastre perfis para manter o histórico organizado.</p>
                    
                    <?= $mensagem ?>
                    
                    <form method="POST" action="clientes.php">
                        <input type="hidden" name="cadastrar_cliente" value="1">
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" placeholder="Nome completo" pattern="[a-záàâãéèêíïóôõöúçñA-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\\s]+" title="O nome deve conter apenas letras e espaços" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone *</label>
                            <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-mail (Opcional)</label>
                            <input type="email" name="email" class="form-control" placeholder="cliente@email.com">
                        </div>
                        <button type="submit" class="btn-submit mt-2">Cadastrar Cliente</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom shadow-sm">
                    <h3 class="mb-4 text-dark" style="font-size:1.4rem;"><i class="bi bi-people text-warning me-2"></i>Base de Clientes</h3>
                    
                    <div class="table-responsive">
                        <table class="table lumiere-table table-borderless table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Telefone</th>
                                    <th>E-mail</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($clientes) > 0): ?>
                                    <?php foreach ($clientes as $cli): ?>
                                        <tr>
                                            <td class="d-flex align-items-center">
                                                <span class="client-avatar"><?= obterIniciaisClientes($cli['nome']) ?></span>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($cli['nome']) ?></span>
                                            </td>
                                            <td class="text-muted"><?= htmlspecialchars($cli['telefone']) ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($cli['email'] ? $cli['email'] : 'Não informado') ?></td>
                                            <td class="text-center">
                                                <a href="editar_cliente.php?id=<?= $cli['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill me-2" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                                <a href="excluir_cliente.php?id=<?= $cli['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" title="Excluir" onclick="return confirm('Deseja realmente excluir esta cliente?');"><i class="bi bi-trash-fill"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">Nenhuma cliente registrada no sistema.</td>
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