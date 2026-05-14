<?php
/**
 * Webhook Service
 * Handles webhook delivery, signature generation, and event triggering
 */

class WebhookService
{
    private Webhook $webhookModel;
    private WebhookLog $webhookLogModel;
    private int $timeout = 10;
    private int $maxRetries = 1;

    public function __construct()
    {
        $this->webhookModel = new Webhook();
        $this->webhookLogModel = new WebhookLog();
    }

    /**
     * Send webhook to a specific endpoint
     */
    public function sendWebhook(array $webhook, string $event, array $data): array
    {
        $payload = $this->buildPayload($event, $data);
        $payloadJson = json_encode($payload);
        $signature = $this->generateSignature($payloadJson, $webhook['secret']);
        
        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Signature: sha256=' . $signature,
            'X-Webhook-Event: ' . $event,
            'X-Webhook-Timestamp: ' . $payload['timestamp']
        ];
        
        $result = [
            'success' => false,
            'response_code' => null,
            'response_body' => null,
            'attempts' => 0
        ];
        
        // Try initial request + retries
        for ($attempt = 1; $attempt <= ($this->maxRetries + 1); $attempt++) {
            $result['attempts'] = $attempt;
            
            try {
                $response = $this->makeRequest($webhook['url'], $payloadJson, $headers);
                $result['response_code'] = $response['code'];
                $result['response_body'] = $response['body'];
                
                // Success if 2xx response
                if ($response['code'] >= 200 && $response['code'] < 300) {
                    $result['success'] = true;
                    break;
                }
                
                // Non-retryable error (4xx)
                if ($response['code'] >= 400 && $response['code'] < 500) {
                    break;
                }
            } catch (Exception $e) {
                $result['response_body'] = $e->getMessage();
            }
            
            // Wait before retry
            if ($attempt <= $this->maxRetries) {
                usleep(500000); // 0.5 seconds
            }
        }
        
        // Log the attempt
        $this->logAttempt(
            $webhook['id'],
            $event,
            $payload,
            $result['response_code'],
            $result['response_body'],
            $result['attempts']
        );
        
        return $result;
    }

    /**
     * Build webhook payload
     */
    private function buildPayload(string $event, array $data): array
    {
        return [
            'event' => $event,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'data' => $data
        ];
    }

    /**
     * Generate HMAC-SHA256 signature
     */
    public function generateSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify HMAC-SHA256 signature
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expected = $this->generateSignature($payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Log a webhook delivery attempt
     */
    public function logAttempt(int $webhookId, string $event, array $payload, ?int $responseCode, ?string $responseBody, int $attempts = 1): int
    {
        // Truncate response body if too long
        if ($responseBody !== null && strlen($responseBody) > 5000) {
            $responseBody = substr($responseBody, 0, 5000) . '... [truncated]';
        }
        
        return $this->webhookLogModel->createLog(
            $webhookId,
            $event,
            $payload,
            $responseCode,
            $responseBody,
            $attempts
        );
    }

    /**
     * Trigger event for a specific user
     */
    public function triggerEvent(string $event, int $userId, array $data): array
    {
        $webhooks = $this->webhookModel->getActiveByUserAndEvent($userId, $event);
        $results = [];
        
        foreach ($webhooks as $webhook) {
            $result = $this->sendWebhook($webhook, $event, $data);
            $results[] = [
                'webhook_id' => $webhook['id'],
                'url' => $webhook['url'],
                'success' => $result['success'],
                'response_code' => $result['response_code'],
                'attempts' => $result['attempts']
            ];
        }
        
        return $results;
    }

    /**
     * Send test webhook to verify configuration
     */
    public function sendTestWebhook(array $webhook): array
    {
        $testData = [
            'test' => true,
            'message' => 'This is a test webhook from Hay API Gateway',
            'webhook_id' => $webhook['id'],
            'timestamp' => time()
        ];
        
        return $this->sendWebhook($webhook, 'test', $testData);
    }

    /**
     * Make HTTP request to webhook URL
     */
    private function makeRequest(string $url, string $payload, array $headers): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'HayAPIGateway/1.0 (Webhook)'
        ]);
        
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }
        
        return [
            'code' => $code,
            'body' => $body
        ];
    }

    /**
     * Get available webhook events
     */
    public function getAvailableEvents(): array
    {
        return Webhook::EVENTS;
    }

    /**
     * Trigger deposit approved event
     */
    public function triggerDepositApproved(int $userId, int $depositId, float $amount): array
    {
        return $this->triggerEvent('deposit_approved', $userId, [
            'deposit_id' => $depositId,
            'amount' => $amount,
            'user_id' => $userId
        ]);
    }

    /**
     * Trigger deposit rejected event
     */
    public function triggerDepositRejected(int $userId, int $depositId, float $amount, string $reason = ''): array
    {
        return $this->triggerEvent('deposit_rejected', $userId, [
            'deposit_id' => $depositId,
            'amount' => $amount,
            'user_id' => $userId,
            'reason' => $reason
        ]);
    }

    /**
     * Trigger low balance warning event
     */
    public function triggerLowBalance(int $userId, float $balance, float $threshold): array
    {
        return $this->triggerEvent('low_balance', $userId, [
            'user_id' => $userId,
            'balance' => $balance,
            'threshold' => $threshold
        ]);
    }

    /**
     * Trigger key quota warning event
     */
    public function triggerKeyQuotaWarning(int $userId, int $keyId, int $usedQuota, int $totalQuota): array
    {
        return $this->triggerEvent('key_quota_warning', $userId, [
            'user_id' => $userId,
            'key_id' => $keyId,
            'used_quota' => $usedQuota,
            'total_quota' => $totalQuota,
            'percentage' => round(($usedQuota / $totalQuota) * 100, 2)
        ]);
    }

    /**
     * Trigger plan expired event
     */
    public function triggerPlanExpired(int $userId, string $planName, string $expiryDate): array
    {
        return $this->triggerEvent('plan_expired', $userId, [
            'user_id' => $userId,
            'plan_name' => $planName,
            'expiry_date' => $expiryDate
        ]);
    }
}
