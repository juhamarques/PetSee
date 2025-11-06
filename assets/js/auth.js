document.addEventListener('DOMContentLoaded', () => {
    async function checkLoginStatus() {
        try {
            const response = await fetch('api/check_auth.php');
            const data = await response.json();

            const loginButton = document.getElementById('login-button');

            if (loginButton) {
                if (data.isLoggedIn) {
                    loginButton.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    `;
                    loginButton.href = "#";
                    loginButton.classList.add('account-icon');
                    loginButton.classList.remove('btn-entrar');

                    const headerContainer = loginButton.closest('.header-container') || document.body;
                    if (!document.getElementById('profile-menu')) {
                        const menu = document.createElement('div');
                        menu.id = 'profile-menu';
                        menu.className = 'profile-menu';
                        menu.innerHTML = `
                            <a class="profile-menu-item" href="perfil.php">Minha Conta</a>
                            <a class="profile-menu-item" href="api/logout.php">Sair</a>
                        `;
                        headerContainer.appendChild(menu);
                    }

                    loginButton.addEventListener('click', function (ev) {
                        ev.preventDefault();
                        const menu = document.getElementById('profile-menu');
                        if (!menu) return;
                        menu.classList.toggle('open');
                    });

                    document.addEventListener('click', function (ev) {
                        const menu = document.getElementById('profile-menu');
                        if (!menu) return;
                        const target = ev.target;
                        if (!menu.contains(target) && target !== loginButton && !loginButton.contains(target)) {
                            menu.classList.remove('open');
                        }
                    });

                } else {
                    loginButton.innerHTML = "Entrar";
                    loginButton.href = "entrar.html";
                    loginButton.classList.add('btn-entrar');
                    loginButton.classList.remove('account-icon');
                    const existingMenu = document.getElementById('profile-menu');
                    if (existingMenu && existingMenu.parentNode) existingMenu.parentNode.removeChild(existingMenu);
                }
            }
        } catch (error) {
            console.error('Erro ao verificar o status de autenticação:', error);
        }
    }
    checkLoginStatus();
});