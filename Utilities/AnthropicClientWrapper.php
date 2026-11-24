<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Utilities;

use Anthropic;
use GuzzleHttp\Client as HttpClient;
use RuntimeException;
use Throwable;

class AnthropicClientWrapper
{
    private const TIMEOUT = 120;
    private const CONNECT_TIMEOUT = 10;

    public function chat(string $prompt, string $apiKey, string $model): string
    {
        $client = Anthropic::factory()
            ->withApiKey($apiKey)
            ->withHttpClient(new HttpClient([
                'timeout' => self::TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
            ]))
            ->make();

        try {
            $result = $client->messages()->create([
                'model' => $model,
                'max_tokens' => 8192,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('Anthropic request failed: '.$exception->getMessage(), 0, $exception);
        }

        foreach ($result->content as $content) {
            if ($content->type === 'text' && (string)$content->text !== '') {
                return (string)$content->text;
            }
        }

        throw new RuntimeException('Anthropic returned an empty response');
    }
}
