<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";
$agenda = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $sql = "SELECT id, nome_cliente, telefone, servico, profissional as profissional, data_hora, observacoes 
            FROM agendamentos WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        $agenda = $stmt->fetch();
        
        if (!$agenda) {
            die("Agendamento não encontrado!");
        }
        $data_atual = date('Y-m-d', strtotime($agenda['data_hora']));
        $hora_atual = date('H:i', strtotime($agenda['data_hora']));
        
    } catch (PDOException $erro) {
        die("Erro ao buscar dados: " . $erro->getMessage());
    }
} else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Carrega os serviços cadastrados no banco para o select de edição
try {
    $stmt_servicos = $conexao->query("SELECT nome, preco, duracao FROM servicos ORDER BY nome ASC");
    $servicos = [];
    $servicos_dados = [];
    while ($row = $stmt_servicos->fetch(PDO::FETCH_ASSOC)) {
        $servicos[] = $row['nome'];
        $servicos_dados[$row['nome']] = [
            'valor' => number_format($row['preco'], 2, ',', '.'),
            'duracao' => $row['duracao'] . ' min'
        ];
    }
} catch (PDOException $erro) {
    $servicos = [];
    $servicos_dados = [];
}

$observacoes = $agenda['observacoes'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $servico = trim($_POST['servico']);
    $profissional = trim($_POST['profissional']);
    $data_reserva = trim($_POST['data_reserva']);
    $hora_reserva = trim($_POST['hora_reserva']);
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    $data_hora_completa = $data_reserva . ' ' . $hora_reserva . ':00';
    $timestamp_agendamento = strtotime($data_hora_completa);
    $horario_agendamento = $timestamp_agendamento !== false ? date('H:i', $timestamp_agendamento) : null;
    $horario_dentro_do_exp = $horario_agendamento !== null && $horario_agendamento >= '07:00' && $horario_agendamento <= '18:00';

    if (!empty($servico) && !empty($profissional) && !empty($data_reserva) && !empty($hora_reserva)) {
        if (!$horario_dentro_do_exp) {
            $mensagem = "<div class='alert alert-warning'>O horário deve ser entre 07:00 e 18:00.</div>";
        } else {
            try {
                $sql = "UPDATE agendamentos 
                    SET servico = :servico, profissional = :profissional, data_hora = :data_hora, observacoes = :observacoes 
                    WHERE id = :id";
            
                $stmt = $conexao->prepare($sql);
                $stmt->bindValue(':servico', $servico);
                $stmt->bindValue(':profissional', $profissional);
                $stmt->bindValue(':data_hora', $data_hora_completa);
                $stmt->bindValue(':observacoes', $observacoes);
                $stmt->bindValue(':id', $id);
            
                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit();
                }
            } catch (PDOException $erro) {
                $mensagem = "<div class='alert alert-danger'>Erro ao atualizar: " . $erro->getMessage() . "</div>";
            }
        }
    } else {
        $mensagem = "<div class='alert alert-warning'>Preencha todos os campos obrigatórios.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Agendamento - Lumiére</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cor-primaria: #D4AF37;
            --cor-secundaria: #F8E1E7;
            --cor-fundo: #FAF9F6;
            --cor-neutra: #4A4543;
            --font-headline: 'Playfair Display', serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--cor-fundo);
            color: var(--cor-neutra);
        }

        .lumiere-navbar {
            background-color: transparent;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-back-arrow {
            color: var(--cor-neutra);
            font-size: 1.5rem;
            text-decoration: none;
            transition: color 0.3s;
        }

        .btn-back-arrow:hover { color: var(--cor-primaria); }

        .navbar-title {
            font-family: var(--font-headline);
            font-size: 1.8rem;
            color: #2D2826;
            margin: 0;
            flex-grow: 1;
            text-align: left;
            padding-left: 15px;
        }

        .edit-container {
            max-width: 750px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .section-tag {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--cor-primaria);
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
        }

        .main-title {
            font-family: var(--font-headline);
            font-size: 2.2rem;
            color: #2D2826;
            margin-bottom: 0.3rem;
        }

        .subtitle {
            font-size: 0.9rem;
            color: var(--cor-neutra);
            opacity: 0.8;
            margin-bottom: 2.5rem;
        }

        .card-lumiere-edit {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            padding: 2.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .client-profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .client-profile-header img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .client-profile-info h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2D2826;
            margin: 0;
        }

        .client-profile-info p {
            font-size: 0.75rem;
            color: var(--cor-neutra);
            opacity: 0.6;
            margin: 0;
        }

        .form-label-lumiere {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--cor-neutra);
            opacity: 0.7;
            text-transform: capitalize;
            margin-bottom: 6px;
        }

        .form-control-lumiere {
            background-color: #FFFFFF;
            border: 1px solid #E5E1DA;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            color: #2D2826;
        }

        .form-control-lumiere:focus {
            border-color: var(--cor-primaria);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.1);
        }

        .textarea-lumiere {
            min-height: 100px;
            resize: none;
            background-color: var(--cor-fundo);
            border-color: #E5E1DA;
        }

        .value-duration-box {
            background-color: #FAF4F5; 
            border-radius: 12px;
            padding: 1.5rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .value-area span {
            font-size: 0.75rem;
            color: var(--cor-neutra);
            opacity: 0.6;
            display: block;
        }

        .value-area h2 {
            font-family: var(--font-headline);
            font-size: 2rem;
            color: #6C5256; 
            margin: 0;
            font-weight: 400;
        }

        .duration-area {
            text-align: right;
            font-size: 0.85rem;
            color: #6C5256;
        }

        .action-buttons-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .btn-lumiere-save {
            background: linear-gradient(90deg, #D4AF37 0%, #E5C564 100%);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 12px;
            border-radius: 50px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
            transition: all 0.3s;
        }

        .btn-lumiere-save:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-lumiere-save i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .btn-lumiere-cancel {
            background-color: #FFFFFF;
            color: var(--cor-neutra);
            border: 1px solid #2D2826;
            font-weight: 400;
            font-size: 0.9rem;
            padding: 12px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn-lumiere-cancel:hover {
            background-color: #F7F2ED;
            color: #000;
        }

        .btn-lumiere-cancel i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

    <nav class="lumiere-navbar">
        <a href="index.php" class="btn-back-arrow"><i class="bi bi-arrow-left"></i></a>
        <h1 class="navbar-title">Lumiére</h1>
        <div>
            <i class="bi bi-bell text-muted fs-5 me-3"></i>
            <img src="https://i.pravatar.cc/32" alt="Foto Perfil" class="rounded-circle">
        </div>
    </nav>

    <div class="edit-container">
        
        <p class="section-tag">Agenda</p>
        <h2 class="main-title">Editar Agendamento</h2>
        <p class="subtitle">Atualize os detalhes do horário reservado para a cliente.</p>

        <?= $mensagem ?>

        <form action="editar.php" method="POST">
            
            <input type="hidden" name="id" value="<?= $agenda['id'] ?>">

            <div class="card card-lumiere-edit">
                
                <div class="client-profile-header">
                    <img src="https://i.pravatar.cc/100?img=47" alt="Avatar da Cliente">
                    <div class="client-profile-info">
                        <h4><?= htmlspecialchars($agenda['nome_cliente']) ?></h4>
                        <p>Cliente ativa • Contato: <?= htmlspecialchars($agenda['telefone']) ?></p>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="servico" class="form-label-lumiere">Serviço</label>
                        <select class="form-select form-control-lumiere" id="servico" name="servico" required>
                            <?php if (count($servicos) > 0): ?>
                                <?php foreach ($servicos as $servico_option): ?>
                                    <option value="<?= htmlspecialchars($servico_option) ?>" <?= $agenda['servico'] === $servico_option ? 'selected' : '' ?>><?= htmlspecialchars($servico_option) ?></option>
                                <?php endforeach; ?>
                                <?php if (!in_array($agenda['servico'], $servicos, true)): ?>
                                    <option value="<?= htmlspecialchars($agenda['servico']) ?>" selected><?= htmlspecialchars($agenda['servico']) ?></option>
                                <?php endif; ?>
                            <?php else: ?>
                                <option value="Corte e Escova" <?= $agenda['servico'] == 'Corte e Escova' ? 'selected' : '' ?>>Corte e Escova</option>
                                <option value="Unha em Gel" <?= $agenda['servico'] == 'Unha em Gel' ? 'selected' : '' ?>>Unha em Gel</option>
                                <option value="Design de Sobrancelha" <?= $agenda['servico'] == 'Design de Sobrancelha' ? 'selected' : '' ?>>Design de Sobrancelha</option>
                                <option value="Mechas / Luzes" <?= $agenda['servico'] == 'Mechas / Luzes' ? 'selected' : '' ?>>Mechas / Luzes</option>
                                <option value="Progressiva" <?= $agenda['servico'] == 'Progressiva' ? 'selected' : '' ?>>Progressiva</option>
                                <?php if(!in_array($agenda['servico'], ['Corte e Escova','Unha em Gel','Design de Sobrancelha','Mechas / Luzes','Progressiva'])): ?>
                                    <option value="<?= htmlspecialchars($agenda['servico']) ?>" selected><?= htmlspecialchars($agenda['servico']) ?></option>
                                <?php endif; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="profissional" class="form-label-lumiere">Profissional</label>
                        <select class="form-select form-control-lumiere" id="profissional" name="profissional" required>
                            <option value="Alice Silva" <?= $agenda['profissional'] == 'Alice Silva' ? 'selected' : '' ?>>Alice Silva</option>
                            <option value="Mariana Costa" <?= $agenda['profissional'] == 'Mariana Costa' ? 'selected' : '' ?>>Mariana Costa</option>
                            <option value="Bianca Ramos" <?= $agenda['profissional'] == 'Bianca Ramos' ? 'selected' : '' ?>>Bianca Ramos</option>
                            <option value="Camila Castro" <?= $agenda['profissional'] == 'Camila Castro' ? 'selected' : '' ?>>Camila Castro</option>
                            <option value="Fernanda Lima" <?= $agenda['profissional'] == 'Fernanda Lima' ? 'selected' : '' ?>>Fernanda Lima</option>
                            <option value="Juliana Souza" <?= $agenda['profissional'] == 'Juliana Souza' ? 'selected' : '' ?>>Juliana Souza</option>
                            <option value="Renata Almeida" <?= $agenda['profissional'] == 'Renata Almeida' ? 'selected' : '' ?>>Renata Almeida</option>
                            <option value="Patrícia Mendes" <?= $agenda['profissional'] == 'Patrícia Mendes' ? 'selected' : '' ?>>Patrícia Mendes</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-2">
                    <div class="col-md-6">
                        <label for="data_reserva" class="form-label-lumiere">Data do Agendamento</label>
                        <input type="date" class="form-control form-control-lumiere" id="data_reserva" name="data_reserva" value="<?= $data_atual ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="hora_reserva" class="form-label-lumiere">Horário</label>
                        <input type="time" class="form-control form-control-lumiere" id="hora_reserva" name="hora_reserva" value="<?= $hora_current ?? $hora_atual ?>" min="07:00" max="18:00" required>
                    </div>
                </div>

            </div>

            <div class="card card-lumiere-edit">
                <label for="observacoes" class="form-label-lumiere mb-2">Observações Especiais</label>
                <textarea name="observacoes" class="form-control form-control-lumiere textarea-lumiere" id="observacoes" placeholder="Cliente prefere produtos sem parabenos. Verificar disponibilidade da tonalidade da linha Wella."><?= htmlspecialchars($observacoes) ?></textarea>
            </div>

            <div class="value-duration-box">
                <div class="value-area">
                    <span>Valor Estimado</span>
                    <h2>R$ 480</h2>
                </div>
                <div class="duration-area fw-semibold">
                    Duração<br>
                    <span class="fw-normal opacity-70">2h 30min</span>
                </div>
            </div>

            <div class="action-buttons-row mb-5">
                <button type="submit" class="btn-lumiere-save"><i class="bi bi-check-circle"></i> Salvar Alterações</button>
                <a href="index.php" class="btn-lumiere-cancel"><i class="bi bi-x"></i> Cancelar</a>
            </div>

        </form>

    </div>

    <script>
        const servicosDados = <?= json_encode($servicos_dados) ?>;

        function atualizarValorDuracao() {
            const servico = document.getElementById('servico').value;
            const valorElemento = document.querySelector('.value-area h2');
            const duracaoElemento = document.querySelector('.duration-area span.fw-normal');

            if (servicosDados[servico]) {
                const dados = servicosDados[servico];
                valorElemento.textContent = 'R$ ' + dados.valor;
                duracaoElemento.textContent = dados.duracao;
            } else {
                valorElemento.textContent = 'R$ 0,00';
                duracaoElemento.textContent = '---';
            }
        }

        document.getElementById('servico').addEventListener('change', atualizarValorDuracao);
        window.addEventListener('load', atualizarValorDuracao);
    </script>

</body>
</html>