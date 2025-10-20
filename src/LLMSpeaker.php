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

class LLMSpeaker implements IsLLMSpeaker
{
    private IsLLMSpeaker $ai;

    public function __construct(string|IsLLMSpeaker $ai)
    {
        if ($ai instanceof IsLLMSpeaker) {
            $this->ai = $ai;
        } else {
            $className = 'Autonomo\AiSpeaker\\' . $ai . 'Speaker';
            if (class_exists($className)) {
                $this->ai = new $className();
            } else {
                throw new \RuntimeException("The speaker class '{$className}' was not found.");
            }
        }
    }

    public function listSpeakers(): array
    {
        return [
            'Anthropic',
            'ChatGPT',
            'Grok',
        ];
    }

    public function changeModel(string $model): void
    {
        $this->ai->changeModel($model);
    }

    public function getModel(): string
    {
        return $this->ai->getModel();
    }

    public function changeSpeaker(IsLLMSpeaker $api): void
    {
        $this->ai = $api;
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
        return $this->ai->chat($messages, $systemPrompt);
    }
}
