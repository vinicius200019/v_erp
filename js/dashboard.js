/**
 * js/dashboard.js - Carrega todos os dados do painel
 */

document.addEventListener("DOMContentLoaded", carregarDashboard);

async function carregarDashboard() {
    try {
        const resp = await fetch('../php/api/api_dashboard.php');
        const data = await resp.json();

        preencherKPIs(data);
        preencherTabelas(data);
        renderizarGraficos(data);
    } catch (error) {
        console.error("Erro ao carregar dashboard:", error);
    }
}

function formatarBRL(valor) {
    return 'R$ ' + parseFloat(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

/* ===== KPIs ===== */

function preencherKPIs(data) {
    document.getElementById('kpi-itens-estoque').textContent = data.itens_estoque || 0;
    document.getElementById('kpi-produtos-ativos').textContent =
        (data.produtos_ativos || 0) + ' produtos ativos';

    document.getElementById('kpi-patrimonio').textContent = formatarBRL(data.valor_patrimonio);

    document.getElementById('kpi-compras-mes').textContent = formatarBRL(data.compras_mes_valor);
    document.getElementById('kpi-compras-qtd').textContent =
        (data.compras_mes_qtd || 0) + ' compras';

    document.getElementById('kpi-vendas-mes').textContent = formatarBRL(data.vendas_mes_valor);
    document.getElementById('kpi-vendas-qtd').textContent =
        (data.vendas_mes_qtd || 0) + ' vendas';

    // Lucro do mês = vendas - compras
    const lucro = parseFloat(data.vendas_mes_valor || 0) - parseFloat(data.compras_mes_valor || 0);
    const lucroEl = document.getElementById('kpi-lucro-mes');
    const statusEl = document.getElementById('lucro-status');

    lucroEl.textContent = formatarBRL(lucro);

    if (lucro > 0) {
        lucroEl.style.color = '#16a34a';
        statusEl.textContent = '📈';
    } else if (lucro < 0) {
        lucroEl.style.color = '#dc2626';
        statusEl.textContent = '📉';
    } else {
        lucroEl.style.color = '#64748b';
        statusEl.textContent = '➖';
    }
}

/* ===== TABELAS ===== */

function preencherTabelas(data) {
    // Top 5 produtos vendidos
    const topContainer = document.getElementById('lista-top-produtos');
    topContainer.innerHTML = '';

    if (!data.top_produtos || data.top_produtos.length === 0) {
        topContainer.innerHTML = `
            <tr><td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">
                Nenhuma venda registrada ainda.
            </td></tr>`;
    } else {
        data.top_produtos.forEach((p, i) => {
            const medalha = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `${i + 1}º`;
            topContainer.innerHTML += `
                <tr>
                    <td style="font-size: 1.2em;">${medalha}</td>
                    <td><strong>${p.nome}</strong></td>
                    <td><span class="badge bg-success">${p.unidades}</span></td>
                    <td>${formatarBRL(p.receita)}</td>
                </tr>`;
        });
    }

    // Estoque crítico
    const critContainer = document.getElementById('lista-estoque-critico');
    critContainer.innerHTML = '';

    if (!data.estoque_critico || data.estoque_critico.length === 0) {
        critContainer.innerHTML = `
            <tr><td colspan="3" style="text-align: center; padding: 20px; color: #16a34a;">
                ✅ Nenhum produto em estado crítico
            </td></tr>`;
    } else {
        data.estoque_critico.forEach(p => {
            critContainer.innerHTML += `
                <tr>
                    <td>${p.sku}</td>
                    <td><strong>${p.nome}</strong></td>
                    <td><span class="badge bg-danger">${p.estoque}</span></td>
                </tr>`;
        });
    }
}

/* ===== GRÁFICOS ===== */

function renderizarGraficos(data) {
    renderizarComparativo(data.comparativo_6meses || []);
    renderizarTopProdutos(data.top_produtos || []);
}

function renderizarComparativo(dados) {
    const ctx = document.getElementById('grafico-comparativo');
    if (!ctx) return;

    const labels = dados.map(d => d.mes);
    const compras = dados.map(d => parseFloat(d.compras));
    const vendas = dados.map(d => parseFloat(d.vendas));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Compras (R$)',
                    data: compras,
                    backgroundColor: '#ef4444',
                    borderRadius: 6
                },
                {
                    label: 'Vendas (R$)',
                    data: vendas,
                    backgroundColor: '#16a34a',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'R$ ' + v.toLocaleString('pt-BR')
                    }
                }
            }
        }
    });
}

function renderizarTopProdutos(dados) {
    const ctx = document.getElementById('grafico-top-produtos');
    if (!ctx) return;

    if (dados.length === 0) {
        ctx.parentElement.innerHTML = '<p style="text-align: center; color: #64748b; padding: 30px;">Sem dados de vendas ainda.</p>';
        return;
    }

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dados.map(p => p.nome),
            datasets: [{
                data: dados.map(p => parseInt(p.unidades)),
                backgroundColor: [
                    '#2563eb', '#16a34a', '#f59e0b', '#ec4899', '#8b5cf6'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}