<?php
session_start();

// VERIFICAÇÃO DE SEGURANÇA ADMIN
// Se NÃO tem usuário logado OU se o nível NÃO é 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] !== 'admin') {
    // Expulsa para o login de admin
    header("Location: loginAdmin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Administrador - Ryan Coach</title>
    
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/usuario.css"> 
    <link rel="stylesheet" href="assets/css/admin.css">

    <?php include 'includes/head_main.php'; ?>
</head>
<body>
    
    <div class="background-overlay"></div>

    <?php include 'includes/sidebar_admin.php'; ?>

    <main id="conteudo">
        </main>

    <script>
        // ---------------------------------------------------------------
        // 1. NAVEGAÇÃO E SISTEMA (GLOBAL)
        // ---------------------------------------------------------------
        
        async function carregarConteudo(pagina) {
            const areaConteudo = document.getElementById('conteudo');

            // Logout direto
            if (pagina === 'logout') {
                window.location.href = 'actions/logout.php';
                return;
            }

            // Feedback visual
            areaConteudo.innerHTML = '<div class="loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>';
            areaConteudo.classList.add('loading');

            try {
                const response = await fetch(`ajax/get_admin_conteudo.php?pagina=${pagina}`);
                
                if (!response.ok) throw new Error('Erro na requisição');
                
                const html = await response.text();
                areaConteudo.innerHTML = html;
                areaConteudo.classList.remove('loading');

                // Atualiza botão ativo na sidebar
                // (Pega só a parte antes do '&' caso tenha parâmetros)
                const paginaBase = pagina.split('&')[0]; 
                const botoes = document.querySelectorAll('#main-aside button[data-pagina]');

                botoes.forEach(btn => {
                    if (btn.dataset.pagina === paginaBase) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

            } catch (error) {
                console.error(error);
                areaConteudo.innerHTML = '<p class="error">Erro ao carregar painel.</p>';
            }
        }

        // Inicialização ao carregar a página
        document.addEventListener('DOMContentLoaded', () => {
            const aside = document.getElementById('main-aside');
            
            // Verifica se voltou de um salvamento (ex: ?page=treino_painel&id=5)
            const params = new URLSearchParams(window.location.search);
            const pageParam = params.get('page');
            const idParam = params.get('id');

            let paginaInicial = 'dashboard'; // Padrão

            if (pageParam) {
                paginaInicial = pageParam;
                if (idParam) {
                    paginaInicial += '&id=' + idParam;
                }
                // Limpa a URL visualmente (opcional, deixa mais bonito)
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Evento de clique na Sidebar
            aside.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (btn && btn.dataset.pagina) {
                    carregarConteudo(btn.dataset.pagina);
                }
            });

            // Carrega a página definida
            carregarConteudo(paginaInicial);
        });

        // Preview de Foto (Perfil)
        function previewImageAdmin(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById("admin-preview");
                    if(img) img.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ---------------------------------------------------------------
        // 1. Gerenciamento DE ALUNOS 
        // ---------------------------------------------------------------
 // Variável global para guardar o aluno selecionado atualmente
                let alunoAtual = null;

                function abrirPainelAluno(aluno) {
                    alunoAtual = aluno; // Salva o obj aluno para usar nos botões
                    
                    // Preenche o Hub
                    document.getElementById("hub-nome").innerText = aluno.nome;
                    document.getElementById("hub-email").innerText = aluno.email;
                    document.getElementById("hub-foto").src = aluno.foto || "assets/img/user-default.png";
                    
                    document.getElementById("modalGerenciarAluno").style.display = "flex";
                }

                function fecharPainelAluno() {
                    document.getElementById("modalGerenciarAluno").style.display = "none";
                }

                // Roteador de Ações do Hub
                function hubAcao(acao) {
                    if(!alunoAtual) return;

                    if (acao === "historico") {
                        // Fecha modal e vai pro histórico
                        fecharPainelAluno();
                        carregarConteudo("aluno_historico&id=" + alunoAtual.id);
                    }
                    else if (acao === "avaliacao") {
                        // Fecha o Hub e abre o modal de avaliação JÁ EXISTENTE
                        fecharPainelAluno();
                        // Chama a função global que já existe no admin.php
                        if(typeof abrirModalAvaliacao === "function") {
                            abrirModalAvaliacao(alunoAtual.id);
                        } else {
                            alert("Erro: Função de avaliação não encontrada.");
                        }
                    }
                    else if (acao === "dieta") {
                        alert("Módulo de Dieta em desenvolvimento.");
                        // Futuro: carregarConteudo("dieta_editor&id=" + alunoAtual.id);
                    }
                    else if (acao === "editar") {
                        // Fecha Hub e abre Editar (Preenche os dados)
                        fecharPainelAluno();
                        preencherModalEditar(alunoAtual);
                    }
                    else if (acao === "excluir") {
                        if(confirm("Tem certeza que deseja apagar o usuário " + alunoAtual.nome + "?")) {
                            window.location.href = "actions/admin_aluno.php?id=" + alunoAtual.id + "&acao=excluir";
                        }
                    }
                }

                // Função auxiliar para preencher o modal de edição
                function preencherModalEditar(aluno) {
                    document.getElementById("edit_id").value = aluno.id;
                    document.getElementById("edit_nome").value = aluno.nome;
                    document.getElementById("edit_email").value = aluno.email;
                    document.getElementById("edit_telefone").value = aluno.telefone;
                    document.getElementById("edit_expiracao").value = aluno.data_expiracao || "";
                    document.getElementById("edit_nivel").value = aluno.nivel || "aluno"; // Preenche o Select
                    
                    document.getElementById("modalEditarAluno").style.display = "flex";
                }

                function closeEditModal() {
                    document.getElementById("modalEditarAluno").style.display = "none";
                    // Reabre o Hub para não perder o fluxo? Opcional.
                    // abrirPainelAluno(alunoAtual); 
                }       



        // ---------------------------------------------------------------
        // 2. FINANCEIRO (MODAL)
        // ---------------------------------------------------------------
        // Abre Modal
        function openModal() {
            document.getElementById('modalLancamento').style.display = 'flex';
        }

        // Fecha Modal
        function closeModal() {
            document.getElementById('modalLancamento').style.display = 'none';
        }

        // Filtra a lista enquanto digita
        function filtrarAlunosFinanceiro() {
            let input = document.getElementById("busca-aluno-input");
            let filter = input.value.toUpperCase();
            let dropdown = document.getElementById("dropdown-alunos");
            let items = dropdown.getElementsByClassName("dropdown-item");
            
            // Se estiver vazio, esconde a lista
            if (filter === "") {
                dropdown.style.display = "none";
                return;
            }
            
            dropdown.style.display = "block";
            let encontrou = false;

            for (let i = 0; i < items.length; i++) {
                let span = items[i].getElementsByTagName("span")[0];
                let txtValue = span.textContent || span.innerText;
                
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    items[i].style.display = ""; // Mostra
                    encontrou = true;
                } else {
                    items[i].style.display = "none"; // Esconde
                }
            }

            // Se não achou ninguém, esconde a lista
            if (!encontrou) dropdown.style.display = "none";
        }

        // Seleciona o aluno e preenche o input oculto
        function selecionarAlunoFinanceiro(id, nome) {
            document.getElementById("busca-aluno-input").value = nome; // Mostra nome visualmente
            document.getElementById("id-aluno-selecionado").value = id; // Define ID para o PHP
            document.getElementById("dropdown-alunos").style.display = "none"; // Fecha lista
        }

        // Fecha a lista se clicar fora dela
        window.addEventListener('click', function(e) {
            let dropdown = document.getElementById("dropdown-alunos");
            let input = document.getElementById("busca-aluno-input");
            if (dropdown && e.target !== input && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });


        // ---------------------------------------------------------------
        // 3. EDITOR DE TREINOS (CRIAÇÃO)
        // ---------------------------------------------------------------

        function toggleNovoTreino() {
            const modal = document.getElementById('box-novo-treino');
            
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex'; // FLEX é essencial para centralizar
            } else {
                modal.style.display = 'none';
            }
        }

        // Fecha se clicar fora do modal (fundo escuro)
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('box-novo-treino');
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        function togglePeriodizacao() {
            var nivel = document.getElementById("selectNivel").value;
            var aviso = document.getElementById("aviso-periodizacao");
            if (nivel === "basico") { aviso.style.display = "none"; } 
            else { aviso.style.display = "block"; }
        }
        // --- LÓGICA DO SELETOR DE ALUNOS (CRIAR TREINO) ---

        // Filtra a lista
        function filtrarAlunosTreino() {
            let input = document.getElementById("busca-aluno-treino");
            let filter = input.value.toUpperCase();
            let dropdown = document.getElementById("dropdown-alunos-treino");
            let items = dropdown.getElementsByClassName("dropdown-item");
            
            if (filter === "") {
                dropdown.style.display = "none";
                return;
            }
            
            dropdown.style.display = "block";
            let encontrou = false;

            for (let i = 0; i < items.length; i++) {
                let span = items[i].getElementsByTagName("span")[0];
                let txtValue = span.textContent || span.innerText;
                
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    items[i].style.display = ""; // Mostra
                    encontrou = true;
                } else {
                    items[i].style.display = "none"; // Esconde
                }
            }

            if (!encontrou) dropdown.style.display = "none";
        }

        // Seleciona o aluno
        function selecionarAlunoTreino(id, nome) {
            document.getElementById("busca-aluno-treino").value = nome; // Mostra nome visual
            document.getElementById("id-aluno-treino-selecionado").value = id; // Preenche ID oculto
            document.getElementById("dropdown-alunos-treino").style.display = "none"; // Fecha lista
        }

        // Fecha a lista se clicar fora (Genérico para qualquer dropdown)
        window.addEventListener('click', function(e) {
            // Dropdown Treino
            let dropTreino = document.getElementById("dropdown-alunos-treino");
            let inputTreino = document.getElementById("busca-aluno-treino");
            if (dropTreino && e.target !== inputTreino && !dropTreino.contains(e.target)) {
                dropTreino.style.display = 'none';
            }
        });


        // ---------------------------------------------------------------
        // 4. PAINEL DO TREINO (ABAS, EXERCÍCIOS E PERIODIZAÇÃO)
        // ---------------------------------------------------------------
        
        // Gerenciamento de Abas (A, B, C)
        function openTab(evt, divName) {
            var i, content, tablinks;
            content = document.getElementsByClassName("division-content");
            for (i = 0; i < content.length; i++) { content[i].className = content[i].className.replace(" active", ""); }
            tablinks = document.getElementsByClassName("div-tab-btn");
            for (i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
            document.getElementById(divName).className += " active";
            evt.currentTarget.className += " active";
        }

        // --- MODAL EXERCÍCIO (Com Lista de Séries) ---
        let seriesArray = [];

        function openExercicioModal(divId, treinoId) {
            document.getElementById("modal_divisao_id").value = divId;
            document.getElementById("modal_treino_id").value = treinoId;
            document.getElementById("modalExercicio").style.display = "flex";
            seriesArray = []; // Reseta lista
            renderSetsList();
        }

        function closeExercicioModal() {
            document.getElementById("modalExercicio").style.display = "none";
        }

        function addSetToList() {
            const qtd = document.getElementById("set_qtd").value;
            const tipo = document.getElementById("set_tipo").value;
            const reps = document.getElementById("set_reps").value;
            const desc = document.getElementById("set_desc").value;
            const rpe = document.getElementById("set_rpe").value;

            if(qtd > 0) {
                seriesArray.push({ qtd, tipo, reps, desc, rpe });
                renderSetsList();
            }
        }

        function renderSetsList() {
            const listDiv = document.getElementById("temp-sets-list");
            const jsonInput = document.getElementById("series_json_input");
            listDiv.innerHTML = "";
            
            if(seriesArray.length === 0) {
                listDiv.innerHTML = "<p style='color:#666; font-size:0.8rem; text-align:center; margin-top:10px;'>Nenhuma série adicionada.</p>";
            } else {
                seriesArray.forEach((s, index) => {
                    listDiv.innerHTML += `
                        <div class="temp-set-item">
                            <span><strong>${s.qtd}x</strong> ${s.tipo.toUpperCase()} (${s.reps} reps)</span>
                            <span style="color:#ff4242; cursor:pointer;" onclick="removeSet(${index})"><i class="fa-solid fa-times"></i></span>
                        </div>
                    `;
                });
            }
            jsonInput.value = JSON.stringify(seriesArray);
        }

        function removeSet(index) {
            seriesArray.splice(index, 1);
            renderSetsList();
        }

        // --- MODAL PERIODIZAÇÃO (Semana) ---
        function openMicroModal(micro, treinoId) {
        document.getElementById('micro_id').value = micro.id;
        document.getElementById('micro_treino_id').value = treinoId;
        
        document.getElementById('micro_fase').value = micro.nome_fase;
        document.getElementById('micro_foco').value = micro.foco_comentario;
        
        // Novos campos
        document.getElementById('micro_reps_comp').value = micro.reps_compostos;
        document.getElementById('micro_desc_comp').value = micro.descanso_compostos; 
        
        document.getElementById('micro_reps_iso').value = micro.reps_isoladores;
        document.getElementById('micro_desc_iso').value = micro.descanso_isoladores; 
        
        document.getElementById('span_semana_num').innerText = micro.semana_numero;
        document.getElementById('modalMicro').style.display = 'flex';
        }
        
        function closeMicroModal() {
            document.getElementById('modalMicro').style.display = 'none';
        }

        // Fechar qualquer modal ao clicar fora
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = "none";
            }
        }

        // FILTRO DE ALUNOS (Busca)
        function filtrarAlunos() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchAluno");
            filter = input.value.toUpperCase();
            table = document.getElementById("tabelaAlunos");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                var tdNome = tr[i].getElementsByTagName("td")[0];
                if (tdNome) {
                    txtValue = tdNome.textContent || tdNome.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }

        // EDITAR ALUNO (Preencher Modal)
        function openEditModal(aluno) {
            document.getElementById("edit_id").value = aluno.id;
            document.getElementById("edit_nome").value = aluno.nome;
            document.getElementById("edit_email").value = aluno.email;
            document.getElementById("edit_telefone").value = aluno.telefone;
            document.getElementById("edit_expiracao").value = aluno.data_expiracao || "";
            
            document.getElementById("modalEditarAluno").style.display = "flex";
        }

        function closeEditModal() {
            document.getElementById("modalEditarAluno").style.display = "none";
        }
    </script>

    <div id="modalNovaAvaliacao" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        
        <div class="modal-header-av">
            <button class="modal-close" onclick="fecharModalAvaliacao()">&times;</button>
            <h3><i class="fa-solid fa-ruler-combined"></i> NOVA AVALIAÇÃO</h3>
        </div>
        
        <form action="actions/avaliacao_add.php" method="POST" enctype="multipart/form-data">
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
        // --- LÓGICA DO MODAL DE AVALIAÇÃO ---

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

    // Preview simples das fotos selecionadas
    function previewFiles() {
        const preview = document.getElementById('preview-area');
        const fileInput = document.getElementById('foto_input');
        const files = fileInput.files;
        
        preview.innerHTML = ""; // Limpa anterior

        if (files) {
            [].forEach.call(files, function(file) {
                if (/\.(jpe?g|png|gif)$/i.test(file.name)) {
                    const reader = new FileReader();
                    reader.addEventListener("load", function() {
                        const img = document.createElement('img');
                        img.src = this.result;
                        img.className = 'thumb-preview';
                        preview.appendChild(img);
                    });
                    reader.readAsDataURL(file);
                }
            });
        }
    }
    </script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>