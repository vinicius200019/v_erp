/**
 * js/produtos.js - Gerencia produtos com preço de custo e margem
 */

let abaAtual = 1;
let produtosCache = [];

document.addEventListener("DOMContentLoaded", renderProdutos);

function formatarBRL(v) {
    return 'R$ ' + parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

function calcularMargem(custo, venda) {
    custo = parseFloat(custo) || 0;
    venda = parseFloat(venda) || 0;
    if (custo === 0 || venda === 0) return null;
    const lucro = venda - custo;
    const percentual = (lucro / custo) * 100;
    return { lucro, percentual };
}

async function renderProdutos() {
    const container = document.getElementById('lista-produtos');
    if (!container) return;

    try {
        const resp = await fetch(`../php/api/api_produtos.php?ativo=${abaAtual}`);
        const produtos = await resp.json();
        produtosCache = produtos;

        container.innerHTML = '';

        if (produtos.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                        Nenhum produto ${abaAtual === 1 ? 'ativo' : 'inativo'} encontrado.
                    </td>
                </tr>`;
            return;
        }

        produtos.forEach(p => {
            const margem = calcularMargem(p.preco_custo, p.preco_venda);
            let margemHtml = '';
            if (margem === null) {
                margemHtml = '<span style="color: #94a3b8; font-size: 0.9em;">— configure preço</span>';
            } else {
                const cor = margem.percentual > 0 ? '#16a34a' : '#dc2626';
                margemHtml = `
                    <strong style="color: ${cor};">${margem.percentual.toFixed(1)}%</strong>
                    <br>
                    <small style="color: #64748b;">+${formatarBRL(margem.lucro)}/un</small>`;
            }

            const acaoMover = abaAtual === 1
                ? `<button class="btn-action btn-warning" onclick="moverStatus(${p.id}, 0)">📦 Inativar</button>`
                : `<button class="btn-action btn-success-action" onclick="moverStatus(${p.id}, 1)">✅ Reativar</button>`;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.sku}</td>
                <td>${p.nome}</td>
                <td><span class="badge ${p.estoque < 5 ? 'bg-danger' : 'bg-success'}">${p.estoque}</span></td>
                <td>${parseFloat(p.preco_custo) > 0 ? formatarBRL(p.preco_custo) : '<span style="color:#94a3b8;">—</span>'}</td>
                <td>${formatarBRL(p.preco_venda)}</td>
                <td>${margemHtml}</td>
                <td>
                    <button class="btn-action btn-edit" onclick="editarProduto(${p.id})">✏️ Editar</button>
                    ${acaoMover}
                </td>
            `;
            container.appendChild(tr);
        });
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
    }
}

function trocarAba(status) {
    abaAtual = status;
    document.getElementById('tab-ativos').classList.toggle('active', status === 1);
    document.getElementById('tab-inativos').classList.toggle('active', status === 0);
    renderProdutos();
}

/* ===== CADASTRO ===== */

function abrirModalNovoProduto() {
    document.getElementById('novo-sku').value = '';
    document.getElementById('novo-nome').value = '';
    document.getElementById('novo-estoque').value = '';
    document.getElementById('novo-custo').value = '';
    document.getElementById('novo-preco').value = '';
    document.getElementById('preview-margem-novo').innerHTML = '';
    document.getElementById('modal-novo-produto').style.display = 'flex';
}

function fecharModalNovoProduto() {
    document.getElementById('modal-novo-produto').style.display = 'none';
}

/* Preview da margem no modal (atualiza ao vivo conforme o usuário digita) */
function atualizarPreviewMargem(prefixo) {
    const custo = document.getElementById(`${prefixo}-custo`).value;
    const venda = document.getElementById(`${prefixo}-preco`).value;
    const preview = document.getElementById(`preview-margem-${prefixo}`);
    const margem = calcularMargem(custo, venda);

    if (margem === null) {
        preview.innerHTML = '';
        preview.className = 'preview-margem';
        return;
    }

    if (margem.percentual > 0) {
        preview.innerHTML = `📈 Margem de lucro: <strong>${margem.percentual.toFixed(1)}%</strong> (+${formatarBRL(margem.lucro)} por unidade)`;
        preview.className = 'preview-margem positivo';
    } else if (margem.percentual < 0) {
        preview.innerHTML = `⚠️ Prejuízo: <strong>${margem.percentual.toFixed(1)}%</strong> (${formatarBRL(margem.lucro)} por unidade)`;
        preview.className = 'preview-margem negativo';
    } else {
        preview.innerHTML = `Margem zero — preço igual ao custo`;
        preview.className = 'preview-margem neutro';
    }
}

async function cadastrarProduto() {
    const sku = document.getElementById('novo-sku').value.trim();
    const nome = document.getElementById('novo-nome').value.trim();
    const estoque = document.getElementById('novo-estoque').value;
    const preco_custo = document.getElementById('novo-custo').value;
    const preco_venda = document.getElementById('novo-preco').value;

    if (!sku || !nome || estoque === '' || preco_custo === '' || preco_venda === '') {
        mostrarToast('Preencha todos os campos');
        return;
    }

    try {
        const resp = await fetch('../php/api/api_criar_produto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ sku, nome, estoque, preco_custo, preco_venda })
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalNovoProduto();
            if (abaAtual !== 1) {
                trocarAba(1);
            } else {
                renderProdutos();
            }
        } else {
            mostrarToast(result.message || 'Erro ao cadastrar produto');
        }
    } catch (error) {
        console.error(error);
        mostrarToast('Falha na comunicação com o servidor');
    }
}

/* ===== EDIÇÃO ===== */

function editarProduto(id) {
    const produto = produtosCache.find(p => p.id == id);
    if (!produto) return;

    document.getElementById('edit-id').value = produto.id;
    document.getElementById('edit-sku').value = produto.sku;
    document.getElementById('edit-nome').value = produto.nome;
    document.getElementById('edit-estoque').value = produto.estoque;
    document.getElementById('edit-custo').value = produto.preco_custo;
    document.getElementById('edit-preco').value = produto.preco_venda;
    atualizarPreviewMargem('edit');
    document.getElementById('modal-produto').style.display = 'flex';
}

function fecharModalProduto() {
    document.getElementById('modal-produto').style.display = 'none';
}

async function salvarProduto() {
    const id = document.getElementById('edit-id').value;
    const sku = document.getElementById('edit-sku').value.trim();
    const nome = document.getElementById('edit-nome').value.trim();
    const estoque = document.getElementById('edit-estoque').value;
    const preco_custo = document.getElementById('edit-custo').value;
    const preco_venda = document.getElementById('edit-preco').value;

    if (!sku || !nome || estoque === '' || preco_custo === '' || preco_venda === '') {
        mostrarToast('Preencha todos os campos');
        return;
    }

    try {
        const resp = await fetch('../php/api/api_editar_produto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, sku, nome, estoque, preco_custo, preco_venda })
        });
        const result = await resp.json();

        if (result.success) {
            fecharModalProduto();
            renderProdutos();
        } else {
            mostrarToast(result.message || 'Erro ao salvar');
        }
    } catch (error) {
        console.error(error);
        mostrarToast('Falha na comunicação com o servidor');
    }
}

async function moverStatus(id, novoStatus) {
    const acao = novoStatus === 1 ? 'reativar' : 'inativar';
    if (!confirm(`Tem certeza que deseja ${acao} este produto?`)) return;

    try {
        const resp = await fetch('../php/api/api_alterar_status_produto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, ativo: novoStatus })
        });
        const result = await resp.json();

        if (result.success) {
            renderProdutos();
        } else {
            mostrarToast(result.message || 'Erro ao alterar status');
        }
    } catch (error) {
        console.error(error);
        mostrarToast('Falha na comunicação com o servidor');
    }
}

function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}