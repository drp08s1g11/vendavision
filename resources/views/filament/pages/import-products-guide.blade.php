<x-filament-panels::page>
    {{-- Banner Informativo com Ações Rápidas --}}
    <div class="p-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Importação Simples do Catálogo (Ambrozio Decora)
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-2xl">
                    Para que você não perca tempo ajustando planilhas, nosso sistema aceita cabeçalhos <strong>100% em português simples</strong>. Baixe a planilha modelo de exemplo, preencha com as suas peças e suba diretamente!
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button
                    wire:click="downloadSampleCsv"
                    type="button"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold rounded-xl text-xs shadow-md transition-all"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Baixar Planilha Modelo Oficial (.CSV)
                </button>
            </div>
        </div>
    </div>

    {{-- Passo a Passo em 4 Etapas Simples --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-2">1</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">1. Baixe o Modelo</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Clique no botão laranja acima. A planilha já vem com as colunas certinhas em português e peças de exemplo preenchidas.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-2">2</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">2. Preencha seus Itens</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Abra no Excel. Pode copiar e colar os dados das suas planilhas antigas embaixo das colunas indicadas.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-2">3</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">3. Salve o Arquivo</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Basta salvar normalmente como <em>CSV</em> ou planilha do Excel. O sistema reconhece acentos automaticamente.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center justify-center text-sm mb-2">4</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">4. Clique em Upload</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Clique no botão <strong>Fazer Upload da Planilha</strong> (no canto superior direito) e envie seu arquivo pronto!
            </p>
        </div>
    </div>

    {{-- Dicionário Simples e Descomplicado das Colunas --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-2">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                O que significa cada coluna da planilha? (Guia Descomplicado)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Veja abaixo uma explicação prática de cada coluna para preencher sem medo de errar:
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                        <th class="py-3 px-3">Coluna na Planilha</th>
                        <th class="py-3 px-3 text-center">Obrigatória?</th>
                        <th class="py-3 px-4">O que significa de forma simples?</th>
                        <th class="py-3 px-4">Exemplo Prático</th>
                        <th class="py-3 px-3">Atenção Especial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-xs">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">codigo_produto_sku</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>O "RG" do seu produto:</strong> Um código único que você dá para cada item para não confundir peças semelhantes.
                        </td>
                        <td class="py-3 px-4 font-mono text-gray-600 dark:text-gray-300">AMB-QD-010</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">
                            Se reenviar a planilha com esse mesmo código, o sistema <strong>atualiza o preço sem duplicar</strong> o produto.
                        </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">nome_do_produto</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>O título do anúncio:</strong> O nome completo que o comprador lê na loja ou marketplace.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-300">Quadro Canvas Abstrato Dourado 60x90cm</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">Pode usar acentos e espaços normalmente.</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">categoria</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>O departamento:</strong> Serve para organizar seus relatórios e filtrar seus produtos.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-300">Quadros e Telas</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">Ex: Espelhos, Almofadas, Vasos, Iluminação.</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">preco_de_custo_fornecedor</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>Quanto você paga:</strong> O valor que a fábrica ou parceiro de dropshipping cobra de você por peça.
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">58.00</td>
                        <td class="py-3 px-3 text-rose-600 dark:text-rose-400 font-semibold">
                            ⚠️ Apenas números e ponto/vírgula. <strong>Não digite "R$"</strong>.
                        </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">preco_de_venda</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Sim</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>Por quanto você vende:</strong> O preço anunciado na loja. O sistema usa ele para calcular seu lucro líquido!
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">169.90</td>
                        <td class="py-3 px-3 text-rose-600 dark:text-rose-400 font-semibold">
                            ⚠️ Apenas números e ponto/vírgula. <strong>Não digite "R$"</strong>.
                        </td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">fornecedor_parceiro</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>Quem fabrica:</strong> Nome ou contato da fábrica para saber a quem repassar o pedido quando sair uma venda.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-300">Arte Quadros SP</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">Ajuda no controle interno operacional.</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">descricao_detalhada</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>Detalhes do produto:</strong> Medidas, tecidos, molduras ou instruções de limpeza.
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-300">Tela canvas 100% algodão com chassi 3cm</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">Pode conter frases explicativas completas.</td>
                    </tr>

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="py-3 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">produto_ativo</td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 font-bold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Opcional</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                            <strong>Está disponível para venda agora?</strong> Digite <strong>1</strong> para Sim ou <strong>0</strong> para Não (pausado).
                        </td>
                        <td class="py-3 px-4 font-mono text-gray-600 dark:text-gray-300">1</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">Se deixar em branco, o sistema assume como 1 (Ativo).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Dicas Anti-Erros (Linguagem Acessível) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                O que acontece se eu subir a planilha duas vezes?
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                <strong>Fique tranquilo!</strong> O sistema nunca criará produtos duplicados. Ele reconhece o produto pelo seu código (<strong>codigo_produto_sku</strong>). Se você subir a planilha alterando o preço de um item, ele apenas atualiza o preço no sistema, mantendo tudo organizado.
            </p>
        </div>

        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                O maior motivo de erro: Escrever "R$" na célula
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                Nas colunas de preço (<strong>preco_de_custo_fornecedor</strong> e <strong>preco_de_venda</strong>), preencha somente o valor puro (como <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded font-bold">58.00</code> ou <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded font-bold">58,00</code>). Se você escrever "R$ 58,00", o computador não entenderá como um valor numérico para calcular o lucro.
            </p>
        </div>
    </div>
</x-filament-panels::page>
