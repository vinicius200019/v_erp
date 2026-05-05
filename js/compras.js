/**
 * js/compras.js - Registro de entradas/compras
 */

async function renderCompras() {
    const container = document.getElementById('lista-compras');
    if (!container) return;

    try {
        const resp = await fetch('../php/api/api_compras.php');
        const compras = await resp.json();

        container.innerHTML = '';

        compras.forEach(c => {
            container.innerHTML += `
                <tr>
                    <td>${new Date(c.data_compra).toLocaleDateString('pt-BR')}</td>
                    <td>${c.produto_nome}</td>
                    <td>${c.quantidade}</td>
                    <td>R$ ${parseFloat(c.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar compras:", error);
    }
}
