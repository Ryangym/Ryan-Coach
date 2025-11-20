<header class="mobile-top-bar">
        <div class="mobile-logo">Ryan Coach</div>
        <div class="mobile-user-actions">
            <img src="assets/img/ryan_coach_atualizado.png" alt="Perfil" class="mobile-profile-pic">
            <button onclick="window.location.href='index.php'" class="mobile-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </header>

    <aside id="main-aside">
        
        <div class="aside-header">
            <h2 class="logo">Ryan Coach</h2>
            <div class="profile-container">
                <img src="assets/img/ryan_coach_atualizado.png" alt="Foto de perfil" class="foto-perfil">
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