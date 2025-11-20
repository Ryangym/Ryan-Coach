<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/usuario.css">

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário - Ryan Coach</title>
    
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/usuario.css">

    <link rel="icon" type="image/png" href="img/icones/favicon3.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                <img src="img/ryan_coach_atualizado.png" alt="Foto de perfil" class="foto-perfil">
                <div class="status-indicator"></div>
            </div>
            <p class="usuario-nome">Ryan Trainer</p>
            <p class="usuario-level">Pro Member</p>
        </div>
        
        <nav class="nav-buttons">
            <button data-pagina="dashboard" class="active">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </button>
            
            <button data-pagina="treinos">
                <i class="fa-solid fa-dumbbell"></i>
                <span>Meus Treinos</span>
            </button>
            
            <button data-pagina="nutrition"> <i class="fa-solid fa-utensils"></i>
                <span>Dieta & Nutrição</span>
            </button>
            
            <button data-pagina="avaliacoes">
                <i class="fa-solid fa-file-medical"></i>
                <span>Avaliações</span>
            </button>
            
            <button data-pagina="perfil">
                <i class="fa-solid fa-user-gear"></i>
                <span>Configurações</span>
            </button>
        </nav>

        <div class="aside-footer">
            <button data-pagina="logout" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair</span>
            </button>
        </div>

    </aside>

    <main id="conteudo">
        </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const BOTAO_PADRAO = 'dashboard'; // Mudei o padrão para testar
            const aside = document.getElementById('main-aside');
            const areaConteudo = document.getElementById('conteudo');
            const botoes = aside.querySelectorAll('button[data-pagina]'); // Seleciona apenas botões de navegação

            // Função assíncrona para buscar o conteúdo
            async function carregarConteudo(pagina) {
                if(pagina === 'logout') {
                    window.location.href = 'index.html'; // Exemplo de logout
                    return;
                }

                // 1. Mostrar feedback de "Carregando..."
                areaConteudo.innerHTML = '<p class="loading">Carregando...</p>';
                areaConteudo.classList.add('loading');

                try {
                    // 2. Fazer a requisição
                    const response = await fetch(`get_conteudo.php?pagina=${pagina}`);
                    
                    if (!response.ok) {
                        throw new Error('Erro ao buscar conteúdo.');
                    }
                    
                    const html = await response.text();
                    
                    // 3. Injetar o HTML
                    areaConteudo.innerHTML = html;
                    areaConteudo.classList.remove('loading');

                    // 4. Atualizar o botão ativo
                    botoes.forEach(btn => {
                        if(btn.dataset.pagina === pagina) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                    });

                } catch (error) {
                    console.error('Falha na requisição:', error);
                    areaConteudo.innerHTML = '<p class="error">Não foi possível carregar o conteúdo.</p>';
                    areaConteudo.classList.remove('loading');
                }
            }

            // Adicionar o "ouvinte" de cliques na 'aside'
            aside.addEventListener('click', (e) => {
                // O truque aqui: e.target pode ser o ícone, então usamos .closest('button')
                const btn = e.target.closest('button');
                
                if (btn && btn.dataset.pagina) {
                    const pagina = btn.dataset.pagina;
                    carregarConteudo(pagina);
                }
            });

            // Carregar o conteúdo padrão ao entrar na página
            carregarConteudo(BOTAO_PADRAO);
        });
    </script>

</body>
</html>