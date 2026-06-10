/**
 * js/minhas-compras.js - Histórico do cliente (suporta venda com múltiplos itens)
 */

const ICONES_PAGAMENTO = {
    'dinheiro': '💵 Dinheiro',
    'pix': '📱 PIX',
    'cartao': '💳 Cartão',
    'transferencia': '🏦 Transferência'
};

document.addEventListener("DOMContentLoaded", () => {
    carregarMinhasCompras();
    atualizarBadgeCarrinho();
});

function atualizarBadgeCarrinho() {
    const json = localStorage.getItem('v_erp_carrinho');
    const carrinho = json ? JSON.parse(json) : [];
    const totalItens = carrinho.reduce((s, i) => s + i.quantidade, 0);
    const badge = document.getElementById('badge-carrinho');
    if (!badge) return;
    if (totalItens > 0) {
        badge.textContent = totalItens;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

async function carregarMinhasCompras() {
    const container = document.getElementById('lista-minhas-compras');
    if (!container) return;

    const cliente = JSON.parse(localStorage.getItem('v_erp_cliente'));
    if (!cliente) {
        window.location.href = 'login.html';
        return;
    }

    try {
        const resp = await fetch(`../php/api/api_minhas_compras.php?id_cliente=${cliente.id}`);
        const compras = await resp.json();

        atualizarEstatisticas(compras);

        container.innerHTML = '';

        if (compras.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                        Você ainda não fez nenhuma compra.
                        <br><br>
                        <a href="loja.html" class="btn btn-comprar" style="text-decoration: none;">
                            🛒 Ir para a Loja
                        </a>
                    </td>
                </tr>`;
            return;
        }

        compras.forEach((c, idx) => {
            const pagamento = ICONES_PAGAMENTO[c.forma_pagamento] || c.forma_pagamento || '-';
            const totalItens = c.itens.reduce((s, i) => s + parseInt(i.quantidade), 0);

            // Resumo dos produtos (até 2 nomes, depois "+ X outros")
            let resumoProdutos = '';
            if (c.itens.length === 1) {
                resumoProdutos = `<strong>${c.itens[0].produto_nome}</strong>`;
            } else if (c.itens.length === 2) {
                resumoProdutos = `<strong>${c.itens[0].produto_nome}</strong>, <strong>${c.itens[1].produto_nome}</strong>`;
            } else {
                resumoProdutos = `<strong>${c.itens[0].produto_nome}</strong> + ${c.itens.length - 1} outro(s)`;
            }

            // Linha principal
            container.innerHTML += `
                <tr>
                    <td>${new Date(c.data_venda).toLocaleString('pt-BR')}</td>
                    <td>
                        ${resumoProdutos}
                        <br>
                       <button onclick="toggleDetalhes(${c.id}, this)" class="btn-ver-detalhes">
                         <span class="seta">▼</span> Ver detalhes
                        </button>
                    </td>
                    <td><span class="badge bg-success">${totalItens}</span></td>
                    <td>${pagamento}</td>
                    <td class="preco-verde-pequeno" style="font-size: 1em;">
                        R$ ${parseFloat(c.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </td>
                    <td>
                        <button class="btn-nota" onclick="abrirNota(${c.id})">📄 Baixar</button>
                    </td>
                </tr>
                <tr id="detalhes-${c.id}" style="display: none;">
                    <td colspan="6" style="background: #f8fafc; padding: 15px;">
                        <strong>Itens desta compra:</strong>
                        <table style="width: 100%; margin-top: 10px;">
                            <thead>
                                <tr style="background: #e2e8f0;">
                                    <th style="padding: 6px;">Produto</th>
                                    <th style="padding: 6px;">SKU</th>
                                    <th style="padding: 6px;">Qtd</th>
                                    <th style="padding: 6px;">Preço Unit.</th>
                                    <th style="padding: 6px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${c.itens.map(i => `
                                    <tr>
                                        <td style="padding: 6px;">${i.produto_nome}</td>
                                        <td style="padding: 6px;">${i.sku}</td>
                                        <td style="padding: 6px; text-align: center;">${i.quantidade}</td>
                                        <td style="padding: 6px; text-align: right;">R$ ${parseFloat(i.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                                        <td style="padding: 6px; text-align: right;"><strong>R$ ${parseFloat(i.subtotal).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar histórico:", error);
        mostrarToast("Erro ao carregar histórico");
    }
}

function toggleDetalhes(id) {
    const linha = document.getElementById('detalhes-' + id);
    if (linha) {
        linha.style.display = linha.style.display === 'none' ? 'table-row' : 'none';
    }
}

function abrirNota(id_venda) {
    window.open('../php/api/api_nota_fiscal.php?id=' + id_venda, '_blank');
}

function atualizarEstatisticas(compras) {
    const totalCompras = compras.length;
    // Soma todos os itens de todas as compras
    const totalItens = compras.reduce((soma, c) =>
        soma + c.itens.reduce((s, i) => s + parseInt(i.quantidade), 0), 0);
    const totalGasto = compras.reduce((soma, c) => soma + parseFloat(c.valor_total), 0);

    let ultimaCompra = '-';
    if (compras.length > 0) {
        ultimaCompra = new Date(compras[0].data_venda).toLocaleDateString('pt-BR');
    }

    document.getElementById('total-compras').textContent = totalCompras;
    document.getElementById('total-itens').textContent = totalItens;
    document.getElementById('total-gasto').textContent =
        'R$ ' + totalGasto.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('ultima-compra').textContent = ultimaCompra;
}

function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}