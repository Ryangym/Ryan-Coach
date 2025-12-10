<?php
$foto_admin = $_SESSION['user_foto'] ?? 'assets/img/user-default.png';
$nome_admin = $_SESSION['user_nome'] ?? 'Admin';
$partes_admin = explode(' ', trim($nome_admin));
$primeiro_nome_admin = strtoupper($partes_admin[0]);
?>
<header class="mobile-top-bar">
        <div class="mobile-logo">Ryan Coach</div>
        <div class="mobile-user-actions">
            <img src="<?php echo $foto_admin; ?>" alt="Perfil" class="mobile-profile-pic">
            <button onclick="window.location.href='index.php'" class="mobile-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </header>

    <aside id="main-aside">
    
    <div class="aside-header">
        <h1 class="logo">Ryan Coach</h1>
        
        <div class="profile-container">
            <img src="<?php echo !empty($_SESSION['user_foto']) ? $_SESSION['user_foto'] : 'assets/img/user-default.png'; ?>" class="foto-perfil">
            <div class="status-indicator"></div>
        </div>
        
        <h3 class="usuario-nome"><?php echo explode(' ', $_SESSION['user_nome'])[0]; ?></h3>
        <span class="usuario-level">ADMIN</span>
    </div>

    <nav class="nav-buttons">
        <button data-pagina="dashboard" class="active" onclick="carregarConteudo('dashboard')">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Visão Geral</span>
        </button>

        <button data-pagina="alunos" onclick="carregarConteudo('alunos')">
            <i class="fa-solid fa-users"></i>
            <span>Alunos</span>
        </button>

        <button data-pagina="treinos_editor" onclick="carregarConteudo('treinos_editor')">
            <i class="fa-solid fa-dumbbell"></i>
            <span>Treinos</span>
        </button>

        <button data-pagina="financeiro" onclick="carregarConteudo('financeiro')">
            <i class="fa-solid fa-sack-dollar"></i>
            <span>Financeiro</span>
        </button>

        <button data-pagina="perfil" onclick="carregarConteudo('perfil')" class="desktop-only">
            <i class="fa-solid fa-gear"></i>
            <span>Configurações</span>
        </button>

        <button onclick="carregarConteudo('admin_menu')" class="mobile-only">
            <i class="fa-solid fa-bars"></i>
            <span>Menu</span>
        </button>
    </nav>

    <div class="aside-footer">
        <button class="btn-logout" onclick="window.location.href='index.php'">
            <i class="fa-solid fa-globe"></i>
            <span>Ver Site</span>
        </button>
        <button data-pagina="logout" class="btn-logout" onclick="window.location.href='actions/logout.php'">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Sair</span>
        </button>
    </div>

</aside>

<style>
    .mobile-only { display: none !important; }
    @media (max-width: 768px) {
        .desktop-only { display: none !important; }
        .mobile-only { display: flex !important; }
    }
</style>