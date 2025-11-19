<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/usuario.css">

    <link rel="icon" type="image/png" href="img/icones/favicon3.png">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Copse&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Story+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cherry+Cream+Soda&display=swap" rel="stylesheet">
</head>
<body>
    
    <aside id="main-aside">
        
        <h2 class="logo">Ryan Coach</h2>
        <img src="img/ryan_coach_atualizado.png" alt="Foto de perfil do usuário" class="foto-perfil">
        <p class="usuario-nome">Usuário</p>
        
        <button data-pagina="dashboard">Dashboard</button>
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