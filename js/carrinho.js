/**
 * js/carrinho.js - Gerencia o carrinho de compras do cliente
 */

document.addEventListener("DOMContentLoaded", () => {
    renderizarCarrinho();
    atualizarBadgeCarrinho();
});

/* ===== HELPERS DE CARRINHO ===== */

function getCarrinho() {
    const json = localStorage.getItem('v_erp_carrinho');
    return json ? JSON.parse(json) : [];
}

function salvarCarrinho(itens) {
    localStorage.setItem('v_erp_carrinho', JSON.stringify(itens));
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

/* ===== RENDERIZAÇÃO ===== */

function renderizarCarrinho() {
    const carrinho = getCarrinho();
    const containerVazio = document.getElementById('carrinho-vazio');
    const containerConteudo = document.getElementById('carrinho-conteudo');
    const tbody = document.getElementById('lista-carrinho');

    if (carrinho.length === 0) {
        containerVazio.style.display = 'block';
        containerConteudo.style.display = 'none';
        return;
    }

    containerVazio.style.display = 'none';
    containerConteudo.style.display = 'block';
    tbody.innerHTML = '';

    let total = 0;
    let totalItens = 0;

    carrinho.forEach((item, index) => {
        const subtotal = item.quantidade * item.preco_venda;
        total += subtotal;
        totalItens += item.quantidade;

        tbody.innerHTML += `
            <tr>
                <td>
                    <strong>${item.nome}</strong>
                    <br>
                    <small style="color: #64748b;">SKU: ${item.sku}</small>
                </td>
                <td class="preco-verde-pequeno" style="font-size: 1em;">
                    R$ ${item.preco_venda.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 6px; justify-content: center;">
                        <button class="btn-qtd" onclick="alterarQtd(${index}, -1)">−</button>
                        <span style="min-width: 30px; text-align: center; font-weight: 700;">${item.quantidade}</span>
                        <button class="btn-qtd" onclick="alterarQtd(${index}, 1)">+</button>
                    </div>
                    <small style="color: #94a3b8; font-size: 0.75em;">${item.estoque_disponivel} em estoque</small>
                </td>
                <td class="preco-verde-pequeno" style="font-size: 1.1em;">
                    R$ ${subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                </td>
                <td>
                    <button class="btn-action btn-warning" onclick="removerItem(${index})">🗑️ Remover</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('resumo-itens').textContent = totalItens;
    document.getElementById('resumo-tipos').textContent = carrinho.length;
    document.getElementById('resumo-total').textContent =
        'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
}

/* ===== AÇÕES ===== */

function alterarQtd(index, delta) {
    const carrinho = getCarrinho();
    const item = carrinho[index];
    const novaQtd = item.quantidade + delta;

    if (novaQtd < 1) {
        // Pergunta se quer remover
        if (confirm(`Deseja remover "${item.nome}" do carrinho?`)) {
            carrinho.splice(index, 1);
            salvarCarrinho(carrinho);
            renderizarCarrinho();
            atualizarBadgeCarrinho();
        }
        return;
    }

    if (novaQtd > item.estoque_disponivel) {
        mostrarToast(`Estoque máximo disponível: ${item.estoque_disponivel}`);
        return;
    }

    item.quantidade = novaQtd;
    salvarCarrinho(carrinho);
    renderizarCarrinho();
    atualizarBadgeCarrinho();
}

function removerItem(index) {
    const carrinho = getCarrinho();
    const item = carrinho[index];
    if (!confirm(`Remover "${item.nome}" do carrinho?`)) return;
    carrinho.splice(index, 1);
    salvarCarrinho(carrinho);
    renderizarCarrinho();
    atualizarBadgeCarrinho();
    mostrarToast('Item removido do carrinho');
}

function limparCarrinho() {
    if (!confirm('Deseja esvaziar todo o carrinho?')) return;
    localStorage.removeItem('v_erp_carrinho');
    renderizarCarrinho();
    atualizarBadgeCarrinho();
}

async function finalizarCarrinho() {
    const carrinho = getCarrinho();
    if (carrinho.length === 0) {
        mostrarToast('Carrinho vazio');
        return;
    }

    // Pega o cliente logado
    const cliente = JSON.parse(localStorage.getItem('v_erp_cliente'));
    if (!cliente) {
        mostrarToast('Sessão expirada');
        setTimeout(() => window.location.href = 'login.html', 1500);
        return;
    }

    const forma_pagamento = document.getElementById('carrinho-pagamento').value;
    const valor_total = carrinho.reduce((s, i) => s + (i.quantidade * i.preco_venda), 0);

    if (!confirm(`Confirmar compra de ${carrinho.length} produto(s) no valor total de R$ ${valor_total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}?`)) {
        return;
    }

    // Prepara os itens pra enviar
    const itens = carrinho.map(c => ({
        id_produto: c.id,
        quantidade: c.quantidade,
        preco_unitario: c.preco_venda
    }));

    try {
        const resp = await fetch('../php/api/api_finalizar_carrinho.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_cliente: cliente.id,
                forma_pagamento: forma_pagamento,
                itens: itens
            })
        });
        const result = await resp.json();

        if (result.success) {
            // Limpa o carrinho
            localStorage.removeItem('v_erp_carrinho');
            mostrarToast('✅ Compra realizada! Abrindo nota fiscal...', true);

            // Abre a nota fiscal
            setTimeout(() => {
                window.open('../php/api/api_nota_fiscal.php?id=' + result.id_venda, '_blank');
                // Após um tempo, volta pra loja
                setTimeout(() => {
                    window.location.href = 'loja.html';
                }, 1500);
            }, 800);
        } else {
            mostrarToast(result.message || 'Erro ao finalizar compra');
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