<?php
if(session_status() === PHP_SESSION_NONE) session_start();

$pagina = $_GET['pagina'] ?? 'dashboard';

// Pega o nome do Admin
$nome_admin = $_SESSION['user_nome'] ?? 'Admin';
$partes_admin = explode(' ', trim($nome_admin));
$primeiro_nome_admin = strtoupper($partes_admin[0]);

switch ($pagina) {
    case 'dashboard':
        echo '
            <section id="admin-dash">
                <header class="dash-header">
                    <h1>Bem vindo, <span class="highlight-text">'.$primeiro_nome_admin.'.</span></h1>
                    <p style="color: #888;">Visão geral do desempenho da academia</p>
                </header>

                <div class="stats-row">
                    
                    <div class="glass-card">
                        <div class="card-label">ALUNOS ATIVOS</div>
                        <div class="card-body">
                            <div class="icon-box" style="color: #00ff00; border-color: #00ff00;">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div class="info-box">
                                <h3>142</h3>
                                <p>+5 essa semana</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-label">RECEITA MENSAL</div>
                        <div class="card-body">
                            <div class="icon-box">
                                <i class="fa-solid fa-brazilian-real-sign"></i>
                            </div>
                            <div class="info-box">
                                <h3>R$ 12.450</h3>
                                <p>Meta: R$ 15k</p>
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
                                <h3>8</h3>
                                <p>Avaliações atrasadas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recent-section">
                    <h3 class="section-title">SOLICITAÇÕES RECENTES</h3>
                    <div class="workout-list">
                        
                        <div class="glass-card workout-item">
                            <div class="wk-left">
                                <div class="wk-icon"><i class="fa-solid fa-user-plus"></i></div>
                                <div class="wk-details">
                                    <h4>João Silva</h4>
                                    <span>Plano Intermediário • Pendente</span>
                                </div>
                            </div>
                            <div class="wk-right">
                                <button class="btn-gold" style="padding: 5px 15px; font-size: 0.7rem;">APROVAR</button>
                            </div>
                        </div>

                        <div class="glass-card workout-item">
                            <div class="wk-left">
                                <div class="wk-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                                <div class="wk-details">
                                    <h4>Maria Oliveira</h4>
                                    <span>Pagamento Confirmado</span>
                                </div>
                            </div>
                            <div class="wk-right">
                                <span class="wk-duration" style="color: #00ff00;">R$ 89,90</span>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        ';
        break;

    case 'alunos':
        echo '
            <section>
                <header class="dash-header">
                    <h1>GERENCIAR <span class="highlight-text">ALUNOS</span></h1>
                </header>
                
                <div class="glass-card" style="padding: 20px;">
                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <input type="text" placeholder="Buscar aluno..." style="flex: 1; padding: 10px; border-radius: 5px; border: 1px solid #333; background: #111; color: #fff;">
                        <button class="btn-gold">BUSCAR</button>
                    </div>

                    <div class="workout-list">
                        <div class="glass-card workout-item" style="background: rgba(255,255,255,0.05);">
                            <div class="wk-left">
                                <div class="wk-icon"><i class="fa-solid fa-user"></i></div>
                                <div class="wk-details">
                                    <h4>Carlos Eduardo</h4>
                                    <span style="color: #FFBA42;">Plano Premium</span>
                                </div>
                            </div>
                            <div class="wk-right" style="display: flex; gap: 10px;">
                                <i class="fa-solid fa-pen-to-square" style="cursor: pointer; color: #fff;"></i>
                                <i class="fa-solid fa-trash" style="cursor: pointer; color: #ff4242;"></i>
                            </div>
                        </div>
                        </div>
                </div>
            </section>
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
        echo '
            <section id="financeiro">
                <header class="dash-header">
                    <h1>CONTROLE <span class="highlight-text">FINANCEIRO</span></h1>
                    <p class="text-desc">Gestão de entradas e assinaturas</p>
                </header>

                <div class="stats-row">
                    <div class="glass-card">
                        <div class="card-label">FATURAMENTO (NOV)</div>
                        <div class="card-body">
                            <div class="icon-box success">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                            </div>
                            <div class="info-box">
                                <h3>R$ 4.250,00</h3>
                                <p class="text-muted">+15% vs mês passado</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-label">A RECEBER (HOJE)</div>
                        <div class="card-body">
                            <div class="icon-box gold">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="info-box">
                                <h3>R$ 350,00</h3>
                                <p class="text-muted">2 Alunos vencendo</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card mt-large">
                    
                    <div class="section-header-row">
                        <h3 class="section-title" style="margin:0">HISTÓRICO DE TRANSAÇÕES</h3>
                        <button class="btn-gold" onclick="alert(\'Modal de lançamento\')">
                            <i class="fa-solid fa-plus"></i> NOVO LANÇAMENTO
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ALUNO</th>
                                    <th>PLANO</th>
                                    <th>DATA</th>
                                    <th>VALOR</th>
                                    <th>STATUS</th>
                                    <th>AÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-mini"><i class="fa-solid fa-user"></i></div>
                                            <span>Carlos Mendes</span>
                                        </div>
                                    </td>
                                    <td>Mensal</td>
                                    <td>19/11/2023</td>
                                    <td><strong>R$ 120,00</strong></td>
                                    <td><span class="status-badge pago">PAGO</span></td>
                                    <td><i class="fa-solid fa-ellipsis-vertical pointer"></i></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-mini"><i class="fa-solid fa-user"></i></div>
                                            <span>Ana Clara</span>
                                        </div>
                                    </td>
                                    <td>Trimestral</td>
                                    <td>18/11/2023</td>
                                    <td><strong>R$ 300,00</strong></td>
                                    <td><span class="status-badge pago">PAGO</span></td>
                                    <td><i class="fa-solid fa-ellipsis-vertical pointer"></i></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-mini"><i class="fa-solid fa-user"></i></div>
                                            <span>Marcos Paulo</span>
                                        </div>
                                    </td>
                                    <td>Consultoria</td>
                                    <td>15/11/2023</td>
                                    <td><strong>R$ 500,00</strong></td>
                                    <td><span class="status-badge pendente">PENDENTE</span></td>
                                    <td><i class="fa-solid fa-paper-plane text-gold pointer" title="Cobrar"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination">
                        <button class="page-btn"><</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">></button>
                    </div>

                </div>
            </section>
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