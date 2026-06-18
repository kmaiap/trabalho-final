<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'conexao.php';

// Busca os agendamentos salvos no banco
$stmt = $conexao->query("SELECT * FROM agendamentos ORDER BY data_hora ASC");
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lumiére - Painel de Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #D4AF37; --tertiary: #FAF9F6; --neutral: #4A4543; --sidebar-bg: #2D2826; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--tertiary); color: var(--neutral); }
        h1, h2, .lumiere-logo { font-family: 'Playfair Display', serif; }
        
        /* Sidebar idêntica ao primeiro código */
        .sidebar { background-color: var(--sidebar-bg); min-width: 260px; height: 100vh; position: fixed; padding: 2rem 0; z-index: 1000; }
        .sidebar .logo-area { padding: 0 2rem; color: var(--primary); }
        .sidebar .nav-link { color: #FAF9F6; opacity: 0.7; padding: 0.8rem 2rem; display: flex; align-items: center; text-decoration: none; transition: 0.2s; }
        .sidebar .nav-link i { margin-right: 15px; }
        /* Aqui no index o Dashboard fica ativo (active) */
        .sidebar .nav-link.active { opacity: 1; color: var(--primary); background: rgba(212, 175, 55, 0.05); border-right: 4px solid var(--primary); }
        .sidebar .sair { position: absolute; bottom: 2rem; width: 100%; padding: 0 2rem; }
        .sidebar .sair a { color: #FAF9F6; opacity: 0.7; text-decoration: none; display: flex; align-items: center; }
        .sidebar .sair a:hover { opacity: 1; color: #dc2626; }
        .sidebar .sair i { margin-right: 15px; }

        /* Ajuste do conteúdo principal para respeitar a sidebar */
        .main-content { margin-left: 260px; padding: 3rem; min-height: 100vh; }
        
        /* Estilos da Tabela de Agendamentos */
        .schedule-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: 1.5rem; }
        .schedule-table th { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #8A8583; border: none; padding: 0 1.5rem; }
        .schedule-table tbody tr { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); transition: 0.3s; }
        .schedule-table tbody tr:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.03); }
        .schedule-table td { padding: 1.5rem; vertical-align: middle; border: none; }
        .schedule-table td:first-child { border-top-left-radius: 16px; border-bottom-left-radius: 16px; }
        .schedule-table td:last-child { border-top-right-radius: 16px; border-bottom-right-radius: 16px; }
        
        .badge-servico { background: #FAF9F6; border: 1px solid #EAE5DF; color: var(--neutral); padding: 6px 14px; border-radius: 30px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .btn-novo { background: var(--primary); color: white; padding: 12px 24px; border-radius: 50px; font-weight: 600; text-decoration: none; letter-spacing: 0.5px; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-novo:hover { background: #BFA030; color: white; }
        .btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 14px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-action-edit { color: var(--neutral); background: #F0E3B8; border: 1px solid #E3D29B; }
        .btn-action-edit:hover { background: #F7E9C3; color: var(--neutral); }
        .btn-action-delete { color: white; background: #7A5A3C; border: 1px solid #7A5A3C; }
        .btn-action-delete:hover { background: #66492F; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-area">
            <h2 class="lumiere-logo mb-0">Lumiére</h2>
            <small style="letter-spacing: 2px; font-size: 0.7rem; opacity: 0.7;">GESTÃO INTERNA</small>
        </div>
        <div class="mt-4">
            <a href="index.php" class="nav-link active"><i class="bi bi-grid-fill"></i> Dashboard</a>
            <a href="cadastrar.php" class="nav-link"><i class="bi bi-calendar3"></i> Agendamentos</a>
        </div>
        <div class="sair">
            <a href="logout.php"><i class="bi bi-box-arrow-left"></i> Sair</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="text-dark mb-1">Painel de Agendamentos</h1>
                <p class="text-muted small mb-0">Gerencie e visualize os próximos horários da sua equipe de beleza.</p>
            </div>
            <div>
                <a href="cadastrar.php" class="btn-novo"><i class="bi bi-plus-lg"></i> Novo Agendamento</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Serviço</th>
                        <th>Profissional</th>
                        <th>Data / Horário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($agendamentos) > 0): ?>
                        <?php foreach ($agendamentos as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nome_cliente'] ?? $row['cliente_nome'] ?? '') ?></strong></td>
                                <td class="text-muted"><?= htmlspecialchars($row['telefone'] ?? $row['cliente_telefone'] ?? '') ?></td>
                                <td><span class="badge-servico"><?= htmlspecialchars($row['servico'] ?? '') ?></span></td>
                                <td><strong><?= htmlspecialchars($row['profissional'] ?? '') ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-clock text-warning"></i>
                                        <span><?= !empty($row['data_hora']) ? date('d/m/Y - H:i', strtotime($row['data_hora'])) : '' ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="editar.php?id=<?= $row['id'] ?>" class="btn-action btn-action-edit">
                                            <i class="bi bi-pencil-fill"></i> Editar
                                        </a>
                                        <a href="excluir.php?id=<?= $row['id'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Deseja excluir este agendamento?');">
                                            <i class="bi bi-trash-fill"></i> Excluir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x d-block fs-3 mb-2 opacity-50"></i>
                                Nenhum agendamento encontrado para os próximos dias.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>