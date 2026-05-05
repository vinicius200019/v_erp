/**
 * js/usuarios.js - Gerencia usuários do sistema
 */

async function renderUsuarios() {
    const container = document.getElementById('lista-usuarios');
    if (!container) return;

    try {
        const resp = await fetch('../php/api/api_usuarios.php');
        const usuarios = await resp.json();

        container.innerHTML = '';

        usuarios.forEach(u => {
            container.innerHTML += `
                <tr>
                    <td>${u.id}</td>
                    <td>${u.email}</td>
                    <td><span class="badge bg-secondary">${u.perfil}</span></td>
                    <td>
                        <button class="btn-delete" onclick="removerUsuario(${u.id})">🗑️</button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar usuários:", error);
    }
}

// Abre o modal e limpa os campos
function abrirModalUsuario() {
    document.getElementById('modal-usuario').style.display = 'flex';
    document.getElementById('novo-email').value = '';
    document.getElementById('nova-senha').value = '';
    document.getElementById('confirmar-senha').value = '';
    document.getElementById('novo-perfil').value = 'admin';
}

// Fecha o modal
function fecharModalUsuario() {
    document.getElementById('modal-usuario').style.display = 'none';
}

// Toast de aviso (some sozinho em 5s)
function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 5000);
}

// Valida e envia para a API
async function salvarUsuario() {
    const email = document.getElementById('novo-email').value.trim();
    const senha = document.getElementById('nova-senha').value;
    const confirmar = document.getElementById('confirmar-senha').value;
    const perfil = document.getElementById('novo-perfil').value;

    if (!email || !senha || !confirmar) {
        mostrarToast('Preencha todos os campos');
        return;
    }

    if (senha !== confirmar) {
        mostrarToast('Senhas diferentes');
        return;
    }

    try {
        const resp = await fetch('../php/api/api_criar_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha, perfil })
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalUsuario();
            renderUsuarios(); // recarrega a lista
        } else {
            mostrarToast(result.message || 'Erro ao criar usuário');
        }
    } catch (error) {
        console.error("Erro ao criar usuário:", error);
        mostrarToast('Falha na comunicação com o servidor');
    }

}

// Remove um usuário (com confirmação)
async function removerUsuario(id) {
    // Pega o usuário logado pra impedir auto-exclusão
    const usuarioLogado = JSON.parse(localStorage.getItem('v_erp_user'));
    if (usuarioLogado && usuarioLogado.id === id) {
        mostrarToast('Você não pode excluir seu próprio usuário');
        return;
    }

    // Pede confirmação antes de excluir
    if (!confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
        return;
    }

    try {
        const resp = await fetch('../php/api/api_excluir_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await resp.json();

        if (result.success) {
            renderUsuarios(); // recarrega a lista
        } else {
            mostrarToast(result.message || 'Erro ao excluir usuário');
        }
    } catch (error) {
        console.error("Erro ao excluir usuário:", error);
        mostrarToast('Falha na comunicação com o servidor');
    }
}

