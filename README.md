# 🚀 V-ERP | Sistema de Gestão Integrada

<p align="center">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/TCPDF-FF6B6B?style=for-the-badge&logo=adobeacrobatreader&logoColor=white" alt="TCPDF" />
</p>

---

## 📝 Sobre o Projeto

O **V-ERP** é uma solução robusta para o Planejamento de Recursos Empresariais. Este sistema foi projetado para centralizar operações comerciais, oferecendo uma interface amigável e um backend eficiente para o controle de **estoque, compras, vendas, contabilidade e gestão de equipe** em tempo real.

O sistema conta com **duas áreas de acesso distintas**: um painel administrativo completo para gestores e uma loja virtual para clientes finais, integradas ao mesmo banco de dados e fluxo contábil.

> **Status do Projeto:** ✅ MVP funcional (Fase de Refinamento e Apresentação)

---

## ✨ Funcionalidades Principais

* **🔐 Controle de Acesso Multiperfil:** Login unificado com redirecionamento automático para área admin ou loja, conforme perfil do usuário.
* **📦 Gestão de Estoque:** Cadastro de produtos com SKU, preço de custo, preço de venda e margem de lucro calculada automaticamente.
* **🏭 Catálogo do Fornecedor:** Tela dedicada para registrar entradas de mercadoria, com atualização automática do estoque.
* **🛒 Loja Virtual com Carrinho:** Cliente pode adicionar múltiplos produtos ao carrinho antes de finalizar a compra.
* **📄 Geração de Nota Fiscal em PDF:** Comprovante automático após cada compra, com detalhamento de itens e dados do cliente.
* **📊 Dashboard Dinâmico:** KPIs em tempo real, gráficos de comparativo Compras vs Vendas, top produtos e estoque crítico.
* **📑 Relatório Contábil Completo (PDF):** Plano de Contas, Lançamentos (Razonete), Controle de Estoque por Custo Médio, DRE e Balanço Patrimonial.
* **💵 Tributação Automática:** Cálculo de Simples Nacional (10%) integrado aos lançamentos contábeis e DRE.

---

## ⚙️ Tecnologias Utilizadas

O projeto utiliza a stack web clássica com bibliotecas auxiliares para gráficos e PDFs:

1.  **Front-end:** Estruturação com HTML5 e estilização moderna com CSS3 (variáveis CSS, gradientes, grid layout).
2.  **Lógica de Client-side:** JavaScript Vanilla para manipulação do DOM, requisições assíncronas (fetch API) e gestão de estado com `localStorage`.
3.  **Visualização de Dados:** [Chart.js](https://www.chartjs.org/) via CDN para gráficos do dashboard.
4.  **Back-end:** PHP 8 com PDO (Prepared Statements) para processamento e segurança contra SQL Injection.
5.  **Geração de PDFs:** [TCPDF 6.x](https://tcpdf.org/) para notas fiscais e relatórios contábeis profissionais.
6.  **Banco de Dados:** MySQL (MariaDB via XAMPP) com Foreign Keys e transações ACID.

---

## 📈 Fluxo do Sistema

[ADMIN] cadastra produto novo no Estoque (com preço de custo + venda)

   ↓

[ADMIN] vai em Fornecedor e registra compra (estoque sobe automaticamente)

   ↓

[CLIENTE] acessa a Loja, adiciona produtos ao carrinho

   ↓

[CLIENTE] finaliza compra escolhendo forma de pagamento

   ↓

[SISTEMA] registra venda, baixa estoque, calcula imposto e gera Nota Fiscal em PDF

   ↓

[ADMIN] acompanha tudo no Dashboard e gera Relatório Contábil em PDF

---

## 📂 Estrutura do Projeto

Abaixo está a organização dos arquivos e pastas do repositório:

```text
v_erp/
│
├── 📄 index.html
├── 📄 README.md
│
├── 📁 css/
│   └── 📄 style.css
│
├── 📁 js/
│   ├── 📄 auth.js
│   ├── 📄 carrinho.js
│   ├── 📄 compras.js
│   ├── 📄 dashboard.js
│   ├── 📄 fornecedor.js
│   ├── 📄 loja.js
│   ├── 📄 login.js
│   ├── 📄 minhas-compras.js
│   ├── 📄 produtos.js
│   ├── 📄 sidebar.js
│   ├── 📄 usuarios.js
│   └── 📄 vendas.js
│
├── 📁 pages/
│   ├── 📄 carrinho.html
│   ├── 📄 compras.html
│   ├── 📄 dashboard.html
│   ├── 📄 fornecedor.html
│   ├── 📄 loja.html
│   ├── 📄 login.html
│   ├── 📄 minhas-compras.html
│   ├── 📄 produtos.html
│   ├── 📄 usuarios.html
│   └── 📄 vendas.html
│
├── 📁 php/
│   │
│   ├── 📁 api/
│   │   ├── 📄 api_alterar_status_produto.php
│   │   ├── 📄 api_cadastrar_cliente.php
│   │   ├── 📄 api_compras.php
│   │   ├── 📄 api_criar_compra.php
│   │   ├── 📄 api_criar_produto.php
│   │   ├── 📄 api_criar_usuario.php
│   │   ├── 📄 api_dashboard.php
│   │   ├── 📄 api_editar_produto.php
│   │   ├── 📄 api_excluir_usuario.php
│   │   ├── 📄 api_finalizar_carrinho.php
│   │   ├── 📄 api_gerar_contabilidade.php
│   │   ├── 📄 api_login.php
│   │   ├── 📄 api_minhas_compras.php
│   │   ├── 📄 api_nota_fiscal.php
│   │   ├── 📄 api_produtos.php
│   │   ├── 📄 api_registrar_venda.php
│   │   ├── 📄 api_sidebar.php
│   │   ├── 📄 api_usuarios.php
│   │   └── 📄 api_vendas.php
│   │
│   ├── 📁 contabilidade/
│   │   └── 📄 calculos.php
│   │
│   ├── 📁 db/
│   │   ├── 📄 carrinho_db.php
│   │   ├── 📄 compras_db.php
│   │   ├── 📄 conexao.php
│   │   ├── 📄 dashboard_db.php
│   │   ├── 📄 login_db.php
│   │   ├── 📄 produtos_db.php
│   │   ├── 📄 sidebar_db.php
│   │   ├── 📄 usuarios_db.php
│   │   └── 📄 vendas_db.php
│   │
│   └── 📦 tcpdf.zip         <-----       ⚠️ DESCOMPACTAR ANTES DE USAR!
│
└── 📁 sql/
    └── 📄 database.sql
```

> ⚠️ **Atenção sobre o TCPDF:** A biblioteca foi compactada (`tcpdf.zip`) para respeitar o limite de upload do GitHub. **É obrigatório descompactá-la** antes de rodar o sistema. Veja o passo 4 da seção de instalação abaixo.

---

## 🚀 Como Rodar na Sua Máquina

Siga o guia passo a passo para configurar o ambiente local de forma correta:

### 1. Pré-requisitos

Certifique-se de ter instalado um ambiente de servidor local que suporte PHP 8+ e MySQL:

- **XAMPP** (Recomendado) — https://www.apachefriends.org/
- **WampServer** — https://www.wampserver.com/

### 2. Download do Projeto

Navegue até a pasta raiz do seu servidor local (geralmente `C:/xampp/htdocs/`) e execute o comando abaixo no terminal:

```bash
git clone https://github.com/vinicius200019/v_erp.git
```

### 3. Configuração do Banco de Dados

1. Abra o painel de controle do seu servidor e inicie os módulos **Apache** e **MySQL**.

2. Acesse o gerenciador de banco de dados no seu navegador: `http://localhost/phpmyadmin/`.

3. Crie um novo banco de dados chamado **`v_erp`**.

4. Clique na aba **Importar**, selecione o arquivo `database.sql` que está dentro da pasta `/sql` do projeto e confirme.

### 4. ⚠️ Descompactar o TCPDF (passo obrigatório)

A biblioteca **TCPDF** (usada para gerar PDFs de notas fiscais e relatórios contábeis) foi compactada para respeitar o limite de upload do GitHub.

1. Navegue até a pasta `v_erp/php/`.

2. Localize o arquivo **`tcpdf.zip`**.

3. Descompacte-o **no mesmo local** (botão direito → Extrair Tudo...).

4. Garanta que a estrutura final fique assim:

```text
php/
├── tcpdf/              ← pasta descompactada
│   ├── tcpdf.php       ← arquivo principal
│   ├── fonts/
│   ├── include/
│   └── ... (demais arquivos)
```

5. Após descompactar, **você pode excluir o arquivo `tcpdf.zip`** para economizar espaço.

> 💡 **Sem essa etapa, a geração de PDFs (notas fiscais e relatórios contábeis) não funcionará.**

### 5. Execução do Sistema

Com tudo configurado, basta acessar o endereço abaixo no seu navegador:

```bash
http://localhost/v_erp/
```

### 6. Credenciais de Acesso de Teste

**Área Administrativa:**
- E-mail: `vini@erp.com`
- Senha: `123456`

**Área do Cliente (Loja):**
- Crie uma conta clicando em **"Criar Conta"** na tela de login, ou utilize uma conta existente do banco de dados.

---

## 🎯 Roteiro de Teste Completo

Para validar todas as funcionalidades, siga este fluxo:

1. **Login como Admin** → Acesse o Dashboard e visualize as métricas.
2. **Cadastre um produto** no Estoque (defina preço de custo e venda — a margem é calculada automaticamente).
3. **Vá em Fornecedor** e registre uma compra desse produto (o estoque será atualizado).
4. **Faça logout e crie uma conta de cliente** com CPF/CNPJ.
5. **Faça login como cliente** → Adicione produtos ao carrinho na Loja.
6. **Finalize a compra** → A nota fiscal será gerada automaticamente em PDF.
7. **Volte ao admin** → Verifique a venda em "Vendas" e clique em **"GERAR CONTABILIDADE"** no Dashboard.
8. **O PDF gerado conterá:** Plano de Contas, Razonete, Custo Médio, DRE e Balanço Patrimonial.

---

## ⌨️ Desenvolvedores

<strong>Vinícius</strong>
