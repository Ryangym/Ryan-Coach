<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db_connect.php';

$aluno_id = $_SESSION['user_id'];
$pagina_raw = $_GET['pagina'] ?? 'dashboard';
$partes = explode('&', $pagina_raw);
$pagina = $partes[0];
-
$divisao_req = $_GET['divisao_id'] ?? null; // Usado no Realizar Treino
$treino_req  = $_GET['treino_id'] ?? null;  // Usado no Visualizar Treino
$micro_req   = $_GET['micro_id'] ?? null;   // Usado no Visualizar Treino

// Nome do Usuário
$nome = explode(' ', trim($_SESSION['user_nome'] ?? 'Atleta'));
$primeiro_nome = strtoupper($nome[0]);

switch ($pagina) {

    case 'listar_treinos_json':
        // Retorna JSON para o Modal montar os botões via JS
        header('Content-Type: application/json');
        require_once '../config/db_connect.php';
        
        $uid = $_SESSION['user_id'];
        $sql = "SELECT id, nome, nivel_plano, data_inicio FROM treinos WHERE aluno_id = :uid ORDER BY criado_em DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $uid]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($result);
        exit; // Encerra aqui para não imprimir HTML extra
    break;

    case 'dashboard':
        // Botão de Start no topo do Dashboard
        echo '<section id="dashboard" class="fade-in">
                <div class="dashboard-container-view">
                    <header class="dash-header">
                        <h1>OLÁ, <span class="highlight-text">'.$primeiro_nome.'</span></h1>
                    </header>
                    
                    <!-- BOTÃO DE AÇÃO PRINCIPAL -->
                    <button class="btn-start-workout" onclick="carregarConteudo(\'realizar_treino\')">
                        <i class="fa-solid fa-play"></i> COMEÇAR TREINO
                    </button>

                    <!-- Resto do Dashboard (Resumo, gráficos...) -->
                    <div class="stats-row">
                        <div class="glass-card">
                           <div class="card-label">FOCO DA SEMANA</div>
                           <p style="color:#ccc">Acesse "Meus Treinos" para ver o planejamento completo.</p>
                        </div>
                    </div>
                </div>
              </section>';
        break;

    // --- NOVA TELA: REALIZAR TREINO ---
    case 'realizar_treino':
        // 1. Busca o Treino Ativo
        $hoje = date('Y-m-d');
        $sql = "SELECT * FROM treinos WHERE aluno_id = :uid ORDER BY criado_em DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $aluno_id]);
        $treino_ativo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$treino_ativo) {
            echo '<section class="empty-state"><h2>Sem treino ativo</h2></section>';
            break;
        }

        // 2. Lógica de Seleção Automática do Dia (Mantida igual)
        if (!$divisao_req) {
            $dia_semana_hoje = date('N'); 
            $dias_treino = json_decode($treino_ativo['dias_semana']); 
            
            $stmt_div = $pdo->prepare("SELECT * FROM treino_divisoes WHERE treino_id = ? ORDER BY letra ASC");
            $stmt_div->execute([$treino_ativo['id']]);
            $divisoes = $stmt_div->fetchAll(PDO::FETCH_ASSOC);
            
            $divisao_sugerida = null;
            $indice_hoje = array_search($dia_semana_hoje, $dias_treino);
            
            if ($indice_hoje !== false) {
                $indice_divisao = $indice_hoje % count($divisoes);
                $divisao_sugerida = $divisoes[$indice_divisao];
                
                echo '<section class="fade-in" style="padding-top:20px;">
                        <h2 class="workout-title" style="text-align:center; font-size:1.2rem;">HOJE É DIA DE:</h2>
                        <div style="text-align:center; margin: 30px 0;">
                             <h1 style="font-size:5rem; color:var(--gold); margin:0;">'.$divisao_sugerida['letra'].'</h1>
                             <p style="color:#888;">'.$divisao_sugerida['nome'].'</p>
                        </div>
                        <button class="btn-start-workout" onclick="carregarConteudo(\'realizar_treino&divisao_id='.$divisao_sugerida['id'].'\')">
                            <i class="fa-solid fa-check"></i> CONFIRMAR
                        </button>
                        <p style="text-align:center; color:#666; margin-top:20px; font-size:0.9rem;">Ou escolha outro:</p>
                        <div class="workout-selection-grid">';
                            foreach($divisoes as $d) {
                                if($d['id'] != $divisao_sugerida['id']) {
                                    echo '<button class="select-workout-btn" onclick="carregarConteudo(\'realizar_treino&divisao_id='.$d['id'].'\')">'.$d['letra'].'</button>';
                                }
                            }
                echo   '</div></section>';
                break; 
            } else {
                echo '<section class="fade-in">
                        <h2 class="workout-title">QUAL O TREINO DE HOJE?</h2>
                        <div class="workout-selection-grid">';
                        foreach($divisoes as $d) {
                            echo '<button class="select-workout-btn" onclick="carregarConteudo(\'realizar_treino&divisao_id='.$d['id'].'\')">'.$d['letra'].'</button>';
                        }
                echo   '</div></section>';
                break;
            }
        }

        // 3. EXIBIÇÃO DO TREINO
        $divisao_id = $divisao_req;
        
        $stmt_d = $pdo->prepare("SELECT * FROM treino_divisoes WHERE id = ?");
        $stmt_d->execute([$divisao_id]);
        $div_atual = $stmt_d->fetch(PDO::FETCH_ASSOC);

        $stmt_ex = $pdo->prepare("SELECT * FROM exercicios WHERE divisao_id = ? ORDER BY ordem ASC");
        $stmt_ex->execute([$divisao_id]);
        $exercicios = $stmt_ex->fetchAll(PDO::FETCH_ASSOC);

        // Periodização
        $micro_atual = null;
        if ($treino_ativo['nivel_plano'] !== 'basico') {
             $stmt_per = $pdo->prepare("SELECT id FROM periodizacoes WHERE treino_id = ?");
             $stmt_per->execute([$treino_ativo['id']]);
             $pid = $stmt_per->fetchColumn();
             if($pid) {
                 $stmt_m = $pdo->prepare("SELECT * FROM microciclos WHERE periodizacao_id = ? AND data_inicio_semana <= ? AND data_fim_semana >= ? LIMIT 1");
                 $stmt_m->execute([$pid, $hoje, $hoje]);
                 $micro_atual = $stmt_m->fetch(PDO::FETCH_ASSOC);
                 if(!$micro_atual) {
                     $stmt_m = $pdo->prepare("SELECT * FROM microciclos WHERE periodizacao_id = ? ORDER BY semana_numero DESC LIMIT 1");
                     $stmt_m->execute([$pid]);
                     $micro_atual = $stmt_m->fetch(PDO::FETCH_ASSOC);
                 }
             }
        }

        echo '<form action="actions/treino_registrar.php" method="POST" id="form-execucao">
                <input type="hidden" name="treino_id" value="'.$treino_ativo['id'].'">
                <input type="hidden" name="divisao_id" value="'.$divisao_id.'">

                <div class="execution-header">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0 15px;">
                        <h2 style="color:#fff; margin:0; font-size:1.2rem;">TREINO '.$div_atual['letra'].'</h2>
                        <button type="button" onclick="carregarConteudo(\'realizar_treino\')" style="background:none; border:none; color:#888;">Trocar</button>
                    </div>
                    <p style="padding:0 15px; color:#666; font-size:0.8rem; margin-top:5px;">'.($micro_atual ? 'Fase: '.$micro_atual['nome_fase'] : 'Treino Livre').'</p>
                </div>

                <div style="padding-bottom: 160px;">'; 

        foreach ($exercicios as $ex) {
            $stmt_s = $pdo->prepare("SELECT * FROM series WHERE exercicio_id = ?");
            $stmt_s->execute([$ex['id']]);
            $series = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

            // Busca Histórico (Corrigido)
            $stmt_hist = $pdo->prepare("SELECT carga_kg, reps_realizadas FROM treino_historico WHERE aluno_id = ? AND exercicio_id = ? ORDER BY data_treino DESC LIMIT 1");
            $stmt_hist->execute([$aluno_id, $ex['id']]);
            $historico = $stmt_hist->fetch(PDO::FETCH_ASSOC);
            
            $ultima_carga = $historico ? $historico['carga_kg'] : '';
            $ultimas_reps = $historico ? $historico['reps_realizadas'] : '';

            // Mapeamento histórico por série
            $stmt_last_date = $pdo->prepare("SELECT MAX(data_treino) FROM treino_historico WHERE aluno_id = ? AND exercicio_id = ?");
            $stmt_last_date->execute([$aluno_id, $ex['id']]);
            $ultima_data = $stmt_last_date->fetchColumn();
            $historico_map = [];
            if ($ultima_data) {
                $stmt_h = $pdo->prepare("SELECT serie_numero, carga_kg, reps_realizadas FROM treino_historico WHERE aluno_id = ? AND exercicio_id = ? AND data_treino = ?");
                $stmt_h->execute([$aluno_id, $ex['id'], $ultima_data]);
                $regs = $stmt_h->fetchAll(PDO::FETCH_ASSOC);
                foreach ($regs as $r) $historico_map[$r['serie_numero']] = $r;
            }

            echo '
            <div class="exec-card">
                <div class="exec-header">
                    <span class="exec-title">'.$ex['nome_exercicio'].'</span>
                    '.($ex['video_url'] ? '<a href="'.$ex['video_url'].'" target="_blank" class="exec-video"><i class="fa-solid fa-circle-play"></i></a>' : '').'
                </div>

                <div class="set-row-header">
                    <span>SÉRIE</span>
                    <span>META</span>
                    <span>CARGA (KG)</span>
                    <span>REPS</span>
                </div>';

                foreach ($series as $s) {
                    $meta_reps = $s['reps_fixas'];
                    $meta_desc = $s['descanso_fixo'];

                    if ($s['categoria'] === 'warmup') { $meta_desc = '30s'; }
                    elseif ($s['categoria'] === 'feeder') { $meta_desc = '60s'; }
                    elseif ($micro_atual) {
                        if ($ex['tipo_mecanica'] == 'composto') {
                            if($micro_atual['reps_compostos']) $meta_reps = $micro_atual['reps_compostos'];
                            if($micro_atual['descanso_compostos']) $meta_desc = $micro_atual['descanso_compostos'].'s';
                        } else {
                            if($micro_atual['reps_isoladores']) $meta_reps = $micro_atual['reps_isoladores'];
                            if($micro_atual['descanso_isoladores']) $meta_desc = $micro_atual['descanso_isoladores'].'s';
                        }
                    }
                    if(!$meta_reps) $meta_reps = "-";

                    // Dados Antigos
                    $dados_ant = isset($historico_map[$s['id']]) ? $historico_map[$s['id']] : null; // Nota: idealmente usar serie_numero se for sequencial, mas id serve se não deletar series
                    // Melhoria: Usar contador $i no loop se preferir, mas vamos manter simples por enquanto
                    
                    // Fallback para placeholder
                    $ph_carga = ($dados_ant && is_numeric($dados_ant['carga_kg'])) ? ($dados_ant['carga_kg']*1) : '-';
                    $ph_reps  = ($dados_ant && $dados_ant['reps_realizadas']) ? $dados_ant['reps_realizadas'] : '-';

                    $input_base = "treino[".$ex['id']."][".$s['id']."]";

                    echo '
                    <div class="set-row-input '.$s['categoria'].'">
                        <div class="set-num">
                            <span style="font-size:1.1rem;">'.$s['quantidade'].'</span>
                            
                            <span class="set-type-label">'.strtoupper($s['categoria']).'</span>
                            
                            <div style="font-size:0.6rem; color:#666;">'.$meta_desc.'</div>
                        </div>
                        
                        <div style="text-align:center;">
                            <span style="color:#fff; font-size:0.9rem; font-weight:bold;">'.$meta_reps.'</span>
                            <span style="display:block; font-size:0.6rem; color:#aaa;">ALVO</span>
                        </div>

                        <div>
                            <input type="tel" name="'.$input_base.'[carga]" class="input-exec" placeholder="Ant: '.$ph_carga.'">
                        </div>

                        <div style="display:flex; align-items:center; gap:5px;">
                            <input type="tel" name="'.$input_base.'[reps]" class="input-exec" placeholder="Ant: '.$ph_reps.'">
                        </div>
                    </div>';
                }

            echo '</div>';
        }

        echo '  </div>
                <button type="submit" class="btn-finish">
                    <i class="fa-solid fa-check"></i> FINALIZAR TREINO
                </button>
              </form>';
        break;



        
    case 'treinos':
        require_once '../config/db_connect.php'; // Garante conexão se não houver
        $aluno_id = $_SESSION['user_id'];
        $hoje = date('Y-m-d');

        // A. BUSCA TODOS OS TREINOS (Para o Select)
        $sql = "SELECT id, nome, data_inicio, data_fim, nivel_plano FROM treinos WHERE aluno_id = :uid ORDER BY criado_em DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $aluno_id]);
        $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($lista)) {
            echo '<section class="empty-state">
                    <i class="fa-solid fa-dumbbell"></i>
                    <h2>Sem treinos ativos</h2>
                  </section>';
            break;
        }

        // B. DEFINE TREINO ATUAL
        $treino = $lista[0];
        if ($treino_req) {
            foreach($lista as $t) {
                if ($t['id'] == $treino_req) { $treino = $t; break; }
            }
        }

        // C. BUSCA DADOS DA PERIODIZAÇÃO
        $micro_atual = null;
        $micros = [];
        $meta_treino = "";

        if ($treino['nivel_plano'] !== 'basico') {
            $stmt = $pdo->prepare("SELECT id, objetivo_macro FROM periodizacoes WHERE treino_id = ?");
            $stmt->execute([$treino['id']]);
            $per = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($per) {
                $meta_treino = $per['objetivo_macro'];
                
                // Busca todos os campos, incluindo os novos descansos
                $stmt = $pdo->prepare("SELECT * FROM microciclos WHERE periodizacao_id = ? ORDER BY semana_numero ASC");
                $stmt->execute([$per['id']]);
                $micros = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Seleção do Microciclo (Clique > Data > Primeiro)
                if ($micro_req) {
                    foreach ($micros as $m) {
                        if ($m['id'] == $micro_req) { $micro_atual = $m; break; }
                    }
                }
                
                if (!$micro_atual) {
                    foreach ($micros as $m) {
                        if ($hoje >= $m['data_inicio_semana'] && $hoje <= $m['data_fim_semana']) {
                            $micro_atual = $m;
                            break;
                        }
                    }
                }

                if (!$micro_atual && !empty($micros)) $micro_atual = $micros[0];
            }
        }

        // D. BUSCA DIVISÕES
        $stmt = $pdo->prepare("SELECT * FROM treino_divisoes WHERE treino_id = ? ORDER BY letra ASC");
        $stmt->execute([$treino['id']]);
        $divisoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- RENDERIZAÇÃO ---
        echo '<section id="meu-treino" class="fade-in">';
        

        // 2. Header
        echo '<div class="workout-header-main">
                <h2 class="workout-title">'.$treino['nome'].'</h2>
                <div class="meta-tags">
                    <span class="tag">'.strtoupper($treino['nivel_plano']).'</span>
                    '.($meta_treino ? '<span class="tag outline">'.$meta_treino.'</span>' : '').'
                </div>
              </div>';

        // 3. Timeline
        if (!empty($micros)) {
            echo '<div class="timeline-container">';
            foreach ($micros as $m) {
                $active = ($micro_atual && $m['id'] == $micro_atual['id']) ? 'active' : '';
                $data = date('d/m', strtotime($m['data_inicio_semana']));
                
                echo '<div class="week-card '.$active.'" onclick="carregarConteudo(\'treinos&treino_id='.$treino['id'].'&micro_id='.$m['id'].'\')">
                        <span class="week-label">SEM '.$m['semana_numero'].'</span>
                        <span class="week-date">'.$data.'</span>
                      </div>';
            }
            echo '</div>';

            // VISUALIZAÇÃO DE FOCO ATUALIZADA (COMPOSTOS VS ISOLADORES)
            if ($micro_atual) {
                // Prepara valores para exibição (fallback para '-')
                $reps_comp = $micro_atual['reps_compostos'] ?: '-';
                $desc_comp = $micro_atual['descanso_compostos'] ? $micro_atual['descanso_compostos'].'s' : '-';
                
                $reps_iso = $micro_atual['reps_isoladores'] ?: '-';
                $desc_iso = $micro_atual['descanso_isoladores'] ? $micro_atual['descanso_isoladores'].'s' : '-';

                echo '<div class="week-focus-box">
                        <div class="focus-header">
                            <h4><i class="fa-solid fa-flag"></i> FASE: '.strtoupper($micro_atual['nome_fase']).'</h4>
                        </div>
                        
                        <div class="focus-grid">
                            <div class="focus-item">
                                <small style="color:var(--gold);">COMPOSTOS</small>
                                <strong>'.$reps_comp.'</strong>
                                <span style="display:block; font-size:0.75rem; color:#ccc; margin-top:4px;">
                                    <i class="fa-solid fa-clock"></i> '.$desc_comp.'
                                </span>
                            </div>
                            <div class="focus-item">
                                <small style="color:var(--gold);">ISOLADORES</small>
                                <strong>'.$reps_iso.'</strong>
                                <span style="display:block; font-size:0.75rem; color:#ccc; margin-top:4px;">
                                    <i class="fa-solid fa-clock"></i> '.$desc_iso.'
                                </span>
                            </div>
                        </div>
                        
                        '.($micro_atual['foco_comentario'] ? '<p class="focus-obs">"'.$micro_atual['foco_comentario'].'"</p>' : '').'
                      </div>';
            }
        }

        // 4. Abas e Exercícios
        echo '<div class="division-tabs">';
        $first = true;
        foreach ($divisoes as $d) {
            $act = $first ? 'active' : '';
            echo '<button class="tab-btn '.$act.'" onclick="abrirTreino(event, \'div_'.$d['id'].'\')">TREINO '.$d['letra'].'</button>';
            $first = false;
        }
        echo '</div>';

        $first = true;
        foreach ($divisoes as $d) {
            $display = $first ? 'block' : 'none';
            
            $stmt = $pdo->prepare("SELECT * FROM exercicios WHERE divisao_id = ? ORDER BY ordem ASC");
            $stmt->execute([$d['id']]);
            $exercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<div id="div_'.$d['id'].'" class="treino-content" style="display:'.$display.'">';
            
            if ($exercicios) {
                foreach ($exercicios as $ex) {
                    $stmt = $pdo->prepare("SELECT * FROM series WHERE exercicio_id = ?");
                    $stmt->execute([$ex['id']]);
                    $series = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo '<div class="exercise-card">
                            <div class="ex-header">
                                <div>
                                    <span class="ex-name">'.$ex['nome_exercicio'].'</span>
                                    <span class="ex-type">'.strtoupper($ex['tipo_mecanica']).'</span>
                                </div>
                                '.($ex['video_url'] ? '<a href="'.$ex['video_url'].'" target="_blank" class="btn-video"><i class="fa-solid fa-play"></i></a>' : '').'
                            </div>
                            <div class="ex-body">
                                '.($ex['observacao_exercicio'] ? '<div class="ex-note"><i class="fa-solid fa-info-circle"></i> '.$ex['observacao_exercicio'].'</div>' : '').'
                                <div class="sets-grid">';
                                
                                foreach ($series as $s) {
                                    // 1. Valores Iniciais (Padrão cadastrado)
                                    $reps = $s['reps_fixas'];
                                    $desc = $s['descanso_fixo'];

                                    // 2. Lógica de Categorias Especiais (Override Fixo)
                                    if ($s['categoria'] === 'warmup') {
                                        $desc = '30s'; // Fixo para Aquecimento
                                    } 
                                    elseif ($s['categoria'] === 'feeder') {
                                        $desc = '60s'; // Fixo para Feeder
                                    } 
                                    else {
                                        // 3. Lógica de Periodização (Séries de Trabalho)
                                        // Só aplica se NÃO for Warmup/Feeder
                                        if ($micro_atual) {
                                            
                                            // Se for COMPOSTO
                                            if ($ex['tipo_mecanica'] == 'composto') {
                                                if (!empty($micro_atual['reps_compostos'])) {
                                                    $reps = $micro_atual['reps_compostos'];
                                                }
                                                // Usa o novo campo descanso_compostos
                                                if (!empty($micro_atual['descanso_compostos'])) {
                                                    $desc = $micro_atual['descanso_compostos'].'s';
                                                }
                                            } 
                                            // Se for ISOLADOR
                                            elseif ($ex['tipo_mecanica'] == 'isolador') {
                                                if (!empty($micro_atual['reps_isoladores'])) {
                                                    $reps = $micro_atual['reps_isoladores'];
                                                }
                                                // Usa o novo campo descanso_isoladores
                                                if (!empty($micro_atual['descanso_isoladores'])) {
                                                    $desc = $micro_atual['descanso_isoladores'].'s';
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Fallbacks visuais
                                    if(empty($reps)) $reps = "Falha";
                                    if(empty($desc)) $desc = "-";

                                    echo '<div class="set-item '.$s['categoria'].'">
                                            <div class="set-top">'.$s['quantidade'].'x '.$s['categoria'].'</div>
                                            <div class="set-bottom">
                                                <span>'.$reps.'</span>
                                                <small>'.$desc.'</small>
                                            </div>
                                          </div>';
                                }
                    echo       '</div>
                            </div>
                          </div>';
                }
            } else {
                echo '<div class="empty-day">Descanso</div>';
            }
            echo '</div>';
            $first = false;
        }

        echo '</section>';
        break;

    
    case 'historico':
        require_once '../config/db_connect.php';
        $aluno_id = $_SESSION['user_id'];
        
        // Verifica se foi pedido o detalhe de uma data específica
        $data_ref = $_GET['data_ref'] ?? null;

        // --- MODO 1: DETALHES DO TREINO (QUANDO CLICA) ---
        // --- MODO 1: DETALHES DO TREINO (AGRUPADO) ---
        if ($data_ref) {
            // 1. Infos Gerais
            $sql_info = "SELECT DISTINCT t.nome as nome_treino, td.letra 
                         FROM treino_historico th
                         JOIN treinos t ON th.treino_id = t.id
                         JOIN treino_divisoes td ON th.divisao_id = td.id
                         WHERE th.aluno_id = :uid AND th.data_treino = :dt";
            $stmt = $pdo->prepare($sql_info);
            $stmt->execute(['uid' => $aluno_id, 'dt' => $data_ref]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Busca Detalhes (JOIN com SERIES para pegar a categoria)
            // Ordenamos por exercício (ordem) e depois pela série
            $sql_detalhes = "SELECT th.*, e.nome_exercicio, s.categoria 
                             FROM treino_historico th
                             JOIN exercicios e ON th.exercicio_id = e.id
                             LEFT JOIN series s ON th.serie_numero = s.id 
                             WHERE th.aluno_id = :uid AND th.data_treino = :dt
                             ORDER BY e.ordem ASC, th.id ASC";
            $stmt = $pdo->prepare($sql_detalhes);
            $stmt->execute(['uid' => $aluno_id, 'dt' => $data_ref]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. AGRUPAMENTO POR EXERCÍCIO
            $treino_agrupado = [];
            foreach ($registros as $reg) {
                $id_ex = $reg['exercicio_id'];
                if (!isset($treino_agrupado[$id_ex])) {
                    $treino_agrupado[$id_ex] = [
                        'nome' => $reg['nome_exercicio'],
                        'series' => []
                    ];
                }
                $treino_agrupado[$id_ex]['series'][] = $reg;
            }

            // --- RENDERIZAÇÃO ---
            echo '<section class="fade-in">
                    <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                        <button onclick="carregarConteudo(\'historico\')" style="background:none; border:none; color:#fff; font-size:1.2rem;">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <div>
                            <span style="color:#888; font-size:0.8rem; text-transform:uppercase;">Visualizando</span>
                            <h2 style="margin:0; color:#fff; font-size:1.2rem;">TREINO '.$info['letra'].'</h2>
                        </div>
                    </div>

                    <div style="margin-bottom:20px; padding:15px; background:rgba(255,186,66,0.1); border-radius:8px; border:1px solid var(--gold); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong style="color:var(--gold); display:block;">'.$info['nome_treino'].'</strong>
                            <span style="color:#ccc; font-size:0.8rem;">'.date('d/m/Y \à\s H:i', strtotime($data_ref)).'</span>
                        </div>
                        <i class="fa-solid fa-calendar-check" style="color:var(--gold); font-size:1.5rem;"></i>
                    </div>

                    <div class="history-details-list">';
                    
                    if (empty($treino_agrupado)) {
                        echo '<p style="text-align:center; color:#666;">Nenhum dado encontrado para este registro.</p>';
                    }

                    foreach ($treino_agrupado as $ex_id => $dados) {
                        echo '<div class="hist-exercise-group">
                                <div class="hist-ex-header">
                                    <i class="fa-solid fa-dumbbell"></i>
                                    <span>'.$dados['nome'].'</span>
                                </div>
                                
                                <table class="hist-sets-table">
                                    <thead>
                                        <tr>
                                            <th width="15%">#</th>
                                            <th width="35%">TIPO</th>
                                            <th width="25%">KG</th>
                                            <th width="25%">REPS</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                    
                                    $contador_serie = 1;
                                    foreach ($dados['series'] as $serie) {
                                        // Fallback se categoria vier vazia
                                        $cat = $serie['categoria'] ? $serie['categoria'] : 'work';
                                        
                                        echo '<tr>
                                                <td style="color:#666; font-weight:bold;">'.$contador_serie.'</td>
                                                <td><span class="badge-set-type '.$cat.'">'.strtoupper($cat).'</span></td>
                                                <td style="color:#fff; font-weight:bold;">'.($serie['carga_kg']*1).'</td>
                                                <td style="color:#fff;">'.$serie['reps_realizadas'].'</td>
                                              </tr>';
                                        $contador_serie++;
                                    }

                        echo '      </tbody>
                                </table>
                              </div>';
                    }

            echo '  </div>
                  </section>';
            
            break;
        }

        // --- MODO 2: LISTA DE DATAS (VISÃO GERAL) ---
        
        // Agrupa por data para não repetir o mesmo treino 20x (uma vez por série)
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

        echo '<section id="historico-lista" class="fade-in">
                <header class="dash-header">
                    <h1>MEU <span class="highlight-text">HISTÓRICO</span></h1>
                </header>';

        if (empty($historico)) {
            echo '<div class="empty-state">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <h2>Nenhum treino registrado</h2>
                    <p>Realize seu primeiro treino para ver o histórico.</p>
                  </div>';
        } else {
            echo '<div class="history-list">';
            
            foreach ($historico as $h) {
                $data_obj = new DateTime($h['data_treino']);
                $dia = $data_obj->format('d');
                $mes = strftime('%b', $data_obj->getTimestamp()); // %b para mês abrev (Jan, Fev)
                
                // Formatação manual de mês em PT-BR caso servidor não esteja configurado
                $meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                $mes_txt = $meses[(int)$data_obj->format('m') - 1];

                $hora = $data_obj->format('H:i');

                // Passamos a data completa como parâmetro na URL
                echo '<div class="history-card" onclick="carregarConteudo(\'historico&data_ref='.$h['data_treino'].'\')">
                        <div class="hist-date-box">
                            <span class="hist-day">'.$dia.'</span>
                            <span class="hist-month">'.$mes_txt.'</span>
                        </div>
                        <div class="hist-info">
                            <span class="hist-title">Treino '.$h['letra'].'</span>
                            <span class="hist-sub">'.$h['nome_treino'].' • '.$hora.'</span>
                        </div>
                        <i class="fa-solid fa-chevron-right hist-arrow"></i>
                      </div>';
            }
            
            echo '</div>';
        }
        
        echo '</section>';
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