<?php declare(strict_types=1);

/**
 * This file is part of ChatGPT Speaker, a PHP Experts, Inc., Project.
 *
 * Copyright © 2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore.smith@autonomo.codes>
 *   GPG Fingerprint: 6CAC F838 454C 8912 8AA2  26DB 89DC D8F1 3BB9 33B3
 *   https://www.phpexperts.pro/
 *   https://www.autonomo.codes/
 *   https://github.com/AutonomoDev/ai-speaker
 *
 * This file is licensed under the Creative Commons No-Derivations v4.0 License.
 * Most rights are reserved.
 */

namespace Autonomo\AiSpeaker;

use PHPExperts\RESTSpeaker\RESTSpeaker;

class GrokSpeaker extends RESTSpeaker implements IsLLMSpeaker
{
    use ModelTrait;

    public function __construct(?GrokAuth $auth = null, string $baseUrl = 'https://api.x.ai/v1/')
    {
        if (!$auth) {
            $auth = new GrokAuth();
        }

        $this->model = 'grok-4';

        parent::__construct($auth, $baseUrl);
    }

    /**
     * Send chat messages to xAI Grok chat completions endpoint.
     *
     * @param array $messages Array of messages as per OpenAI chat format:
     *  [
     *    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
     *    ['role' => 'user', 'content' => 'Hello!']
     *  ]
     * @return array[string, object] The LLM's API response
     * @throws \Exception on HTTP or API error
     */
    public function chat(array $messages, string $systemPrompt = ''): array
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        $endpoint = 'chat/completions';

        // POST request
        $response = $this->post($endpoint, $payload);

        // The API response typically has `choices` array with message content
        if (!isset($response->choices[0]->message->content)) {
            throw new \Exception('Invalid API response: ' . json_encode($response));
        }

        return [$response->choices[0]->message->content, $response];
    }

    public function changeModel(string $model): void
    {
        $this->model = $model;
    }
}
