<?php
session_start();
require_once 'config/db_connect.php'; // Necessário para checar o banco atualizado

// 1. Verifica Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] !== 'aluno') {
    header("Location: login.php");
    exit;
}

// 2. Verifica Status do Plano (TRAVA DE SEGURANÇA)
$stmt = $pdo->prepare("SELECT data_expiracao FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Se data_expiracao for NULL ou menor que hoje, bloqueia
$hoje = date('Y-m-d');
if (empty($user_data['data_expiracao']) || $user_data['data_expiracao'] < $hoje) {
    // Exceção: Se for admin testando, não bloqueia (opcional, mas bom pra teste)
    // Mas aqui é arquivo de usuário, então bloqueia.
    header("Location: bloqueado.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Usuário - Ryan Coach</title>
    
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/usuario.css">

    <?php include 'includes/head_main.php'; ?>
</head>
<body>
    
    <div class="background-overlay"></div>

    <?php include 'includes/sidebar_usuario.php'; ?>

    <main id="conteudo">
        </main>

    <script>
        // Função Global de Navegação
        window.carregarConteudo = async function(pagina) {
            const area = document.getElementById('conteudo');
            const botoes = document.querySelectorAll('#main-aside button');

            // Feedback Visual
            area.innerHTML = '<div class="loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>';
            area.classList.add('loading');

            try {
                // Requisição Limpa
                const req = await fetch(`ajax/get_conteudo.php?pagina=${pagina}`);
                if (!req.ok) throw new Error('Erro na rede');
                
                const html = await req.text();
                
                area.innerHTML = html;
                area.classList.remove('loading');

                // Atualiza Menu Lateral
                // Pega só o nome da página (antes do &)
                const base = pagina.split('&')[0];
                botoes.forEach(btn => {
                    if (btn.dataset.pagina === base) btn.classList.add('active');
                    else btn.classList.remove('active');
                });

            } catch (err) {
                console.error(err);
                area.innerHTML = '<p class="error">Erro ao carregar.</p>';
            }
        };

        // Inicialização
        document.addEventListener('DOMContentLoaded', () => {
            carregarConteudo('dashboard'); // Ou 'treinos' se preferir iniciar lá

            // Listener do Menu Lateral
            document.getElementById('main-aside').addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (btn && btn.dataset.pagina) {
                    if (btn.dataset.pagina === 'logout') window.location.href = 'actions/logout.php';
                    else carregarConteudo(btn.dataset.pagina);
                }
            });
        });

        // Preview de Imagem (Perfil)
        window.previewImage = function(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.getElementById('preview-img');
                    if (img) img.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        };
        
        // Função de Abas (Global para funcionar no HTML injetado)
        window.abrirTreino = function(evt, id) {
            const contents = document.getElementsByClassName("treino-content");
            for (let i = 0; i < contents.length; i++) contents[i].style.display = "none";
            
            const tabs = document.getElementsByClassName("tab-btn");
            for (let i = 0; i < tabs.length; i++) tabs[i].classList.remove("active");
            
            document.getElementById(id).style.display = "block";
            evt.currentTarget.classList.add("active");
        };
    </script>
    <script>
            function abrirTreino(evt, divName) {
                var i, content, tablinks;
                content = document.getElementsByClassName("treino-content");
                for (i = 0; i < content.length; i++) {
                    content[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("tab-btn");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }
                document.getElementById(divName).style.display = "block";
                evt.currentTarget.className += " active";
            }
        </script>

    <div id="modalTreinoOpcoes" class="modal-overlay" style="display: none;">
        <div class="modal-content selection-modal">
            <button class="modal-close" onclick="fecharModalTreinos()">&times;</button>
            
            <div id="step-type">
                <h3 class="modal-title">O QUE DESEJA ACESSAR?</h3>
                <div class="modal-grid-options">
                    <div class="option-card" onclick="irParaListaTreinos()">
                        <i class="fa-solid fa-dumbbell"></i>
                        <span>Fichas de Treino</span>
                    </div>
                    <div class="option-card outline" onclick="irParaHistorico()">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Histórico Realizado</span>
                    </div>
                </div>
            </div>

            <div id="step-list" style="display: none;">
                <div class="modal-header-row">
                    <button class="btn-back" onclick="voltarStepType()"><i class="fa-solid fa-arrow-left"></i></button>
                    <h3 class="modal-title">QUAL PLANEJAMENTO?</h3>
                </div>
                <div id="lista-treinos-container" class="treinos-list-scroll">
                    <div class="loading-spinner"><i class="fa-solid fa-circle-notch fa-spin"></i></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- LÓGICA DO MODAL DE TREINOS ---
    
    function abrirModalTreinos() {
        document.getElementById('modalTreinoOpcoes').style.display = 'flex';
        voltarStepType(); // Sempre reseta para a primeira tela
    }

    function fecharModalTreinos() {
        document.getElementById('modalTreinoOpcoes').style.display = 'none';
    }

    function irParaHistorico() {
        fecharModalTreinos();
        carregarConteudo('historico');
    }

    function irParaListaTreinos() {
        // 1. Muda a tela do modal
        document.getElementById('step-type').style.display = 'none';
        document.getElementById('step-list').style.display = 'block';
        
        const container = document.getElementById('lista-treinos-container');
        container.innerHTML = '<div style="color:#fff; padding:20px;"><i class="fa-solid fa-circle-notch fa-spin"></i> Buscando...</div>';

        // 2. Busca a lista via AJAX
        fetch('ajax/get_conteudo.php?pagina=listar_treinos_json')
            .then(res => res.json())
            .then(data => {
                container.innerHTML = ''; // Limpa loading
                
                if (data.length === 0) {
                    container.innerHTML = '<p style="color:#888;">Nenhum treino encontrado.</p>';
                    return;
                }

                data.forEach(treino => {
                    const btn = document.createElement('button');
                    btn.className = 'btn-treino-select';
                    
                    // Formata data simples
                    const dataInicio = new Date(treino.data_inicio).toLocaleDateString('pt-BR');
                    
                    btn.innerHTML = `
                        <strong>${treino.nome}</strong>
                        <span>${treino.nivel_plano.toUpperCase()} • Início: ${dataInicio}</span>
                    `;
                    
                    btn.onclick = function() {
                        fecharModalTreinos();
                        // Carrega o treino específico
                        carregarConteudo('treinos&treino_id=' + treino.id);
                    };
                    
                    container.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<p style="color:red;">Erro ao carregar lista.</p>';
            });
    }

    function voltarStepType() {
        document.getElementById('step-list').style.display = 'none';
        document.getElementById('step-type').style.display = 'block';
    }

    // Fecha ao clicar fora
    window.onclick = function(event) {
        const modal = document.getElementById('modalTreinoOpcoes');
        if (event.target == modal) {
            fecharModalTreinos();
        }
    }
    </script>

    <div id="modalNovaAvaliacao" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        
        <div class="modal-header-av">
            <button class="modal-close" onclick="fecharModalAvaliacao()">&times;</button>
            <h3><i class="fa-solid fa-ruler-combined"></i> NOVA AVALIAÇÃO</h3>
        </div>
        
        <form action="actions/avaliacao_add.php" method="POST" enctype="multipart/form-data" id="formAvaliacao">
            <input type="hidden" name="aluno_id" id="av_aluno_id" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">

            <div class="modal-body-scroll">
                
                <div class="form-section-box">
                    <span class="section-label-gold">DADOS GERAIS</span>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label class="label-mini">Data</label>
                            <input type="date" name="data_avaliacao" class="input-dark" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label class="label-mini">Gênero (p/ Cálculo BF)</label>
                            <select name="genero" class="input-dark">
                                <option value="M">Masculino</option>
                                <option value="F">Feminino</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="label-mini">Idade</label>
                            <input type="number" name="idade" class="input-dark" placeholder="Anos">
                        </div>
                        <div>
                            <label class="label-mini">Altura (cm)</label>
                            <input type="number" name="altura" class="input-dark" placeholder="Ex: 175" required>
                        </div>
                        <div>
                            <label class="label-mini">Peso (kg)</label>
                            <input type="number" step="0.1" name="peso" class="input-dark" placeholder="00.0" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-box">
                    <span class="section-label-gold">TRONCO & PERÍMETROS</span>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                        <div>
                            <label class="label-mini">Pescoço</label>
                            <input type="number" step="0.1" name="pescoco" class="input-dark" placeholder="0.0">
                        </div>
                        <div>
                            <label class="label-mini">Ombros</label>
                            <input type="number" step="0.1" name="ombro" class="input-dark" placeholder="0.0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                        <div>
                            <label class="label-mini">Tórax Inspirado</label>
                            <input type="number" step="0.1" name="torax_inspirado" class="input-dark" placeholder="0.0">
                        </div>
                        <div>
                            <label class="label-mini">Tórax Relaxado</label>
                            <input type="number" step="0.1" name="torax_relaxado" class="input-dark" placeholder="0.0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="label-mini">Cintura</label>
                            <input type="number" step="0.1" name="cintura" class="input-dark" placeholder="0.0">
                        </div>
                        <div>
                            <label class="label-mini">Abdômen</label>
                            <input type="number" step="0.1" name="abdomen" class="input-dark" placeholder="0.0">
                        </div>
                        <div>
                            <label class="label-mini">Quadril</label>
                            <input type="number" step="0.1" name="quadril" class="input-dark" placeholder="0.0">
                        </div>
                    </div>
                </div>

                <div class="form-section-box">
                    <span class="section-label-gold">MEMBROS SUPERIORES (DIR / ESQ)</span>
                    
                    <div style="margin-bottom: 10px;">
                        <label class="label-mini" style="color:#fff;">Braço Relaxado</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="number" step="0.1" name="braco_dir_relaxado" class="input-dark" placeholder="Direito">
                            <input type="number" step="0.1" name="braco_esq_relaxado" class="input-dark" placeholder="Esquerdo">
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label class="label-mini" style="color:#fff;">Braço Contraído</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="number" step="0.1" name="braco_dir_contraido" class="input-dark" placeholder="Direito">
                            <input type="number" step="0.1" name="braco_esq_contraido" class="input-dark" placeholder="Esquerdo">
                        </div>
                    </div>

                    <div>
                        <label class="label-mini" style="color:#fff;">Antebraço</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="number" step="0.1" name="antebraco_dir" class="input-dark" placeholder="Direito">
                            <input type="number" step="0.1" name="antebraco_esq" class="input-dark" placeholder="Esquerdo">
                        </div>
                    </div>
                </div>

                <div class="form-section-box">
                    <span class="section-label-gold">MEMBROS INFERIORES (DIR / ESQ)</span>
                    
                    <div style="margin-bottom: 10px;">
                        <label class="label-mini" style="color:#fff;">Coxa</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="number" step="0.1" name="coxa_dir" class="input-dark" placeholder="Direita">
                            <input type="number" step="0.1" name="coxa_esq" class="input-dark" placeholder="Esquerda">
                        </div>
                    </div>

                    <div>
                        <label class="label-mini" style="color:#fff;">Panturrilha</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="number" step="0.1" name="panturrilha_dir" class="input-dark" placeholder="Direita">
                            <input type="number" step="0.1" name="panturrilha_esq" class="input-dark" placeholder="Esquerda">
                        </div>
                    </div>
                </div>

                <div class="form-section-box">
                    <span class="section-label-gold">FOTOS</span>
                    <input type="file" name="fotos[]" id="foto_input" multiple accept="image/*" style="display: none;" onchange="previewFiles()">
                    <label for="foto_input" class="upload-zone">
                        <i class="fa-solid fa-camera upload-icon"></i>
                        <div class="upload-text">Adicionar Fotos</div>
                    </label>
                    <div id="preview-area" class="preview-container"></div>
                </div>

                <div class="form-section-box" style="margin-bottom:0;">
                    <span class="section-label-gold">VÍDEO (OPCIONAL)</span>
                    <label class="label-mini">Link (Youtube / Drive)</label>
                    <input type="text" name="videos_links" class="input-dark" placeholder="Cole o link aqui...">
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-save-modal">SALVAR E CALCULAR</button>
            </div>
        </form>
    </div>
</div>

    <script>
    // Abre o modal. 
    // Se passar 'idAluno', é o admin abrindo para um aluno específico.
    function abrirModalAvaliacao(idAluno = null) {
        if (idAluno) {
            document.getElementById('av_aluno_id').value = idAluno;
        }
        document.getElementById('modalNovaAvaliacao').style.display = 'flex';
    }

    function fecharModalAvaliacao() {
        document.getElementById('modalNovaAvaliacao').style.display = 'none';
    }

    
   // --- LÓGICA DE UPLOAD BLINDADA ---
    
    // Configurações
    const TARGET_SIZE = 2 * 1024 * 1024; // Tenta comprimir se maior que 2MB
    const HARD_LIMIT = 15 * 1024 * 1024; // 15MB (Bloqueia envio se for maior que isso)
    const MAX_WIDTH = 1600; 
    const QUALITY = 0.7;

    // Monitora seleção de arquivos
    const fotoInput = document.getElementById('foto_input');
    if(fotoInput) {
        fotoInput.addEventListener('change', async function(e) {
            const files = e.target.files;
            if (!files || files.length === 0) return;

            // Feedback visual
            const label = document.querySelector('.upload-zone .upload-text');
            const originalText = "Adicionar Fotos"; // Texto padrão
            label.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Preparando fotos...';
            
            const dataTransfer = new DataTransfer();
            const previewArea = document.getElementById('preview-area');
            previewArea.innerHTML = ""; 

            // Processa cada arquivo individualmente
            for (let file of files) {
                
                // 1. Validação de Tipo
                if (!/\.(jpe?g|png|webp)$/i.test(file.name)) {
                    continue; // Ignora arquivos que não são imagem
                }

                // 2. Validação de Limite Extremo (Server Crash)
                if (file.size > HARD_LIMIT) {
                    alert(`A imagem "${file.name}" é GIGANTE (${(file.size/1024/1024).toFixed(1)}MB). O limite é 15MB. Ela será ignorada.`);
                    continue;
                }

                let finalFile = file;

                // 3. Tenta Comprimir se for grande
                if (file.size > TARGET_SIZE) {
                    try {
                        console.log(`Tentando comprimir ${file.name}...`);
                        finalFile = await compressImage(file);
                        console.log(`Sucesso: ${(finalFile.size/1024/1024).toFixed(2)}MB`);
                    } catch (err) {
                        console.warn("Falha na compressão, usando original:", err);
                        finalFile = file; // Fallback: usa a original se der erro
                    }
                }

                // Adiciona à lista final
                dataTransfer.items.add(finalFile);

                // Gera Preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'thumb-preview';
                    previewArea.appendChild(img);
                }
                reader.readAsDataURL(finalFile);
            }

            // Atualiza o input com a nova lista (processada ou original)
            document.getElementById('foto_input').files = dataTransfer.files;
            
            // Atualiza texto do botão
            const total = dataTransfer.files.length;
            if (total > 0) {
                label.innerText = total + (total === 1 ? ' foto pronta' : ' fotos prontas');
                document.querySelector('.upload-zone').style.borderColor = 'var(--gold)';
            } else {
                label.innerText = originalText;
            }
        });
    }

    // Função de Compressão
    function compressImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error('Erro no Canvas Blob'));
                            return;
                        }
                        const newFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(newFile);
                    }, 'image/jpeg', QUALITY);
                };
                img.onerror = (err) => reject(err);
            };
            reader.onerror = (err) => reject(err);
        });
    }

    // Validação no Envio (Submit)
    const formAvaliacao = document.querySelector('form[action*="avaliacao_add"]');
    if (formAvaliacao) {
        formAvaliacao.onsubmit = function(e) {
            const btn = document.querySelector('.btn-save-modal');
            
            // Feedback visual
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ENVIANDO...';
            btn.disabled = true;
            btn.style.opacity = "0.7";
            
            return true;
        };
    }
    </script>

    <div id="modalAvaliacaoOpcoes" class="modal-overlay" style="display: none;">
        <div class="modal-content selection-modal">
            <button class="modal-close" onclick="fecharModalAvaliacoes()">&times;</button>
            
            <div id="step-type-av">
                <h3 class="modal-title">O QUE DESEJA VER?</h3>
                <div class="modal-grid-options">
                    
                    <div class="option-card" onclick="irParaAvaliacoes()">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Minhas Avaliações</span>
                    </div>
                    
                    <div class="option-card outline" onclick="irParaProgresso()">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Meu Progresso</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalAvaliacoes() {
            document.getElementById('modalAvaliacaoOpcoes').style.display = 'flex';
        }

        function fecharModalAvaliacoes() {
            document.getElementById('modalAvaliacaoOpcoes').style.display = 'none';
        }

        function irParaAvaliacoes() {
            fecharModalAvaliacoes();
            carregarConteudo('avaliacoes');
        }

        function irParaProgresso() {
            fecharModalAvaliacoes();
            carregarConteudo('progresso');
        }

        // Fecha ao clicar fora
        window.onclick = function(event) {
            const m1 = document.getElementById('modalTreinoOpcoes');
            const m2 = document.getElementById('modalAvaliacaoOpcoes');
            if (event.target == m1) fecharModalTreinos();
            if (event.target == m2) fecharModalAvaliacoes();
        }
    </script>
    <script>
        window.toggleAccordion = function(id) {
        const card = document.getElementById(id);
        if (!card) return;

        // SELETOR CORRIGIDO: Agora busca pela classe certa
        const body = card.querySelector(".accordion-body");
        const arrow = card.querySelector(".accordion-arrow");
        
        if (body.style.display === "none" || body.style.display === "") {
            body.style.display = "block";
            card.classList.add("active");
            if(arrow) arrow.style.transform = "rotate(90deg)"; // Gira a setinha
        } else {
            body.style.display = "none";
            card.classList.remove("active");
            if(arrow) arrow.style.transform = "rotate(0deg)"; // Volta a setinha
        }
    };
    </script>
    <script>
    // --- LÓGICA DO DASHBOARD PROGRESSO (FIXED) ---

    // 1. Trocar Tabelas
    window.switchTable = function(tabName, btn) {
        document.querySelectorAll('.table-container').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-pill').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabName).style.display = 'block';
        btn.classList.add('active');
    };

    // 2. Gráfico Master
    let masterChartInstance = null;
    let chartDataStore = null;

    window.initMasterChart = function() {
        const input = document.getElementById('chart-master-data');
        if (!input) return;
        
        try {
            chartDataStore = JSON.parse(input.value);
            // Inicia com Peso por padrão
            renderChart('peso');
        } catch (e) {
            console.error("Erro ao ler dados do gráfico", e);
        }
    };

    window.switchChart = function(metric, btn) {
        document.querySelectorAll('.chart-btn').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        renderChart(metric);
    };

    function renderChart(metric) {
        const ctx = document.getElementById('masterChart');
        if (!ctx || !chartDataStore) return;

        // Se já existe um gráfico, DESTRUA ele antes de criar outro
        if (masterChartInstance) {
            masterChartInstance.destroy();
        }

        // Configurações Visuais
        let label = 'Peso (kg)';
        let color = '#FFBA42'; // Gold
        let data = chartDataStore[metric];

        if (metric === 'bf') { label = '% Gordura'; color = '#ff4d4d'; } // Vermelho
        if (metric === 'magra') { label = 'Massa Magra (kg)'; color = '#00e676'; } // Verde

        // Monta o Gradiente
        const context = ctx.getContext('2d');
        const gradient = context.createLinearGradient(0, 0, 0, 300);
        
        // Conversão Hex para RGB simples para o gradiente
        let r=255, g=186, b=66; // Gold default
        if(metric === 'bf') { r=255; g=77; b=77; }
        if(metric === 'magra') { r=0; g=230; b=118; }

        gradient.addColorStop(0, `rgba(${r}, ${g}, ${b}, 0.5)`);
        gradient.addColorStop(1, `rgba(${r}, ${g}, ${b}, 0)`);

        masterChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartDataStore.labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#161616',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                layout: { padding: { top: 10, bottom: 10, left: 0, right: 10 } },
                scales: {
                    y: { 
                        grid: { color: 'rgba(255,255,255,0.05)' }, 
                        ticks: { color: '#888', font: { size: 11 } } 
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { color: '#888', font: { size: 11 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } 
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    }
    </script>
    <script>
        // --- LÓGICA DE CHECK DA DIETA ---
    async function toggleRefeicao(refeicaoId, btn) {
        // 1. Efeito Visual Imediato (UX Rápida)
        const card = document.getElementById('ref_' + refeicaoId);
        const icon = btn.querySelector('i');
        
        // Alterna classes visualmente antes de esperar o servidor
        btn.classList.toggle('checked');
        card.classList.toggle('completed'); // Deixa o card meio transparente

        // 2. Envia para o Servidor (Background)
        try {
            const response = await fetch('actions/dieta_check.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refeicao_id: refeicaoId })
            });

            const data = await response.json();
            
            // Se o servidor confirmar, ótimo. Se der erro, desfazemos.
            if (!response.ok) {
                throw new Error('Erro ao salvar');
            }

        } catch (error) {
            console.error(error);
            // Reverte o visual se deu erro (feedback de falha)
            btn.classList.toggle('checked');
            card.classList.toggle('completed');
            alert("Erro de conexão. Tente novamente.");
        }
    }
    </script>
    
    <script>
    // --- LÓGICA DO CRONÔMETRO ---
    let timerInterval;
    let seconds = 0;
    let isRunning = false;

    // Funções de Visibilidade
    function mostrarTimer() {
        document.getElementById('float-timer').style.display = 'flex';
    }

    function fecharTimer() {
        document.getElementById('float-timer').style.display = 'none';
        resetTimer(); // Opcional: reseta ao fechar
    }

    function toggleTimer() {
        const btn = document.getElementById('btn-timer-toggle');
        const icon = btn.querySelector('i');
        const widget = document.getElementById('float-timer');

        if (isRunning) {
            clearInterval(timerInterval);
            isRunning = false;
            icon.classList.remove('fa-pause');
            icon.classList.add('fa-play');
            widget.classList.remove('running');
        } else {
            isRunning = true;
            icon.classList.remove('fa-play');
            icon.classList.add('fa-pause');
            widget.classList.add('running');
            
            timerInterval = setInterval(() => {
                seconds++;
                updateTimerDisplay();
            }, 1000);
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        seconds = 0;
        isRunning = false;
        updateTimerDisplay();
        
        const btn = document.getElementById('btn-timer-toggle');
        const icon = btn.querySelector('i');
        const widget = document.getElementById('float-timer');
        
        if(icon) {
            icon.classList.remove('fa-pause');
            icon.classList.add('fa-play');
        }
        if(widget) widget.classList.remove('running');
    }

    function updateTimerDisplay() {
        const display = document.getElementById('timer-val');
        if (!display) return;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        display.innerText = (mins < 10 ? "0" : "") + mins + ":" + (secs < 10 ? "0" : "") + secs;
    }

    // --- LÓGICA DE ARRASTAR (DRAG & DROP) ---
    // Agora que o elemento está no HTML fixo, o JS o encontra facilmente.
    
    const dragItem = document.getElementById("float-timer");
    let active = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;
    let xOffset = 0;
    let yOffset = 0;

    // Eventos de Toque (Mobile)
    dragItem.addEventListener("touchstart", dragStart, {passive: false});
    dragItem.addEventListener("touchend", dragEnd, {passive: false});
    dragItem.addEventListener("touchmove", drag, {passive: false});

    // Eventos de Mouse (PC)
    dragItem.addEventListener("mousedown", dragStart);
    dragItem.addEventListener("mouseup", dragEnd);
    dragItem.addEventListener("mousemove", drag);

    function dragStart(e) {
        // Ignora se clicar nos botões (Play/Reset/Fechar)
        if (e.target.tagName === 'BUTTON' || e.target.closest('button') || e.target.closest('.fa-times')) {
            return;
        }

        if (e.type === "touchstart") {
            initialX = e.touches[0].clientX - xOffset;
            initialY = e.touches[0].clientY - yOffset;
        } else {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
        }

        if (e.target === dragItem || dragItem.contains(e.target)) {
            active = true;
        }
    }

    function dragEnd(e) {
        initialX = currentX;
        initialY = currentY;
        active = false;
    }

    function drag(e) {
        if (active) {
            e.preventDefault(); // Impede a tela de rolar
        
            if (e.type === "touchmove") {
                currentX = e.touches[0].clientX - initialX;
                currentY = e.touches[0].clientY - initialY;
            } else {
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
            }

            xOffset = currentX;
            yOffset = currentY;

            setTranslate(currentX, currentY, dragItem);
        }
    }

    function setTranslate(xPos, yPos, el) {
        el.style.transform = "translate3d(" + xPos + "px, " + yPos + "px, 0)";
    }
</script>

    <div id="float-timer" class="timer-widget" style="display: none;">
        
        <div style="position: absolute; top: -8px; left: -8px; background: #333; border: 1px solid #555; color: #fff; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; cursor: pointer; z-index: 10;" onclick="fecharTimer()">
            <i class="fa-solid fa-times"></i>
        </div>

        <div class="timer-display" id="timer-val">00:00</div>
        <div class="timer-controls">
            <button type="button" class="t-btn reset" onclick="resetTimer()">
                <i class="fa-solid fa-rotate-left"></i>
            </button>
            <button type="button" class="t-btn toggle" id="btn-timer-toggle" onclick="toggleTimer()">
                <i class="fa-solid fa-play"></i>
            </button>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>