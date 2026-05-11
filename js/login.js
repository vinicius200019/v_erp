/**
 * js/login.js - Login + Cadastro de cliente
 */

// Se já estiver logado, redireciona pra área correta
document.addEventListener("DOMContentLoaded", () => {
    const admin = localStorage.getItem("v_erp_admin");
    const cliente = localStorage.getItem("v_erp_cliente");
    if (admin) window.location.href = "../index.html";
    else if (cliente) window.location.href = "loja.html";
});

function trocarAuthTab(tab) {
    document.getElementById('tab-entrar').classList.toggle('active', tab === 'entrar');
    document.getElementById('tab-cadastrar').classList.toggle('active', tab === 'cadastrar');
    document.getElementById('form-entrar').classList.toggle('active', tab === 'entrar');
    document.getElementById('form-cadastrar').classList.toggle('active', tab === 'cadastrar');
}

async function handleLogin() {
    const email = document.getElementById('login-email').value.trim();
    const senha = document.getElementById('login-senha').value;

    if (!email || !senha) {
        mostrarToast("Preencha todos os campos");
        return;
    }

    try {
        const resp = await fetch('../php/api/api_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha })
        });
        const result = await resp.json();

        if (result.success) {
            // Decide pra onde mandar baseado no perfil
            if (result.user.perfil === 'admin') {
                localStorage.setItem("v_erp_admin", JSON.stringify(result.user));
                window.location.href = "../index.html";
            } else if (result.user.perfil === 'usuario') {
                // "usuario" = cliente da loja
                localStorage.setItem("v_erp_cliente", JSON.stringify(result.user));
                window.location.href = "loja.html";
            } else {
                mostrarToast("Perfil de usuário desconhecido");
            }
        } else {
            mostrarToast(result.message || "E-mail ou senha incorretos");
        }
    } catch (error) {
        console.error(error);
        mostrarToast("Falha na comunicação com o servidor");
    }
}

async function handleCadastro() {
    const email = document.getElementById('cad-email').value.trim();
    const cpf = document.getElementById('cad-cpf').value.replace(/\D/g, ''); // só números
    const senha = document.getElementById('cad-senha').value;
    const senha2 = document.getElementById('cad-senha2').value;

    if (!email || !cpf || !senha || !senha2) {
        mostrarToast("Preencha todos os campos");
        return;
    }
    if (cpf.length !== 11 && cpf.length !== 14) {
        mostrarToast("CPF deve ter 11 dígitos ou CNPJ 14 dígitos");
        return;
    }
    if (senha !== senha2) {
        mostrarToast("Senhas diferentes");
        return;
    }

    try {
        const resp = await fetch('../php/api/api_cadastrar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, cpf_cnpj: cpf, senha })
        });
        const result = await resp.json();

        if (result.success) {
            mostrarToast("Conta criada! Faça login para entrar.");
            // Volta pra aba de login e preenche o email
            trocarAuthTab('entrar');
            document.getElementById('login-email').value = email;
            // Limpa o form de cadastro
            document.getElementById('cad-email').value = '';
            document.getElementById('cad-cpf').value = '';
            document.getElementById('cad-senha').value = '';
            document.getElementById('cad-senha2').value = '';
        } else {
            mostrarToast(result.message || "Erro ao cadastrar");
        }
    } catch (error) {
        console.error(error);
        mostrarToast("Falha na comunicação com o servidor");
    }
}

function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}