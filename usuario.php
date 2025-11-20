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
        document.addEventListener('DOMContentLoaded', () => {
            const BOTAO_PADRAO = 'dashboard'; // Mudei o padrão para testar
            const aside = document.getElementById('main-aside');
            const areaConteudo = document.getElementById('conteudo');
            const botoes = aside.querySelectorAll('button[data-pagina]'); // Seleciona apenas botões de navegação

            // Função assíncrona para buscar o conteúdo
            async function carregarConteudo(pagina) {
                if(pagina === 'logout') {
                    window.location.href = 'index.php'; // Exemplo de logout
                    return;
                }

                // 1. Mostrar feedback de "Carregando..."
                areaConteudo.innerHTML = '<p class="loading">Carregando...</p>';
                areaConteudo.classList.add('loading');

                try {
                    // 2. Fazer a requisição
                    const response = await fetch(`ajax/get_conteudo.php?pagina=${pagina}`);
                    
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