<x-filament-panels::page.simple>
    <form wire:submit.prevent="save" class="space-y-6 text-left">
        {{-- Senha Atual / Temporária --}}
        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Senha Temporária Atual <span class="text-red-500">*</span>
            </label>
            <input 
                id="current_password"
                type="password" 
                wire:model="current_password" 
                placeholder="Digite a senha temporária atual (ex: password)"
                autocomplete="current-password"
                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm"
            />
            @error('current_password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nova Senha --}}
        <div>
            <div class="flex justify-between items-center mb-1">
                <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Nova Senha Permanente <span class="text-red-500">*</span>
                </label>
                <span class="text-xs font-mono {{ strlen($new_password) >= 14 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-400' }}">
                    {{ strlen($new_password) }} / 14 caracteres
                </span>
            </div>
            <input 
                id="new_password"
                type="password" 
                wire:model.live.debounce.150ms="new_password" 
                placeholder="Crie sua nova senha segura"
                autocomplete="new-password"
                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm font-mono"
            />
            @error('new_password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirmação da Nova Senha --}}
        <div>
            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Confirmar Nova Senha <span class="text-red-500">*</span>
            </label>
            <input 
                id="new_password_confirmation"
                type="password" 
                wire:model.live.debounce.150ms="new_password_confirmation" 
                placeholder="Repita a nova senha exatamente igual"
                autocomplete="new-password"
                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm font-mono"
            />
            @error('new_password_confirmation')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Checklist Interativo de Critérios de Segurança --}}
        <div class="p-4 bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2.5 text-xs">
            <p class="font-semibold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                <span>Critérios de Segurança Obrigatórios:</span>
                @if ($this->isAllCriteriaMet)
                    <span class="text-green-600 dark:text-green-400 font-bold flex items-center gap-1">
                        ✓ Todos os requisitos atendidos!
                    </span>
                @else
                    <span class="text-amber-600 dark:text-amber-400 font-normal">
                        Preencha os requisitos abaixo
                    </span>
                @endif
            </p>

            <ul class="space-y-1.5 pt-1 border-t border-gray-200/60 dark:border-gray-800">
                {{-- 1. Mínimo 14 caracteres --}}
                <li class="flex items-center gap-2 {{ $this->hasMinLength ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full {{ $this->hasMinLength ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }} text-[10px] font-bold">
                        {{ $this->hasMinLength ? '✓' : '○' }}
                    </span>
                    <span>No mínimo <strong>14 dígitos / caracteres</strong></span>
                </li>

                {{-- 2. Letra Maiúscula --}}
                <li class="flex items-center gap-2 {{ $this->hasUppercase ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full {{ $this->hasUppercase ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }} text-[10px] font-bold">
                        {{ $this->hasUppercase ? '✓' : '○' }}
                    </span>
                    <span>Pelo menos uma <strong>letra maiúscula</strong> (A-Z)</span>
                </li>

                {{-- 3. Letra Minúscula --}}
                <li class="flex items-center gap-2 {{ $this->hasLowercase ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full {{ $this->hasLowercase ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }} text-[10px] font-bold">
                        {{ $this->hasLowercase ? '✓' : '○' }}
                    </span>
                    <span>Pelo menos uma <strong>letra minúscula</strong> (a-z)</span>
                </li>

                {{-- 4. Número --}}
                <li class="flex items-center gap-2 {{ $this->hasNumber ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full {{ $this->hasNumber ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }} text-[10px] font-bold">
                        {{ $this->hasNumber ? '✓' : '○' }}
                    </span>
                    <span>Pelo menos um <strong>número</strong> (0-9)</span>
                </li>

                {{-- 5. Caractere Especial Permitido (!@#$%&*) --}}
                <li class="flex items-center gap-2 {{ $this->hasSpecial ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full {{ $this->hasSpecial ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }} text-[10px] font-bold">
                        {{ $this->hasSpecial ? '✓' : '○' }}
                    </span>
                    <span>Pelo menos um <strong>caractere especial permitido</strong>: <code class="px-1 py-0.5 rounded bg-gray-200 dark:bg-gray-800 text-amber-600 dark:text-amber-400 font-mono font-bold">! @ # $ % & *</code></span>
                </li>

                {{-- 6. Confirmação Idêntica --}}
                <li class="flex items-center gap-2 {{ $this->hasMatchedConfirmation ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full {{ $this->hasMatchedConfirmation ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }} text-[10px] font-bold">
                        {{ $this->hasMatchedConfirmation ? '✓' : '○' }}
                    </span>
                    <span>Confirmação idêntica digitada</span>
                </li>
            </ul>
        </div>

        {{-- Botão de Submissão --}}
        <div>
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-500 text-white font-semibold rounded-lg shadow-md transition duration-200 text-sm flex items-center justify-center gap-2"
            >
                <span>Ativar Nova Senha e Acessar o VendaVision</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>

        {{-- Opção de Logout --}}
        <div class="text-center pt-2">
            <button 
                type="button" 
                wire:click="logout" 
                class="text-xs text-gray-500 hover:text-red-600 dark:hover:text-red-400 underline transition"
            >
                Sair do sistema
            </button>
        </div>
    </form>
</x-filament-panels::page.simple>
