/**
 * js/auth.js - Gerencia login e proteção de rotas
 */

// Ao carregar qualquer página, verifica se o usuário existe no localStorage
document.addEventListener("DOMContentLoaded", () => {
    const usuarioLogado = localStorage.getItem("v_erp_user");

    // Se não estiver logado e não estiver na página de login, manda para o login
    if (!usuarioLogado && !window.location.pathname.includes("login.html")) {
        window.location.href = "/v_erp/pages/login.html";
    }

    // Se já estiver logado e tentar acessar o login, manda para o início
    if (usuarioLogado && window.location.pathname.includes("login.html")) {
        window.location.href = "/v_erp/index.html";
    }

    renderizarBotaoAuth();
});

// Função para criar o botão de Sair dinamicamente no menu
function renderizarBotaoAuth() {
    const navUl = document.querySelector("nav ul");
    const usuarioLogado = localStorage.getItem("v_erp_user");

    if (usuarioLogado && navUl) {
        const li = document.createElement("li");
        li.innerHTML = `<a href="#" onclick="logout()" class="btn-logout" style="color: #ef4444;">Sair</a>`;
        navUl.appendChild(li);
    }
}

// Função para deslogar
function logout() {
    localStorage.removeItem("v_erp_user");
    window.location.href = "/v_erp/pages/login.html";
}
