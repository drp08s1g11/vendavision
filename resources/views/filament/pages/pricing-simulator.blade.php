<x-filament-panels::page>
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
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Preço de Venda Sugerido</span>
            <div class="mt-2 text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                R$ {{ number_format($calc['suggested_price'], 2, ',', '.') }}
            </div>
            <div class="mt-1 flex items-center text-xs text-gray-500 dark:text-gray-400">
                <span class="font-medium text-amber-600 dark:text-amber-400 mr-1">Markup: {{ number_format($calc['markup'], 2, ',', '.') }}x</span>
                sobre o custo do fornecedor
            </div>
        </div>

        {{-- Lucro Líquido Estimado --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lucro Líquido por Unidade</span>
            <div class="mt-2 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                R$ {{ number_format($calc['net_profit'], 2, ',', '.') }}
            </div>
            <div class="mt-1 flex items-center text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                Margem Líquida Real: {{ number_format($calc['net_margin'], 1, ',', '.') }}%
            </div>
        </div>

        {{-- Ponto de Equilíbrio --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ponto de Equilíbrio (Break-even)</span>
            <div class="mt-2 text-3xl font-extrabold text-blue-600 dark:text-blue-400">
                R$ {{ number_format($calc['break_even_price'], 2, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Preço mínimo para lucro R$ 0,00 (sem prejuízo)
            </div>
        </div>

        {{-- Deduções Totais --}}
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Taxas & Impostos Somados</span>
            <div class="mt-2 text-3xl font-extrabold text-rose-600 dark:text-rose-400">
                R$ {{ number_format($calc['commission_amount'] + $calc['tax_amount'], 2, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Comissão: R$ {{ number_format($calc['commission_amount'], 2, ',', '.') }} | Impostos: R$ {{ number_format($calc['tax_amount'], 2, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Decomposição do Preço de Venda (Visual Breakdown) --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4">
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
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
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500"></span><span class="text-gray-600 dark:text-gray-400">Fornecedor: <strong>{{ $costPct }}%</strong> (R$ {{ number_format($calc['cost_price'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-sky-500"></span><span class="text-gray-600 dark:text-gray-400">Envio/Taxa Fixa: <strong>{{ $shippingPct }}%</strong> (R$ {{ number_format($calc['shipping_cost'] + $calc['fixed_fee'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-gray-600 dark:text-gray-400">Marketplace: <strong>{{ $commPct }}%</strong> (R$ {{ number_format($calc['commission_amount'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span><span class="text-gray-600 dark:text-gray-400">Impostos: <strong>{{ $taxPct }}%</strong> (R$ {{ number_format($calc['tax_amount'], 2, ',', '.') }})</span></div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span class="text-gray-600 dark:text-gray-400">Lucro Líquido: <strong>{{ $profitPct }}%</strong> (R$ {{ number_format($calc['net_profit'], 2, ',', '.') }})</span></div>
        </div>
    </div>

    {{-- Matriz Comparativa Multi-Canais --}}
    <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">
                    Comparativo Multi-Marketplaces (Mesma Margem Desejada de {{ $this->data['target_margin'] ?? 25 }}%)
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Preço necessário a ser praticado em cada plataforma para manter rigorosamente o mesmo lucro de bolso.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                        <th class="py-3 px-4">Canal de Venda</th>
                        <th class="py-3 px-4">Comissão Base</th>
                        <th class="py-3 px-4">Taxa Fixa</th>
                        <th class="py-3 px-4">Preço Sugerido</th>
                        <th class="py-3 px-4">Comissão Paga</th>
                        <th class="py-3 px-4 text-right">Lucro no Bolso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($channels as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                {{ $c['name'] }}
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $c['commission_percent'] }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">R$ {{ number_format($c['fixed_fee'], 2, ',', '.') }}</td>
                            <td class="py-3 px-4 font-bold text-amber-600 dark:text-amber-400">
                                R$ {{ number_format($c['suggested_price'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-rose-600 dark:text-rose-400">
                                - R$ {{ number_format($c['commission_amount'], 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-600 dark:text-emerald-400 text-right">
                                R$ {{ number_format($c['net_profit'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
