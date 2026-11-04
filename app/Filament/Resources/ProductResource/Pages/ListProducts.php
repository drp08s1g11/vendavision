<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Pages\ImportProductsGuide;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Produto'),

            Actions\Action::make('import_guide')
                ->label('Planilha Modelo / Instruções')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(ImportProductsGuide::getUrl()),
        ];
    }
}
