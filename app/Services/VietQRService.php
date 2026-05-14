<?php
/**
 * VietQR Service
 * Handles VietQR code generation for bank transfers
 */

class VietQRService
{
    /**
     * Generate VietQR image URL
     */
    public function generateQR(
        string $bankId,
        string $accountNo,
        int $amount,
        string $description,
        string $accountName = ''
    ): string {
        $baseUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png";
        
        $params = [
            'amount' => $amount,
            'addInfo' => $description
        ];
        
        if (!empty($accountName)) {
            $params['accountName'] = $accountName;
        }
        
        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Generate a unique reference code for deposits
     */
    public function generateReferenceCode(): string
    {
        $timestamp = time();
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 3));
        
        return 'DEP' . $timestamp . $random;
    }

    /**
     * Get list of supported Vietnamese banks
     */
    public function getBankList(): array
    {
        return [
            ['id' => 'VCB', 'name' => 'Vietcombank'],
            ['id' => 'TCB', 'name' => 'Techcombank'],
            ['id' => 'MB', 'name' => 'MB Bank'],
            ['id' => 'ACB', 'name' => 'ACB'],
            ['id' => 'VPB', 'name' => 'VPBank'],
            ['id' => 'TPB', 'name' => 'TPBank'],
            ['id' => 'STB', 'name' => 'Sacombank'],
            ['id' => 'HDB', 'name' => 'HDBank'],
            ['id' => 'VIB', 'name' => 'VIB'],
            ['id' => 'SHB', 'name' => 'SHB']
        ];
    }
}
