<?php
// Inicia sessão se não estiver iniciada
if(session_status() === PHP_SESSION_NONE) session_start();

// Define qual página foi pedida
$pagina = $_GET['pagina'] ?? 'treinos';

// Lógica do Nome (Pega da sessão e separa o primeiro nome)
$nome_completo = $_SESSION['user_nome'] ?? 'Atleta';
$partes_nome = explode(' ', trim($nome_completo));
$primeiro_nome = strtoupper($partes_nome[0]); // Ex: "JOÃO"

switch ($pagina) {
    case 'dashboard':
        echo '
            <section id="dashboard">
                <div class="dashboard-container-view">
                    
                    <header class="dash-header">
                        <h1>BEM-VINDO, <span class="highlight-text">'.$primeiro_nome.'.</span></h1>
                    </header>

                    <div class="stats-row">
                        
                        <div class="glass-card next-workout">
                            <div class="card-label">PRÓXIMO TREINO</div>
                            <div class="card-body">
                                <div class="icon-box">
                                    <i class="fa-solid fa-dumbbell"></i>
                                </div>
                                <div class="info-box">
                                    <h3>PEITO & TRÍCEPS</h3>
                                    <p>QUARTA-FEIRA • 19:00</p>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card weekly-focus">
                            <div class="card-label">FOCO SEMANAL</div>
                            <div class="card-body">
                                <div class="mini-chart-box">
                                    <i class="fa-solid fa-chart-area"></i>
                                </div>
                                <div class="info-box">
                                    <h3>Cardio 80%</h3>
                                    <p>Alta Intensidade</p>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card calories-card">
                            <div class="card-label">CALORIAS DIÁRIAS</div>
                            <div class="card-body vertical-body">
                                <div class="cal-display">
                                    <i class="fa-solid fa-fire flame"></i>
                                    <span class="val-current">2350</span>
                                    <span class="val-divider">/</span>
                                    <span class="val-goal">2800 KCAL</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: 84%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card chart-section">
                        <div class="card-header-row">
                            <h3 class="section-title">SEU PROGRESSO SEMANAL</h3>
                        </div>
                        <div class="chart-wrapper">
                            <svg viewBox="0 0 800 200" class="main-chart-svg" preserveAspectRatio="none">
                                <line x1="0" y1="150" x2="800" y2="150" stroke="rgba(255,255,255,0.1)" stroke-width="1" />
                                <line x1="0" y1="100" x2="800" y2="100" stroke="rgba(255,255,255,0.1)" stroke-width="1" />
                                <line x1="0" y1="50" x2="800" y2="50" stroke="rgba(255,255,255,0.1)" stroke-width="1" />

                                <polyline points="0,160 100,140 200,150 300,80 400,100 500,60 600,110 700,50 800,30"
                                        fill="none"
                                        stroke="var(--gold)"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="chart-line-path" />
                                
                                <circle cx="100" cy="140" r="5" class="chart-point" />
                                <circle cx="200" cy="150" r="5" class="chart-point red" />
                                <circle cx="300" cy="80" r="5" class="chart-point" />
                                <circle cx="400" cy="100" r="5" class="chart-point red" />
                                <circle cx="500" cy="60" r="5" class="chart-point" />
                                <circle cx="600" cy="110" r="5" class="chart-point red" />
                                <circle cx="700" cy="50" r="5" class="chart-point" />
                            </svg>
                            
                            <div class="chart-labels">
                                <span>SEG</span><span>TER</span><span>QUA</span><span>QUI</span><span>SEX</span><span>SÁB</span><span>DOM</span>
                            </div>
                        </div>
                    </div>

                    <div class="recent-section">
                        <h3 class="section-title">ÚLTIMOS TREINOS</h3>
                        <div class="workout-list">
                            
                            <div class="glass-card workout-item">
                                <div class="wk-left">
                                    <div class="wk-icon"><i class="fa-solid fa-person-running"></i></div>
                                    <div class="wk-details">
                                        <h4>Peito & Tríceps</h4>
                                        <span>20 AGO • 15:30</span>
                                    </div>
                                </div>
                                <div class="wk-right">
                                    <span class="wk-duration">1h 40m</span>
                                </div>
                            </div>

                            <div class="glass-card workout-item">
                                <div class="wk-left">
                                    <div class="wk-icon"><i class="fa-solid fa-bicycle"></i></div>
                                    <div class="wk-details">
                                        <h4>Cardio Intenso</h4>
                                        <span>18 AGO • 09:00</span>
                                    </div>
                                </div>
                                <div class="wk-right">
                                    <span class="wk-duration">45 min</span>
                                </div>
                            </div>

                            <div class="glass-card workout-item">
                                <div class="wk-left">
                                    <div class="wk-icon"><i class="fa-solid fa-weight-hanging"></i></div>
                                    <div class="wk-details">
                                        <h4>Costas & Bíceps</h4>
                                        <span>16 AGO • 18:30</span>
                                    </div>
                                </div>
                                <div class="wk-right">
                                    <span class="wk-duration">1h 20m</span>
                                </div>
                            </div>

                        </div>
                        <div class="view-more-wrapper">
                            <button class="btn-gold">VER MAIS</button>
                        </div>
                    </div>
                </div>
            </section>
        ';
        break;
    case 'treinos':
        require_once '../config/db_connect.php';
        if(session_status() === PHP_SESSION_NONE) session_start();
        $aluno_id = $_SESSION['user_id'];
        $hoje = date('Y-m-d');

        // 1. BUSCA O TREINO ATIVO (Vigente)
        $sql = "SELECT * FROM treinos WHERE aluno_id = :uid AND data_inicio <= :hj AND data_fim >= :hj LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $aluno_id, 'hj' => $hoje]);
        $treino = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$treino) {
            echo '
            <section id="treinos-vazio" style="text-align:center; padding-top:50px;">
                <i class="fa-solid fa-dumbbell" style="font-size:4rem; color:#333; margin-bottom:20px;"></i>
                <h2 style="color:#fff; margin-bottom:10px;">Nenhum treino ativo</h2>
                <p style="color:#888;">Você não possui um planejamento vigente para hoje.<br>Entre em contato com o treinador.</p>
            </section>';
            break;
        }

        // 2. SE AVANÇADO: BUSCA MICROCICLO ATUAL
        $micro_atual = null;
        $todos_micros = [];
        
        if ($treino['nivel_plano'] !== 'basico') {
            $stmt_per = $pdo->prepare("SELECT id FROM periodizacoes WHERE treino_id = ?");
            $stmt_per->execute([$treino['id']]);
            $periodizacao_id = $stmt_per->fetchColumn();

            if ($periodizacao_id) {
                // Busca todos para a timeline
                $stmt_micros = $pdo->prepare("SELECT * FROM microciclos WHERE periodizacao_id = ? ORDER BY semana_numero ASC");
                $stmt_micros->execute([$periodizacao_id]);
                $todos_micros = $stmt_micros->fetchAll(PDO::FETCH_ASSOC);

                // Descobre qual é o de hoje
                foreach ($todos_micros as $m) {
                    if ($hoje >= $m['data_inicio_semana'] && $hoje <= $m['data_fim_semana']) {
                        $micro_atual = $m;
                        break;
                    }
                }
                // Fallback: Se hoje não caiu em nenhum (ex: dia off entre treinos), pega o mais próximo ou o último
                if (!$micro_atual && !empty($todos_micros)) $micro_atual = $todos_micros[0]; 
            }
        }

        // 3. BUSCA DIVISÕES
        $stmt_div = $pdo->prepare("SELECT * FROM treino_divisoes WHERE treino_id = ? ORDER BY letra ASC");
        $stmt_div->execute([$treino['id']]);
        $divisoes = $stmt_div->fetchAll(PDO::FETCH_ASSOC);

        echo '<section id="meu-treino">';
        
        // --- HEADER DO TREINO ---
        echo '
        <div class="dash-header" style="margin-bottom:20px;">
            <h2 style="font-family:Orbitron; color:#fff;">'.$treino['nome'].'</h2>
            <p style="color:#888;">Foco: '.($micro_atual ? $micro_atual['nome_fase'] : 'Geral').'</p>
        </div>';

        // --- TIMELINE (Só Avançado) ---
        if (!empty($todos_micros)) {
            echo '<div class="timeline-wrapper">';
            foreach ($todos_micros as $m) {
                $active = ($micro_atual && $m['id'] == $micro_atual['id']) ? 'active' : '';
                $inicio = date('d/m', strtotime($m['data_inicio_semana']));
                echo '
                <div class="micro-card '.$active.'">
                    <span class="micro-title">SEM '.$m['semana_numero'].'</span>
                    <span class="micro-dates">'.$inicio.'</span>
                </div>';
            }
            echo '</div>';

            // Aviso da Semana
            if ($micro_atual) {
                echo '
                <div class="week-focus-box">
                    <h4 style="color:var(--gold); margin-bottom:5px;"><i class="fa-solid fa-crosshairs"></i> Estratégia da Semana</h4>
                    <p style="color:#ccc; font-size:0.9rem; margin-bottom:5px;">
                        <strong>Compostos:</strong> '.$micro_atual['reps_compostos'].' reps | 
                        <strong>Isoladores:</strong> '.$micro_atual['reps_isoladores'].' reps
                    </p>
                    '.($micro_atual['foco_comentario'] ? '<p style="color:#888; font-size:0.8rem; margin-top:5px;">"'.$micro_atual['foco_comentario'].'"</p>' : '').'
                </div>';
            }
        }

        // --- ABAS DE DIVISÃO ---
        echo '<div class="division-tabs">';
        $first = true;
        foreach ($divisoes as $div) {
            $active = $first ? 'active' : '';
            echo '<button class="tab-btn '.$active.'" onclick="abrirTreino(event, \'tdiv_'.$div['id'].'\')">TREINO '.$div['letra'].'</button>';
            $first = false;
        }
        echo '</div>';

        // --- CONTEÚDO DAS ABAS (Exercícios) ---
        $firstContent = true;
        foreach ($divisoes as $div) {
            $display = $firstContent ? 'block' : 'none';
            
            // Busca exercícios
            $stmt_ex = $pdo->prepare("SELECT * FROM exercicios WHERE divisao_id = ? ORDER BY ordem ASC");
            $stmt_ex->execute([$div['id']]);
            $exercicios = $stmt_ex->fetchAll(PDO::FETCH_ASSOC);

            echo '<div id="tdiv_'.$div['id'].'" class="treino-content" style="display:'.$display.';">';
            
            if (count($exercicios) > 0) {
                foreach ($exercicios as $ex) {
                    
                    // Busca séries
                    $stmt_ser = $pdo->prepare("SELECT * FROM series WHERE exercicio_id = ?");
                    $stmt_ser->execute([$ex['id']]);
                    $series = $stmt_ser->fetchAll(PDO::FETCH_ASSOC);

                    echo '
                    <div class="workout-card">
                        <div class="workout-header">
                            <div>
                                <span class="workout-name">'.$ex['nome_exercicio'].'</span>
                                <span class="workout-meta">'.strtoupper($ex['tipo_mecanica']).'</span>
                            </div>
                            '.($ex['video_url'] ? '<a href="'.$ex['video_url'].'" target="_blank" class="video-btn"><i class="fa-solid fa-play"></i></a>' : '').'
                        </div>
                        '.($ex['observacao_exercicio'] ? '<p style="color:#888; font-size:0.85rem; margin-bottom:15px;"><i class="fa-solid fa-circle-info"></i> '.$ex['observacao_exercicio'].'</p>' : '').'
                        
                        <div class="sets-grid">';
                            
                            foreach ($series as $s) {
                                // LÓGICA DE REPRA E DESCANSO INTELIGENTE
                                $reps_display = $s['reps_fixas'];
                                $desc_display = $s['descanso_fixo'];

                                // Se for Avançado, tenta pegar da periodização
                                if ($treino['nivel_plano'] !== 'basico' && $micro_atual) {
                                    if ($ex['tipo_mecanica'] == 'composto' && !empty($micro_atual['reps_compostos'])) {
                                        $reps_display = $micro_atual['reps_compostos'];
                                    } elseif ($ex['tipo_mecanica'] == 'isolador' && !empty($micro_atual['reps_isoladores'])) {
                                        $reps_display = $micro_atual['reps_isoladores'];
                                    }
                                    // Se tiver descanso global na semana
                                    if (!empty($micro_atual['descanso_segundos'])) {
                                        $desc_display = $micro_atual['descanso_segundos']."s";
                                    }
                                }

                                // Fallback final se ainda estiver vazio
                                if (empty($reps_display)) $reps_display = "Falha";
                                if (empty($desc_display)) $desc_display = "-";

                                echo '
                                <div class="set-box '.$s['categoria'].'">
                                    <div class="set-info">
                                        <span class="set-type">'.$s['quantidade'].'x '.strtoupper($s['categoria']).'</span>
                                        <i class="fa-solid fa-check-circle" style="color:#333;"></i>
                                    </div>
                                    <div class="set-reps">'.$reps_display.'</div>
                                    <div class="set-rest"><i class="fa-solid fa-stopwatch"></i> '.$desc_display.'</div>
                                </div>';
                            }

                    echo '</div>
                    </div>';
                }
            } else {
                echo '<p style="color:#666; text-align:center; padding:30px;">Descanso ou sem exercícios cadastrados.</p>';
            }
            echo '</div>'; // Fim da aba
            $firstContent = false;
        }

        echo '
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
            <section id="perfil-section">
                <header class="dash-header">
                    <h1>MEU <span class="highlight-text">PERFIL</span></h1>
                </header>

                <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
                    <form action="actions/update_profile.php" method="POST" enctype="multipart/form-data" class="form-profile">
                        
                        <div class="profile-photo-section">
                            <div class="photo-wrapper">
                                <img src="'.$foto.'" alt="Foto Perfil" id="preview-img">
                                <label for="foto-upload" class="upload-btn-float">
                                    <i class="fa-solid fa-camera"></i>
                                </label>
                                <input type="file" name="foto" id="foto-upload" style="display: none;" accept="image/*" onchange="previewImage(this)">
                            </div>
                            <p class="photo-hint">Toque na câmera para alterar</p>
                        </div>

                        <div class="input-grid">
                            <div>
                                <label class="input-label">Nome Completo</label>
                                <input type="text" name="nome" value="'.$user['nome'].'" class="input-field" required>
                            </div>
                            <div>
                                <label class="input-label">Telefone (WhatsApp)</label>
                                <input type="text" name="telefone" value="'.$user['telefone'].'" class="input-field">
                            </div>
                        </div>

                        <div>
                            <label class="input-label">E-mail de Acesso</label>
                            <input type="email" name="email" value="'.$user['email'].'" class="input-field" required>
                        </div>

                        <hr class="form-divider">

                        <div>
                            <h3 class="password-section-title">Segurança</h3>
                            <p class="password-section-desc">Preencha apenas se quiser alterar sua senha.</p>
                        </div>

                        <div class="input-grid">
                            <div>
                                <label class="input-label">Nova Senha</label>
                                <input type="password" name="nova_senha" class="input-field" placeholder="********">
                            </div>
                            <div>
                                <label class="input-label">Confirmar Nova Senha</label>
                                <input type="password" name="confirma_senha" class="input-field" placeholder="********">
                            </div>
                        </div>

                        <div style="text-align: right; margin-top: 10px;">
                            <button type="submit" class="btn-gold">SALVAR ALTERAÇÕES</button>
                        </div>
                    </form>
                </div>
            </section>

        ';
        break;

    case 'avaliacoes':
        echo '
            <section id="avaliacoes">
                <h1>Avaliações Físicas</h1>
                <p>Histórico de avaliações, medidas, progresso, etc.</p>
            </section>
        ';
        break;

    case 'pagamentos':
        echo '
            <section id="pagamentos">
                <h1>Pagamentos</h1>
                <p>Status da sua assinatura, histórico de pagamentos, etc.</p>
            </section>
        ';
        break;

    default:
        // Caso a página pedida não exista
        echo '
            <section id="erro">
                <h1>Página não encontrada</h1>
                <p>O conteúdo solicitado não foi encontrado.</p>
            </section>
        ';
        break;
}

?>