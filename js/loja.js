/**
 * js/loja.js - Vitrine da loja com botão "Adicionar ao Carrinho"
 */

let produtoSelecionadoAdd = null;

document.addEventListener("DOMContentLoaded", () => {
    carregarProdutosLoja();
    atualizarBadgeCarrinho();
});

/* ===== CARRINHO (LOCAL STORAGE) ===== */

function getCarrinho() {
    const json = localStorage.getItem('v_erp_carrinho');
    return json ? JSON.parse(json) : [];
}

function salvarCarrinho(itens) {
    localStorage.setItem('v_erp_carrinho', JSON.stringify(itens));
}

function adicionarAoCarrinho(produto, quantidade) {
    const carrinho = getCarrinho();
    const existe = carrinho.find(item => item.id == produto.id);

    if (existe) {
        existe.quantidade += quantidade;
    } else {
        carrinho.push({
            id: produto.id,
            nome: produto.nome,
            sku: produto.sku,
            preco_venda: parseFloat(produto.preco_venda),
            estoque_disponivel: parseInt(produto.estoque),
            quantidade: quantidade
        });
    }

    salvarCarrinho(carrinho);
    atualizarBadgeCarrinho();
}

function atualizarBadgeCarrinho() {
    const carrinho = getCarrinho();
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

/* ===== LISTAGEM DE PRODUTOS ===== */

async function carregarProdutosLoja() {
    const container = document.getElementById('lista-loja');
    if (!container) return;

    try {
        const resp = await fetch('../php/api/api_produtos.php?ativo=1');
        const produtos = await resp.json();

        container.innerHTML = '';

        if (produtos.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px; color: #64748b;">
                        Nenhum produto disponível no momento.
                    </td>
                </tr>`;
            return;
        }

        produtos.forEach(p => {
            const semEstoque = parseInt(p.estoque) <= 0;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${p.nome}</strong></td>
                <td class="preco-verde-pequeno" style="font-size: 1.1em;">
                    R$ ${parseFloat(p.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
                <td>
                    <span class="badge ${semEstoque ? 'bg-danger' : 'bg-success'}">
                        ${p.estoque} ${semEstoque ? '(esgotado)' : 'unidades'}
                    </span>
                </td>
                <td>
                    ${semEstoque
                        ? `<button class="btn-comprar" disabled style="opacity:0.4; cursor:not-allowed;">Indisponível</button>`
                        : `<button class="btn-comprar" onclick='abrirModalAdicionar(${JSON.stringify(p)})'>🛒 Adicionar</button>`
                    }
                </td>
            `;
            container.appendChild(tr);
        });
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
    }
}

/* ===== MODAL DE ADICIONAR ===== */

function abrirModalAdicionar(produto) {
    produtoSelecionadoAdd = produto;

    // Verifica quanto já tem no carrinho desse produto
    const carrinho = getCarrinho();
    const noCarrinho = carrinho.find(i => i.id == produto.id);
    const jaAdicionado = noCarrinho ? noCarrinho.quantidade : 0;
    const estoqueRestante = parseInt(produto.estoque) - jaAdicionado;

    document.getElementById('add-nome-produto').textContent = produto.nome;
    document.getElementById('add-preco-unit').textContent =
        'R$ ' + parseFloat(produto.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2});

    const info = jaAdicionado > 0
        ? `${produto.estoque} em estoque (${jaAdicionado} já no carrinho)`
        : `${produto.estoque} unidades disponíveis`;
    document.getElementById('add-estoque-info').textContent = info;

    const inputQtd = document.getElementById('add-qtd');
    inputQtd.value = 1;
    inputQtd.max = estoqueRestante > 0 ? estoqueRestante : 1;

    if (estoqueRestante <= 0) {
        mostrarToast('Você já adicionou todo o estoque disponível deste produto');
        return;
    }

    atualizarPreviewAdd();
    document.getElementById('modal-adicionar').style.display = 'flex';
}

function atualizarPreviewAdd() {
    if (!produtoSelecionadoAdd) return;

    let qtd = parseInt(document.getElementById('add-qtd').value) || 0;
    const max = parseInt(document.getElementById('add-qtd').max);

    if (qtd > max) {
        qtd = max;
        document.getElementById('add-qtd').value = max;
        mostrarToast(`Estoque máximo disponível: ${max}`);
    }
    if (qtd < 1) qtd = 0;

    const subtotal = qtd * parseFloat(produtoSelecionadoAdd.preco_venda);
    document.getElementById('add-subtotal').textContent =
        'R$ ' + subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
}

function fecharModalAdicionar() {
    document.getElementById('modal-adicionar').style.display = 'none';
    produtoSelecionadoAdd = null;
}

function confirmarAdicionar() {
    const qtd = parseInt(document.getElementById('add-qtd').value);
    if (!qtd || qtd < 1) {
        mostrarToast('Quantidade inválida');
        return;
    }

    adicionarAoCarrinho(produtoSelecionadoAdd, qtd);
    mostrarToast(`✅ ${qtd} un. de "${produtoSelecionadoAdd.nome}" adicionado ao carrinho!`, true);
    fecharModalAdicionar();
}

function mostrarToast(mensagem, sucesso = false) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    toast.style.background = sucesso ? '#16a34a' : '';
    setTimeout(() => toast.classList.remove('show'), 4000);
}