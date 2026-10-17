<x-filament-panels::page>
    @if (! $isUnlocked)
        {{-- TELA DE BLOQUEIO / CONFIRMAÇÃO DE SENHA --}}
        <div class="max-w-md mx-auto my-12 p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 text-center">
            <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Área de Segurança Restrita</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Para visualizar ou alterar as chaves das APIs dos marketplaces, por favor confirme a senha da sua conta de administrador.
            </p>

            <form wire:submit.prevent="unlockVault" class="space-y-4">
                <input 
                    type="password" 
                    wire:model="confirmPassword" 
                    placeholder="Sua senha atual"
                    required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 text-sm"
                />

                <button 
                    type="submit" 
                    class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 text-white font-semibold rounded-lg shadow-md transition duration-200 text-sm"
                >
                    Desbloquear Cofre de APIs
                </button>
            </form>
        </div>
    @else
        {{-- TELA DESBLOQUEADA --}}
        <div class="flex justify-between items-center mb-4 p-4 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 rounded-xl">
            <div class="flex items-center space-x-3">
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-sm font-medium text-green-900 dark:text-green-300">
                    Cofre Seguro Desbloqueado • Criptografia AES-256 ativa
                </span>
            </div>
            <button 
                wire:click="lockVault" 
                class="text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 underline"
            >
                Bloquear Agora
            </button>
        </div>

        <form wire:submit.prevent="save">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" size="lg" color="amber">
                    Salvar e Criptografar Credenciais
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>