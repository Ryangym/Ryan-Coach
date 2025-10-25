<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/usuario.css">
    <script src="js/asidebar.js"></script>
</head>
<body>
    <aside id="main-aside">
        <button onclick="location.reload()" class="menu-item">
            Dashboard
        </button>
        <button onclick="mostrar('visualizartreinos')" class="menu-item">
            Visualizar Treinos
        </button>
        <button onclick="mostrar('horarios')" class="menu-item">
            Horários
        </button>
        <button onclick="mostrar('mensagens')" class="menu-item">
            Mensagens
        </button>
        <button onclick="mostrar('perfildoinstrutor')" class="menu-item">
            Perfil do Instrutor
        </button>
        <button onclick="mostrar('cadastrartreino')" class="menu-item">
            Cadastrar Treino
        </button>
        <button class="menu-item" onclick="window.location.href='/index.php'">Voltar ao Início</button>
        <button class="menu-item" onclick="confirmarSaida()">Encerrar Sessão</button>
        <script>
            function confirmarSaida() {
                const confirmar = confirm("Tem certeza que deseja sair?");
                if (confirmar) {
                    window.location.href = "PHP/logout.php"; // Redireciona para onde encerra a sessão
                }
            }
        </script>

    </aside>

    <div class="menu-overlay" onclick="toggleMenu()"></div>
	<section id="conteudo">

        <div class="userinfo">
			<h3>usuario</h3>
        </div>


        <p style="color: white; font-size: 2rem; margin-left: 4%; font-family: Revolution">Meu Treino:</p>


    </section>




<script>
    function mostrar(secao) {
				const conteudo = document.getElementById("conteudo");
				const buttons = document.querySelectorAll(".menu-item");

				// Remover a classe 'active' de todos os botões
				buttons.forEach((button) => button.classList.remove("active"));

				// Adicionar a classe 'active' ao botão clicado
				const activeButton = document.querySelector(
					`button[onclick="mostrar('${secao}')"]`
				);
				activeButton.classList.add("active");

				// Carregar o conteúdo correspondente
				if (secao === "plano") {
    const planoHTML = document.getElementById("secao-plano").innerHTML;
    conteudo.innerHTML = `
        <?php exibirHamburguerMenu(); ?>
        ${planoHTML}
    `;
				} else if (secao === "horarios") {
					conteudo.innerHTML = `
			<?php exibirHamburguerMenu(); ?>
            <div class="container">
				<div class="modalidade">
				<h2 class="TituloModalidades">MUSCULAÇÃO: ESCULPINDO O CORPO DE HÉRCULES</h2>
				<p class="titulohorarios">Horários:</p>
				<ul class="titulohorarios">
					<li>Segunda a Sexta: 05:00 às 23:00</li>
					<li>Sábado: 08:00 às 14:00</li>
					<li>Domingo: Fechado</li>
				</ul>
				</div>

				<div class="modalidade">
				<h2 class="TituloModalidades">TREINAMENTO FUNCIONAL: TREINAMENTO ESPARTANO</h2>
				<p class="titulohorarios">Horários:</p>
				<ul class="titulohorarios">
					<li>Segunda a Sexta: 05:00 às 23:00</li>
					<li>Sábado: 08:00 às 14:00</li>
					<li>Domingo: Fechado</li>
				</ul>
				</div>

				<div class="modalidade">
				<h2 class="TituloModalidades">BOXE E CONDICIONAMENTO DE GOLPES: BOXE ARES</h2>
				<p class="titulohorarios" >Horários:</p>
				<ul class="titulohorarios">
					<li>Segunda a Sexta: 19:00 às 21:00</li>
					<li>Sábado: 11:00 às 14:00</li>
					<li>Domingo: Fechado</li>
				</ul>
				</div>

				<div class="modalidade">
				<h2 class="TituloModalidades">YOGA E RECUPERAÇÃO MUSCULAR: YOGA APOLÍNEA</h2>
				<p class="titulohorarios">Horários:</p>
				<ul class="titulohorarios">
					<li>Segunda a Sexta: 08:00 às 10:00</li>
					<li>Sábado: 09:00 às 11:00</li>
					<li>Domingo: Fechado</li>
				</ul>
				</div>

			</div>
        `;

                }}
</script>
</body>
</html>