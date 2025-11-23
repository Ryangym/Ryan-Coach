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
        document.addEventListener('DOMContentLoaded', () => {
            const BOTAO_PADRAO = 'dashboard'; 
            const aside = document.getElementById('main-aside');
            const areaConteudo = document.getElementById('conteudo');
            const botoes = aside.querySelectorAll('button[data-pagina]'); 

            async function carregarConteudo(pagina) {
                if(pagina === 'logout') {
                    window.location.href = 'actions/logout.php';
                    return;
                }

                areaConteudo.innerHTML = '<p class="loading">Carregando...</p>';
                areaConteudo.classList.add('loading');

                try {
                    const response = await fetch(`ajax/get_conteudo.php?pagina=${pagina}`);
                    
                    if (!response.ok) {
                        throw new Error('Erro ao buscar conteúdo.');
                    }
                    
                    const html = await response.text();
                    
                    areaConteudo.innerHTML = html; // Aqui o HTML entra, mas o script antigo morria
                    areaConteudo.classList.remove('loading');

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

            aside.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (btn && btn.dataset.pagina) {
                    carregarConteudo(btn.dataset.pagina);
                }
            });

            carregarConteudo(BOTAO_PADRAO);
        });

        // --- FUNÇÃO DE PREVIEW (Adicione ISTO aqui fora do DOMContentLoaded) ---
        function previewImage(input) {
            // Verifica se tem arquivo selecionado
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                // Quando terminar de ler a imagem
                reader.onload = function(e) {
                    // Encontra a imagem no DOM (pelo ID que definimos no get_conteudo)
                    var imgPreview = document.getElementById('preview-img');
                    if(imgPreview) {
                        imgPreview.src = e.target.result; // Troca o src pela nova imagem
                    }
                }
                
                // Lê o arquivo como uma URL de dados
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>