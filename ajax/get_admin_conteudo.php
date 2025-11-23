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

    case 'treinos_editor':
        echo '
            <section>
                <header class="dash-header">
                    <h1>EDITOR DE <span class="highlight-text">TREINOS</span></h1>
                </header>
                <div class="glass-card">
                    <p style="color: #ccc; margin-bottom: 1rem;">Selecione um aluno para atribuir treino:</p>
                    <select style="width: 100%; padding: 10px; background: #111; color: #fff; border: 1px solid #333; border-radius: 5px; margin-bottom: 1rem;">
                        <option>Selecione...</option>
                        <option>João Silva</option>
                        <option>Maria Oliveira</option>
                    </select>
                    <textarea placeholder="Descreva o treino ou cole o JSON do treino..." style="width: 100%; height: 150px; background: #111; color: #fff; border: 1px solid #333; padding: 10px; border-radius: 5px;"></textarea>
                    <div style="margin-top: 15px; text-align: right;">
                        <button class="btn-gold">SALVAR TREINO</button>
                    </div>
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
                        
                        <div style="display: flex; gap: 40px; flex-wrap: wrap;" class="admin-profile-layout">
                            
                            <div class="profile-photo-section" style="flex: 0 0 200px;">
                                <div class="photo-wrapper" style="width: 180px; height: 180px;">
                                    <img src="'.$foto.'" id="admin-preview">
                                    <label for="admin-upload" class="upload-btn-float">
                                        <i class="fa-solid fa-pen"></i>
                                    </label>
                                    <input type="file" name="foto" id="admin-upload" style="display: none;" onchange="previewImageAdmin(this)">
                                </div>
                                <h3 style="margin-top: 15px; color: #fff; text-align: center;">'.$user['nome'].'</h3>
                                <div style="text-align: center; margin-top: 5px;">
                                    <span class="status-badge" style="background: rgba(255,66,66,0.2); color: #ff4242;">MASTER ADMIN</span>
                                </div>
                            </div>

                            <div style="flex: 1; min-width: 300px;">
                                <h3 class="section-title" style="font-size: 1.1rem;">Dados de Acesso</h3>
                                
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
                                    
                                    <div>
                                        <label class="input-label">Email</label>
                                        <input type="email" name="email" value="'.$user['email'].'" class="input-field">
                                    </div>

                                    <hr class="form-divider">

                                    <h3 class="password-section-title" style="color: #ff4242;">Segurança</h3>
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

                                    <div style="text-align: right; margin-top: 10px;">
                                        <button type="submit" class="btn-gold" style="background: #ff4242; color: #fff; border: none;">ATUALIZAR PERFIL</button>
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