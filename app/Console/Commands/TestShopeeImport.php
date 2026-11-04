<?php

namespace App\Console\Commands;

use App\Services\ShopeeReportImportService;
use Illuminate\Console\Command;

class TestShopeeImport extends Command
{
    protected $signature = 'test:shopee-import {path}';
    protected $description = 'Test Shopee CSV report import';

    public function handle(ShopeeReportImportService $service)
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("Arquivo não encontrado: {$path}");
            return 1;
        }

        $content = file_get_contents($path);
        $result = $service->importFromCsvContent($content);

        $this->info(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return 0;
    }
}
