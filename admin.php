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
                    const response = await fetch(`ajax/get_admin_conteudo.php?pagina=${pagina}`);
                    
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