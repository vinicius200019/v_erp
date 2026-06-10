/**
 * js/auth.js - Proteção de rotas (admin vs cliente)
 */

document.addEventListener("DOMContentLoaded", () => {
    const admin = localStorage.getItem("v_erp_admin");
    const cliente = localStorage.getItem("v_erp_cliente");
    const path = window.location.pathname;

    // Páginas da área do cliente
   const paginasCliente = ["loja.html", "carrinho.html", "minhas-compras.html"];
    const ehAreaCliente = paginasCliente.some(p => path.includes(p));
    const ehLogin = path.includes("login.html");

    // Se está na área do cliente: precisa ser cliente
    if (ehAreaCliente) {
        if (!cliente) {
            window.location.href = "/v_erp/pages/login.html";
            return;
        }
        renderizarBotaoLogout('cliente');
        return;
    }

    // Se NÃO está em login nem na área do cliente → área admin → precisa ser admin
    if (!ehLogin) {
        if (!admin) {
            // Se for cliente tentando acessar admin, manda pra loja
            if (cliente) {
                window.location.href = "/v_erp/pages/loja.html";
            } else {
                window.location.href = "/v_erp/pages/login.html";
            }
            return;
        }
        renderizarBotaoLogout('admin');
    }
});

function renderizarBotaoLogout(tipo) {
    const navUl = document.querySelector("nav ul");
    if (!navUl) return;
    const li = document.createElement("li");
    li.innerHTML = `<a href="#" onclick="logout('${tipo}')" class="btn-logout" style="color: #ef4444;">Sair</a>`;
    navUl.appendChild(li);
}

function logout(tipo) {
    if (tipo === 'admin') {
        localStorage.removeItem("v_erp_admin");
    } else {
        localStorage.removeItem("v_erp_cliente");
    }
    window.location.href = "/v_erp/pages/login.html";
}