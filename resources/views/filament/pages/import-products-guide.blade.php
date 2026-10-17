<x-filament-panels::page>
    {{-- Banner Informativo com Ações Rápidas --}}
    <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Migração Facilitada do Catálogo Antigo
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-2xl">
                    Para evitar erros e garantir que o sistema processe 100% dos seus produtos sem rejeição, disponibilizamos a planilha modelo oficial com os nomes das colunas e formatações exatas.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button
                    wire:click="downloadSampleCsv"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-all"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Baixar Planilha Modelo (.CSV)
                </button>
            </div>
        </div>
    </div>

    {{-- Passo a Passo Ilustrado --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-3">1</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">Baixe o Modelo</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Clique no botão acima para baixar o arquivo com o cabeçalho padronizado e exemplos reais da Ambrozio Decora.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-3">2</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">Preencha os Dados</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Abra no Excel ou Google Sheets. Copie e cole os produtos das suas planilhas antigas nas colunas correspondentes.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-3">3</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">Salve em CSV</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                No Excel, use <em>Salvar Como > CSV UTF-8 (delimitado por vírgulas ou ponto e vírgula)</em> para preservar acentos.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center justify-center text-sm mb-3">4</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">Faça o Upload</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Clique no botão <strong>Fazer Upload da Planilha</strong> no topo desta página. O sistema processa e atualiza a base!
            </p>
        </div>
    </div>

    {{-- Dicionário Detalhado das Colunas --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-2">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                Dicionário de Dados das Colunas da Planilha
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Utilize exatamente os nomes das colunas abaixo na primeira linha da sua planilha.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                        <th class="py-3 px-3">Nome da Coluna</th>
                        <th class="py-3 px-3 text-center">Obrigatória?</th>
                        <th class="py-3 px-3">Tipo de Dado</th>
                        <th class="py-3 px-4">Regra de Formatação</th>
                        <th class="py-3 px-4">Exemplo Válido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">sku</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Texto (até 64)</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Código identificador único. <strong>Se o SKU já existir, o sistema atualiza o produto sem duplicá-lo.</strong>
                        </td>
                        <td class="py-3 px-4 font-mono text-gray-500 dark:text-gray-400">AMB-QD-010</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">name</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Texto (até 255)</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Nome completo e comercial do produto anunciado na loja.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">Quadro Canvas Abstrato Dourado 60x90cm</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">category</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Texto</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Categoria para organização e filtros gerenciais da loja.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">Quadros e Telas</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">cost_price</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Decimal (R$)</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Custo cobrado pelo fornecedor dropshipping. <strong>Apenas números</strong> (ex: 58.00 ou 58,00). Não colocar "R$".
                        </td>
                        <td class="py-3 px-4 font-mono text-emerald-600 dark:text-emerald-400">58.00</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">sale_price</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Decimal (R$)</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Preço de venda anunciado ao consumidor final. Apenas números. Não colocar "R$".
                        </td>
                        <td class="py-3 px-4 font-mono text-emerald-600 dark:text-emerald-400">169.90</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">stock_reference</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Texto</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Nome, cidade ou contato do fabricante parceiro de dropshipping.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">Arte Quadros SP</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">description</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Texto Longo</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Dimensões, especificações de materiais ou detalhes do acabamento.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">Chassi de madeira reflorestada 3cm</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">status</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">Booleano (1 ou 0)</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            Informe <strong>1</strong> para Ativo (disponível para venda) ou <strong>0</strong> para Inativo.
                        </td>
                        <td class="py-3 px-4 font-mono text-gray-600 dark:text-gray-400">1</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Dicas e Boas Práticas contra Frustração do Usuário --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase flex items-center gap-2">
                <span class="text-emerald-500 font-bold">✔</span> Atualização Inteligente (Sem Duplicidades)
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                Você pode reenviar a mesma planilha várias vezes com novos preços! O sistema utiliza o campo <strong>sku</strong> como chave única: se o produto já existir na loja, ele apenas atualiza o custo e o preço de venda sem criar cópias repetidas.
            </p>
        </div>

        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase flex items-center gap-2">
                <span class="text-amber-500 font-bold">💡</span> Cuidado com o símbolo "R$"
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                Nas colunas <strong>cost_price</strong> e <strong>sale_price</strong>, preencha apenas o número puro (ex: <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">68.50</code>). Não escreva textos ou o símbolo de moeda dentro da célula, para que o sistema consiga calcular as margens com precisão matemática.
            </p>
        </div>
    </div>
</x-filament-panels::page>
