/**
 * js/loja.js - Vitrine da loja para clientes
 */

let produtoSelecionado = null;

document.addEventListener("DOMContentLoaded", carregarProdutosLoja);

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
                        : `<button class="btn-comprar" onclick='abrirModalCompra(${JSON.stringify(p)})'>🛒 Comprar</button>`
                    }
                </td>
            `;
            container.appendChild(tr);
        });
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
    }
}

function abrirModalCompra(produto) {
    produtoSelecionado = produto;

    document.getElementById('compra-nome-produto').textContent = produto.nome;
    document.getElementById('compra-preco-unit').textContent =
        'R$ ' + parseFloat(produto.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('compra-estoque-info').textContent =
        `Disponível em estoque: ${produto.estoque} unidades`;

    const inputQtd = document.getElementById('compra-qtd');
    inputQtd.value = 1;
    inputQtd.max = produto.estoque;

    atualizarTotal();
    document.getElementById('modal-compra').style.display = 'flex';
}

function atualizarTotal() {
    if (!produtoSelecionado) return;

    let qtd = parseInt(document.getElementById('compra-qtd').value) || 0;
    const max = parseInt(produtoSelecionado.estoque);

    // Não deixa passar do estoque
    if (qtd > max) {
        qtd = max;
        document.getElementById('compra-qtd').value = max;
        mostrarToast(`Estoque máximo: ${max} unidades`);
    }
    if (qtd < 1) qtd = 0;

    const total = qtd * parseFloat(produtoSelecionado.preco_venda);
    document.getElementById('compra-total').textContent =
        'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
}

function fecharModalCompra() {
    document.getElementById('modal-compra').style.display = 'none';
    produtoSelecionado = null;
}

function cancelarCompra() {
    if (confirm('Deseja realmente cancelar a compra?')) {
        fecharModalCompra();
    }
}

async function finalizarCompra() {
    const qtd = parseInt(document.getElementById('compra-qtd').value);

    if (!qtd || qtd < 1) {
        mostrarToast('Quantidade inválida');
        return;
    }
    if (qtd > parseInt(produtoSelecionado.estoque)) {
        mostrarToast('Quantidade maior que o estoque disponível');
        return;
    }

    if (!confirm(`Confirmar compra de ${qtd} unidade(s) de "${produtoSelecionado.nome}"?`)) {
        return;
    }

    // Pega o cliente logado
    const cliente = JSON.parse(localStorage.getItem('v_erp_cliente'));
    if (!cliente) {
        mostrarToast('Sessão expirada, faça login novamente');
        setTimeout(() => window.location.href = 'login.html', 1500);
        return;
    }

    const valor_total = qtd * parseFloat(produtoSelecionado.preco_venda);

    try {
        const resp = await fetch('../php/api/api_registrar_venda.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_produto: produtoSelecionado.id,
                id_cliente: cliente.id,
                quantidade: qtd,
                valor_total: valor_total
            })
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalCompra();
            mostrarToast('✅ Compra realizada com sucesso!', true);
            carregarProdutosLoja(); // recarrega pra atualizar estoque
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
    if (sucesso) {
        toast.style.background = '#16a34a';
    } else {
        toast.style.background = '';
    }
    setTimeout(() => toast.classList.remove('show'), 5000);
}