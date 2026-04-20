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