<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Utilities;

use Anthropic;

class AnthropicClientWrapper
{
    public function chat(string $prompt, string $apiKey): string
    {
        $client = Anthropic::client($apiKey);

        $result = $client->messages()->create([
            'model' => 'claude-3-7-sonnet-20250219', // @todo: Determine this automatically
            'max_tokens' => 8192,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        return $result->content[0]->text;
    }
}
