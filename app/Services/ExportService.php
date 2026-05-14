<?php
/**
 * Export Service
 * Handles exporting data to CSV format
 */

class ExportService
{
    /**
     * Export usage logs to CSV content
     */
    public function exportUsageLogs(int $userId, ?string $startDate = null, ?string $endDate = null): string
    {
        $logs = $this->getUsageLogs($userId, $startDate, $endDate);
        
        $headers = [
            'Date',
            'API Key',
            'Endpoint',
            'Model',
            'Input Tokens',
            'Output Tokens',
            'Total Tokens',
            'Cost ($)',
            'Response Time (ms)',
            'Status'
        ];
        
        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log['created_at'] ?? '',
                $log['key_name'] ?? $log['api_key_id'] ?? '',
                $log['endpoint'] ?? '',
                $log['model'] ?? '',
                $log['input_tokens'] ?? 0,
                $log['output_tokens'] ?? 0,
                ($log['input_tokens'] ?? 0) + ($log['output_tokens'] ?? 0),
                number_format($log['cost'] ?? 0, 6),
                $log['response_time_ms'] ?? 0,
                ($log['status_code'] ?? 200) < 400 ? 'Success' : 'Error'
            ];
        }
        
        return $this->generateCSV($headers, $rows);
    }

    /**
     * Export transactions to CSV content
     */
    public function exportTransactions(int $userId, ?string $startDate = null, ?string $endDate = null): string
    {
        $transactions = $this->getTransactions($userId, $startDate, $endDate);
        
        $headers = [
            'Date',
            'Transaction ID',
            'Type',
            'Amount ($)',
            'Description',
            'Reference',
            'Status'
        ];
        
        $rows = [];
        foreach ($transactions as $tx) {
            $rows[] = [
                $tx['created_at'] ?? '',
                $tx['id'] ?? '',
                ucfirst($tx['type'] ?? ''),
                ($tx['type'] === 'credit' ? '+' : '-') . number_format($tx['amount'] ?? 0, 2),
                $tx['description'] ?? '',
                $tx['reference_id'] ?? '-',
                ucfirst($tx['status'] ?? '')
            ];
        }
        
        return $this->generateCSV($headers, $rows);
    }

    /**
     * Generate CSV content from headers and rows
     */
    public function generateCSV(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Write headers
        fputcsv($output, $headers);
        
        // Write rows
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Stream CSV download with proper headers
     */
    public function streamDownload(string $filename, string $content): void
    {
        // Clean any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $content;
        exit;
    }

    /**
     * Generate filename with date range
     */
    public function generateFilename(string $type, ?string $startDate, ?string $endDate): string
    {
        $name = $type;
        
        if ($startDate || $endDate) {
            $name .= '_';
            if ($startDate) {
                $name .= date('Ymd', strtotime($startDate));
            }
            $name .= '-';
            if ($endDate) {
                $name .= date('Ymd', strtotime($endDate));
            }
        } else {
            $name .= '_' . date('Ymd');
        }
        
        return $name . '.csv';
    }

    /**
     * Get usage logs from database
     */
    private function getUsageLogs(int $userId, ?string $startDate, ?string $endDate): array
    {
        $db = $this->getDb();
        
        $sql = "SELECT ul.*, ak.name as key_name 
                FROM usage_logs ul 
                LEFT JOIN api_keys ak ON ul.api_key_id = ak.id 
                WHERE ul.user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        if ($startDate) {
            $sql .= " AND ul.created_at >= :start_date";
            $params['start_date'] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $sql .= " AND ul.created_at <= :end_date";
            $params['end_date'] = $endDate . ' 23:59:59';
        }
        
        $sql .= " ORDER BY ul.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get transactions from database
     */
    private function getTransactions(int $userId, ?string $startDate, ?string $endDate): array
    {
        $db = $this->getDb();
        
        $sql = "SELECT * FROM transactions WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate . ' 23:59:59';
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get database connection
     */
    private function getDb(): PDO
    {
        static $pdo = null;
        
        if ($pdo === null) {
            $config = require CONFIG_PATH . '/database.php';
            
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        }
        
        return $pdo;
    }
}
