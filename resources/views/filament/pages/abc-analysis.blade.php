<x-filament-panels::page>
    <style>
        /* ==========================================================
           ESTILOS ADAPTATIVOS DE ALTO CONTRASTE (CLARO & ESCURO)
           ========================================================== */
        .abc-class-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .abc-class-card.border-a { border-left: 4px solid #10b981; }
        .abc-class-card.border-b { border-left: 4px solid #f59e0b; }
        .abc-class-card.border-c { border-left: 4px solid #9ca3af; }

        .abc-class-card .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .abc-class-card .card-revenue {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 0.75rem;
        }
        .abc-class-card .card-meta {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            justify-content: space-between;
            color: #6b7280;
        }
        .abc-class-card .card-strategy {
            font-size: 0.75rem;
            margin-top: 0.75rem;
            line-height: 1.4;
            color: #374151;
        }

        /* TABELA DE PRODUTOS PARETO */
        .abc-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .abc-table th {
            padding: 12px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .abc-table td {
            padding: 12px 12px;
            font-size: 0.8125rem;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease;
        }
        .abc-table tr:hover td {
            background-color: #f9fafb;
        }

        .abc-badge-a {
            display: inline-block;
            padding: 2px 8px;
            font-weight: 800;
            font-size: 0.75rem;
            border-radius: 4px;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .abc-badge-b {
            display: inline-block;
            padding: 2px 8px;
            font-weight: 800;
            font-size: 0.75rem;
            border-radius: 4px;
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .abc-badge-c {
            display: inline-block;
            padding: 2px 8px;
            font-weight: 800;
            font-size: 0.75rem;
            border-radius: 4px;
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        /* ==========================================================
           TEMA ESCURO (.dark) - ALTO CONTRASTE E ZERO FADE
           ========================================================== */
        .dark .abc-class-card {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .abc-class-card.border-a { border-left-color: #10b981 !important; }
        .dark .abc-class-card.border-b { border-left-color: #f59e0b !important; }
        .dark .abc-class-card.border-c { border-left-color: #9ca3af !important; }

        .dark .abc-class-card .card-meta {
            color: #9ca3af !important;
        }
        .dark .abc-class-card .card-strategy {
            color: #d1d5db !important;
        }
        .dark .abc-class-card .card-strategy strong {
            color: #ffffff !important;
        }

        /* Banner de explicação no escuro */
        .dark .abc-banner {
            background-color: rgba(245, 158, 11, 0.1) !important;
            border-color: rgba(245, 158, 11, 0.3) !important;
            color: #fef3c7 !important;
        }
        .dark .abc-banner strong {
            color: #ffffff !important;
        }

        /* Card do gráfico no escuro */
        .dark .abc-box {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .abc-box h3 {
            color: #ffffff !important;
        }

        /* TABELA NO ESCURO */
        .dark .abc-table-container {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .abc-table th {
            color: #9ca3af !important;
            border-bottom-color: #374151 !important;
            background-color: #111827 !important;
        }
        .dark .abc-table td {
            border-bottom-color: #1f2937 !important;
            background-color: transparent !important;
        }
        /* HOVER NO ESCURO: NUNCA FICA BRANCO! */
        .dark .abc-table tr:hover td {
            background-color: #1f2937 !important;
        }
        .dark .abc-table .prod-name {
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .dark .abc-table .prod-cat {
            color: #9ca3af !important;
        }
        .dark .abc-table .prod-sku {
            color: #fbbf24 !important;
        }
        .dark .abc-table .prod-rev {
            color: #ffffff !important;
            font-weight: 800 !important;
        }
        .dark .abc-table .prod-profit {
            color: #34d399 !important;
            font-weight: 800 !important;
        }
        .dark .abc-table .prod-margin {
            color: #93c5fd !important;
            font-weight: 700 !important;
        }
        .dark .abc-table .prod-pct {
            color: #f3f4f6 !important;
            font-weight: 600 !important;
        }
        .dark .abc-table .prod-acc {
            color: #fbbf24 !important;
            font-weight: 800 !important;
        }

        .dark .abc-badge-a {
            background-color: #064e3b !important;
            color: #a7f3d0 !important;
            border-color: #059669 !important;
        }
        .dark .abc-badge-b {
            background-color: #78350f !important;
            color: #fde68a !important;
            border-color: #d97706 !important;
        }
        .dark .abc-badge-c {
            background-color: #1f2937 !important;
            color: #9ca3af !important;
            border-color: #374151 !important;
        }
    </style>

    @php
        $data = $this->abcData;
        $totalRev = $data['total_revenue'] > 0 ? $data['total_revenue'] : 1;
        $pctA = round(($data['class_a']['revenue'] / $totalRev) * 100, 1);
        $pctB = round(($data['class_b']['revenue'] / $totalRev) * 100, 1);
        $pctC = round(($data['class_c']['revenue'] / $totalRev) * 100, 1);
    @endphp

    {{-- Explicação Metodológica --}}
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-3 abc-banner">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <span class="font-bold">O que é a Curva ABC (Princípio de Pareto)?</span>
            Metodologia consagrada em Ciência de Dados e Logística que classifica os produtos pelo impacto real no faturamento:
            <strong>Classe A</strong> (80% da receita — itens vitais), <strong>Classe B</strong> (15% — intermediários) e <strong>Classe C</strong> (5% — cauda longa/baixo giro).
        </div>
    </div>

    {{-- Cards das Classes ABC --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
        {{-- Classe A --}}
        <div class="abc-class-card border-a">
            <div class="card-head">
                <span class="abc-badge-a">CLASSE A • Vital (80%)</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-bold">{{ $data['class_a']['count'] }} produtos</span>
            </div>
            <div class="card-revenue text-emerald-600 dark:text-emerald-400">
                R$ {{ number_format($data['class_a']['revenue'], 2, ',', '.') }}
            </div>
            <div class="card-meta">
                <span>{{ $pctA }}% do faturamento</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Lucro: R$ {{ number_format($data['class_a']['profit'], 2, ',', '.') }}</span>
            </div>
            <p class="card-strategy">
                <strong>Estratégia:</strong> Estoque prioritário com o fornecedor. Anúncios em destaque no Mercado Livre e Shopee.
            </p>
        </div>

        {{-- Classe B --}}
        <div class="abc-class-card border-b">
            <div class="card-head">
                <span class="abc-badge-b">CLASSE B • Intermediário (15%)</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-bold">{{ $data['class_b']['count'] }} produtos</span>
            </div>
            <div class="card-revenue text-amber-600 dark:text-amber-400">
                R$ {{ number_format($data['class_b']['revenue'], 2, ',', '.') }}
            </div>
            <div class="card-meta">
                <span>{{ $pctB }}% do faturamento</span>
                <span class="font-bold text-amber-600 dark:text-amber-400">Lucro: R$ {{ number_format($data['class_b']['profit'], 2, ',', '.') }}</span>
            </div>
            <p class="card-strategy">
                <strong>Estratégia:</strong> Monitorar margens e criar kits promocionais para migrar produtos para a Classe A.
            </p>
        </div>

        {{-- Classe C --}}
        <div class="abc-class-card border-c">
            <div class="card-head">
                <span class="abc-badge-c">CLASSE C • Cauda Longa (5%)</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-bold">{{ $data['class_c']['count'] }} produtos</span>
            </div>
            <div class="card-revenue text-gray-700 dark:text-gray-200">
                R$ {{ number_format($data['class_c']['revenue'], 2, ',', '.') }}
            </div>
            <div class="card-meta">
                <span>{{ $pctC }}% do faturamento</span>
                <span class="font-bold text-gray-600 dark:text-gray-300">Lucro: R$ {{ number_format($data['class_c']['profit'], 2, ',', '.') }}</span>
            </div>
            <p class="card-strategy">
                <strong>Estratégia:</strong> Avaliar exclusão de produtos sem giro ou ajustar markup para não gerar custo de catálogo.
            </p>
        </div>
    </div>

    {{-- Barra Visual de Distribuição Pareto --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4 abc-box">
        <h3 class="text-xs font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider mb-2">
            Distribuição da Receita Consolidada por Classe
        </h3>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden flex shadow-inner">
            <div class="bg-emerald-500 h-4" style="width: {{ $pctA }}%" title="Classe A: {{ $pctA }}%"></div>
            <div class="bg-amber-500 h-4" style="width: {{ $pctB }}%" title="Classe B: {{ $pctB }}%"></div>
            <div class="bg-gray-400 h-4" style="width: {{ $pctC }}%" title="Classe C: {{ $pctC }}%"></div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
            <div class="flex gap-4">
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Classe A: <strong>{{ $pctA }}%</strong></span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Classe B: <strong>{{ $pctB }}%</strong></span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span> Classe C: <strong>{{ $pctC }}%</strong></span>
            </div>
            <span>Total Faturado no Período: <strong class="text-gray-900 dark:text-white">R$ {{ number_format($data['total_revenue'], 2, ',', '.') }}</strong></span>
        </div>
    </div>

    {{-- Tabela de Produtos Classificados (Alto Contraste) --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4 abc-box abc-table-container">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                    Ranking Detalhado do Catálogo por Contribuição
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Produtos ordenados por representatividade na geração de caixa da Ambrozio Decora.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="abc-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 8%;">Classe</th>
                        <th style="width: 12%;">SKU</th>
                        <th style="width: 28%;">Produto</th>
                        <th style="width: 8%; text-align: center;">Volume</th>
                        <th style="width: 12%;">Faturamento</th>
                        <th style="width: 10%;">Lucro Real</th>
                        <th style="width: 6%; text-align: center;">Margem</th>
                        <th style="width: 6%; text-align: right;">% Receita</th>
                        <th style="width: 6%; text-align: right;">% Acumulada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['products'] as $p)
                        @php
                            $badgeClass = match($p['class']) {
                                'A' => 'abc-badge-a',
                                'B' => 'abc-badge-b',
                                default => 'abc-badge-c',
                            };
                        @endphp
                        <tr>
                            <td class="font-bold text-gray-400 dark:text-gray-500 text-xs">{{ $p['rank'] }}º</td>
                            <td>
                                <span class="{{ $badgeClass }}">{{ $p['class'] }}</span>
                            </td>
                            <td class="prod-sku font-mono text-xs font-bold">{{ $p['sku'] }}</td>
                            <td>
                                <div class="prod-name">{{ $p['name'] }}</div>
                                <div class="prod-cat text-[11px]">{{ $p['category'] }}</div>
                            </td>
                            <td class="text-center font-bold text-gray-700 dark:text-gray-200">{{ $p['total_quantity'] }} un</td>
                            <td class="prod-rev">
                                R$ {{ number_format($p['total_revenue'], 2, ',', '.') }}
                            </td>
                            <td class="prod-profit">
                                R$ {{ number_format($p['total_profit'], 2, ',', '.') }}
                            </td>
                            <td class="prod-margin text-center text-xs">
                                {{ number_format($p['margin'], 1, ',', '.') }}%
                            </td>
                            <td class="prod-pct text-right text-xs">
                                {{ number_format($p['percentage'], 1, ',', '.') }}%
                            </td>
                            <td class="prod-acc text-right font-mono text-xs">
                                {{ number_format($p['accumulated'], 1, ',', '.') }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-gray-400 dark:text-gray-500">
                                Nenhuma venda com status Pago ou Enviado registrada para cálculo da Curva ABC.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
