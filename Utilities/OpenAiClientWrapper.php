<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Utilities;

use OpenAI;

class OpenAiClientWrapper
{
    public function chat(string $prompt, string $apiKey): string
    {
        $client = OpenAI::client($apiKey);

        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
        ]);

        return $result->choices[0]->message->content;
    }
}
