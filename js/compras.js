/**
 * js/compras.js - Registro de compras (entradas de mercadoria)
 */

document.addEventListener("DOMContentLoaded", renderCompras);

async function renderCompras() {
    const container = document.getElementById('lista-compras');
    if (!container) return;

    try {
        const resp = await fetch('../php/api/api_compras.php');
        const compras = await resp.json();

        container.innerHTML = '';

        if (compras.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px; color: #64748b;">
                        Nenhuma compra registrada ainda.
                    </td>
                </tr>`;
            return;
        }

        compras.forEach(c => {
            container.innerHTML += `
                <tr>
                    <td>${new Date(c.data_compra).toLocaleString('pt-BR')}</td>
                    <td>${c.produto_nome}</td>
                    <td><span class="badge bg-success">${c.quantidade}</span></td>
                    <td>R$ ${parseFloat(c.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar compras:", error);
    }
}

/* ===== REGISTRAR NOVA COMPRA ===== */

async function abrirModalCompra() {
    // Limpa campos
    document.getElementById('compra-quantidade').value = '';
    document.getElementById('compra-valor').value = '';
    document.getElementById('compra-produto').value = '';

    // Carrega lista de produtos ativos no dropdown
    try {
        const resp = await fetch('../php/api/api_produtos.php?ativo=1');
        const produtos = await resp.json();
        const select = document.getElementById('compra-produto');

        // Mantém só a opção padrão
        select.innerHTML = '<option value="">-- Selecione um produto --</option>';

        if (produtos.length === 0) {
            mostrarToast('Cadastre um produto antes de registrar compras');
            return;
        }

        produtos.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = `${p.sku} - ${p.nome} (Estoque atual: ${p.estoque})`;
            select.appendChild(opt);
        });

        document.getElementById('modal-compra').style.display = 'flex';
    } catch (error) {
        console.error(error);
        mostrarToast('Erro ao carregar produtos');
    }
}

function fecharModalCompra() {
    document.getElementById('modal-compra').style.display = 'none';
}

async function salvarCompra() {
    const id_produto = document.getElementById('compra-produto').value;
    const quantidade = document.getElementById('compra-quantidade').value;
    const valor_total = document.getElementById('compra-valor').value;

    if (!id_produto) {
        mostrarToast('Selecione um produto');
        return;
    }
    if (!quantidade || parseInt(quantidade) <= 0) {
        mostrarToast('Quantidade deve ser maior que zero');
        return;
    }
    if (valor_total === '' || parseFloat(valor_total) < 0) {
        mostrarToast('Informe um valor válido');
        return;
    }

    try {
        const resp = await fetch('../php/api/api_criar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_produto, quantidade, valor_total })
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalCompra();
            renderCompras();
        } else {
            mostrarToast(result.message || 'Erro ao registrar compra');
        }
    } catch (error) {
        console.error(error);
        mostrarToast('Falha na comunicação com o servidor');
    }
}

/* ===== TOAST ===== */

function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}