<?php
/**
 * Token Counter Service
 * Estimates token counts for text and chat messages using character-based heuristics
 * Note: Uses approximations since tiktoken library is not available without Composer
 */

class TokenCounterService
{
    /**
     * Character divisors for different model types
     */
    private const CHARS_PER_TOKEN_GPT = 4.0;
    private const CHARS_PER_TOKEN_CLAUDE = 3.5;
    private const CHARS_PER_TOKEN_DEFAULT = 4.0;
    
    /**
     * Overhead tokens per message for chat formatting
     */
    private const MESSAGE_OVERHEAD_TOKENS = 4;

    /**
     * Count tokens for a given text string
     * 
     * @param string $text The text to count tokens for
     * @param string $model The model name (e.g., 'gpt-4', 'claude-3-opus')
     * @return int Estimated token count
     */
    public function countTokens(string $text, string $model): int
    {
        if (empty($text)) {
            return 0;
        }

        $charsPerToken = $this->getCharsPerToken($model);
        $charCount = mb_strlen($text, 'UTF-8');
        
        return (int) ceil($charCount / $charsPerToken);
    }

    /**
     * Count tokens for an array of chat messages
     * Each message should have 'role' and 'content' keys
     * 
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param string $model The model name
     * @return int Estimated total token count
     */
    public function countChatTokens(array $messages, string $model): int
    {
        if (empty($messages)) {
            return 0;
        }

        $totalTokens = 0;

        foreach ($messages as $message) {
            // Count tokens for the message content
            $content = $message['content'] ?? '';
            $totalTokens += $this->countTokens($content, $model);
            
            // Add overhead for role and message formatting
            // This accounts for special tokens like role markers
            $totalTokens += self::MESSAGE_OVERHEAD_TOKENS;
        }

        return $totalTokens;
    }

    /**
     * Get the tokenizer type based on model name prefix
     * 
     * @param string $model The model name
     * @return string Tokenizer type: 'gpt', 'claude', or 'default'
     */
    public function getTokenizerType(string $model): string
    {
        $modelLower = strtolower($model);

        if (strpos($modelLower, 'gpt') === 0 || strpos($modelLower, 'gpt-') === 0) {
            return 'gpt';
        }

        if (strpos($modelLower, 'claude') === 0 || strpos($modelLower, 'claude-') === 0) {
            return 'claude';
        }

        return 'default';
    }

    /**
     * Get characters per token ratio for a given model
     * 
     * @param string $model The model name
     * @return float Characters per token ratio
     */
    private function getCharsPerToken(string $model): float
    {
        $tokenizerType = $this->getTokenizerType($model);

        switch ($tokenizerType) {
            case 'gpt':
                return self::CHARS_PER_TOKEN_GPT;
            case 'claude':
                return self::CHARS_PER_TOKEN_CLAUDE;
            default:
                return self::CHARS_PER_TOKEN_DEFAULT;
        }
    }
}
