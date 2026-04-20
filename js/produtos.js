/**
 * js/produtos.js - Gerencia a visualização de produtos
 */

async function renderProdutos() {
    const container = document.getElementById('lista-produtos');
    if (!container) return;

    try {
        const resp = await fetch('../php/api/api_produtos.php');
        const produtos = await resp.json();

        container.innerHTML = ''; // Limpa a tabela

        produtos.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.sku}</td>
                <td>${p.nome}</td>
                <td><span class="badge ${p.estoque < 5 ? 'bg-danger' : 'bg-success'}">${p.estoque}</span></td>
                <td>R$ ${parseFloat(p.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td>
                    <button class="btn-edit" onclick="editarProduto(${p.id})">✏️</button>
                </td>
            `;
            container.appendChild(tr);
        });
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
    }
}