<?php
// Define qual página foi pedida na URL (ex: get_conteudo.php?pagina=perfil)
$pagina = $_GET['pagina'] ?? 'treinos';

// Retorna o conteúdo HTML baseado na página pedida

switch ($pagina) {
    case 'dashboard':
        echo '
            <section id="dashboard">
                <div class="dashboard-container-view">
                    
                    <header class="dash-header">
                        <h1>BEM-VINDO, <span class="highlight-text">RYAN.</span></h1>
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
        echo '
            <section id="treinos">
                <h1>Meus Treinos</h1>
                <p>Aqui você verá sua planilha de treinos, vídeos, etc.</p>
                </section>
        ';
        break;

    case 'perfil':
        echo '
            <section id="perfil">
                <h1>Meu Perfil</h1>
                <p>Informações pessoais, foto, objetivos, etc.</p>
                <input type="text" placeholder="Seu nome">
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