<?php
/**
 * Analytics Service
 * Handles analytics data aggregation and calculations for charts
 */

class AnalyticsService
{
    private UsageLog $usageLogModel;
    private User $userModel;

    public function __construct()
    {
        $this->usageLogModel = new UsageLog();
        $this->userModel = new User();
    }

    /**
     * Get daily usage data for charts
     *
     * @param int $userId User ID
     * @param int $days Number of days
     * @return array Daily usage data with date, api_calls, tokens_used
     */
    public function getDailyUsage(int $userId, int $days = 7): array
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as api_calls,
                    COALESCE(SUM(tokens_used), 0) as tokens_used,
                    COALESCE(SUM(input_tokens), 0) as input_tokens,
                    COALESCE(SUM(output_tokens), 0) as output_tokens,
                    COALESCE(SUM(cost), 0) as cost,
                    COALESCE(AVG(response_time_ms), 0) as avg_response_time
                FROM usage_logs 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $results = $this->usageLogModel->query($sql, ['user_id' => $userId, 'days' => $days]);
        
        // Fill in missing dates with zeros
        $filledData = [];
        $startDate = new DateTime("-{$days} days");
        $endDate = new DateTime();
        
        $dataByDate = [];
        foreach ($results as $row) {
            $dataByDate[$row['date']] = $row;
        }
        
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (isset($dataByDate[$dateStr])) {
                $filledData[] = $dataByDate[$dateStr];
            } else {
                $filledData[] = [
                    'date' => $dateStr,
                    'api_calls' => 0,
                    'tokens_used' => 0,
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'cost' => 0,
                    'avg_response_time' => 0
                ];
            }
        }
        
        return $filledData;
    }

    /**
     * Get model usage breakdown for pie/doughnut chart
     *
     * @param int $userId User ID
     * @param int $days Number of days
     * @return array Model breakdown data
     */
    public function getModelBreakdown(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    COALESCE(model, 'unknown') as model,
                    COUNT(*) as count,
                    COALESCE(SUM(tokens_used), 0) as tokens,
                    COALESCE(SUM(cost), 0) as cost
                FROM usage_logs 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY model
                ORDER BY count DESC
                LIMIT 6";
        
        return $this->usageLogModel->query($sql, ['user_id' => $userId, 'days' => $days]);
    }

    /**
     * Get daily costs for bar chart
     *
     * @param int $userId User ID
     * @param int $days Number of days
     * @return array Daily cost data
     */
    public function getDailyCosts(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COALESCE(SUM(cost), 0) as cost,
                    COUNT(*) as requests
                FROM usage_logs 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $results = $this->usageLogModel->query($sql, ['user_id' => $userId, 'days' => $days]);
        
        // Fill missing dates
        $filledData = [];
        $startDate = new DateTime("-{$days} days");
        $endDate = new DateTime();
        
        $dataByDate = [];
        foreach ($results as $row) {
            $dataByDate[$row['date']] = $row;
        }
        
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (isset($dataByDate[$dateStr])) {
                $filledData[] = $dataByDate[$dateStr];
            } else {
                $filledData[] = [
                    'date' => $dateStr,
                    'cost' => 0,
                    'requests' => 0
                ];
            }
        }
        
        return $filledData;
    }

    /**
     * Get summary statistics for the user
     *
     * @param int $userId User ID
     * @return array Summary statistics
     */
    public function getSummaryStats(int $userId): array
    {
        // All-time stats
        $allTimeSql = "SELECT 
                           COUNT(*) as total_api_calls,
                           COALESCE(SUM(tokens_used), 0) as total_tokens_used,
                           COALESCE(SUM(cost), 0) as total_spent
                       FROM usage_logs 
                       WHERE user_id = :user_id";
        
        $allTimeResult = $this->usageLogModel->query($allTimeSql, ['user_id' => $userId]);
        $allTime = $allTimeResult[0] ?? ['total_api_calls' => 0, 'total_tokens_used' => 0, 'total_spent' => 0];
        
        // Last 30 days average
        $avgSql = "SELECT 
                       COALESCE(AVG(daily_calls), 0) as avg_daily_calls,
                       COALESCE(AVG(daily_cost), 0) as avg_daily_cost
                   FROM (
                       SELECT 
                           DATE(created_at) as date,
                           COUNT(*) as daily_calls,
                           SUM(cost) as daily_cost
                       FROM usage_logs 
                       WHERE user_id = :user_id 
                           AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                       GROUP BY DATE(created_at)
                   ) as daily_stats";
        
        $avgResult = $this->usageLogModel->query($avgSql, ['user_id' => $userId]);
        $avg = $avgResult[0] ?? ['avg_daily_calls' => 0, 'avg_daily_cost' => 0];
        
        // Most used model
        $modelSql = "SELECT 
                         COALESCE(model, 'unknown') as model,
                         COUNT(*) as count
                     FROM usage_logs 
                     WHERE user_id = :user_id 
                         AND model IS NOT NULL
                     GROUP BY model 
                     ORDER BY count DESC 
                     LIMIT 1";
        
        $modelResult = $this->usageLogModel->query($modelSql, ['user_id' => $userId]);
        $mostUsedModel = $modelResult[0]['model'] ?? 'N/A';
        
        // Peak hour
        $peakSql = "SELECT 
                        HOUR(created_at) as hour,
                        COUNT(*) as count
                    FROM usage_logs 
                    WHERE user_id = :user_id 
                        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY HOUR(created_at) 
                    ORDER BY count DESC 
                    LIMIT 1";
        
        $peakResult = $this->usageLogModel->query($peakSql, ['user_id' => $userId]);
        $peakHour = isset($peakResult[0]['hour']) ? sprintf('%02d:00', $peakResult[0]['hour']) : 'N/A';
        
        // Cost trend (compare last 7 days vs previous 7 days)
        $trendSql = "SELECT 
                         SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN cost ELSE 0 END) as recent_cost,
                         SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN cost ELSE 0 END) as previous_cost
                     FROM usage_logs 
                     WHERE user_id = :user_id 
                         AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
        
        $trendResult = $this->usageLogModel->query($trendSql, ['user_id' => $userId]);
        $trend = $trendResult[0] ?? ['recent_cost' => 0, 'previous_cost' => 0];
        
        $costTrend = 'stable';
        $costTrendPercent = 0;
        if ($trend['previous_cost'] > 0) {
            $costTrendPercent = (($trend['recent_cost'] - $trend['previous_cost']) / $trend['previous_cost']) * 100;
            if ($costTrendPercent > 5) {
                $costTrend = 'up';
            } elseif ($costTrendPercent < -5) {
                $costTrend = 'down';
            }
        }
        
        // Success rate
        $successSql = "SELECT 
                           COUNT(*) as total,
                           SUM(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 ELSE 0 END) as successful
                       FROM usage_logs 
                       WHERE user_id = :user_id 
                           AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        
        $successResult = $this->usageLogModel->query($successSql, ['user_id' => $userId]);
        $success = $successResult[0] ?? ['total' => 0, 'successful' => 0];
        $successRate = $success['total'] > 0 ? round(($success['successful'] / $success['total']) * 100, 1) : 100;
        
        return [
            'total_api_calls' => (int) $allTime['total_api_calls'],
            'total_tokens_used' => (int) $allTime['total_tokens_used'],
            'total_spent' => (float) $allTime['total_spent'],
            'avg_daily_calls' => round($avg['avg_daily_calls'], 1),
            'avg_daily_cost' => round($avg['avg_daily_cost'], 4),
            'most_used_model' => $mostUsedModel,
            'peak_hour' => $peakHour,
            'cost_trend' => $costTrend,
            'cost_trend_percent' => round($costTrendPercent, 1),
            'success_rate' => $successRate
        ];
    }

    /**
     * Get hourly distribution data
     *
     * @param int $userId User ID
     * @param int $days Number of days
     * @return array Hourly distribution
     */
    public function getHourlyDistribution(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count,
                    COALESCE(SUM(tokens_used), 0) as tokens,
                    COALESCE(SUM(cost), 0) as cost
                FROM usage_logs 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY HOUR(created_at)
                ORDER BY hour";
        
        $results = $this->usageLogModel->query($sql, ['user_id' => $userId, 'days' => $days]);
        
        // Fill all 24 hours
        $hourlyData = array_fill(0, 24, ['hour' => 0, 'count' => 0, 'tokens' => 0, 'cost' => 0]);
        
        foreach ($results as $row) {
            $hour = (int) $row['hour'];
            $hourlyData[$hour] = [
                'hour' => $hour,
                'count' => (int) $row['count'],
                'tokens' => (int) $row['tokens'],
                'cost' => (float) $row['cost']
            ];
        }
        
        // Reset keys
        return array_values($hourlyData);
    }

    /**
     * Get endpoint breakdown
     *
     * @param int $userId User ID
     * @param int $days Number of days
     * @return array Endpoint breakdown
     */
    public function getEndpointBreakdown(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    endpoint,
                    COUNT(*) as count,
                    COALESCE(SUM(tokens_used), 0) as tokens,
                    COALESCE(SUM(cost), 0) as cost,
                    COALESCE(AVG(response_time_ms), 0) as avg_response_time
                FROM usage_logs 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY endpoint
                ORDER BY count DESC
                LIMIT 10";
        
        return $this->usageLogModel->query($sql, ['user_id' => $userId, 'days' => $days]);
    }
}
