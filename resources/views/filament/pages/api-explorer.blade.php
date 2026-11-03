<x-filament-panels::page>
    <style>
        .json-viewer {
            background-color: #0d1117;
            color: #58a6ff;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8125rem;
            line-height: 1.6;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #30363d;
            max-height: 550px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.75rem;
        }
    </style>

    <form wire:submit.prevent="executeRequest" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-paper-airplane" color="primary">
                Disparar Requisição e Inspecionar Resposta Real
            </x-filament::button>

            <span class="text-xs text-gray-500 dark:text-gray-400">
                Gera a assinatura criptografada HMAC-SHA256 exigida pela Shopee e busca os dados brutos.
            </span>
        </div>
    </form>

    @if($hasExecuted && $responseResult)
        <div class="mt-8 space-y-4">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-800 dark:text-gray-100">Status HTTP:</span>
                        @if($responseResult['status_code'] >= 200 && $responseResult['status_code'] < 300)
                            <span class="status-pill bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300">
                                {{ $responseResult['status_code'] }} OK (Sucesso)
                            </span>
                        @else
                            <span class="status-pill bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300">
                                {{ $responseResult['status_code'] }} Erro
                            </span>
                        @endif

                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Tempo de Resposta: <strong>{{ $responseResult['duration_ms'] }} ms</strong>
                        </span>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                        {{ $responseResult['request_url'] }}
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Retorno Bruto da Shopee (Payload JSON para Mapeamento):
                    </h4>
                    <pre class="json-viewer">{{ json_encode($responseResult['response_body'] ?? $responseResult['error'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <details class="cursor-pointer">
                        <summary class="text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-primary-600">
                            🔍 Ver parâmetros e assinatura enviados na requisição
                        </summary>
                        <pre class="json-viewer mt-2" style="max-height: 200px;">{{ json_encode($responseResult['query_sent'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
