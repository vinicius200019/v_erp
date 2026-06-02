/**
 * js/fornecedor.js - Catálogo do fornecedor para compra de mercadoria
 */

let produtoSelecionadoForn = null;

document.addEventListener("DOMContentLoaded", carregarFornecedor);

function formatarBRL(v) {
    return 'R$ ' + parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

async function carregarFornecedor() {
    const container = document.getElementById('lista-fornecedor');
    if (!container) return;

    try {
        const resp = await fetch('../php/api/api_produtos.php?ativo=1');
        const produtos = await resp.json();

        container.innerHTML = '';

        if (produtos.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #64748b;">
                        Nenhum produto cadastrado. Cadastre produtos no <a href="produtos.html">Estoque</a> primeiro.
                    </td>
                </tr>`;
            return;
        }

        produtos.forEach(p => {
            const semCusto = parseFloat(p.preco_custo) <= 0;
            const acao = semCusto
                ? `<small style="color: #ef4444;">⚠️ Configure o preço de custo no Estoque</small>`
                : `<button class="btn-comprar-forn" onclick='abrirModalCompraForn(${JSON.stringify(p)})'>🛒 Comprar</button>`;

            container.innerHTML += `
                <tr>
                    <td><strong>${p.sku}</strong></td>
                    <td>${p.nome}</td>
                    <td><span class="badge ${p.estoque < 5 ? 'bg-danger' : 'bg-success'}">${p.estoque}</span></td>
                    <td style="color: #ef4444; font-weight: 700; font-size: 1.05em;">
                        ${semCusto ? '<span style="color:#94a3b8;">—</span>' : formatarBRL(p.preco_custo)}
                    </td>
                    <td>${acao}</td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
        mostrarToast('Erro ao carregar produtos do fornecedor');
    }
}

function abrirModalCompraForn(produto) {
    produtoSelecionadoForn = produto;

    document.getElementById('forn-nome-produto').textContent = produto.nome;
    document.getElementById('forn-preco-unit').textContent = formatarBRL(produto.preco_custo);
    document.getElementById('forn-estoque-info').textContent =
        `Estoque atual: ${produto.estoque} unidades. A compra vai somar ao estoque.`;
    document.getElementById('forn-qtd').value = 1;
    document.getElementById('forn-pagamento').value = 'pix';

    atualizarPreviewForn();
    document.getElementById('modal-comprar-forn').style.display = 'flex';
}

function atualizarPreviewForn() {
    if (!produtoSelecionadoForn) return;
    let qtd = parseInt(document.getElementById('forn-qtd').value) || 0;
    if (qtd < 0) qtd = 0;

    const total = qtd * parseFloat(produtoSelecionadoForn.preco_custo);
    document.getElementById('forn-total').textContent = formatarBRL(total);
}

function fecharModalFornecedor() {
    document.getElementById('modal-comprar-forn').style.display = 'none';
    produtoSelecionadoForn = null;
}

async function confirmarCompraForn() {
    const qtd = parseInt(document.getElementById('forn-qtd').value);
    const forma_pagamento = document.getElementById('forn-pagamento').value;

    if (!qtd || qtd < 1) {
        mostrarToast('Quantidade inválida');
        return;
    }

    const total = qtd * parseFloat(produtoSelecionadoForn.preco_custo);

    if (!confirm(`Confirmar compra de ${qtd} un. de "${produtoSelecionadoForn.nome}" por ${formatarBRL(total)}?`)) {
        return;
    }

    try {
        const resp = await fetch('../php/api/api_criar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_produto: produtoSelecionadoForn.id,
                quantidade: qtd,
                valor_total: total,
                forma_pagamento: forma_pagamento
            })
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalFornecedor();
            mostrarToast('✅ Compra registrada! Estoque atualizado.', true);
            carregarFornecedor();
        } else {
            mostrarToast(result.message || 'Erro ao registrar compra');
        }
    } catch (error) {
        console.error(error);
        mostrarToast('Falha na comunicação com o servidor');
    }
}

function mostrarToast(mensagem, sucesso = false) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    toast.style.background = sucesso ? '#16a34a' : '';
    setTimeout(() => toast.classList.remove('show'), 4000);
}