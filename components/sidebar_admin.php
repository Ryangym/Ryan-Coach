<header class="mobile-top-bar">
        <div class="mobile-logo">Ryan Coach</div>
        <div class="mobile-user-actions">
            <img src="img/ryan_coach_atualizado.png" alt="Perfil" class="mobile-profile-pic">
            <button onclick="window.location.href='index.html'" class="mobile-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </div>
    </header>

    <aside id="main-aside">
        
        <div class="aside-header">
            <h2 class="logo">Ryan Coach</h2>
            <div class="profile-container">
                <img src="img/ryan_coach_atualizado.png" alt="Admin Profile" class="foto-perfil" style="border-color: #ff4242;"> 
                <div class="status-indicator" style="background-color: #ff4242;"></div>
            </div>
            <p class="usuario-nome">Ryan Admin</p>
            <p class="usuario-level" style="color: #ff4242; background: rgba(255, 66, 66, 0.1);">Master Coach</p>
        </div>
        
        <nav class="nav-buttons">
            <button data-pagina="dashboard" class="active">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Visão Geral</span>
            </button>
            
            <button data-pagina="alunos">
                <i class="fa-solid fa-users"></i>
                <span>Gerenciar Alunos</span>
            </button>
            
            <button data-pagina="treinos_editor">
                <i class="fa-solid fa-dumbbell"></i>
                <span>Editor de Treinos</span>
            </button>
            
            <button data-pagina="financeiro">
                <i class="fa-solid fa-sack-dollar"></i>
                <span>Financeiro</span>
            </button>
            
            <button data-pagina="config">
                <i class="fa-solid fa-gear"></i>
                <span>Sistema</span>
            </button>
        </nav>

        <div class="aside-footer">
            <button data-pagina="logout" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair do Admin</span>
            </button>
        </div>

    </aside>