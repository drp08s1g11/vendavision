<x-filament-panels::page>
    <style>
        /* ==========================================================
           ESTILOS ADAPTATIVOS DE ALTO CONTRASTE (CLARO & ESCURO)
           ========================================================== */
        .import-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .import-table th {
            padding: 12px 14px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .import-table td {
            padding: 14px 14px;
            font-size: 0.8125rem;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease;
        }

        /* TEMA CLARO */
        .import-table tr:hover td {
            background-color: #f9fafb;
        }
        .import-table .col-code {
            font-family: ui-monospace, monospace;
            font-weight: 800;
            color: #b45309; /* Âmbar escuro legível */
        }
        .import-table .col-desc {
            color: #1f2937; /* Preto/cinza escuro com alto contraste */
            line-height: 1.5;
        }
        .import-table .col-desc strong {
            color: #111827;
            font-weight: 700;
        }
        .import-table .col-example {
            font-family: ui-monospace, monospace;
            color: #047857; /* Verde esmeralda escuro */
            font-weight: 700;
        }
        .import-table .col-note {
            color: #4b5563;
            font-size: 0.75rem;
            line-height: 1.4;
        }
        .badge-sim {
            display: inline-block;
            padding: 3px 10px;
            font-weight: 800;
            font-size: 0.75rem;
            border-radius: 9999px;
            background-color: #ffe4e6;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }
        .badge-opc {
            display: inline-block;
            padding: 3px 10px;
            font-weight: 700;
            font-size: 0.75rem;
            border-radius: 9999px;
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }

        /* ==========================================================
           TEMA ESCURO (.dark) - ALTO CONTRASTE E ZERO FADE
           ========================================================== */
        .dark .import-table-container {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .import-table th {
            color: #9ca3af !important;
            border-bottom-color: #374151 !important;
            background-color: #111827 !important;
        }
        .dark .import-table td {
            border-bottom-color: #1f2937 !important;
            background-color: transparent !important;
        }
        /* HOVER NO ESCURO: Jamais fica branco! Fica num cinza escuro sutil */
        .dark .import-table tr:hover td {
            background-color: #1f2937 !important;
        }
        /* Nome da coluna em âmbar dourado de altíssimo contraste */
        .dark .import-table .col-code {
            color: #f59e0b !important;
            font-weight: 800 !important;
        }
        /* Descrição em branco/cinza claríssimo: FIM DO FADE! */
        .dark .import-table .col-desc {
            color: #f3f4f6 !important;
        }
        .dark .import-table .col-desc strong {
            color: #ffffff !important;
            font-weight: 800 !important;
        }
        /* Exemplos práticos em verde brilhante */
        .dark .import-table .col-example {
            color: #34d399 !important;
        }
        /* Observações em cinza claro legível */
        .dark .import-table .col-note {
            color: #d1d5db !important;
        }
        /* Badges de Obrigatória no escuro */
        .dark .badge-sim {
            background-color: #4c0519 !important;
            color: #fda4af !important;
            border-color: #9f1239 !important;
        }
        .dark .badge-opc {
            background-color: #1f2937 !important;
            color: #9ca3af !important;
            border-color: #374151 !important;
        }

        /* Cards informativos e passos no modo escuro */
        .dark .guide-step-card {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .guide-step-card h3 {
            color: #ffffff !important;
        }
        .dark .guide-step-card p {
            color: #9ca3af !important;
        }
        .dark .guide-tip-card {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }
        .dark .guide-tip-card p {
            color: #d1d5db !important;
        }
        .dark .guide-tip-card code {
            background-color: #374151 !important;
            color: #ffffff !important;
        }
    </style>

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
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm guide-step-card">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-2">1</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">1. Baixe o Modelo</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Clique no botão laranja acima. A planilha já vem com as colunas certinhas em português e peças de exemplo preenchidas.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm guide-step-card">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-2">2</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">2. Preencha seus Itens</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Abra no Excel. Pode copiar e colar os dados das suas planilhas antigas embaixo das colunas indicadas.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm guide-step-card">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-extrabold flex items-center justify-center text-sm mb-2">3</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">3. Salve o Arquivo</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Basta salvar normalmente como <em>CSV</em> ou planilha do Excel. O sistema reconhece acentos automaticamente.
            </p>
        </div>

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm guide-step-card">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center justify-center text-sm mb-2">4</div>
            <h3 class="text-xs font-bold text-gray-900 dark:text-gray-100 uppercase">4. Clique em Upload</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Clique no botão <strong>Fazer Upload da Planilha</strong> (no canto superior direito) e envie seu arquivo pronto!
            </p>
        </div>
    </div>

    {{-- Dicionário Simples e Descomplicado das Colunas (Alto Contraste) --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mt-2 import-table-container">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                O que significa cada coluna da planilha? (Guia Descomplicado)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Veja abaixo uma explicação prática de cada coluna para preencher sem medo de errar:
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="import-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Coluna na Planilha</th>
                        <th style="width: 10%; text-align: center;">Obrigatória?</th>
                        <th style="width: 38%;">O que significa de forma simples?</th>
                        <th style="width: 16%;">Exemplo Prático</th>
                        <th style="width: 16%;">Atenção Especial</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-code">codigo_produto_sku</td>
                        <td style="text-align: center;">
                            <span class="badge-sim">Sim</span>
                        </td>
                        <td class="col-desc">
                            <strong>O "RG" do seu produto:</strong> Um código único que você dá para cada item para não confundir peças semelhantes.
                        </td>
                        <td class="col-example">AMB-QD-010</td>
                        <td class="col-note">
                            Se reenviar a planilha com esse mesmo código, o sistema <strong>atualiza o preço sem duplicar</strong> o produto.
                        </td>
                    </tr>

                    <tr>
                        <td class="col-code">nome_do_produto</td>
                        <td style="text-align: center;">
                            <span class="badge-sim">Sim</span>
                        </td>
                        <td class="col-desc">
                            <strong>O título do anúncio:</strong> O nome completo que o comprador lê na loja ou marketplace.
                        </td>
                        <td class="col-example" style="font-family: inherit; font-size: 0.75rem;">Quadro Canvas Dourado 60x90cm</td>
                        <td class="col-note">Pode usar acentos e espaços normalmente.</td>
                    </tr>

                    <tr>
                        <td class="col-code">categoria</td>
                        <td style="text-align: center;">
                            <span class="badge-opc">Opcional</span>
                        </td>
                        <td class="col-desc">
                            <strong>O departamento:</strong> Serve para organizar seus relatórios e filtrar seus produtos.
                        </td>
                        <td class="col-example" style="font-family: inherit; font-size: 0.75rem;">Quadros e Telas</td>
                        <td class="col-note">Ex: Espelhos, Almofadas, Vasos, Iluminação.</td>
                    </tr>

                    <tr>
                        <td class="col-code">preco_de_custo_fornecedor</td>
                        <td style="text-align: center;">
                            <span class="badge-sim">Sim</span>
                        </td>
                        <td class="col-desc">
                            <strong>Quanto você paga:</strong> O valor que a fábrica ou parceiro de dropshipping cobra de você por peça.
                        </td>
                        <td class="col-example">58.00</td>
                        <td class="col-note" style="color: #ef4444; font-weight: 700;">
                            ⚠️ Apenas números e ponto/vírgula. <strong>Não digite "R$"</strong>.
                        </td>
                    </tr>

                    <tr>
                        <td class="col-code">preco_de_venda</td>
                        <td style="text-align: center;">
                            <span class="badge-sim">Sim</span>
                        </td>
                        <td class="col-desc">
                            <strong>Por quanto você vende:</strong> O preço anunciado na loja. O sistema usa ele para calcular seu lucro líquido!
                        </td>
                        <td class="col-example">169.90</td>
                        <td class="col-note" style="color: #ef4444; font-weight: 700;">
                            ⚠️ Apenas números e ponto/vírgula. <strong>Não digite "R$"</strong>.
                        </td>
                    </tr>

                    <tr>
                        <td class="col-code">fornecedor_parceiro</td>
                        <td style="text-align: center;">
                            <span class="badge-opc">Opcional</span>
                        </td>
                        <td class="col-desc">
                            <strong>Quem fabrica:</strong> Nome ou contato da fábrica para saber a quem repassar o pedido quando sair uma venda.
                        </td>
                        <td class="col-example" style="font-family: inherit; font-size: 0.75rem;">Arte Quadros SP</td>
                        <td class="col-note">Ajuda no controle interno operacional.</td>
                    </tr>

                    <tr>
                        <td class="col-code">descricao_detalhada</td>
                        <td style="text-align: center;">
                            <span class="badge-opc">Opcional</span>
                        </td>
                        <td class="col-desc">
                            <strong>Detalhes do produto:</strong> Medidas, tecidos, molduras ou instruções de limpeza.
                        </td>
                        <td class="col-example" style="font-family: inherit; font-size: 0.75rem;">Chassi de madeira 3cm</td>
                        <td class="col-note">Pode conter frases explicativas completas.</td>
                    </tr>

                    <tr>
                        <td class="col-code">produto_ativo</td>
                        <td style="text-align: center;">
                            <span class="badge-opc">Opcional</span>
                        </td>
                        <td class="col-desc">
                            <strong>Está disponível para venda agora?</strong> Digite <strong>1</strong> para Sim ou <strong>0</strong> para Não (pausado).
                        </td>
                        <td class="col-example">1</td>
                        <td class="col-note">Se deixar em branco, o sistema assume como 1 (Ativo).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Dicas Anti-Erros (Linguagem Acessível) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm guide-tip-card">
            <h4 class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                O que acontece se eu subir a planilha duas vezes?
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                <strong>Fique tranquilo!</strong> O sistema nunca criará produtos duplicados. Ele reconhece o produto pelo seu código (<strong>codigo_produto_sku</strong>). Se você subir a planilha alterando o preço de um item, ele apenas atualiza o preço no sistema, mantendo tudo organizado.
            </p>
        </div>

        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm guide-tip-card">
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
