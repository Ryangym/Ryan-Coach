<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Ryan Coach</title>
    
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/usuario.css"> 
    <link rel="icon" type="image/png" href="img/icones/favicon3.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&family=Orbitron:wght@700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <div class="background-overlay"></div>

    <header class="mobile-top-bar">
        <div class="mobile-logo">Ryan Coach</div>
        <div class="mobile-user-actions">
            <img src="img/ryan_coach_atualizado.png" alt="Perfil" class="mobile-profile-pic">
            <button onclick="window.location.href='index.html'" class="mobile-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </header>

    <aside id="main-aside">
        
        <div class="aside-header">
            <h2 class="logo">Ryan Coach</h2>
            <div class="profile-container">
                <img src="img/ryan_coach_atualizado.png" alt="Admin Profile" class="foto-perfil" style="border-color: #ff4242;"> 
                <div class="status-indicator" style="background-color: #ff4242;"></div>
            </div>
            <p class="usuario-nome">Ryan Admin</p>
            <p class="usuario-level" style="color: #ff4242; background: rgba(255, 66, 66, 0.1);">Master Coach</p>
        </div>
        
        <nav class="nav-buttons">
            <button data-pagina="dashboard" class="active">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Visão Geral</span>
            </button>
            
            <button data-pagina="alunos">
                <i class="fa-solid fa-users"></i>
                <span>Gerenciar Alunos</span>
            </button>
            
            <button data-pagina="treinos_editor">
                <i class="fa-solid fa-dumbbell"></i>
                <span>Editor de Treinos</span>
            </button>
            
            <button data-pagina="financeiro">
                <i class="fa-solid fa-sack-dollar"></i>
                <span>Financeiro</span>
            </button>
            
            <button data-pagina="config">
                <i class="fa-solid fa-gear"></i>
                <span>Sistema</span>
            </button>
        </nav>

        <div class="aside-footer">
            <button data-pagina="logout" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair do Admin</span>
            </button>
        </div>

    </aside>

    <main id="conteudo">
        </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const BOTAO_PADRAO = 'dashboard';
            const aside = document.getElementById('main-aside');
            const areaConteudo = document.getElementById('conteudo');
            const botoes = aside.querySelectorAll('button[data-pagina]');

            async function carregarConteudo(pagina) {
                if(pagina === 'logout') {
                    window.location.href = 'index.html';
                    return;
                }

                // Feedback visual mantendo o estilo
                areaConteudo.innerHTML = '<div class="loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>';
                areaConteudo.classList.add('loading');

                try {
                    // Aponta para o novo arquivo PHP de admin
                    const response = await fetch(`get_admin_conteudo.php?pagina=${pagina}`);
                    
                    if (!response.ok) throw new Error('Erro na requisição');
                    
                    const html = await response.text();
                    
                    areaConteudo.innerHTML = html;
                    areaConteudo.classList.remove('loading');

                    // Atualiza classe active
                    botoes.forEach(btn => {
                        btn.classList.toggle('active', btn.dataset.pagina === pagina);
                    });

                } catch (error) {
                    console.error(error);
                    areaConteudo.innerHTML = '<p class="error">Erro ao carregar painel administrativo.</p>';
                }
            }

            aside.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (btn && btn.dataset.pagina) {
                    carregarConteudo(btn.dataset.pagina);
                }
            });

            carregarConteudo(BOTAO_PADRAO);
        });
    </script>

</body>
</html>