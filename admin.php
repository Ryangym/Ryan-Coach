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
        document.addEventListener('DOMContentLoaded', () => {
            const BOTAO_PADRAO = 'dashboard';
            const aside = document.getElementById('main-aside');
            const areaConteudo = document.getElementById('conteudo');
            const botoes = aside.querySelectorAll('button[data-pagina]');

            async function carregarConteudo(pagina) {
                if(pagina === 'logout') {
                    // Agora redireciona para o script PHP que destrói a sessão
                    window.location.href = 'actions/logout.php'; 
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


        // --- FUNÇÃO DE PREVIEW DO ADMIN ---
        function previewImageAdmin(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    // O ID da imagem no admin é 'admin-preview'
                    var imgPreview = document.getElementById('admin-preview');
                    if(imgPreview) {
                        imgPreview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

   
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

</body>
</html>