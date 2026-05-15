# API Documentation

## Gioi Thieu

Hay API Gateway cung cap API tuong thich voi OpenAI, cho phep ban su dung cac model AI khac nhau thong qua mot endpoint duy nhat.

## Base URL

```
https://yourdomain.com
```

## Authentication

Tat ca API requests can header Authorization:

```
Authorization: Bearer YOUR_API_KEY
```

Lay API Key tai: Dashboard > API Keys > Create New Key

## Endpoints

### 1. Chat Completions

Tao hoi thoai voi AI model.

**POST** `/v1/chat/completions`

#### Request Headers

| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer YOUR_API_KEY | Yes |
| Content-Type | application/json | Yes |

#### Request Body

```json
{
  "model": "gpt-4",
  "messages": [
    {"role": "system", "content": "You are a helpful assistant."},
    {"role": "user", "content": "Hello!"}
  ],
  "temperature": 0.7,
  "max_tokens": 1000,
  "stream": false,
  "top_p": 1,
  "frequency_penalty": 0,
  "presence_penalty": 0
}
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| model | string | required | Model ID |
| messages | array | required | Mang cac message |
| temperature | float | 0.7 | Do ngau nhien (0-2) |
| max_tokens | int | 1000 | So token toi da tra ve |
| stream | boolean | false | Streaming response |
| top_p | float | 1 | Nucleus sampling |
| frequency_penalty | float | 0 | Giam lap lai (0-2) |
| presence_penalty | float | 0 | Tang da dang (0-2) |
| stop | array | null | Stop sequences |
| n | int | 1 | So responses |

#### Message Object

```json
{
  "role": "user | assistant | system",
  "content": "Message content"
}
```

#### Response (Non-streaming)

```json
{
  "id": "chatcmpl-abc123",
  "object": "chat.completion",
  "created": 1234567890,
  "model": "gpt-4",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "Hello! How can I help you today?"
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 20,
    "completion_tokens": 10,
    "total_tokens": 30
  }
}
```

#### Response (Streaming)

Khi `stream: true`, response tra ve theo chunks:

```
data: {"id":"chatcmpl-abc123","object":"chat.completion.chunk","created":1234567890,"model":"gpt-4","choices":[{"index":0,"delta":{"content":"Hello"},"finish_reason":null}]}

data: {"id":"chatcmpl-abc123","object":"chat.completion.chunk","created":1234567890,"model":"gpt-4","choices":[{"index":0,"delta":{"content":"!"},"finish_reason":null}]}

data: {"id":"chatcmpl-abc123","object":"chat.completion.chunk","created":1234567890,"model":"gpt-4","choices":[{"index":0,"delta":{},"finish_reason":"stop"}]}

data: [DONE]
```

### 2. List Models

Lay danh sach models kha dung.

**GET** `/v1/models`

#### Response

```json
{
  "object": "list",
  "data": [
    {
      "id": "gpt-4",
      "object": "model",
      "created": 1687882411,
      "owned_by": "openai"
    },
    {
      "id": "gpt-3.5-turbo",
      "object": "model",
      "created": 1677610602,
      "owned_by": "openai"
    }
  ]
}
```

### 3. Retrieve Model

Lay thong tin chi tiet model.

**GET** `/v1/models/{model_id}`

#### Response

```json
{
  "id": "gpt-4",
  "object": "model",
  "created": 1687882411,
  "owned_by": "openai"
}
```

## Error Codes

| HTTP Code | Error Type | Description |
|-----------|------------|-------------|
| 400 | invalid_request_error | Request khong hop le |
| 401 | authentication_error | API key khong hop le |
| 402 | insufficient_credits | Het tien/token |
| 403 | permission_denied | Khong co quyen truy cap model |
| 429 | rate_limit_exceeded | Vuot gioi han rate limit |
| 500 | server_error | Loi server |
| 503 | service_unavailable | Service khong kha dung |

#### Error Response Format

```json
{
  "error": {
    "message": "Invalid API key provided",
    "type": "authentication_error",
    "code": "invalid_api_key",
    "param": null
  }
}
```

## Rate Limits

Rate limits phu thuoc vao plan cua ban:

| Plan | Requests/Minute | Requests/Day |
|------|-----------------|--------------|
| Free | 10 | 100 |
| Basic | 60 | 1000 |
| Pro | 120 | 5000 |
| Enterprise | 300 | Unlimited |

Khi vuot rate limit, ban se nhan response:

```json
{
  "error": {
    "message": "Rate limit exceeded. Please wait 60 seconds.",
    "type": "rate_limit_exceeded",
    "code": "rate_limit"
  }
}
```

Headers cho rate limit:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1234567890
```

## Code Examples

### cURL

```bash
curl -X POST https://yourdomain.com/v1/chat/completions \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "gpt-4",
    "messages": [{"role": "user", "content": "Hello!"}],
    "temperature": 0.7
  }'
```

### Streaming voi cURL

```bash
curl -X POST https://yourdomain.com/v1/chat/completions \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -N \
  -d '{
    "model": "gpt-4",
    "messages": [{"role": "user", "content": "Tell me a story"}],
    "stream": true
  }'
```

### Python

```python
import openai

# Cau hinh client
client = openai.OpenAI(
    api_key="YOUR_API_KEY",
    base_url="https://yourdomain.com/v1"
)

# Non-streaming
response = client.chat.completions.create(
    model="gpt-4",
    messages=[
        {"role": "system", "content": "You are a helpful assistant."},
        {"role": "user", "content": "Hello!"}
    ],
    temperature=0.7
)

print(response.choices[0].message.content)
```

### Python Streaming

```python
import openai

client = openai.OpenAI(
    api_key="YOUR_API_KEY",
    base_url="https://yourdomain.com/v1"
)

stream = client.chat.completions.create(
    model="gpt-4",
    messages=[{"role": "user", "content": "Tell me a story"}],
    stream=True
)

for chunk in stream:
    if chunk.choices[0].delta.content:
        print(chunk.choices[0].delta.content, end="")
```

### Python voi Requests

```python
import requests
import json

url = "https://yourdomain.com/v1/chat/completions"
headers = {
    "Authorization": "Bearer YOUR_API_KEY",
    "Content-Type": "application/json"
}
data = {
    "model": "gpt-4",
    "messages": [{"role": "user", "content": "Hello!"}]
}

response = requests.post(url, headers=headers, json=data)
result = response.json()
print(result["choices"][0]["message"]["content"])
```

### JavaScript / Node.js

```javascript
const OpenAI = require('openai');

const openai = new OpenAI({
  apiKey: 'YOUR_API_KEY',
  baseURL: 'https://yourdomain.com/v1'
});

async function chat() {
  const response = await openai.chat.completions.create({
    model: 'gpt-4',
    messages: [{ role: 'user', content: 'Hello!' }],
    temperature: 0.7
  });
  
  console.log(response.choices[0].message.content);
}

chat();
```

### JavaScript Streaming

```javascript
const OpenAI = require('openai');

const openai = new OpenAI({
  apiKey: 'YOUR_API_KEY',
  baseURL: 'https://yourdomain.com/v1'
});

async function streamChat() {
  const stream = await openai.chat.completions.create({
    model: 'gpt-4',
    messages: [{ role: 'user', content: 'Tell me a story' }],
    stream: true
  });
  
  for await (const chunk of stream) {
    const content = chunk.choices[0]?.delta?.content || '';
    process.stdout.write(content);
  }
}

streamChat();
```

### JavaScript voi Fetch

```javascript
async function callAPI() {
  const response = await fetch('https://yourdomain.com/v1/chat/completions', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_API_KEY',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      model: 'gpt-4',
      messages: [{ role: 'user', content: 'Hello!' }]
    })
  });
  
  const data = await response.json();
  console.log(data.choices[0].message.content);
}
```

### PHP

```php
<?php

$apiKey = 'YOUR_API_KEY';
$url = 'https://yourdomain.com/v1/chat/completions';

$data = [
    'model' => 'gpt-4',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ],
    'temperature' => 0.7
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
echo $result['choices'][0]['message']['content'];
```

### C# / .NET

```csharp
using System.Net.Http;
using System.Text;
using System.Text.Json;

var client = new HttpClient();
client.DefaultRequestHeaders.Add("Authorization", "Bearer YOUR_API_KEY");

var request = new
{
    model = "gpt-4",
    messages = new[]
    {
        new { role = "user", content = "Hello!" }
    }
};

var content = new StringContent(
    JsonSerializer.Serialize(request),
    Encoding.UTF8,
    "application/json"
);

var response = await client.PostAsync(
    "https://yourdomain.com/v1/chat/completions",
    content
);

var result = await response.Content.ReadAsStringAsync();
Console.WriteLine(result);
```

### Go

```go
package main

import (
    "bytes"
    "encoding/json"
    "fmt"
    "io/ioutil"
    "net/http"
)

func main() {
    url := "https://yourdomain.com/v1/chat/completions"
    
    data := map[string]interface{}{
        "model": "gpt-4",
        "messages": []map[string]string{
            {"role": "user", "content": "Hello!"},
        },
    }
    
    jsonData, _ := json.Marshal(data)
    
    req, _ := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
    req.Header.Set("Authorization", "Bearer YOUR_API_KEY")
    req.Header.Set("Content-Type", "application/json")
    
    client := &http.Client{}
    resp, _ := client.Do(req)
    defer resp.Body.Close()
    
    body, _ := ioutil.ReadAll(resp.Body)
    fmt.Println(string(body))
}
```

## Best Practices

### 1. Xu ly Errors

Luon kiem tra error response:

```python
try:
    response = client.chat.completions.create(...)
except openai.APIError as e:
    print(f"API Error: {e.message}")
except openai.RateLimitError:
    print("Rate limited, waiting...")
    time.sleep(60)
```

### 2. Retry Logic

Implement exponential backoff:

```python
import time

def call_with_retry(func, max_retries=3):
    for i in range(max_retries):
        try:
            return func()
        except Exception as e:
            if i == max_retries - 1:
                raise
            time.sleep(2 ** i)  # 1, 2, 4 seconds
```

### 3. Token Optimization

- Su dung max_tokens hop ly
- Giu system prompt ngan gon
- Xoa messages cu khong can thiet

### 4. Security

- Khong commit API key vao git
- Su dung environment variables
- Rotate keys dinh ky
- Set IP whitelist neu co the

### 5. Monitoring

- Theo doi usage trong dashboard
- Set up alerts cho low balance
- Log requests de debug

## SDKs Ho Tro

Do API tuong thich OpenAI, ban co the dung bat ky OpenAI SDK nao:

- Python: `openai`
- Node.js: `openai`
- Go: `github.com/sashabaranov/go-openai`
- .NET: `OpenAI-DotNet`
- Ruby: `ruby-openai`
- Rust: `async-openai`

Chi can thay doi `base_url` thanh domain cua ban.

## Lien He

- Tai lieu: https://yourdomain.com/docs
- Dashboard: https://yourdomain.com/dashboard
- Support: https://yourdomain.com/support
