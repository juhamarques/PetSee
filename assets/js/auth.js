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
                    loginButton.href = "perfil.php";
                    loginButton.classList.add('account-icon');
                    loginButton.classList.remove('btn-entrar');
                } else {
                    loginButton.innerHTML = "Entrar";
                    loginButton.href = "entrar.html";
                    loginButton.classList.add('btn-entrar');
                    loginButton.classList.remove('account-icon');
                }
            }
        } catch (error) {
            console.error('Erro ao verificar o status de autenticação:', error);
        }
    }
    checkLoginStatus(); 
});