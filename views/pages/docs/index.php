<?php
/**
 * API Documentation Page
 * Variables: $pageTitle, $user, $userApiKey, $models, $plans, $baseUrl
 */

$exampleApiKey = $userApiKey ? 'hay_' . substr($userApiKey['key_hash'], 0, 8) . '...' : 'hay_your_api_key_here';
?>

<div class="docs-container">
    <!-- Sidebar Navigation -->
    <aside class="docs-sidebar">
        <nav class="docs-nav">
            <h4>Documentation</h4>
            <ul>
                <li><a href="#introduction">Introduction</a></li>
                <li><a href="#authentication">Authentication</a></li>
                <li><a href="#endpoints">Endpoints</a></li>
                <li><a href="#code-examples">Code Examples</a></li>
                <li><a href="#available-models">Available Models</a></li>
                <li><a href="#rate-limits">Rate Limits</a></li>
                <li><a href="#error-codes">Error Codes</a></li>
                <li><a href="#best-practices">Best Practices</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="docs-content">
        <!-- Introduction -->
        <section id="introduction" class="docs-section">
            <h1>API Documentation</h1>
            <p class="lead">Welcome to the API documentation. Our API is OpenAI-compatible, making it easy to integrate with existing applications.</p>
            
            <div class="info-box">
                <h3>Base URL</h3>
                <div class="code-block">
                    <code id="base-url"><?= htmlspecialchars($baseUrl) ?></code>
                    <button class="copy-btn" data-target="base-url" title="Copy to clipboard">
                        <i class="icon-copy"></i>
                    </button>
                </div>
            </div>

            <h3>Key Features</h3>
            <ul>
                <li>OpenAI-compatible API format</li>
                <li>Multiple AI models available</li>
                <li>Streaming support for real-time responses</li>
                <li>Token-based usage tracking</li>
                <li>Flexible rate limits based on your plan</li>
            </ul>
        </section>

        <!-- Authentication -->
        <section id="authentication" class="docs-section">
            <h2>Authentication</h2>
            <p>All API requests require authentication using a Bearer token. You can create and manage your API keys from the <a href="/keys">API Keys</a> page.</p>

            <h3>Getting Your API Key</h3>
            <ol>
                <li><?php if ($user): ?>You're logged in! Go to <a href="/keys">API Keys</a> to create a new key.<?php else: ?><a href="/register">Register</a> or <a href="/login">login</a> to your account.<?php endif; ?></li>
                <li>Navigate to the <a href="/keys">API Keys</a> section.</li>
                <li>Click "Create New Key" and give it a name.</li>
                <li>Copy your API key immediately - it will only be shown once.</li>
            </ol>

            <h3>Using Your API Key</h3>
            <p>Include your API key in the <code>Authorization</code> header of every request:</p>
            
            <div class="code-block">
                <pre id="auth-header">Authorization: Bearer <?= htmlspecialchars($exampleApiKey) ?></pre>
                <button class="copy-btn" data-target="auth-header" title="Copy to clipboard">
                    <i class="icon-copy"></i>
                </button>
            </div>

            <div class="warning-box">
                <strong>Important:</strong> Keep your API key secure. Do not share it publicly or commit it to version control. If compromised, revoke it immediately from the API Keys page.
            </div>
        </section>

        <!-- Endpoints -->
        <section id="endpoints" class="docs-section">
            <h2>Endpoints</h2>
            
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <span class="method post">POST</span>
                    <code>/v1/chat/completions</code>
                </div>
                <p>Create a chat completion. Send a list of messages and receive an AI-generated response.</p>

                <h4>Request Body</h4>
                <div class="table-responsive">
                    <table class="params-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>model</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>The model to use for completion. See <a href="#available-models">Available Models</a>.</td>
                            </tr>
                            <tr>
                                <td><code>messages</code></td>
                                <td>array</td>
                                <td>Yes</td>
                                <td>Array of message objects with <code>role</code> and <code>content</code>.</td>
                            </tr>
                            <tr>
                                <td><code>temperature</code></td>
                                <td>number</td>
                                <td>No</td>
                                <td>Sampling temperature (0-2). Higher values = more random. Default: 1</td>
                            </tr>
                            <tr>
                                <td><code>max_tokens</code></td>
                                <td>integer</td>
                                <td>No</td>
                                <td>Maximum tokens to generate. Default varies by model.</td>
                            </tr>
                            <tr>
                                <td><code>stream</code></td>
                                <td>boolean</td>
                                <td>No</td>
                                <td>Enable streaming responses. Default: false</td>
                            </tr>
                            <tr>
                                <td><code>top_p</code></td>
                                <td>number</td>
                                <td>No</td>
                                <td>Nucleus sampling parameter (0-1). Default: 1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4>Example Request</h4>
                <div class="code-block">
                    <pre id="request-example">{
    "model": "codex-5.4",
    "messages": [
        {"role": "system", "content": "You are a helpful assistant."},
        {"role": "user", "content": "Hello!"}
    ],
    "temperature": 0.7,
    "max_tokens": 1000
}</pre>
                    <button class="copy-btn" data-target="request-example" title="Copy to clipboard">
                        <i class="icon-copy"></i>
                    </button>
                </div>

                <h4>Example Response</h4>
                <div class="code-block">
                    <pre id="response-example">{
    "id": "chatcmpl-abc123",
    "object": "chat.completion",
    "created": 1677858242,
    "model": "codex-5.4",
    "choices": [
        {
            "index": 0,
            "message": {
                "role": "assistant",
                "content": "Hello! How can I assist you today?"
            },
            "finish_reason": "stop"
        }
    ],
    "usage": {
        "prompt_tokens": 20,
        "completion_tokens": 9,
        "total_tokens": 29
    }
}</pre>
                    <button class="copy-btn" data-target="response-example" title="Copy to clipboard">
                        <i class="icon-copy"></i>
                    </button>
                </div>

                <h4>Streaming Response</h4>
                <p>When <code>stream: true</code> is set, the API returns Server-Sent Events (SSE). Each event contains a chunk of the response:</p>
                <div class="code-block">
                    <pre id="stream-example">data: {"id":"chatcmpl-abc123","object":"chat.completion.chunk","choices":[{"delta":{"content":"Hello"},"index":0}]}

data: {"id":"chatcmpl-abc123","object":"chat.completion.chunk","choices":[{"delta":{"content":"!"},"index":0}]}

data: [DONE]</pre>
                    <button class="copy-btn" data-target="stream-example" title="Copy to clipboard">
                        <i class="icon-copy"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Code Examples -->
        <section id="code-examples" class="docs-section">
            <h2>Code Examples</h2>
            <p>Here are ready-to-use examples in popular programming languages:</p>

            <div class="code-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="curl">cURL</button>
                    <button class="tab-btn" data-tab="python">Python</button>
                    <button class="tab-btn" data-tab="nodejs">Node.js</button>
                    <button class="tab-btn" data-tab="php">PHP</button>
                </div>

                <div class="tab-content">
                    <!-- cURL -->
                    <div class="tab-pane active" id="tab-curl">
                        <div class="code-block">
                            <pre id="code-curl">curl <?= htmlspecialchars($baseUrl) ?>/chat/completions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <?= htmlspecialchars($exampleApiKey) ?>" \
  -d '{
    "model": "codex-5.4",
    "messages": [
      {"role": "system", "content": "You are a helpful assistant."},
      {"role": "user", "content": "Hello!"}
    ],
    "temperature": 0.7,
    "max_tokens": 1000
  }'</pre>
                            <button class="copy-btn" data-target="code-curl" title="Copy to clipboard">
                                <i class="icon-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Python -->
                    <div class="tab-pane" id="tab-python">
                        <div class="code-block">
                            <pre id="code-python">import requests

API_KEY = "<?= htmlspecialchars($exampleApiKey) ?>"
BASE_URL = "<?= htmlspecialchars($baseUrl) ?>"

headers = {
    "Content-Type": "application/json",
    "Authorization": f"Bearer {API_KEY}"
}

data = {
    "model": "codex-5.4",
    "messages": [
        {"role": "system", "content": "You are a helpful assistant."},
        {"role": "user", "content": "Hello!"}
    ],
    "temperature": 0.7,
    "max_tokens": 1000
}

response = requests.post(
    f"{BASE_URL}/chat/completions",
    headers=headers,
    json=data
)

result = response.json()
print(result["choices"][0]["message"]["content"])</pre>
                            <button class="copy-btn" data-target="code-python" title="Copy to clipboard">
                                <i class="icon-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Node.js -->
                    <div class="tab-pane" id="tab-nodejs">
                        <div class="code-block">
                            <pre id="code-nodejs">const API_KEY = "<?= htmlspecialchars($exampleApiKey) ?>";
const BASE_URL = "<?= htmlspecialchars($baseUrl) ?>";

async function chatCompletion() {
    const response = await fetch(`${BASE_URL}/chat/completions`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${API_KEY}`
        },
        body: JSON.stringify({
            model: "codex-5.4",
            messages: [
                { role: "system", content: "You are a helpful assistant." },
                { role: "user", content: "Hello!" }
            ],
            temperature: 0.7,
            max_tokens: 1000
        })
    });

    const data = await response.json();
    console.log(data.choices[0].message.content);
}

chatCompletion();</pre>
                            <button class="copy-btn" data-target="code-nodejs" title="Copy to clipboard">
                                <i class="icon-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PHP -->
                    <div class="tab-pane" id="tab-php">
                        <div class="code-block">
                            <pre id="code-php">&lt;?php
$apiKey = "<?= htmlspecialchars($exampleApiKey) ?>";
$baseUrl = "<?= htmlspecialchars($baseUrl) ?>";

$data = [
    "model" => "codex-5.4",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful assistant."],
        ["role" => "user", "content" => "Hello!"]
    ],
    "temperature" => 0.7,
    "max_tokens" => 1000
];

$ch = curl_init("$baseUrl/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
echo $result["choices"][0]["message"]["content"];</pre>
                            <button class="copy-btn" data-target="code-php" title="Copy to clipboard">
                                <i class="icon-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Available Models -->
        <section id="available-models" class="docs-section">
            <h2>Available Models</h2>
            <p>The following models are currently available through our API:</p>

            <?php if (!empty($models)): ?>
            <div class="table-responsive">
                <table class="models-table">
                    <thead>
                        <tr>
                            <th>Model Name</th>
                            <th>Description</th>
                            <th>Input Price</th>
                            <th>Output Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($models as $model): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($model['model_name']) ?></code></td>
                            <td><?= htmlspecialchars($model['provider_name'] ?? 'AI Model') ?> - <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $model['model_name']))) ?></td>
                            <td>$<?= number_format((float)($model['input_price_per_1k'] ?? 0), 4) ?> / 1K tokens</td>
                            <td>$<?= number_format((float)($model['output_price_per_1k'] ?? 0), 4) ?> / 1K tokens</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="info-box">
                <p>No models are currently configured. Please contact support for more information.</p>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <h4>Pricing Note</h4>
                <p>Prices are per 1,000 tokens. A token is approximately 4 characters or 0.75 words. Input tokens are from your messages, output tokens are from the AI response.</p>
            </div>
        </section>

        <!-- Rate Limits -->
        <section id="rate-limits" class="docs-section">
            <h2>Rate Limits</h2>
            <p>To ensure fair usage and service stability, API requests are subject to rate limits based on your subscription plan.</p>

            <?php if (!empty($plans)): ?>
            <div class="table-responsive">
                <table class="plans-table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Requests/Minute</th>
                            <th>Daily Token Limit</th>
                            <th>Price Multiplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($plan['name']) ?></strong></td>
                            <td><?= number_format((int)($plan['rate_limit_per_minute'] ?? 0)) ?> req/min</td>
                            <td><?= number_format((int)($plan['daily_token_limit'] ?? 0)) ?> tokens</td>
                            <td><?= number_format((float)($plan['price_multiplier'] ?? 1), 2) ?>x</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="info-box">
                <p>Rate limit information is currently unavailable. Please contact support.</p>
            </div>
            <?php endif; ?>

            <h3>Rate Limit Headers</h3>
            <p>Each API response includes headers to help you track your usage:</p>
            <div class="table-responsive">
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Header</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>X-RateLimit-Limit</code></td>
                            <td>Maximum requests allowed per minute</td>
                        </tr>
                        <tr>
                            <td><code>X-RateLimit-Remaining</code></td>
                            <td>Requests remaining in current window</td>
                        </tr>
                        <tr>
                            <td><code>X-RateLimit-Reset</code></td>
                            <td>Unix timestamp when the rate limit resets</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Handling Rate Limits</h3>
            <p>When you exceed your rate limit, the API returns a <code>429 Too Many Requests</code> error. Implement exponential backoff in your code:</p>
            <div class="code-block">
                <pre id="rate-limit-example">import time

def call_api_with_retry(max_retries=3):
    for attempt in range(max_retries):
        response = make_api_call()
        if response.status_code == 429:
            wait_time = 2 ** attempt  # 1, 2, 4 seconds
            time.sleep(wait_time)
            continue
        return response
    raise Exception("Max retries exceeded")</pre>
                <button class="copy-btn" data-target="rate-limit-example" title="Copy to clipboard">
                    <i class="icon-copy"></i>
                </button>
            </div>
        </section>

        <!-- Error Codes -->
        <section id="error-codes" class="docs-section">
            <h2>Error Codes</h2>
            <p>The API uses standard HTTP status codes to indicate success or failure:</p>

            <div class="table-responsive">
                <table class="error-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>200</code></td>
                            <td>OK</td>
                            <td>Request succeeded.</td>
                        </tr>
                        <tr>
                            <td><code>400</code></td>
                            <td>Bad Request</td>
                            <td>Invalid request format or missing required parameters.</td>
                        </tr>
                        <tr>
                            <td><code>401</code></td>
                            <td>Unauthorized</td>
                            <td>Invalid or missing API key.</td>
                        </tr>
                        <tr>
                            <td><code>402</code></td>
                            <td>Payment Required</td>
                            <td>Insufficient credits. Add funds to your account.</td>
                        </tr>
                        <tr>
                            <td><code>403</code></td>
                            <td>Forbidden</td>
                            <td>API key does not have permission for this operation.</td>
                        </tr>
                        <tr>
                            <td><code>404</code></td>
                            <td>Not Found</td>
                            <td>The requested model or resource was not found.</td>
                        </tr>
                        <tr>
                            <td><code>429</code></td>
                            <td>Too Many Requests</td>
                            <td>Rate limit exceeded. Wait before retrying.</td>
                        </tr>
                        <tr>
                            <td><code>500</code></td>
                            <td>Internal Server Error</td>
                            <td>Something went wrong on our end. Try again later.</td>
                        </tr>
                        <tr>
                            <td><code>503</code></td>
                            <td>Service Unavailable</td>
                            <td>The service is temporarily unavailable.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Error Response Format</h3>
            <p>Error responses follow the OpenAI format:</p>
            <div class="code-block">
                <pre id="error-format">{
    "error": {
        "message": "Invalid API key provided",
        "type": "invalid_request_error",
        "code": "invalid_api_key"
    }
}</pre>
                <button class="copy-btn" data-target="error-format" title="Copy to clipboard">
                    <i class="icon-copy"></i>
                </button>
            </div>
        </section>

        <!-- Best Practices -->
        <section id="best-practices" class="docs-section">
            <h2>Best Practices</h2>
            
            <h3>Optimize Token Usage</h3>
            <ul>
                <li><strong>Be concise:</strong> Shorter prompts use fewer input tokens.</li>
                <li><strong>Set max_tokens:</strong> Limit response length to control costs.</li>
                <li><strong>Use system messages wisely:</strong> Keep system prompts focused and relevant.</li>
            </ul>

            <h3>Handle Errors Gracefully</h3>
            <ul>
                <li><strong>Implement retry logic:</strong> Use exponential backoff for transient errors.</li>
                <li><strong>Check response status:</strong> Always verify the HTTP status code before processing.</li>
                <li><strong>Log errors:</strong> Keep track of errors for debugging and monitoring.</li>
            </ul>

            <h3>Security</h3>
            <ul>
                <li><strong>Never expose API keys:</strong> Keep keys server-side, never in client code.</li>
                <li><strong>Use environment variables:</strong> Store API keys in environment variables, not in code.</li>
                <li><strong>Rotate keys regularly:</strong> Periodically generate new keys and revoke old ones.</li>
                <li><strong>Monitor usage:</strong> Check your dashboard regularly for unusual activity.</li>
            </ul>

            <h3>Streaming Best Practices</h3>
            <ul>
                <li><strong>Use streaming for long responses:</strong> Improves perceived latency for users.</li>
                <li><strong>Handle partial data:</strong> Process chunks as they arrive, not all at once.</li>
                <li><strong>Implement timeouts:</strong> Set reasonable timeouts for streaming connections.</li>
            </ul>

            <h3>Batching Requests</h3>
            <p>For multiple independent queries, consider batching or parallelizing requests while respecting rate limits:</p>
            <div class="code-block">
                <pre id="batch-example">import asyncio
import aiohttp

async def batch_requests(prompts):
    async with aiohttp.ClientSession() as session:
        tasks = [make_request(session, prompt) for prompt in prompts]
        # Limit concurrent requests to respect rate limits
        semaphore = asyncio.Semaphore(5)
        async def limited_request(task):
            async with semaphore:
                return await task
        results = await asyncio.gather(*[limited_request(t) for t in tasks])
    return results</pre>
                <button class="copy-btn" data-target="batch-example" title="Copy to clipboard">
                    <i class="icon-copy"></i>
                </button>
            </div>
        </section>

        <!-- Support -->
        <section id="support" class="docs-section">
            <h2>Need Help?</h2>
            <p>If you have questions or need assistance:</p>
            <ul>
                <li><strong>Support Tickets:</strong> <a href="/tickets">Create a support ticket</a></li>
                <li><strong>Dashboard:</strong> <a href="/dashboard">View your usage and settings</a></li>
                <li><strong>API Keys:</strong> <a href="/keys">Manage your API keys</a></li>
            </ul>
        </section>
    </div>
</div>
