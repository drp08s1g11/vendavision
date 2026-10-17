<x-filament-panels::page>
    <style>
        /* ==========================================================
           ESTILOS ADAPTATIVOS DE ALTO CONTRASTE (CLARO & ESCURO)
           ========================================================== */
        .sim-kpi-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .sim-kpi-card .card-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
        }
        .sim-kpi-card .card-sub {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        /* TABELA COMPARATIVA MULTI-CANAIS */
        .sim-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .sim-table th {
            padding: 12px 16px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .sim-table td {
            padding: 14px 16px;
            font-size: 0.8125rem;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease;
        }
        .sim-table tr:hover td {
            background-color: #f9fafb;
        }
        .sim-table .col-name {
            font-weight: 700;
            color: #111827;
        }
        .sim-table .col-info {
            color: #374151;
            font-weight: 500;
        }
        .sim-table .col-price {
            font-weight: 800;
            color: #b45309;
        }
        .sim-table .col-comm {
            font-weight: 600;
            color: #b91c1c;
        }
        .sim-table .col-profit {
            font-weight: 800;
            color: #047857;
            text-align: right;
        }

        /* ==========================================================
           TEMA ESCURO (.dark) - ALTO CONTRASTE E ZERO FADE
           ========================================================== */
        .dark .sim-kpi-card {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .sim-kpi-card .card-title {
            color: #9ca3af !important;
        }
        .dark .sim-kpi-card .card-sub {
            color: #d1d5db !important;
        }

        .dark .sim-box {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .sim-box h3 {
            color: #ffffff !important;
        }
        .dark .sim-box p {
            color: #9ca3af !important;
        }

        /* TABELA NO MODO ESCURO */
        .dark .sim-table-container {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .sim-table th {
            color: #9ca3af !important;
            border-bottom-color: #374151 !important;
            background-color: #111827 !important;
        }
        .dark .sim-table td {
            border-bottom-color: #1f2937 !important;
            background-color: transparent !important;
        }
        /* HOVER NO ESCURO: NUNCA FICA BRANCO! */
        .dark .sim-table tr:hover td {
            background-color: #1f2937 !important;
        }
        .dark .sim-table .col-name {
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .dark .sim-table .col-info {
            color: #d1d5db !important;
        }
        .dark .sim-table .col-price {
            color: #fbbf24 !important;
            font-weight: 800 !important;
        }
        .dark .sim-table .col-comm {
            color: #f87171 !important;
            font-weight: 700 !important;
        }
        .dark .sim-table .col-profit {
            color: #34d399 !important;
            font-weight: 800 !important;
        }
    </style>

    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php
        $calc = $this->calculationResults;
        $channels = $this->channelsComparison;
        $price = $calc['suggested_price'] > 0 ? $calc['suggested_price'] : 1;
        $costPct = round(($calc['cost_price'] / $price) * 100, 1);
        $shippingPct = round((($calc['shipping_cost'] + $calc['fixed_fee']) / $price) * 100, 1);
        $commPct = round(($calc['commission_amount'] / $price) * 100, 1);
        $taxPct = round(($calc['tax_amount'] / $price) * 100, 1);
        $profitPct = round(($calc['net_profit'] / $price) * 100, 1);
    @endphp

    {{-- Cards de Resultados em Destaque --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
        {{-- Preço Sugerido --}}
        <div class="sim-kpi-card">
            <span class="card-title">Preço de Venda Sugerido</span>
            <div class="mt-2 text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                R$ {{ number_format($calc['suggested_price'], 2, ',', '.') }}
            </div>
            <div class="card-sub flex items-center">
                <span class="font-bold text-amber-600 dark:text-amber-400 mr-1">Markup: {{ number_format($calc['markup'], 2, ',', '.') }}x</span>
                sobre o custo do fornecedor
            </div>
        </div>

        {{-- Lucro Líquido Estimado --}}
        <div class="sim-kpi-card">
            <span class="card-title">Lucro Líquido por Unidade</span>
            <div class="mt-2 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                R$ {{ number_format($calc['net_profit'], 2, ',', '.') }}
            </div>
            <div class="card-sub font-semibold text-emerald-600 dark:text-emerald-400">
                Margem Líquida Real: {{ number_format($calc['net_margin'], 1, ',', '.') }}%
            </div>
        </div>

        {{-- Ponto de Equilíbrio --}}
        <div class="sim-kpi-card">
            <span class="card-title">Ponto de Equilíbrio (Break-even)</span>
            <div class="mt-2 text-3xl font-extrabold text-blue-600 dark:text-blue-400">
                R$ {{ number_format($calc['break_even_price'], 2, ',', '.') }}
            </div>
            <div class="card-sub">
                Preço mínimo para lucro R$ 0,00 (sem prejuízo)
            </div>
        </div>

        {{-- Deduções Totais --}}
        <div class="sim-kpi-card">
            <span class="card-title">Taxas & Impostos Somados</span>
            <div class="mt-2 text-3xl font-extrabold text-rose-600 dark:text-rose-400">
                R$ {{ number_format($calc['commission_amount'] + $calc['tax_amount'], 2, ',', '.') }}
            </div>
            <div class="card-sub">
                Comissão: R$ {{ number_format($calc['commission_amount'], 2, ',', '.') }} | Impostos: R$ {{ number_format($calc['tax_amount'], 2, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Decomposição do Preço de Venda (Visual Breakdown) --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4 sim-box">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Decomposição de Cada R$ 100 Faturados
        </h3>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden flex shadow-inner">
            <div class="bg-indigo-500 h-4" style="width: {{ min(100, max(0, $costPct)) }}%" title="Custo Fornecedor: {{ $costPct }}%"></div>
            <div class="bg-sky-500 h-4" style="width: {{ min(100, max(0, $shippingPct)) }}%" title="Frete/Taxas: {{ $shippingPct }}%"></div>
            <div class="bg-amber-500 h-4" style="width: {{ min(100, max(0, $commPct)) }}%" title="Comissão Marketplace: {{ $commPct }}%"></div>
            <div class="bg-rose-500 h-4" style="width: {{ min(100, max(0, $taxPct)) }}%" title="Impostos: {{ $taxPct }}%"></div>
            <div class="bg-emerald-500 h-4" style="width: {{ min(100, max(0, $profitPct)) }}%" title="Lucro Líquido: {{ $profitPct }}%"></div>
        </div>
        <div class="mt-3 flex flex-wrap gap-4 text-xs">
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500"></span><span class="text-gray-700 dark:text-gray-300">Fornecedor: <strong>{{ $costPct }}%</strong> (R$ {{ number_format($calc['cost_price'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-sky-500"></span><span class="text-gray-700 dark:text-gray-300">Envio/Taxa Fixa: <strong>{{ $shippingPct }}%</strong> (R$ {{ number_format($calc['shipping_cost'] + $calc['fixed_fee'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-gray-700 dark:text-gray-300">Marketplace: <strong>{{ $commPct }}%</strong> (R$ {{ number_format($calc['commission_amount'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span><span class="text-gray-700 dark:text-gray-300">Impostos: <strong>{{ $taxPct }}%</strong> (R$ {{ number_format($calc['tax_amount'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span class="text-gray-700 dark:text-gray-300">Lucro Líquido: <strong>{{ $profitPct }}%</strong> (R$ {{ number_format($calc['net_profit'], 2, ',', '.') }})</span></div>
        </div>
    </div>

    {{-- Matriz Comparativa Multi-Canais (Alto Contraste) --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4 sim-box sim-table-container">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                    Comparativo Multi-Marketplaces (Mesma Margem Desejada de {{ $this->data['target_margin'] ?? 25 }}%)
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Preço necessário a ser praticado em cada plataforma para manter rigorosamente o mesmo lucro de bolso.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="sim-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">Canal de Venda</th>
                        <th style="width: 14%;">Comissão Base</th>
                        <th style="width: 14%;">Taxa Fixa</th>
                        <th style="width: 18%;">Preço Sugerido</th>
                        <th style="width: 14%;">Comissão Paga</th>
                        <th style="width: 12%; text-align: right;">Lucro no Bolso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($channels as $c)
                        <tr>
                            <td class="col-name flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                {{ $c['name'] }}
                            </td>
                            <td class="col-info">{{ $c['commission_percent'] }}</td>
                            <td class="col-info">R$ {{ number_format($c['fixed_fee'], 2, ',', '.') }}</td>
                            <td class="col-price">
                                R$ {{ number_format($c['suggested_price'], 2, ',', '.') }}
                            </td>
                            <td class="col-comm">
                                - R$ {{ number_format($c['commission_amount'], 2, ',', '.') }}
                            </td>
                            <td class="col-profit">
                                R$ {{ number_format($c['net_profit'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
