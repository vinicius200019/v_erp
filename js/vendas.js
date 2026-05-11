/**
 * js/vendas.js - Histórico geral de vendas (visão admin)
 */

let vendasCache = []; // armazena todas as vendas pra filtrar localmente

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
        mostrarToast("Erro ao carregar vendas");
    }
}

function renderizarVendas(vendas) {
    const container = document.getElementById('lista-vendas');
    if (!container) return;

    container.innerHTML = '';

    if (vendas.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #64748b;">
                    Nenhuma venda encontrada com os filtros aplicados.
                </td>
            </tr>`;
        return;
    }

    vendas.forEach(v => {
        container.innerHTML += `
            <tr>
                <td>${new Date(v.data_venda).toLocaleString('pt-BR')}</td>
                <td>${v.cliente_email}</td>
                <td><strong>${v.produto_nome}</strong></td>
                <td><span class="badge bg-success">${v.quantidade}</span></td>
                <td class="preco-verde-pequeno" style="font-size: 1em;">
                    R$ ${parseFloat(v.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
            </tr>
        `;
    });
}

function atualizarEstatisticas(vendas) {
    const totalVendas = vendas.length;
    const totalItens = vendas.reduce((s, v) => s + parseInt(v.quantidade), 0);
    const receita = vendas.reduce((s, v) => s + parseFloat(v.valor_total), 0);
    const ticketMedio = totalVendas > 0 ? receita / totalVendas : 0;

    document.getElementById('stat-total-vendas').textContent = totalVendas;
    document.getElementById('stat-itens-vendidos').textContent = totalItens;
    document.getElementById('stat-receita').textContent =
        'R$ ' + receita.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    document.getElementById('stat-ticket-medio').textContent =
        'R$ ' + ticketMedio.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

/* ===== FILTROS ===== */

function aplicarFiltros() {
    const termo = document.getElementById('filtro-busca').value.toLowerCase().trim();
    const dataInicio = document.getElementById('filtro-data-inicio').value;
    const dataFim = document.getElementById('filtro-data-fim').value;

    let filtradas = vendasCache.filter(v => {
        // Filtro de busca (produto ou cliente)
        if (termo) {
            const matchProduto = v.produto_nome.toLowerCase().includes(termo);
            const matchCliente = v.cliente_email.toLowerCase().includes(termo);
            if (!matchProduto && !matchCliente) return false;
        }

        // Filtro de data
        const dataVenda = v.data_venda.split(' ')[0]; // pega só YYYY-MM-DD
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