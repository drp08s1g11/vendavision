# Venda Vision • Ambrozio Decora

> **Projeto Integrador em Computação IV (UNIVESP)**  
> **Curso:** Bacharelado em Engenharia da Computação / Ciência de Dados  
> **Grupo:** DRP08-S1-G11  
> **Parceiro / Estudo de Caso:** Ambrozio Decora (E-commerce & Dropshipping)  

---

## 1. Visão Geral do Projeto

O **Venda Vision** é uma plataforma web empresarial desenvolvida para solucionar o problema de controle descentralizado e precificação manual da **Ambrozio Decora**, empresa atuante no segmento de decoração e móveis operando sob o modelo de *dropshipping* em múltiplos *marketplaces* (Mercado Livre, Shopee, Amazon e Venda Direta).

A solução substitui planilhas manuais suscetíveis a inconsistências por uma arquitetura relacional robusta, fornecendo consolidação de receitas, custos de aquisição congelados por venda, apuração em tempo real de lucro líquido e inteligência comercial orientada a dados.

---

## 2. Pilares Arquiteturais & Stack Tecnológica

| Camada | Tecnologia Adotada | Justificativa Técnica |
| :--- | :--- | :--- |
| **Linguagem & Runtime** | **PHP 8.4** | Desempenho moderno, tipagem estrita e novos recursos sintáticos. |
| **Framework Web** | **Laravel 11.x** | Arquitetura MVC consolidada, Eloquent ORM, migrações versionadas e segurança nativa. |
| **Painel & UI Reativa** | **Filament v3 + Livewire** | Interface administrativa corporativa de alto padrão, SPA reativo e acessível. |
| **Banco de Dados** | **MySQL 8.4** | SGBD relacional ACID com integridade referencial estrita (`restrictOnDelete`). |
| **Infraestrutura** | **Docker + Laravel Sail** | Ambientes de desenvolvimento e produção conteinerizados e 100% reprodutíveis. |
| **Testes de Software** | **PHPUnit 11** | Suíte de testes automatizados com cobertura de regras de negócio, persistência e segurança. |

---

## 3. Módulos & Funcionalidades Entregues

### 📊 Dashboard Executivo & Inteligência de Dados
- **4 Cards de KPIs com Sparklines Reais:** Faturamento Consolidado, Lucro Líquido Real, Custos Fornecedor/Operação e Volume/Ticket Médio.
- **Gráfico de Evolução Diária:** Série temporal comparando Faturamento Bruto vs. Lucro Real vs. Custos.
- **Gráfico de Rosca de Canais:** Participação percentual de cada marketplace no faturamento da empresa.
- **Duplo Ranking Analítico:** Top 5 Produtos Mais Rentáveis (R$) vs. Top 5 Produtos em Volume de Giro (unidades).
- **Filtro Temporal Dinâmico:** Seletor retrátil com intervalo de datas (*Data Inicial* e *Data Final*) e filtro por canal de venda que recalcula todos os indicadores em tempo real.

### 🏷️ Gestão de Catálogo & Dropshipping
- Cadastro centralizado com separação de **Custo do Fornecedor** e **Preço de Venda Praticado**.
- **Cálculo instantâneo de margem bruta** no formulário de cadastro.
- **Importação em Lote via Planilha:** Upload de planilhas CSV/XLSX com correspondência automática de SKU e atualização em massa.
- **Proteção Referencial:** Produtos vinculados a vendas históricas não podem ser excluídos fisicamente (`restrictOnDelete`), garantindo rastreabilidade contábil.

### 🛒 Registro de Vendas & Snapshots Imutáveis
- Registro de vendas multicanal com fretes e comissões específicas de cada plataforma.
- **Histórico Congelado (*Snapshots*):** Ao concluir a venda, o sistema grava `unit_price`, `unit_cost` e subtotais no momento exato da transação, garantindo que reajustes futuros do fornecedor não adulterem o lucro de vendas passadas.
- **Exportação de Relatórios:** Geração sob demanda de planilhas detalhadas (CSV e Excel) para a contabilidade.

### 🧮 Simulador de Precificação & Margem Reversa
- Ferramenta dedicada para dropshipping: a partir do custo do fornecedor, frete, comissão do canal e impostos (Simples Nacional), calcula o **Preço Sugerido** para garantir a margem líquida desejada no bolso.
- Cálculo automático do **Ponto de Equilíbrio (*Break-even*)** e **Markup multiplicador**.
- **Matriz Comparativa Multi-Marketplaces:** Simulação simultânea do mesmo produto nos diferentes canais de venda (Mercado Livre Premium/Clássico, Shopee, Amazon, WhatsApp).

### 📈 Análise de Curva ABC (Princípio de Pareto)
- Classificação automatizada do catálogo em **Classe A** (80% da receita), **Classe B** (15%) e **Classe C** (5%).
- Tabela analítica com percentual de contribuição individual e acumulado.
- Recomendações estratégicas de estoque e anúncios para cada faixa de produtos.

---

## 4. Segurança da Informação & Conformidade LGPD

O sistema foi endurecido seguindo as diretrizes da **OWASP**, **ISO 27001** e **LGPD**:
1. **Conformidade LGPD:** Coleta e armazenamento mínimo necessário — sem dados sensíveis supérfluos de consumidores finais (sem guarda de CPF, endereços completos ou nomes desnecessários).
2. **Sessão Segura & Timeout de Inatividade:** Sessão expira automaticamente após 30 minutos de inatividade (`SESSION_LIFETIME=30`).
3. **Expiração ao Fechar o Navegador:** Cookie descartado imediatamente ao fechar abas/janelas (`SESSION_EXPIRE_ON_CLOSE=true`).
4. **Eliminação do "Lembrar de mim" Indeterminado:** Remoção de cookies persistentes de longa duração na tela de login administrativo.
5. **Bloqueio de Sessão Simultânea (*Single Active Session*):** Ao realizar um novo login, sessões anteriores do mesmo usuário são invalidadas no banco de dados e no guard, emitindo alerta visual de concorrência.
6. **Criptografia de Credenciais:** Cofre de integrações com armazenamento cifrado via `AES-256-CBC` e flag `SESSION_ENCRYPT=true`.
7. **Cabeçalhos de Segurança HTTP:** Injeção de `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy` e HSTS via middleware.
8. **Acessibilidade:** Suporte nativo ao seletor de Modo Escuro / Claro / Alto Contraste.

---

## 5. Como Executar o Projeto Localmente

### Pré-requisitos
- **Docker** e **Docker Compose** instalados (ou WSL2 no Windows).
- **Git** instalado.

### Passo a Passo

1. **Clonar o Repositório:**
   ```bash
   git clone <URL_DO_REPOSITORIO>
   cd venda-vision
   ```

2. **Configurar as Variáveis de Ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Subir os Contêineres Docker via Laravel Sail:**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Gerar a Chave da Aplicação:**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

5. **Executar as Migrações e Popular a Base de Dados:**
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

6. **Acessar a Aplicação:**
   - URL: **[http://localhost/admin](http://localhost/admin)**
   - **E-mail:** `admin@ambrozio.com.br`
   - **Senha:** `password`

---

## 6. Verificação e Testes de Software

O projeto conta com uma suíte abrangente de testes automatizados unitários e funcionais (Feature/Unit). Para executar:

```bash
./vendor/bin/sail test
```

### Resultados da Suíte de Testes (100% Green):
- `Tests\Unit\SaleCalculationTest`: Validação dos cálculos matemáticos de margem de produto e totais de venda.
- `Tests\Feature\BusinessRulesTest`: Criação de produtos, integridade relacional, cálculo dinâmico de lucro e persistência de itens.
- `Tests\Feature\SecuritySessionTest`: Verificação de configurações de segurança, ausência de cookies persistentes de 5 anos e bloqueio de logins simultâneos.
- `Tests\Feature\PricingSimulatorTest`: Renderização e validação das fórmulas de markup e ponto de equilíbrio.
- `Tests\Feature\AbcAnalysisTest`: Classificação de Pareto e precisão do faturamento acumulado.
- `Tests\Feature\ExampleTest`: Redirecionamento seguro da rota raiz para o painel administrativo.

---

## 7. Rastreabilidade com os Objetivos do Projeto Integrador

| Requisito do Relatório Parcial | Situação no Código | Evidência / Componente |
| :--- | :---: | :--- |
| **Substituição de planilhas manuais** | Concluído | Migrações `products`, `sales`, `sale_items` em MySQL 8.4 |
| **Cálculo automatizado de lucro e margem** | Concluído | Modelos `Sale`, `SaleItem` e `SaleResource` |
| **Registro de custos e dropshipping** | Concluído | Snapshots históricos em `sale_items` |
| **Exportação de relatórios gerenciais** | Concluído | `SaleExporter` (CSV / XLSX) |
| **Importação de dados legado** | Concluído | `ProductImporter` (Upload em lote) |
| **Inteligência de Vendas e Pareto** | Concluído | `AbcAnalysis` e `PricingSimulator` |
| **Testes de software automatizados** | Concluído | 11 testes e 38 asserções no PHPUnit |
| **Acessibilidade de interface** | Concluído | Tema Escuro/Claro nativo em `AdminPanelProvider` |
| **Conformidade com a LGPD** | Concluído | Minimização de dados pessoais e segurança da informação |
