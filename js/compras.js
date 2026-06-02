/**
 * js/compras.js - Apenas exibe histórico de compras (cadastro é em fornecedor.html)
 */

document.addEventListener("DOMContentLoaded", renderCompras);

const ICONES_PAGAMENTO = {
    'dinheiro': '💵 Dinheiro',
    'pix': '📱 PIX',
    'cartao': '💳 Cartão',
    'transferencia': '🏦 Transferência'
};

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
                    <td colspan="5" style="text-align: center; padding: 30px; color: #64748b;">
                        Nenhuma compra registrada ainda. Vá em <a href="fornecedor.html">Fornecedor</a> para registrar.
                    </td>
                </tr>`;
            return;
        }

        compras.forEach(c => {
            const pagamento = ICONES_PAGAMENTO[c.forma_pagamento] || c.forma_pagamento;
            container.innerHTML += `
                <tr>
                    <td>${new Date(c.data_compra).toLocaleString('pt-BR')}</td>
                    <td>${c.produto_nome}</td>
                    <td><span class="badge bg-success">${c.quantidade}</span></td>
                    <td>${pagamento}</td>
                    <td>R$ ${parseFloat(c.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar compras:", error);
    }
}