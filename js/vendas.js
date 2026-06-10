/**
 * js/vendas.js - Histórico de vendas para o admin (suporta múltiplos itens)
 */

let vendasCache = [];

const ICONES_PAGAMENTO = {
    'dinheiro': '💵 Dinheiro',
    'pix': '📱 PIX',
    'cartao': '💳 Cartão',
    'transferencia': '🏦 Transferência'
};

document.addEventListener("DOMContentLoaded", carregarVendas);

async function carregarVendas() {
    try {
        const resp = await fetch('../php/api/api_vendas.php');
        const vendas = await resp.json();
        vendasCache = vendas;

        atualizarEstatisticas(vendas);
        renderizarVendas(vendas);
    } catch (error) {
        console.error("Erro ao carregar vendas:", error);
    }
}

function renderizarVendas(vendas) {
    const container = document.getElementById('lista-vendas');
    if (!container) return;

    container.innerHTML = '';

    if (vendas.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">
                    Nenhuma venda encontrada com os filtros aplicados.
                </td>
            </tr>`;
        return;
    }

    vendas.forEach(v => {
        const pagamento = ICONES_PAGAMENTO[v.forma_pagamento] || v.forma_pagamento || '-';
        const totalItens = v.itens.reduce((s, i) => s + parseInt(i.quantidade), 0);

        let resumoProdutos = '';
        if (v.itens.length === 1) {
            resumoProdutos = `<strong>${v.itens[0].produto_nome}</strong>`;
        } else if (v.itens.length === 2) {
            resumoProdutos = `<strong>${v.itens[0].produto_nome}</strong>, <strong>${v.itens[1].produto_nome}</strong>`;
        } else {
            resumoProdutos = `<strong>${v.itens[0].produto_nome}</strong> + ${v.itens.length - 1} outro(s)`;
        }

        container.innerHTML += `
            <tr>
                <td>${new Date(v.data_venda).toLocaleString('pt-BR')}</td>
                <td>${v.cliente_email}</td>
                <td>
                    ${resumoProdutos}
                    <br>
                   <button onclick="toggleDetalhesV(${v.id}, this)" class="btn-ver-detalhes">
                     <span class="seta">▼</span> Ver detalhes
                    </button>
                </td>
                <td><span class="badge bg-success">${totalItens}</span></td>
                <td>${pagamento}</td>
                <td class="preco-verde-pequeno" style="font-size: 1em;">
                    R$ ${parseFloat(v.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
            </tr>
            <tr id="detalhesv-${v.id}" style="display: none;">
                <td colspan="6" style="background: #f8fafc; padding: 15px;">
                    <strong>Itens desta venda:</strong>
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
                            ${v.itens.map(i => `
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
}

function toggleDetalhesV(id) {
    const linha = document.getElementById('detalhesv-' + id);
    if (linha) {
        linha.style.display = linha.style.display === 'none' ? 'table-row' : 'none';
    }
}

function atualizarEstatisticas(vendas) {
    const totalVendas = vendas.length;
    const totalItens = vendas.reduce((s, v) =>
        s + v.itens.reduce((s2, i) => s2 + parseInt(i.quantidade), 0), 0);
    const receita = vendas.reduce((s, v) => s + parseFloat(v.valor_total), 0);
    const ticketMedio = totalVendas > 0 ? receita / totalVendas : 0;

    document.getElementById('stat-total-vendas').textContent = totalVendas;
    document.getElementById('stat-itens-vendidos').textContent = totalItens;
    document.getElementById('stat-receita').textContent =
        'R$ ' + receita.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('stat-ticket-medio').textContent =
        'R$ ' + ticketMedio.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

function aplicarFiltros() {
    const termo = document.getElementById('filtro-busca').value.toLowerCase().trim();
    const dataInicio = document.getElementById('filtro-data-inicio').value;
    const dataFim = document.getElementById('filtro-data-fim').value;

    let filtradas = vendasCache.filter(v => {
        if (termo) {
            // Busca em qualquer um dos produtos da venda OU no email do cliente
            const matchCliente = v.cliente_email.toLowerCase().includes(termo);
            const matchProduto = v.itens.some(i => i.produto_nome.toLowerCase().includes(termo));
            if (!matchCliente && !matchProduto) return false;
        }
        const dataVenda = v.data_venda.split(' ')[0];
        if (dataInicio && dataVenda < dataInicio) return false;
        if (dataFim && dataVenda > dataFim) return false;
        return true;
    });

    atualizarEstatisticas(filtradas);
    renderizarVendas(filtradas);
}

function limparFiltros() {
    document.getElementById('filtro-busca').value = '';
    document.getElementById('filtro-data-inicio').value = '';
    document.getElementById('filtro-data-fim').value = '';
    atualizarEstatisticas(vendasCache);
    renderizarVendas(vendasCache);
}

function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}