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

</body>
</html>