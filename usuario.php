<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/usuario.css">
</head>
<body>
    
    <nav class="desktop-navbar">
        <div class="glass-morphism">
            <a href="index.html" class="logo">
                <h2>Ryan Coach</h2>
            </a>
            <div class="links-content">
                <a href="index.html#servicos">Serviços</a>
                <a href="usuario.php">Usuario</a>
                <a href="index.html#contato">Contato</a>
            </div>
            <img src="img/login.png" alt="" class="login-nav">
        </div>
    </nav>

    <aside id="main-aside">
        <img src="img/ryan_coach_atualizado.png" alt="Ryan Coach" style="width: 80%; margin: 15px auto; border-radius: 50%;">
        
        <button data-pagina="treinos">Meus Treinos</button>
        <button data-pagina="perfil">Meu Perfil</button>
        <button data-pagina="avaliacoes">Avaliações</button>
        <button data-pagina="pagamentos">Pagamentos</button>
    </aside>

    <main id="conteudo">
        </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const BOTAO_PADRAO = 'treinos';
            const aside = document.getElementById('main-aside');
            const areaConteudo = document.getElementById('conteudo');
            const botoes = aside.querySelectorAll('button');

            // Função assíncrona para buscar o conteúdo
            async function carregarConteudo(pagina) {
                // 1. Mostrar feedback de "Carregando..."
                areaConteudo.innerHTML = '<p class="loading">Carregando...</p>';
                areaConteudo.classList.add('loading');

                try {
                    // 2. Fazer a requisição para o novo arquivo PHP
                    const response = await fetch(`get_conteudo.php?pagina=${pagina}`);
                    
                    if (!response.ok) {
                        throw new Error('Erro ao buscar conteúdo.');
                    }
                    
                    const html = await response.text();
                    
                    // 3. Injetar o HTML recebido na área de conteúdo
                    areaConteudo.innerHTML = html;
                    areaConteudo.classList.remove('loading');

                    // 4. Atualizar o botão ativo
                    botoes.forEach(btn => {
                        btn.classList.toggle('active', btn.dataset.pagina === pagina);
                    });

                } catch (error) {
                    console.error('Falha na requisição:', error);
                    areaConteudo.innerHTML = '<p class="error">Não foi possível carregar o conteúdo.</p>';
                    areaConteudo.classList.remove('loading');
                }
            }

            // Adicionar o "ouvinte" de cliques na 'aside'
            aside.addEventListener('click', (e) => {
                // Verificar se o clique foi em um botão com 'data-pagina'
                if (e.target.tagName === 'BUTTON' && e.target.dataset.pagina) {
                    const pagina = e.target.dataset.pagina;
                    carregarConteudo(pagina);
                }
            });

            // Carregar o conteúdo padrão ("treinos") ao entrar na página
            carregarConteudo(BOTAO_PADRAO);
        });
    </script>

</body>
</html>