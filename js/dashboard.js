/**
 * js/dashboard.js - Carrega as estatísticas do ERP
 */

document.addEventListener("DOMContentLoaded", () => {
    // Só executa se estivermos na index (onde existem os IDs de estatísticas)
    if (document.getElementById('stat-produtos')) {
        carregarEstatisticas();
    }
});

async function carregarEstatisticas() {
    try {
        // Busca total de produtos e valor em estoque
        const respDash = await fetch('../php/api/api_dashboard.php');
        const dataDash = await respDash.json();

        document.getElementById('stat-estoque').textContent = dataDash.total || '0';
        document.getElementById('stat-vendas').textContent = 'R$ ' + dataDash.valor;

        // Busca quantidade de usuários (operadores)
        const respUser = await fetch('../php/api/api_usuarios.php');
        const dataUser = await respUser.json();
        document.getElementById('stat-usuarios').textContent = dataUser.length || '0';

        // Busca quantidade de produtos únicos
        const respProd = await fetch('../php/api/api_produtos.php');
        const dataProd = await respProd.json();
        document.getElementById('stat-produtos').textContent = dataProd.length || '0';

    } catch (error) {
        console.error("Erro ao carregar dashboard:", error);
        // Em caso de erro, define como zero para não ficar o traço "-"
        document.querySelectorAll('.stat-number').forEach(el => el.textContent = '0');
    }
}