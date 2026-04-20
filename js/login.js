/**
 * js/login.js - Processa o acesso ao sistema
 */

async function handleLogin() {
    const email = document.getElementById('login-email').value;
    const senha = document.getElementById('login-senha').value;

    if (!email || !senha) {
        alert("Por favor, preencha todos os campos.");
        return;
    }

    try {
        const response = await fetch('../php/api/api_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha })
        });

        const result = await response.json();

        if (result.success) {
            // Salva os dados do usuário para o auth.js validar
            localStorage.setItem("v_erp_user", JSON.stringify(result.user));
            // Redireciona para a página inicial
            window.location.href = "../index.html";
        } else {
            alert("Erro: " + result.message);
        }
    } catch (error) {
        console.error("Erro ao fazer login:", error);
        alert("Falha na comunicação com o servidor.");
    }
}