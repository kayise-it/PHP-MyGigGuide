<?php

namespace Tests\Unit;

use App\Services\SnapScanService;
use Tests\TestCase;

class SnapScanServiceTest extends TestCase
{
    public function test_normalizes_snapscan_code_from_payment_url(): void
    {
        $service = new SnapScanService;

        $this->assertSame(
            'peMSrHSr',
            $service->normalizeCode('https://pos.snapscan.io/qr/peMSrHSr?amount=2000')
        );
    }

    public function test_builds_payment_url_with_reference(): void
    {
        $service = new SnapScanService;

        $url = $service->paymentUrl('peMSrHSr', 50, 'mgg-request-99');

        $this->assertStringContainsString('pos.snapscan.io/qr/peMSrHSr', $url);
        $this->assertStringContainsString('amount=5000', $url);
        $this->assertStringContainsString('strict=true', $url);
        $this->assertStringContainsString('id=mgg-request-99', $url);
    }

    public function test_tip_amounts_default_to_four_presets(): void
    {
        $service = new SnapScanService;

        $this->assertSame([20, 50, 100, 200], $service->tipAmountsZar());
    }
}
