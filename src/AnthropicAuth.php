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

use PHPExperts\RESTSpeaker\RESTAuth;
use PHPExperts\RESTSpeaker\RESTSpeaker;

class AnthropicAuth extends RESTAuth
{
    private string $apiKey;

    public function __construct(?RESTSpeaker $apiClient = null)
    {
        parent::__construct(RESTAuth::AUTH_MODE_XAPI, $apiClient);

        $this->apiKey = env('ANTHROPIC_API_KEY');
    }

    public function generateXAPITokenOptions(): array
    {
        return [
            'headers' => [
                'x-api-key' => $this->apiKey
            ]
        ];
    }
}
