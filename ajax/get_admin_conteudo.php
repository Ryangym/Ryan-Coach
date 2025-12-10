<?php
if(session_status() === PHP_SESSION_NONE) session_start();

$pagina = $_GET['pagina'] ?? 'dashboard';

// Pega o nome do Admin
$nome_admin = $_SESSION['user_nome'] ?? 'Admin';
$partes_admin = explode(' ', trim($nome_admin));
$primeiro_nome_admin = strtoupper($partes_admin[0]);

switch ($pagina) {
    case 'dashboard':
        require_once '../config/db_connect.php';

        // 1. TOTAIS (Mantido)
        $query_alunos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE nivel = 'aluno'");
        $total_alunos = $query_alunos->fetchColumn();

        $sql_receita = "SELECT SUM(valor) as total FROM pagamentos WHERE status = 'pago' AND MONTH(data_pagamento) = MONTH(CURRENT_DATE()) AND YEAR(data_pagamento) = YEAR(CURRENT_DATE())";
        $query_receita = $pdo->query($sql_receita);
        $receita_mensal = $query_receita->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $sql_pendencias = "SELECT COUNT(*) FROM pagamentos WHERE status = 'pendente'";
        $query_pendencias = $pdo->query($sql_pendencias);
        $total_pendencias = $query_pendencias->fetchColumn();

        // 2. NOVAS QUERIES INTELIGENTES
        
        // A. Próximos Vencimentos (Status Pendente, ordenado por data mais próxima)
        $sql_vencimentos = "SELECT p.data_vencimento, p.valor, u.nome, u.foto 
                            FROM pagamentos p 
                            JOIN usuarios u ON p.usuario_id = u.id 
                            WHERE p.status = 'pendente' 
                            ORDER BY p.data_vencimento ASC 
                            LIMIT 4";
        $lista_vencimentos = $pdo->query($sql_vencimentos)->fetchAll(PDO::FETCH_ASSOC);

        // B. Novos Alunos (Últimos cadastros)
        $sql_novos = "SELECT nome, foto, data_cadastro 
                      FROM usuarios 
                      WHERE nivel = 'aluno' 
                      ORDER BY id DESC 
                      LIMIT 4";
        $lista_novos = $pdo->query($sql_novos)->fetchAll(PDO::FETCH_ASSOC);

        echo '
            <section id="admin-dash">
                <header class="dash-header">
                    <h1>OLÁ, <span class="highlight-text">'.$primeiro_nome_admin.'.</span></h1>
                    <p style="color: #888;">Visão geral do desempenho da academia</p>
                </header>

                <div class="stats-row">
                    <div class="glass-card">
                        <div class="card-label">ALUNOS TOTAIS</div>
                        <div class="card-body">
                            <div class="icon-box" style="color: #00ff00; border-color: #00ff00;">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="info-box">
                                <h3>'.$total_alunos.'</h3>
                                <p>Cadastrados</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-label">RECEITA (MÊS)</div>
                        <div class="card-body">
                            <div class="icon-box">
                                <i class="fa-solid fa-brazilian-real-sign"></i>
                            </div>
                            <div class="info-box">
                                <h3>R$ '.number_format($receita_mensal, 2, ',', '.').'</h3>
                                <p>Faturamento Atual</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-label">PENDÊNCIAS</div>
                        <div class="card-body">
                            <div class="icon-box" style="color: #ff4242; border-color: #ff4242;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </div>
                            <div class="info-box">
                                <h3>'.$total_pendencias.'</h3>
                                <p>Pagamentos em aberto</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="insights-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 10px;">
                    
                    <div class="glass-card" style="padding: 0; overflow: hidden;">
                        <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="color: #fff; font-family: Orbitron; font-size: 1rem; margin:0;">
                                <i class="fa-regular fa-calendar-xmark" style="color: #ff4242; margin-right: 10px;"></i> VENCIMENTOS
                            </h3>
                            <span style="font-size: 0.7rem; color: #666; text-transform: uppercase;">Prioridade</span>
                        </div>
                        
                        <div style="padding: 10px;">';
                        
                        if(count($lista_vencimentos) > 0) {
                            foreach($lista_vencimentos as $v) {
                                $foto = !empty($v['foto']) ? $v['foto'] : 'assets/img/icones/user-default.png';
                                $data = date('d/m', strtotime($v['data_vencimento']));
                                
                                // Lógica visual para data (se já passou, fica vermelho forte)
                                $is_atrasado = strtotime($v['data_vencimento']) < time();
                                $cor_data = $is_atrasado ? '#ff4242' : '#ccc';
                                $texto_data = $is_atrasado ? 'VENCEU '.$data : 'VENCE '.$data;

                                echo '
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; margin-bottom: 5px; background: rgba(255,255,255,0.02); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <img src="'.$foto.'" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #555;">
                                        <div>
                                            <h4 style="color: #ddd; font-size: 0.9rem; margin: 0;">'.$v['nome'].'</h4>
                                            <span style="color: var(--gold); font-size: 0.8rem;">R$ '.number_format($v['valor'], 2, ',', '.').'</span>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="color: '.$cor_data.'; font-size: 0.75rem; font-weight: bold; display: block;">'.$texto_data.'</span>
                                        <button class="btn-gold" style="padding: 2px 8px; font-size: 0.6rem; height: auto;" onclick="carregarConteudo(\'financeiro\')">COBRAR</button>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '<p style="text-align: center; color: #666; padding: 20px;">Nenhuma pendência próxima.</p>';
                        }

        echo '          </div>
                    </div>

                    <div class="glass-card" style="padding: 0; overflow: hidden;">
                        <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="color: #fff; font-family: Orbitron; font-size: 1rem; margin:0;">
                                <i class="fa-solid fa-rocket" style="color: var(--gold); margin-right: 10px;"></i> NOVOS MEMBROS
                            </h3>
                            <span style="font-size: 0.7rem; color: #666; text-transform: uppercase;">Crescimento</span>
                        </div>
                        
                        <div style="padding: 10px;">';
                        
                        if(count($lista_novos) > 0) {
                            foreach($lista_novos as $n) {
                                $foto = !empty($n['foto']) ? $n['foto'] : 'assets/img/icones/user-default.png';
                                $data_cadastro = date('d/m', strtotime($n['data_cadastro']));

                                echo '
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; margin-bottom: 5px; background: rgba(255,255,255,0.02); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <img src="'.$foto.'" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #555;">
                                        <div>
                                            <h4 style="color: #ddd; font-size: 0.9rem; margin: 0;">'.$n['nome'].'</h4>
                                            <span style="color: #666; font-size: 0.75rem;">Entrou em '.$data_cadastro.'</span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 5px;">
                                        <button style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 30px; height: 30px; border-radius: 50%; cursor: pointer;" title="Enviar Treino" onclick="carregarConteudo(\'treinos_editor\')"><i class="fa-solid fa-dumbbell"></i></button>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '<p style="text-align: center; color: #666; padding: 20px;">Nenhum cadastro recente.</p>';
                        }

        echo '          </div>
                    </div>

                </div>
            </section>
        ';
        break;

    case 'alunos':
        require_once '../config/db_connect.php';
        
        // Busca todos os alunos
        $sql = "SELECT * FROM usuarios WHERE nivel = 'aluno' ORDER BY nome ASC";
        $alunos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $total_alunos = count($alunos);

        echo '
            <section id="gerenciar-alunos">
                <header class="dash-header">
                    <h1>GERENCIAR <span class="highlight-text">ALUNOS</span></h1>
                    <p class="text-desc">Controle total da base de atletas ('.$total_alunos.')</p>
                </header>
                
                <div class="glass-card mt-large">
                    
                    <div class="section-header-row">
                        <div style="flex: 1; position: relative; max-width: 400px;">
                            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #666;"></i>
                            <input type="text" id="searchAluno" onkeyup="filtrarAlunos()" placeholder="Buscar por nome ou email..." class="admin-input" style="padding-left: 40px;">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table" id="tabelaAlunos">
                            <thead>
                                <tr>
                                    <th>ALUNO</th>
                                    <th>CONTATO</th>
                                    <th>CADASTRO</th>
                                    <th style="text-align: right;">AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
                            if ($total_alunos > 0) {
                                foreach ($alunos as $a) {
                                    $foto = !empty($a['foto']) ? $a['foto'] : 'assets/img/icones/user-default.png';
                                    $data_cadastro = date('d/m/y', strtotime($a['data_cadastro']));
                                    
                                    // Link do Zap limpo (remove caracteres não numéricos)
                                    $zap_clean = preg_replace('/[^0-9]/', '', $a['telefone']);
                                    $link_zap = "https://wa.me/55".$zap_clean;

                                    // Dados para o Modal (Json no atributo data)
                                    $dados_json = htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8');

                                    echo '
                                    <tr class="aluno-row">
                                        <td>
                                            <div class="user-cell">
                                                <img src="'.$foto.'" class="table-avatar" alt="Foto">
                                                <div>
                                                    <span style="display:block; font-weight:bold; color:#fff;">'.$a['nome'].'</span>
                                                    <span style="font-size:0.8rem; color:#666;">'.$a['email'].'</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <span style="color:#ccc; font-size:0.9rem;">'.$a['telefone'].'</span>
                                                <a href="'.$link_zap.'" target="_blank" class="btn-action-icon btn-confirm" title="Chamar no WhatsApp" style="width: 25px; height: 25px; font-size: 0.8rem;">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td style="color:#888;">'.$data_cadastro.'</td>
                                        <td style="text-align: right;">
                                            <div style="display:flex; gap:5px; justify-content:flex-end;">
                                                
                                                <button class="btn-action-icon" onclick="carregarConteudo(\'aluno_historico&id='.$a['id'].'\')" title="Ver Histórico de Treinos" style="border-color: #888; color: #ccc;">
                                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                                </button>

                                                <button class="btn-action-icon" onclick=\'openEditModal('.$dados_json.')\' title="Editar Dados">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                
                                                <a href="actions/admin_aluno.php?id='.$a['id'].'&acao=promover" class="btn-action-icon" style="border-color: var(--gold); color: var(--gold);" onclick="return confirm(\'ATENÇÃO: Tem certeza que deseja transformar este aluno em ADMINISTRADOR?\n\nEle terá acesso total ao sistema, incluindo financeiro.\')" title="Promover a Admin">
                                                    <i class="fa-solid fa-user-shield"></i>
                                                </a>

                                                <a href="actions/admin_aluno.php?id='.$a['id'].'&acao=excluir" class="btn-action-icon btn-delete" onclick="return confirm(\'Tem certeza que deseja remover este aluno?\')" title="Remover Aluno">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" style="text-align:center; padding:30px;">Nenhum aluno encontrado.</td></tr>';
                            }

        echo '              </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div id="modalEditarAluno" class="modal-overlay">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeEditModal()">&times;</button>
                    
                    <h3 class="section-title" style="color: var(--gold); margin-bottom: 20px; text-align: center;">
                        <i class="fa-solid fa-user-pen"></i> Editar Aluno
                    </h3>
                    
                    <form action="actions/admin_aluno.php" method="POST">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Nome Completo</label>
                                <input type="text" name="nome" id="edit_nome" class="admin-input" required>
                            </div>
                        </div>

                        <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Email</label>
                                <input type="email" name="email" id="edit_email" class="admin-input" required>
                            </div>
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Telefone</label>
                                <input type="text" name="telefone" id="edit_telefone" class="admin-input">
                            </div>
                        </div>

                        <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#FFBA42; font-size: 0.8rem;">Vencimento do Plano (Acesso)</label>
                                <input type="date" name="data_expiracao" id="edit_expiracao" class="admin-input">
                                <p style="font-size:0.7rem; color:#666; margin-top:5px;">Se passar desta data, o aluno perde o acesso.</p>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="color:#ff4242; font-size: 0.8rem;">Redefinir Senha (Opcional)</label>
                            <input type="text" name="nova_senha" class="admin-input" placeholder="Deixe vazio para não alterar">
                            <p style="font-size:0.7rem; color:#666; margin-top:5px;">Se preencher, a senha do aluno será trocada.</p>
                        </div>

                        <button type="submit" class="btn-gold" style="width: 100%; padding: 15px;">SALVAR ALTERAÇÕES</button>
                    </form>
                </div>
            </div>

        ';
        break;

    case 'aluno_historico':
        require_once '../config/db_connect.php';
        
        $aluno_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $data_ref = $_GET['data_ref'] ?? null;

        if (!$aluno_id) { echo "Aluno não identificado."; break; }

        // Busca dados do aluno para o cabeçalho
        $stmt_aluno = $pdo->prepare("SELECT nome, foto FROM usuarios WHERE id = ?");
        $stmt_aluno->execute([$aluno_id]);
        $dados_aluno = $stmt_aluno->fetch(PDO::FETCH_ASSOC);
        $foto_aluno = $dados_aluno['foto'] ?? 'assets/img/user-default.png';

        // --- MODO 1: DETALHES DO TREINO (TABELA) ---
        if ($data_ref) {
            // Infos Gerais do Treino
            $sql_info = "SELECT DISTINCT t.nome as nome_treino, td.letra 
                         FROM treino_historico th
                         JOIN treinos t ON th.treino_id = t.id
                         JOIN treino_divisoes td ON th.divisao_id = td.id
                         WHERE th.aluno_id = :uid AND th.data_treino = :dt";
            $stmt = $pdo->prepare($sql_info);
            $stmt->execute(['uid' => $aluno_id, 'dt' => $data_ref]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            // Exercícios e Cargas
            $sql_detalhes = "SELECT th.*, e.nome_exercicio 
                             FROM treino_historico th
                             JOIN exercicios e ON th.exercicio_id = e.id
                             WHERE th.aluno_id = :uid AND th.data_treino = :dt
                             ORDER BY th.id ASC";
            $stmt = $pdo->prepare($sql_detalhes);
            $stmt->execute(['uid' => $aluno_id, 'dt' => $data_ref]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '
            <section id="admin-historico-detalhe">
                <div style="display:flex; align-items:center; gap:20px; margin-bottom:30px;">
                    <button class="btn-action-icon" onclick="carregarConteudo(\'aluno_historico&id='.$aluno_id.'\')">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div>
                        <h2 style="color:#fff; font-family:Orbitron; margin:0;">DETALHES DO TREINO</h2>
                        <p style="color:#888; font-size:0.9rem;">Atleta: <strong style="color:var(--gold);">'.$dados_aluno['nome'].'</strong></p>
                    </div>
                </div>

                <div class="glass-card">
                    <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:8px; margin-bottom:20px; border-left:4px solid var(--gold);">
                        <h3 style="color:#fff; margin:0; font-size:1.1rem;">Treino '.$info['letra'].' - '.$info['nome_treino'].'</h3>
                        <span style="color:#888; font-size:0.85rem;">Realizado em: '.date('d/m/Y \à\s H:i', strtotime($data_ref)).'</span>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>EXERCÍCIO</th>
                                    <th style="text-align:center;">SÉRIE</th>
                                    <th style="text-align:center;">CARGA (KG)</th>
                                    <th style="text-align:center;">REPS</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
                            foreach ($registros as $reg) {
                                echo '<tr>
                                        <td style="color:#fff; font-weight:bold;">'.$reg['nome_exercicio'].'</td>
                                        <td style="text-align:center; color:#888;">#'.$reg['serie_numero'].'</td>
                                        <td style="text-align:center;"><span class="status-badge" style="background:#222; color:#fff; border:1px solid #444;">'.($reg['carga_kg']*1).'kg</span></td>
                                        <td style="text-align:center;"><span class="status-badge" style="background:#222; color:#ccc; border:1px solid #444;">'.$reg['reps_realizadas'].'</span></td>
                                      </tr>';
                            }

            echo '          </tbody>
                        </table>
                    </div>
                </div>
            </section>';
            break;
        }

        // --- MODO 2: TIMELINE (LISTA DE DATAS) ---
        $sql_lista = "SELECT th.data_treino, t.nome as nome_treino, td.letra
                      FROM treino_historico th
                      JOIN treinos t ON th.treino_id = t.id
                      JOIN treino_divisoes td ON th.divisao_id = td.id
                      WHERE th.aluno_id = :uid
                      GROUP BY th.data_treino
                      ORDER BY th.data_treino DESC";
        $stmt = $pdo->prepare($sql_lista);
        $stmt->execute(['uid' => $aluno_id]);
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '
        <section id="admin-historico-lista">
            <header class="dash-header">
                <div style="display:flex; align-items:center; gap:15px;">
                    <img src="'.$foto_aluno.'" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid var(--gold);">
                    <div>
                        <h1>HISTÓRICO DE <span class="highlight-text">TREINOS</span></h1>
                        <p class="text-desc">Visualizando: '.$dados_aluno['nome'].'</p>
                    </div>
                </div>
            </header>

            <div class="glass-card mt-large">';
            
            if (empty($historico)) {
                echo '<p style="text-align:center; color:#666; padding:40px;">Este aluno ainda não registrou nenhum treino.</p>';
            } else {
                echo '<div class="admin-timeline">';
                
                foreach ($historico as $h) {
                    $data = date('d/m/Y', strtotime($h['data_treino']));
                    $hora = date('H:i', strtotime($h['data_treino']));
                    $link = 'aluno_historico&id='.$aluno_id.'&data_ref='.$h['data_treino'];

                    echo '
                    <div class="timeline-item" onclick="carregarConteudo(\''.$link.'\')">
                        <div class="tl-date">
                            <span class="tl-day">'.$data.'</span>
                            <span class="tl-time">'.$hora.'</span>
                        </div>
                        <div class="tl-content">
                            <span class="tl-badge">TREINO '.$h['letra'].'</span>
                            <strong class="tl-title">'.$h['nome_treino'].'</strong>
                        </div>
                        <div class="tl-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                    </div>';
                }
                echo '</div>';
            }

        echo '</div>
        </section>';
        break;

    case 'treinos_editor':
        require_once '../config/db_connect.php';
        
        // 1. LISTAR TREINOS EXISTENTES
        // Busca treinos com nome do aluno
        $sql_list = "SELECT t.*, u.nome as nome_aluno, u.foto as foto_aluno 
                     FROM treinos t 
                     JOIN usuarios u ON t.aluno_id = u.id 
                     ORDER BY t.criado_em DESC";
        $treinos = $pdo->query($sql_list)->fetchAll(PDO::FETCH_ASSOC);

        // 2. LISTA DE ALUNOS (Para o Form de Cadastro)
        $alunos = $pdo->query("SELECT id, nome FROM usuarios WHERE nivel = 'aluno' ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo '
            <section id="editor-treinos">
                <header class="dash-header">
                    <h1>EDITOR DE <span class="highlight-text">TREINOS</span></h1>
                    <p class="text-desc">Gerencie as periodizações e fichas dos alunos.</p>
                </header>

                <div class="glass-card">
                    <div class="section-header-row">
                        <h3 class="section-title" style="margin:0"><i class="fa-solid fa-list"></i> PLANEJAMENTOS</h3>
                        <button class="btn-gold" onclick="toggleNovoTreino()">
                            <i class="fa-solid fa-plus"></i> NOVO TREINO
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ALUNO</th>
                                    <th>NOME DO TREINO</th>
                                    <th>TIPO</th>
                                    <th>VIGÊNCIA</th>
                                    <th style="text-align:right;">AÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
                            if (count($treinos) > 0) {
                                foreach ($treinos as $t) {
                                    $foto = !empty($t['foto_aluno']) ? $t['foto_aluno'] : 'assets/img/icones/user-default.png';
                                    $inicio = date('d/m', strtotime($t['data_inicio']));
                                    $fim = date('d/m', strtotime($t['data_fim']));
                                    
                                    // Badge de Nível
                                    $corBadge = ($t['nivel_plano'] == 'basico') ? '#ccc' : (($t['nivel_plano'] == 'avancado') ? '#FFBA42' : '#ff4242');
                                    
                                    echo '
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <img src="'.$foto.'" class="table-avatar" alt="Foto">
                                                <span>'.$t['nome_aluno'].'</span>
                                            </div>
                                        </td>
                                        <td><strong style="color:#fff;">'.$t['nome'].'</strong><br><span style="font-size:0.8rem; color:#666;">Divisão '.$t['divisao_nome'].'</span></td>
                                        <td><span class="status-badge" style="color:'.$corBadge.'; border-color:'.$corBadge.'; background:transparent;">'.strtoupper($t['nivel_plano']).'</span></td>
                                        <td style="color:#888;">'.$inicio.' a '.$fim.'</td>
                                        <td style="text-align:right;">
                                            <div style="display:flex; gap:10px; justify-content:flex-end; align-items:center;">
                                                <a href="actions/treino_delete.php?id='.$t['id'].'" 
                                                class="btn-action-icon btn-delete" 
                                                onclick="return confirm(\'Tem certeza que deseja EXCLUIR este planejamento?\n\nIsso apagará permanentemente:\n- Todas as divisões\n- Exercícios e Séries\n- Histórico de periodização vinculados a ele.\')" 
                                                title="Excluir Treino">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                                <button class="btn-gold" style="padding: 5px 15px; font-size: 0.8rem;" onclick="carregarConteudo(\'treino_painel&id='.$t['id'].'\')">
                                                    GERENCIAR <i class="fa-solid fa-arrow-right"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">Nenhum treino criado ainda.</td></tr>';
                            }

        echo '              </tbody>
                        </table>
                    </div>
                </div>

                <div class="glass-card mt-large" id="box-novo-treino" style="display:none; border: 1px solid var(--gold); margin-top: 30px;">
                    <h3 class="section-title" style="color: var(--gold);"><i class="fa-solid fa-dumbbell"></i> Criar Nova Estrutura</h3>
                    
                    <form action="actions/treino_create.php" method="POST">
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label class="input-label">Selecione o Atleta</label>
                                <select name="aluno_id" class="admin-input" required>
                                    <option value="">Escolha...</option>';
                                    foreach($alunos as $al) { echo '<option value="'.$al['id'].'">'.$al['nome'].'</option>'; }
        echo '                  </select>
                            </div>
                            <div class="form-col">
                                <label class="input-label">Nome do Planejamento</label>
                                <input type="text" name="nome" class="admin-input" placeholder="Ex: Hipertrofia Fase 1" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label class="input-label">Tipo de Plano</label>
                                <select name="nivel" class="admin-input" id="selectNivel" onchange="togglePeriodizacao()" required>
                                    <option value="basico">Básico (Ficha Fixa)</option>
                                    <option value="avancado">Avançado (Periodizado)</option>
                                    <option value="premium">Premium (Periodizado +)</option>
                                </select>
                            </div>
                            <div class="form-col">
                                <label class="input-label">Data de Início</label>
                                <input type="date" name="data_inicio" class="admin-input" required value="'.date('Y-m-d').'">
                            </div>
                            <div class="form-col" style="flex: 0 0 150px;">
                                <label class="input-label">Divisão</label>
                                <input type="text" name="divisao" class="admin-input" placeholder="Ex: ABC" maxlength="5" style="text-transform:uppercase;" required>
                            </div>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label class="input-label">Dias de Treino</label>
                            <div class="days-selector">
                                <label><input type="checkbox" name="dias_semana[]" value="1" class="day-checkbox"><span class="day-label">SEG</span></label>
                                <label><input type="checkbox" name="dias_semana[]" value="2" class="day-checkbox"><span class="day-label">TER</span></label>
                                <label><input type="checkbox" name="dias_semana[]" value="3" class="day-checkbox"><span class="day-label">QUA</span></label>
                                <label><input type="checkbox" name="dias_semana[]" value="4" class="day-checkbox"><span class="day-label">QUI</span></label>
                                <label><input type="checkbox" name="dias_semana[]" value="5" class="day-checkbox"><span class="day-label">SEX</span></label>
                                <label><input type="checkbox" name="dias_semana[]" value="6" class="day-checkbox"><span class="day-label">SÁB</span></label>
                                <label><input type="checkbox" name="dias_semana[]" value="7" class="day-checkbox"><span class="day-label">DOM</span></label>
                            </div>
                        </div>

                        <div id="aviso-periodizacao" class="alert-box">
                            <span class="alert-title">Modo Periodização Ativo</span>
                            <p class="alert-text">Serão gerados 12 Microciclos automaticamente.</p>
                        </div>

                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn-gold" style="background: transparent; border: 1px solid #555; color: #ccc; margin-right: 10px;" onclick="toggleNovoTreino()">Cancelar</button>
                            <button type="submit" class="btn-gold">CRIAR ESTRUTURA</button>
                        </div>
                    </form>
                </div>
            </section>

        ';
        break;

    case 'financeiro':
        require_once '../config/db_connect.php';
        
        // 1. CÁLCULOS (Mantive igual)
        $sql_fat = "SELECT SUM(valor) as total FROM pagamentos WHERE status = 'pago' AND MONTH(data_pagamento) = MONTH(CURRENT_DATE()) AND YEAR(data_pagamento) = YEAR(CURRENT_DATE())";
        $stmt_fat = $pdo->query($sql_fat);
        $faturamento = $stmt_fat->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $sql_pend = "SELECT SUM(valor) as total FROM pagamentos WHERE status = 'pendente'";
        $stmt_pend = $pdo->query($sql_pend);
        $pendente = $stmt_pend->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 2. LISTA DE ALUNOS (Para o Select)
        $alunos = $pdo->query("SELECT id, nome FROM usuarios WHERE nivel = 'aluno' ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 3. HISTÓRICO (Agora buscando a FOTO também)
        $sql_hist = "SELECT p.*, u.nome as nome_aluno, u.foto as foto_aluno 
                     FROM pagamentos p 
                     JOIN usuarios u ON p.usuario_id = u.id 
                     ORDER BY p.id DESC LIMIT 10";
        $transacoes = $pdo->query($sql_hist)->fetchAll(PDO::FETCH_ASSOC);

        echo '
            <section id="financeiro">
                <header class="dash-header">
                    <h1>CONTROLE <span class="highlight-text">FINANCEIRO</span></h1>
                    <p class="text-desc">Gestão de caixa e assinaturas</p>
                </header>

                <div class="stats-row">
                    <div class="glass-card">
                        <div class="card-label">FATURAMENTO (MÊS ATUAL)</div>
                        <div class="card-body">
                            <div class="icon-box success"><i class="fa-solid fa-arrow-trend-up"></i></div>
                            <div class="info-box">
                                <h3>R$ '.number_format($faturamento, 2, ',', '.').'</h3>
                                <p class="text-muted">Entradas confirmadas</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-label">A RECEBER (PENDENTE)</div>
                        <div class="card-body">
                            <div class="icon-box gold"><i class="fa-solid fa-clock"></i></div>
                            <div class="info-box">
                                <h3>R$ '.number_format($pendente, 2, ',', '.').'</h3>
                                <p class="text-muted">Previsão de entrada</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card mt-large">
                    
                    <div class="section-header-row">
                        <h3 class="section-title" style="margin:0">HISTÓRICO DE CAIXA</h3>
                        <button class="btn-gold" onclick="openModal()">
                            <i class="fa-solid fa-plus"></i> NOVO LANÇAMENTO
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ALUNO</th>
                                    <th>DESCRIÇÃO</th>
                                    <th>VENCIMENTO</th>
                                    <th>VALOR</th>
                                    <th>STATUS</th>
                                    <th style="text-align: right;">AÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
                            if (count($transacoes) > 0) {
                                foreach ($transacoes as $t) {
                                    $statusClass = ($t['status'] == 'pago') ? 'pago' : 'pendente';
                                    $dataExibicao = date('d/m/Y', strtotime($t['data_vencimento']));
                                    
                                    $fotoUser = !empty($t['foto_aluno']) ? $t['foto_aluno'] : 'assets/img/icones/user-default.png';
                                    
                                    // Botões de Ação
                                    $btns = '<div style="display:flex; gap:15px; justify-content:flex-end;">';
                                    
                                    // 1. Botão Pagar/Estornar
                                    if ($t['status'] == 'pendente') {
                                        $btns .= '<a href="actions/financeiro_status.php?id='.$t['id'].'&acao=pagar" class="btn-action-icon btn-confirm" title="Confirmar Pagamento"><i class="fa-solid fa-check"></i></a>';
                                    } else {
                                        $btns .= '<a href="actions/financeiro_status.php?id='.$t['id'].'&acao=estornar" class="btn-action-icon btn-undo" title="Desfazer/Estornar"><i class="fa-solid fa-rotate-left"></i></a>';
                                    }

                                    // 2. Botão Excluir (NOVO)
                                    $btns .= '<a href="actions/financeiro_status.php?id='.$t['id'].'&acao=excluir" class="btn-action-icon btn-delete" title="Excluir Registro" onclick="return confirm(\'Tem certeza que deseja excluir este lançamento? Isso não pode ser desfeito.\')"><i class="fa-solid fa-trash"></i></a>';
                                    
                                    $btns .= '</div>';

                                    echo '
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <img src="'.$fotoUser.'" class="table-avatar" alt="Foto">
                                                <span>'.$t['nome_aluno'].'</span>
                                            </div>
                                        </td>
                                        <td>'.$t['descricao'].'</td>
                                        <td>'.$dataExibicao.'</td>
                                        <td><strong>R$ '.number_format($t['valor'], 2, ',', '.').'</strong></td>
                                        <td><span class="status-badge '.$statusClass.'">'.strtoupper($t['status']).'</span></td>
                                        <td style="text-align: right;">'.$btns.'</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #666;">Nenhum lançamento encontrado.</td></tr>';
                            }

        echo '              </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <div id="modalLancamento" class="modal-overlay">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                    
                    <h3 class="section-title" style="color: var(--gold); margin-bottom: 20px; text-align: center;">
                        <i class="fa-solid fa-money-bill-wave"></i> Novo Lançamento
                    </h3>
                    
                    <form action="actions/financeiro_add.php" method="POST">
                        <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Aluno</label>
                                <select name="usuario_id" class="admin-input" required>
                                    <option value="">Selecione o aluno...</option>';
                                    foreach($alunos as $aluno) {
                                        echo '<option value="'.$aluno['id'].'">'.$aluno['nome'].'</option>';
                                    }
        echo '                  </select>
                            </div>
                        </div>

                        <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div style="flex: 2;">
                                <label style="color:#ccc; font-size: 0.8rem;">Descrição</label>
                                <input type="text" name="descricao" class="admin-input" placeholder="Ex: Plano Trimestral" required>
                            </div>
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Valor (R$)</label>
                                <input type="number" name="valor" step="0.01" class="admin-input" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="row-flex" style="display: flex; gap: 15px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Vencimento</label>
                                <input type="date" name="data_vencimento" class="admin-input" required value="'.date('Y-m-d').'">
                            </div>
                            <div style="flex: 1;">
                                <label style="color:#ccc; font-size: 0.8rem;">Status</label>
                                <select name="status" class="admin-input">
                                    <option value="pago">Pago (Entrada)</option>
                                    <option value="pendente">Pendente (Aguardando)</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-gold" style="width: 100%; padding: 15px; font-size: 1rem;">REGISTRAR VENDA</button>
                    </form>
                </div>
            </div>

        ';
        break;
    
    case 'treino_painel':
        require_once '../config/db_connect.php';
        $treino_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        if (!$treino_id) { echo "ID Inválido"; break; }

        // 1. BUSCAR DADOS GERAIS
        $sql = "SELECT t.*, u.nome as nome_aluno FROM treinos t JOIN usuarios u ON t.aluno_id = u.id WHERE t.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $treino_id]);
        $treino = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. BUSCAR DIVISÕES
        $sql_div = "SELECT * FROM treino_divisoes WHERE treino_id = :id ORDER BY letra ASC";
        $stmt_div = $pdo->prepare($sql_div);
        $stmt_div->execute(['id' => $treino_id]);
        $divisoes = $stmt_div->fetchAll(PDO::FETCH_ASSOC);

        // 3. BUSCAR PERIODIZAÇÃO E MICROCICLOS (Lógica Adicionada)
        $microciclos = [];
        if ($treino['nivel_plano'] !== 'basico') {
            $stmt_per = $pdo->prepare("SELECT id FROM periodizacoes WHERE treino_id = ?");
            $stmt_per->execute([$treino_id]);
            $periodizacao_id = $stmt_per->fetchColumn();

            if ($periodizacao_id) {
                $stmt_micro = $pdo->prepare("SELECT * FROM microciclos WHERE periodizacao_id = ? ORDER BY semana_numero ASC");
                $stmt_micro->execute([$periodizacao_id]);
                $microciclos = $stmt_micro->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
        // --- INICIO HTML ---
        echo '
            <section id="painel-treino">
                <div style="display:flex; align-items:center; gap:20px; margin-bottom:30px;">
                    <button class="btn-action-icon" onclick="carregarConteudo(\'treinos_editor\')"><i class="fa-solid fa-arrow-left"></i></button>
                    <div>
                        <h2 style="color:#fff; font-family:Orbitron; margin:0;">'.$treino['nome'].'</h2>
                        <p style="color:#888; font-size:0.9rem;">Aluno: <strong style="color:var(--gold);">'.$treino['nome_aluno'].'</strong> • '.strtoupper($treino['nivel_plano']).'</p>
                    </div>
                </div>

                ';
                if (!empty($microciclos)) {
                    echo '<h3 class="section-title" style="font-size:1rem; margin-bottom:10px;">PERIODIZAÇÃO (12 SEMANAS)</h3>
                          <div class="timeline-wrapper">';
                    
                    foreach ($microciclos as $m) {
                        $inicio = date('d/m', strtotime($m['data_inicio_semana']));
                        $fim = date('d/m', strtotime($m['data_fim_semana']));
                        
                        // Marca a semana atual
                        $hoje = date('Y-m-d');
                        $activeClass = ($hoje >= $m['data_inicio_semana'] && $hoje <= $m['data_fim_semana']) ? 'active' : '';
                        
                        // JSON para o modal
                        $m_json = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');

                        echo '
                        <div class="micro-card '.$activeClass.'" onclick=\'openMicroModal('.$m_json.', '.$treino_id.')\'>
                            <span class="micro-week">SEMANA '.$m['semana_numero'].' <i class="fa-solid fa-pen" style="font-size:0.6rem; margin-left:5px;"></i></span>
                            <span class="micro-date">'.$inicio.' - '.$fim.'</span>
                            <div style="margin-top:5px; font-size:0.7rem; color: inherit; opacity:0.7;">'.$m['nome_fase'].'</div>
                        </div>';
                    }
                    echo '</div>';
                }
        
        echo '
                <div class="glass-card">
                    <div class="division-tabs">';
                        $first = true;
                        foreach ($divisoes as $div) {
                            $active = $first ? 'active' : '';
                            echo '<button class="div-tab-btn '.$active.'" onclick="openTab(event, \'div_'.$div['letra'].'\')">TREINO '.$div['letra'].'</button>';
                            $first = false;
                        }
        echo '      </div>';

                    // CONTEÚDO DAS ABAS (Lista de Exercícios)
                    $firstContent = true;
                    foreach ($divisoes as $div) {
                        $display = $firstContent ? 'active' : '';
                        
                        // Busca exercícios desta divisão
                        $sqlEx = "SELECT * FROM exercicios WHERE divisao_id = ? ORDER BY ordem ASC";
                        $stmtEx = $pdo->prepare($sqlEx);
                        $stmtEx->execute([$div['id']]);
                        $exercicios = $stmtEx->fetchAll(PDO::FETCH_ASSOC);

                        echo '
                        <div id="div_'.$div['letra'].'" class="division-content '.$display.'">
                            
                            <div class="div-header">
                                <div><h3 style="color:#fff; margin:0;">Ficha '.$div['letra'].'</h3></div>
                                <button class="btn-gerenciar" onclick="openExercicioModal('.$div['id'].', '.$treino_id.')">
                                    <i class="fa-solid fa-plus"></i> ADD EXERCÍCIO
                                </button>
                            </div>

                            <div class="exercise-list">';
                                
                                if (count($exercicios) > 0) {
                                    foreach ($exercicios as $ex) {
                                        // Busca as séries
                                        $sqlSeries = "SELECT * FROM series WHERE exercicio_id = ?";
                                        $stmtSeries = $pdo->prepare($sqlSeries);
                                        $stmtSeries->execute([$ex['id']]);
                                        $series = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);

                                        echo '
                                        <div class="exercise-card">
                                            <div class="ex-info">
                                                <span class="ex-meta">'.strtoupper($ex['tipo_mecanica']).'</span>
                                                <h4>'.$ex['nome_exercicio'].'</h4>
                                                <div class="sets-container">';
                                                    foreach ($series as $s) {
                                                        $infoReps = $s['reps_fixas'] ? "(".$s['reps_fixas'].")" : "";
                                                        echo '<span class="set-tag '.$s['categoria'].'">'.$s['quantidade'].'x '.strtoupper($s['categoria']).' '.$infoReps.'</span>';
                                                    }
                                        echo '  </div>
                                            </div>
                                            <div class="ex-actions">
                                                <button class="btn-action-icon"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn-action-icon btn-delete"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        </div>';
                                    }
                                } else {
                                    echo '<p style="text-align:center; color:#666; padding:30px;">Nenhum exercício cadastrado.</p>';
                                }
                        
                        echo '</div>
                        </div>';
                        $firstContent = false;
                    }

        echo '  </div>
            </section>

            <div id="modalExercicio" class="modal-overlay">
                <div class="modal-content" style="max-width: 700px;">
                    <button class="modal-close" onclick="closeExercicioModal()">&times;</button>
                    
                    <h3 class="section-title" style="color:var(--gold); margin-bottom:20px;">Novo Exercício</h3>
                    
                    <form action="actions/treino_add_exercicio.php" method="POST" id="formExercicio">
                        <input type="hidden" name="divisao_id" id="modal_divisao_id">
                        <input type="hidden" name="treino_id" id="modal_treino_id">
                        <input type="hidden" name="series_data" id="series_json_input">

                        <div class="row-flex" style="display:flex; gap:15px; margin-bottom:15px;">
                            <div style="flex:2;">
                                <label class="input-label">Nome do Exercício</label>
                                <input type="text" name="nome_exercicio" class="admin-input" placeholder="Ex: Supino Reto" required>
                            </div>
                            <div style="flex:1;">
                                <label class="input-label">Mecânica</label>
                                <select name="tipo_mecanica" class="admin-input">
                                    <option value="livre">Livre / Máquina</option>
                                    <option value="composto">Composto (Periodizado)</option>
                                    <option value="isolador">Isolador (Periodizado)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row-flex" style="display:flex; gap:15px; margin-bottom:15px;">
                            <div style="flex:1;">
                                <label class="input-label">Link Vídeo (Youtube/Drive)</label>
                                <input type="text" name="video_url" class="admin-input" placeholder="https://...">
                            </div>
                            <div style="flex:1;">
                                <label class="input-label">Observação</label>
                                <input type="text" name="observacao" class="admin-input" placeholder="Ex: Segurar 2s na descida">
                            </div>
                        </div>

                        <hr style="border:0; border-top:1px solid #333; margin:20px 0;">

                        <h4 style="color:#fff; font-size:0.9rem; margin-bottom:10px;">Configuração de Séries</h4>
                        
                        <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:8px;">
                            <div style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
                                <div style="width:60px;">
                                    <label class="input-label" style="font-size:0.7rem;">Qtd</label>
                                    <input type="number" id="set_qtd" class="admin-input" value="1" style="padding:8px;">
                                </div>
                                <div style="flex:1; min-width:100px;">
                                    <label class="input-label" style="font-size:0.7rem;">Tipo</label>
                                    <select id="set_tipo" class="admin-input" style="padding:8px;">
                                        <option value="warmup">Warm Up (Aquecimento)</option>
                                        <option value="feeder">Feeder (Reconhecimento)</option>
                                        <option value="work">Work Set (Valendo)</option>
                                        <option value="top">Top Set (Carga Máx)</option>
                                        <option value="backoff">Backoff (Redução)</option>
                                        <option value="falha">Falha Total</option>
                                    </select>
                                </div>
                                <div style="flex:1; min-width:80px;">
                                    <label class="input-label" style="font-size:0.7rem;">Reps</label>
                                    <input type="text" id="set_reps" class="admin-input" placeholder="Ex: 8-12" style="padding:8px;">
                                </div>
                                <div style="flex:1; min-width:80px;">
                                    <label class="input-label" style="font-size:0.7rem;">Descanso</label>
                                    <input type="text" id="set_desc" class="admin-input" placeholder="Ex: 90s" style="padding:8px;">
                                </div>
                                <div style="width:60px;">
                                    <label class="input-label" style="font-size:0.7rem;">RPE</label>
                                    <input type="number" id="set_rpe" class="admin-input" placeholder="1-10" style="padding:8px;">
                                </div>
                                <button type="button" class="btn-gold" onclick="addSetToList()" style="padding:8px 15px;">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            <div id="temp-sets-list">
                                <p style="color:#666; font-size:0.8rem; text-align:center; margin-top:10px;">Nenhuma série adicionada.</p>
                            </div>
                        </div>

                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn-gold" style="background:transparent; border:1px solid #555; color:#ccc; margin-right:10px;" onclick="closeExercicioModal()">Cancelar</button>
                            <button type="submit" class="btn-gold">SALVAR EXERCÍCIO</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="modalMicro" class="modal-overlay">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeMicroModal()">&times;</button>
                    
                    <h3 class="section-title" style="color:var(--gold); margin-bottom:20px;">
                        <i class="fa-solid fa-calendar-week"></i> Configurar Semana <span id="span_semana_num"></span>
                    </h3>
                    
                    <form action="actions/treino_edit_micro.php" method="POST">
                        <input type="hidden" name="micro_id" id="micro_id">
                        <input type="hidden" name="treino_id" id="micro_treino_id">

                        <div style="margin-bottom:15px;">
                            <label class="input-label">Fase / Nome da Semana</label>
                            <input type="text" name="nome_fase" id="micro_fase" class="admin-input" placeholder="Ex: Força ou Choque" required>
                        </div>

                        <h4 style="color:#fff; font-size:0.8rem; margin-bottom:5px; border-bottom:1px solid #333; padding-bottom:5px;">Multiarticulares / Compostos</h4>
                        <div class="row-flex" style="display:flex; gap:15px; margin-bottom:15px;">
                            <div style="flex:2;">
                                <label class="input-label">Faixa de Repetições</label>
                                <input type="text" name="reps_compostos" id="micro_reps_comp" class="admin-input" placeholder="Ex: 6 a 8">
                            </div>
                            <div style="flex:1;">
                                <label class="input-label">Descanso (seg)</label>
                                <input type="number" name="descanso_compostos" id="micro_desc_comp" class="admin-input" placeholder="Ex: 120">
                            </div>
                        </div>

                        <h4 style="color:#fff; font-size:0.8rem; margin-bottom:5px; border-bottom:1px solid #333; padding-bottom:5px;">Isoladores / Monoarticulares</h4>
                        <div class="row-flex" style="display:flex; gap:15px; margin-bottom:15px;">
                            <div style="flex:2;">
                                <label class="input-label">Faixa de Repetições</label>
                                <input type="text" name="reps_isoladores" id="micro_reps_iso" class="admin-input" placeholder="Ex: 10 a 12">
                            </div>
                            <div style="flex:1;">
                                <label class="input-label">Descanso (seg)</label>
                                <input type="number" name="descanso_isoladores" id="micro_desc_iso" class="admin-input" placeholder="Ex: 60">
                            </div>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label class="input-label">Foco / Comentário para o Aluno</label>
                            <textarea name="foco_comentario" id="micro_foco" class="admin-input" rows="3" placeholder="Ex: Focar na progressão de carga..."></textarea>
                        </div>

                        <button type="submit" class="btn-gold" style="width:100%;">SALVAR SEMANA</button>
                    </form>
                </div>
            </div>

        ';
        break;

    case 'perfil':
        require_once '../config/db_connect.php';
        if(session_status() === PHP_SESSION_NONE) session_start();
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $foto = $user['foto'] ? $user['foto'] : 'assets/img/user-default.png';

        echo '
            <section id="perfil-admin">
                <header class="dash-header">
                    <h1>CONFIGURAÇÕES DO <span class="highlight-text">ADMIN</span></h1>
                </header>

                <div class="glass-card profile-admin">
                    <form action="actions/update_profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="admin-profile-layout">
                            
                            <div class="profile-photo-section">
                                <div class="photo-wrapper">
                                    <img src="'.$foto.'" id="admin-preview">
                                    <label for="admin-upload" class="upload-btn-float">
                                        <i class="fa-solid fa-pen"></i>
                                    </label>
                                    <input type="file" name="foto" id="admin-upload" style="display: none;" onchange="previewImageAdmin(this)">
                                </div>
                                <h3 style="margin-top: 15px; color: #fff; text-align: center; margin-bottom: 5px;">'.$user['nome'].'</h3>
                                <span class="status-badge" style="background: rgba(255,66,66,0.2); color: #ff4242;">MASTER ADMIN</span>
                            </div>

                            <div class="profile-form-section">
                                <h3 class="section-title" style="font-size: 1.1rem; margin-bottom: 15px;">Dados de Acesso</h3>
                                
                                <div class="form-profile">
                                    <div class="input-grid">
                                        <div>
                                            <label class="input-label">Nome Admin</label>
                                            <input type="text" name="nome" value="'.$user['nome'].'" class="input-field">
                                        </div>
                                        <div>
                                            <label class="input-label">Telefone</label>
                                            <input type="text" name="telefone" value="'.$user['telefone'].'" class="input-field">
                                        </div>
                                    </div>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label class="input-label">Email</label>
                                        <input type="email" name="email" value="'.$user['email'].'" class="input-field">
                                    </div>

                                    <hr class="form-divider">

                                    <h3 class="password-section-title" style="color: #ff4242; margin-bottom: 15px;">Segurança</h3>
                                    <div class="input-grid">
                                        <div>
                                            <label class="input-label">Nova Senha</label>
                                            <input type="password" name="nova_senha" class="input-field" placeholder="********">
                                        </div>
                                        <div>
                                            <label class="input-label">Confirmar</label>
                                            <input type="password" name="confirma_senha" class="input-field" placeholder="********">
                                        </div>
                                    </div>

                                    <div style="text-align: right; margin-top: 20px;">
                                        <button type="submit" class="btn-gold" style="background: #ff4242; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold;">ATUALIZAR PERFIL</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        ';
        break;

    default:
        echo '<section><h1>Página não encontrada</h1></section>';
        break;
}
?>