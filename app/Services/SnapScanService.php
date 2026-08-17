<?php

namespace App\Services;

class SnapScanService
{
    /**
     * @return list<int>
     */
    public function tipAmountsZar(): array
    {
        $amounts = config('snapscan.tip_amounts_zar', [20, 50, 100, 200]);
        if (! is_array($amounts)) {
            return [20, 50, 100, 200];
        }

        $out = [];
        foreach ($amounts as $amount) {
            $zar = (int) $amount;
            if ($zar > 0) {
                $out[] = $zar;
            }
        }

        return $out !== [] ? $out : [20, 50, 100, 200];
    }

    public function normalizeCode(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        if (preg_match('~pos\.snapscan\.io/qr/([A-Za-z0-9]+)~', $input, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~^[A-Za-z0-9]{4,64}$~', $input) === 1) {
            return $input;
        }

        return null;
    }

    public function paymentUrl(string $snapscanCode, int $amountZar, ?string $reference = null): string
    {
        abort_if($amountZar <= 0, 422, 'Tip amount must be greater than zero.');

        $code = $this->normalizeCode($snapscanCode);
        abort_unless($code, 422, 'Invalid SnapScan code.');

        $query = [
            'amount' => (string) ($amountZar * 100),
            'strict' => 'true',
        ];

        $reference = trim((string) $reference);
        if ($reference !== '') {
            $query['id'] = $reference;
        }

        $base = rtrim((string) config('snapscan.payment_base_url'), '/');

        return $base.'/'.rawurlencode($code).'?'.http_build_query($query);
    }

    public function requestReference(int $songRequestId): string
    {
        return 'mgg-request-'.$songRequestId;
    }

    /**
     * @return array{enabled: bool, amounts_zar: list<int>, snapscan_code: ?string}
     */
    public function tipsPayload(?string $snapscanCode): array
    {
        $code = $this->normalizeCode($snapscanCode);

        return [
            'enabled' => $code !== null,
            'amounts_zar' => $this->tipAmountsZar(),
            'snapscan_code' => $code,
        ];
    }
}
