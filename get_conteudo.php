<?php
// Define qual página foi pedida na URL (ex: get_conteudo.php?pagina=perfil)
$pagina = $_GET['pagina'] ?? 'treinos';

// Simples sistema de roteamento para devolver o HTML correto
// IMPORTANTE: Não coloque <html>, <head> ou <body> aqui.
// Apenas o HTML que deve ir dentro da <main id="conteudo">.

switch ($pagina) {
    case 'treinos':
        echo '
            <section id="treinos">
                <h1>Meus Treinos</h1>
                <p>Aqui você verá sua planilha de treinos, vídeos, etc.</p>
                </section>
        ';
        break;

    case 'perfil':
        echo '
            <section id="perfil">
                <h1>Meu Perfil</h1>
                <p>Informações pessoais, foto, objetivos, etc.</p>
                <input type="text" placeholder="Seu nome">
            </section>
        ';
        break;

    case 'avaliacoes':
        echo '
            <section id="avaliacoes">
                <h1>Avaliações Físicas</h1>
                <p>Histórico de avaliações, medidas, progresso, etc.</p>
            </section>
        ';
        break;

    case 'pagamentos':
        echo '
            <section id="pagamentos">
                <h1>Pagamentos</h1>
                <p>Status da sua assinatura, histórico de pagamentos, etc.</p>
            </section>
        ';
        break;

    default:
        // Caso a página pedida não exista
        echo '
            <section id="erro">
                <h1>Página não encontrada</h1>
                <p>O conteúdo solicitado não foi encontrado.</p>
            </section>
        ';
        break;
}

?>