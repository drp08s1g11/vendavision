<?php

namespace Tests\Feature;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Filament\Resources\SaleResource\Pages\ListSales;
use App\Models\ApiIntegration;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesBackupAndIntegrationsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_backup_service_imports_and_prevents_duplicate_records(): void
    {
        $service = new SaleBackupService();

        $csv = "Código do Pedido;Data da Venda;Canal / Marketplace;Status do Pedido;Faturamento (R$);Custo Total (R$);Lucro Bruto (R$);Margem Real (%)\n" .
            "PED-1001;15/10/2026;Shopee;Pago;100,00;30,00;70,00;70,00%\n" .
            "PED-1002;16/10/2026;Shopee;Pago;200,00;60,00;140,00;70,00%\n";

        // 1ª importação: 2 criados
        $res1 = $service->importFromCsvContent($csv);
        $this->assertTrue($res1['success'], $res1['message'] ?? 'erro');
        $this->assertEquals(2, $res1['created']);
        $this->assertEquals(0, $res1['skipped']);
        $this->assertEquals(2, Sale::count());

        // 2ª importação com o mesmo arquivo: 0 criados, 2 ignorados por serem idênticos
        $res2 = $service->importFromCsvContent($csv);
        $this->assertTrue($res2['success']);
        $this->assertEquals(0, $res2['created']);
        $this->assertEquals(2, $res2['skipped']);
        $this->assertEquals(2, Sale::count(), 'O número de vendas deve continuar 2, sem registros duplicados!');

        // 3ª importação com 1 pedido novo e 1 existente: cria apenas o novo
        $csvWithNew = "Código do Pedido;Data da Venda;Canal / Marketplace;Status do Pedido;Faturamento (R$);Custo Total (R$);Lucro Bruto (R$);Margem Real (%)\n" .
            "PED-1001;15/10/2026;Shopee;Pago;100,00;30,00;70,00;70,00%\n" .
            "PED-1003;17/10/2026;Shopee;Pago;150,00;50,00;100,00;66,67%\n";

        $res3 = $service->importFromCsvContent($csvWithNew);
        $this->assertTrue($res3['success']);
        $this->assertEquals(1, $res3['created']);
        $this->assertEquals(1, $res3['skipped']);
        $this->assertEquals(3, Sale::count());
    }

    public function test_only_active_api_actions_are_visible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Apenas Shopee está ativa
        ApiIntegration::create([
            'name' => 'Shopee Oficial',
            'provider' => 'shopee',
            'is_active' => true,
        ]);

        $component = Livewire::test(ListSales::class);

        // Shopee e Backup visíveis
        $component->assertActionVisible('importShopeeReport');
        $component->assertActionVisible('syncShopee');
        $component->assertActionVisible('importBackupSales');

        // Mercado Livre e syncAll ocultos
        $component->assertActionHidden('syncMeli');
        $component->assertActionHidden('syncAll');

        // Ativa Mercado Livre
        ApiIntegration::create([
            'name' => 'Mercado Livre Loja',
            'provider' => 'mercadolivre',
            'is_active' => true,
        ]);

        $componentAfterMeli = Livewire::test(ListSales::class);
        $componentAfterMeli->assertActionVisible('syncMeli');
        $componentAfterMeli->assertActionVisible('syncAll');
    }
}
