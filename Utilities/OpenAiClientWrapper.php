<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Utilities;

use GuzzleHttp\Client as HttpClient;
use OpenAI;
use RuntimeException;
use Throwable;

class OpenAiClientWrapper
{
    private const TIMEOUT = 120;
    private const CONNECT_TIMEOUT = 10;

    public function chat(string $prompt, string $apiKey, string $model): string
    {
        $client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient(new HttpClient([
                'timeout' => self::TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
            ]))
            ->make();

        try {
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
            ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('OpenAI request failed: '.$exception->getMessage(), 0, $exception);
        }

        $content = (string)($result->choices[0]->message->content ?? '');
        if ($content === '') {
            throw new RuntimeException('OpenAI returned an empty response');
        }

        return $content;
    }
}
