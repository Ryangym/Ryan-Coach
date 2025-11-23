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
        async function carregarConteudo(pagina) {
            const areaConteudo = document.getElementById('conteudo');

            // Lógica de Logout
            if (pagina === 'logout') {
                window.location.href = 'actions/logout.php';
                return;
            }

            // Feedback visual de carregamento
            areaConteudo.innerHTML = '<div class="loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>';
            areaConteudo.classList.add('loading');

            try {
                const response = await fetch(`ajax/get_admin_conteudo.php?pagina=${pagina}`);

                if (!response.ok) throw new Error('Erro na requisição');

                const html = await response.text();

                areaConteudo.innerHTML = html;
                areaConteudo.classList.remove('loading');

                // Atualiza classe active na Sidebar
                // Truque: Se a página for "treino_painel&id=1", pegamos só "treino_painel"
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
                areaConteudo.innerHTML = '<p class="error">Erro ao carregar painel administrativo.</p>';
            }
        }

        // 2. INICIALIZAÇÃO INTELIGENTE
        document.addEventListener('DOMContentLoaded', () => {
            const aside = document.getElementById('main-aside');

            // Verifica se tem parâmetros na URL (ex: voltou do salvamento)
            const params = new URLSearchParams(window.location.search);
            const pageParam = params.get('page'); // ex: 'treino_painel'
            const idParam = params.get('id');     // ex: '15'

            let paginaInicial = 'dashboard'; // Padrão

            // Se tiver parametro, usa ele
            if (pageParam) {
                paginaInicial = pageParam;
                if (idParam) {
                    paginaInicial += '&id=' + idParam;
                }
                
                // (Opcional) Limpa a URL para não ficar "suja" visualmente
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Adiciona o clique nos botões da barra lateral
            aside.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (btn && btn.dataset.pagina) {
                    carregarConteudo(btn.dataset.pagina);
                }
            });

            // Carrega a página decidida (Dashboard ou Treino Específico)
            carregarConteudo(paginaInicial);
        });

        // 3. FUNÇÃO DE PREVIEW DE FOTO (Global)
        function previewImageAdmin(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var imgPreview = document.getElementById('admin-preview');
                    if (imgPreview) {
                        imgPreview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script>

                function openModal() {
                    document.getElementById("modalLancamento").style.display = "flex";
                }
                function closeModal() {
                    document.getElementById("modalLancamento").style.display = "none";
                }
                // Fecha ao clicar fora
                window.onclick = function(event) {
                    var modal = document.getElementById("modalLancamento");
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
           
    </script>
    <script>
                // Filtro de Busca na Tabela
                function filtrarAlunos() {
                    var input, filter, table, tr, td, i, txtValue;
                    input = document.getElementById("searchAluno");
                    filter = input.value.toUpperCase();
                    table = document.getElementById("tabelaAlunos");
                    tr = table.getElementsByTagName("tr");

                    for (i = 1; i < tr.length; i++) { // Começa do 1 para pular o cabeçalho
                        // Verifica Nome (index 0) e Email (dentro da div)
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

                // Modal de Edição
                function openEditModal(aluno) {
                    document.getElementById("edit_id").value = aluno.id;
                    document.getElementById("edit_nome").value = aluno.nome;
                    document.getElementById("edit_email").value = aluno.email;
                    document.getElementById("edit_telefone").value = aluno.telefone;
                    // Carrega a data de expiração
                    document.getElementById("edit_expiracao").value = aluno.data_expiracao;
                    
                    document.getElementById("modalEditarAluno").style.display = "flex";
                }

                function closeEditModal() {
                    document.getElementById("modalEditarAluno").style.display = "none";
                }
                
                // Fecha ao clicar fora
                window.onclick = function(event) {
                    var modal = document.getElementById("modalEditarAluno");
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>

            <script>
                function toggleNovoTreino() {
                    var box = document.getElementById("box-novo-treino");
                    if(box.style.display === "none") {
                        box.style.display = "block";
                        box.scrollIntoView({behavior: "smooth"});
                    } else {
                        box.style.display = "none";
                    }
                }
                
                function togglePeriodizacao() {
                    var nivel = document.getElementById("selectNivel").value;
                    var aviso = document.getElementById("aviso-periodizacao");
                    if (nivel === "basico") { aviso.style.display = "none"; } 
                    else { aviso.style.display = "block"; }
                }
            </script>
    

    <script>
                // Gerenciamento de Abas
                function openTab(evt, divName) {
                    var i, content, tablinks;
                    content = document.getElementsByClassName("division-content");
                    for (i = 0; i < content.length; i++) { content[i].className = content[i].className.replace(" active", ""); }
                    tablinks = document.getElementsByClassName("div-tab-btn");
                    for (i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
                    document.getElementById(divName).className += " active";
                    evt.currentTarget.className += " active";
                }

                // Modal Logic
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

                // Adicionar Série ao Array Temporário
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

                // Renderizar lista visual e atualizar input hidden
                function renderSetsList() {
                    const listDiv = document.getElementById("temp-sets-list");
                    const jsonInput = document.getElementById("series_json_input");
                    
                    listDiv.innerHTML = "";
                    
                    if(seriesArray.length === 0) {
                        listDiv.innerHTML = "<p style=\'color:#666; font-size:0.8rem; text-align:center; margin-top:10px;\'>Nenhuma série adicionada.</p>";
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
                    
                    // Salva no input hidden para enviar pro PHP
                    jsonInput.value = JSON.stringify(seriesArray);
                }

                function removeSet(index) {
                    seriesArray.splice(index, 1);
                    renderSetsList();
                }
            </script>

</body>
</html>