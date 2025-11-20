<!-- Barra de navegação mobile -->
    <nav class="mobile-navbar">
        <button class="menu-toggle" id="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <img class="logo-mobilenav" src="assets/img/icones/icon-nav.png" alt="">

        <nav class="mobile-nav" id="mobile-nav">
            <button class="btnLogin-popup-mobile">
                <img src="assets/img/login.png" alt="">
                <p>Login</p>
            </button>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="index.php#modalidades">Modalidades</a></li>
                <li><a href="index.php#ft-footer">Contato</a></li>
                <li><a href="planos.php">Planos</a></li>
            </ul>
            <div class="redes-mobilenavbar">
                <a href="https://www.instagram.com/ct.olympo?igsh=b3NiaDBtcXF5bDlp&utm_source=qr">
                    <img src="assets/IMG/icones/insta.png" alt="">
                </a>
                <a href="https://www.facebook.com/">
                    <img src="assets/IMG/icones/facebook.png" alt="">
                </a>
                <a href="https://youtube.com/@ctolympo?si=6mtc_D-h9szc3hAP">
                    <img src="assets/IMG/icones/youtube.png" alt="">
                </a>
            </div>
        </nav>
    </nav>



    <!-- Barra de navegação desktop -->
    <nav class="desktop-navbar">
            <div class="glass-morphism">
                <a href="index.php" class="logo">
                    <h2 class="logo">Ryan Coach</h2>
                </a>
                <div class="links-content">
                    <a class="navlinks" href="admin.php">Admin</a>
                    <a class="navlinks" href="usuario.php">Usuario</a>
                    <a class="navlinks" href="index.php#contato">Contato</a>
                </div>

                <img src="assets/img/login.png" alt="Login" class="login-nav" id="userMenuToggle">
                <div id="profileMenu" class="profile-dropdown-menu">
                    <div class="profile-card">
                        <ul class="profile-list">
                            <li class="profile-element">
                                <a href="login.php" class="profile-link">
                                    <svg xmlns=" http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-user-icon lucide-user">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <p class="profile-label">Entrar/Cadastro</p>
                                </a>
                            </li>
                            <li class="profile-element">
                                <a href="loginAdmin.php" class="profile-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-user-lock-icon lucide-user-lock">
                                        <circle cx="10" cy="7" r="4" />
                                        <path d="M10.3 15H7a4 4 0 0 0-4 4v2" />
                                        <path d="M15 15.5V14a2 2 0 0 1 4 0v1.5" />
                                        <rect width="8" height="5" x="13" y="16" rx=".899" />
                                    </svg>
                                    <p class="profile-label">Admin Login</p>
                                </a>
                            </li>
                        </ul>

                    </div>
                </div>
        </nav>