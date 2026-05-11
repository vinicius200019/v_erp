/**
 * js/minhas-compras.js - Histórico de compras do cliente logado
 */

document.addEventListener("DOMContentLoaded", carregarMinhasCompras);

async function carregarMinhasCompras() {
    const container = document.getElementById('lista-minhas-compras');
    if (!container) return;

    // Pega o cliente logado
    const cliente = JSON.parse(localStorage.getItem('v_erp_cliente'));
    if (!cliente) {
        window.location.href = 'login.html';
        return;
    }

    try {
        const resp = await fetch(`../php/api/api_minhas_compras.php?id_cliente=${cliente.id}`);
        const compras = await resp.json();

        // Atualiza os números do topo (estatísticas pessoais)
        atualizarEstatisticas(compras);

        // Preenche a tabela
        container.innerHTML = '';

        if (compras.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">
                        Você ainda não fez nenhuma compra. 
                        <br><br>
                        <a href="loja.html" class="btn btn-comprar" style="text-decoration: none;">
                            🛒 Ir para a Loja
                        </a>
                    </td>
                </tr>`;
            return;
        }

        compras.forEach(c => {
            container.innerHTML += `
                <tr>
                    <td>${new Date(c.data_venda).toLocaleString('pt-BR')}</td>
                    <td><strong>${c.produto_nome}</strong></td>
                    <td><span class="badge bg-success">${c.quantidade}</span></td>
                    <td class="preco-verde-pequeno" style="font-size: 1em;">
                        R$ ${parseFloat(c.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar histórico:", error);
        mostrarToast("Erro ao carregar histórico");
    }
}

function atualizarEstatisticas(compras) {
    const totalCompras = compras.length;
    const totalItens = compras.reduce((soma, c) => soma + parseInt(c.quantidade), 0);
    const totalGasto = compras.reduce((soma, c) => soma + parseFloat(c.valor_total), 0);

    let ultimaCompra = '-';
    if (compras.length > 0) {
        // Como a API ordena DESC, a primeira é a mais recente
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