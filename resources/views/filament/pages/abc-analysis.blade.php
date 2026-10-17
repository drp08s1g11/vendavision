<x-filament-panels::page>
    @php
        $data = $this->abcData;
        $totalRev = $data['total_revenue'] > 0 ? $data['total_revenue'] : 1;
        $pctA = round(($data['class_a']['revenue'] / $totalRev) * 100, 1);
        $pctB = round(($data['class_b']['revenue'] / $totalRev) * 100, 1);
        $pctC = round(($data['class_c']['revenue'] / $totalRev) * 100, 1);
    @endphp

    {{-- Explicação Metodológica --}}
    <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-200 flex items-start gap-3">
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
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border-l-4 border-l-emerald-500 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                    CLASSE A • Vital (80%)
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $data['class_a']['count'] }} produtos</span>
            </div>
            <div class="mt-3 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                R$ {{ number_format($data['class_a']['revenue'], 2, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                <span>{{ $pctA }}% do faturamento</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Lucro: R$ {{ number_format($data['class_a']['profit'], 2, ',', '.') }}</span>
            </div>
            <p class="mt-3 text-[11px] text-gray-600 dark:text-gray-400 leading-tight">
                <strong>Estratégia:</strong> Estoque prioritário com o fornecedor. Anúncios em destaque no Mercado Livre e Shopee.
            </p>
        </div>

        {{-- Classe B --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border-l-4 border-l-amber-500 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">
                    CLASSE B • Intermediário (15%)
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $data['class_b']['count'] }} produtos</span>
            </div>
            <div class="mt-3 text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                R$ {{ number_format($data['class_b']['revenue'], 2, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                <span>{{ $pctB }}% do faturamento</span>
                <span class="font-semibold text-amber-600 dark:text-amber-400">Lucro: R$ {{ number_format($data['class_b']['profit'], 2, ',', '.') }}</span>
            </div>
            <p class="mt-3 text-[11px] text-gray-600 dark:text-gray-400 leading-tight">
                <strong>Estratégia:</strong> Monitorar margens e criar kits promocionais para migrar produtos para a Classe A.
            </p>
        </div>

        {{-- Classe C --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border-l-4 border-l-gray-400 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    CLASSE C • Cauda Longa (5%)
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $data['class_c']['count'] }} produtos</span>
            </div>
            <div class="mt-3 text-2xl font-extrabold text-gray-700 dark:text-gray-300">
                R$ {{ number_format($data['class_c']['revenue'], 2, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                <span>{{ $pctC }}% do faturamento</span>
                <span class="font-semibold text-gray-600 dark:text-gray-400">Lucro: R$ {{ number_format($data['class_c']['profit'], 2, ',', '.') }}</span>
            </div>
            <p class="mt-3 text-[11px] text-gray-600 dark:text-gray-400 leading-tight">
                <strong>Estratégia:</strong> Avaliar exclusão de produtos sem giro ou ajustar markup para não gerar custo de catálogo.
            </p>
        </div>
    </div>

    {{-- Barra Visual de Distribuição Pareto --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4">
        <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
            Distribuição da Receita Consolidada por Classe
        </h3>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden flex shadow-inner">
            <div class="bg-emerald-500 h-4" style="width: {{ $pctA }}%" title="Classe A: {{ $pctA }}%"></div>
            <div class="bg-amber-500 h-4" style="width: {{ $pctB }}%" title="Classe B: {{ $pctB }}%"></div>
            <div class="bg-gray-400 h-4" style="width: {{ $pctC }}%" title="Classe C: {{ $pctC }}%"></div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <div class="flex gap-4">
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Classe A: <strong>{{ $pctA }}%</strong></span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Classe B: <strong>{{ $pctB }}%</strong></span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span> Classe C: <strong>{{ $pctC }}%</strong></span>
            </div>
            <span>Total Faturado no Período: <strong>R$ {{ number_format($data['total_revenue'], 2, ',', '.') }}</strong></span>
        </div>
    </div>

    {{-- Tabela de Produtos Classificados --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                    Ranking Detalhado do Catálogo por Contribuição
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Produtos ordenados por representatividade na geração de caixa da Ambrozio Decora.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">Classe</th>
                        <th class="py-3 px-3">SKU</th>
                        <th class="py-3 px-4">Produto</th>
                        <th class="py-3 px-3 text-center">Volume</th>
                        <th class="py-3 px-3">Faturamento</th>
                        <th class="py-3 px-3">Lucro Real</th>
                        <th class="py-3 px-3 text-center">Margem</th>
                        <th class="py-3 px-3 text-right">% Receita</th>
                        <th class="py-3 px-3 text-right">% Acumulada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($data['products'] as $p)
                        @php
                            $badgeColor = match($p['class']) {
                                'A' => 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
                                'B' => 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                                default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3 font-bold text-gray-400 text-xs">{{ $p['rank'] }}º</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded border {{ $badgeColor }}">
                                    {{ $p['class'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-xs font-mono text-gray-500 dark:text-gray-400">{{ $p['sku'] }}</td>
                            <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">
                                <div>{{ $p['name'] }}</div>
                                <div class="text-[11px] text-gray-400">{{ $p['category'] }}</div>
                            </td>
                            <td class="py-3 px-3 text-center font-semibold text-gray-700 dark:text-gray-300">{{ $p['total_quantity'] }} un</td>
                            <td class="py-3 px-3 font-bold text-gray-900 dark:text-gray-100">
                                R$ {{ number_format($p['total_revenue'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-emerald-600 dark:text-emerald-400">
                                R$ {{ number_format($p['total_profit'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center text-xs font-bold text-gray-600 dark:text-gray-300">
                                {{ number_format($p['margin'], 1, ',', '.') }}%
                            </td>
                            <td class="py-3 px-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">
                                {{ number_format($p['percentage'], 1, ',', '.') }}%
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-xs font-bold text-amber-600 dark:text-amber-400">
                                {{ number_format($p['accumulated'], 1, ',', '.') }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-gray-400">
                                Nenhuma venda com status Pago ou Enviado registrada para cálculo da Curva ABC.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
