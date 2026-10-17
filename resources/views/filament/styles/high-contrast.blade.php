<style>
    /* ==========================================================
       VENDA VISION - TEMA DE ALTO CONTRASTE E LEGIBILIDADE MÁXIMA
       Aplica-se ao Dashboard (http://localhost/admin) e a todo o painel
       ========================================================== */

    /* ----------------------------------------------------------
       MODO ESCURO (.dark) - FIM DO FADE E ALTO CONTRASTE WCAG AAA
       ---------------------------------------------------------- */

    /* 1. SEÇÕES E FILTROS DO DASHBOARD */
    .dark .fi-section {
        background-color: #111827 !important;
        border: 1px solid #374151 !important;
    }
    .dark .fi-section-header-heading {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .dark .fi-section-header-description {
        color: #d1d5db !important; /* Cinza claríssimo nítido - SEM FADE! */
        font-weight: 500 !important;
    }

    /* 2. STATS OVERVIEW CARDS (4 KPIs SUPERIORES) */
    .dark .fi-wi-stats-overview-stat {
        background-color: #111827 !important;
        border: 1px solid #374151 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
    }
    .dark .fi-wi-stats-overview-stat-label {
        color: #e5e7eb !important; /* Rótulo em cinza claro com alto contraste */
        font-weight: 700 !important;
    }
    .dark .fi-wi-stats-overview-stat-value {
        color: #ffffff !important; /* Valores numéricos em branco puro */
        font-weight: 800 !important;
    }
    .dark .fi-wi-stats-overview-stat-description {
        color: #d1d5db !important; /* Fim do fade nas descrições de KPIs */
        font-weight: 500 !important;
    }

    /* 3. GRÁFICOS (EVOLUÇÃO DIÁRIA & CANAIS) */
    .dark .fi-wi-chart {
        background-color: #111827 !important;
        border: 1px solid #374151 !important;
    }
    .dark .fi-wi-chart-heading {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    /* 4. TABELAS DE RANKING (TOP PRODUTOS & TOP VOLUME) */
    .dark .fi-ta-ctn {
        background-color: #111827 !important;
        border: 1px solid #374151 !important;
    }
    .dark .fi-ta-header-heading {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .dark .fi-ta-header-cell-label {
        color: #9ca3af !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
    }
    .dark .fi-ta-row {
        border-bottom: 1px solid #1f2937 !important;
    }
    /* HOVER DA TABELA NO ESCURO: Jamais fica branco! Fica num cinza escuro elegante */
    .dark .fi-ta-row:hover,
    .dark .fi-ta-row:hover td {
        background-color: #1f2937 !important;
    }
    /* Nome do produto na tabela */
    .dark .fi-ta-cell .fi-ta-text-item {
        color: #ffffff !important;
        font-weight: 600 !important;
    }
    /* SKU e descrições de linha: Dourado vibrante / cinza claro sem fade */
    .dark .fi-ta-record-description {
        color: #fbbf24 !important; /* SKU em dourado âmbar nítido */
        font-weight: 600 !important;
    }

    /* ----------------------------------------------------------
       TEMA CLARO - PRETO/CINZA ESCURO DE ALTO CONTRASTE
       ---------------------------------------------------------- */
    .fi-section-header-description {
        color: #374151;
    }
    .fi-wi-stats-overview-stat-label {
        color: #374151;
        font-weight: 700;
    }
    .fi-wi-stats-overview-stat-description {
        color: #4b5563;
    }
    .fi-ta-record-description {
        color: #b45309; /* Âmbar escuro legível */
        font-weight: 600;
    }
</style>
