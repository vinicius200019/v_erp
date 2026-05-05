/**
 * js/sidebar.js - Carrega o menu superior dinamicamente
 */
document.addEventListener("DOMContentLoaded", async () => {
    const navUl = document.querySelector("nav ul");
    if (!navUl) return;

    try {
        const response = await fetch('/v_erp/php/api/api_sidebar.php');
        const menuItems = await response.json();

        // Limpa o menu atual (opcional)
        navUl.innerHTML = '';

        menuItems.forEach(item => {
            const li = document.createElement("li");
            // Ajusta o caminho se estiver dentro da pasta /pages/
            const prefixo = window.location.pathname.includes("/pages/") ? "../" : "";
            
            // Se o link já tiver "pages/", removemos para não duplicar se já estivermos na pasta
            let linkFinal = item.link;
            if (prefixo === "../" && linkFinal.includes("pages/")) {
                linkFinal = linkFinal.replace("pages/", "");
            }

            li.innerHTML = `<a href="${prefixo}${linkFinal}">${item.nome}</a>`;
            navUl.appendChild(li);
        });
    } catch (error) {
        console.error("Erro ao carregar menu:", error);
    }
});
