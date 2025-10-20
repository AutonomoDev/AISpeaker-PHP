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

namespace Autonomo\AiSpeaker\DTO;

use Carbon\Carbon;
use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\SimpleDTO\NestedDTO;
use PHPExperts\SimpleDTO\SimpleDTO;

/**
 * @internal
 * @property-read string  $role
 * @property-read mixed  $content
 * @property-read ?string $refusal
 * @property-read array   $annotations
 */
class ChatGptChoiceMessage extends SimpleDTO
{
    public function __construct(array              $input,
                                bool               $isJson = bool,
                                array              $options = [],
                                ?DataTypeValidator $validator = null
    )
    {
        if ($isJson === true) {
            //if (json_validate())
            $input['content'] = json_decode(stripFirstAndLastLines($input['content']));
        }

        parent::__construct($input, $options, $validator);
    }
}

/**
 * @internal
 * @property-read int $index
 * @property-read ChatGptChoiceMessage $nessage
 * @property-read ?string $logprobs
 * @property-read string $finish_reason
 */
class ChatGptChoice extends NestedDTO
{
}

/**
 * @property-read int $cached_tokens
 * @property-read int $audio_tokens
 */
class PromptTokensDetails extends SimpleDTO
{
}

/**
 * @internal
 * @property-read int $reasoning_tokens
 * @property-read int $audio_tokens
 * @property-read int $accepted_prediction_tokens
 * @property-read int $rejected_prediction_tokens
 */
class CompletionTokensDetails extends SimpleDTO
{
}

/**
 * @property-read int                     $prompt_tokens
 * @property-read int                     $completion_tokens
 * @property-read int                     $total_tokens
 * @property-read PromptTokensDetails     $prompt_token_details
 * @property-read CompletionTokensDetails $completion_token_details
 */
class ChatGptResponseUsage extends NestedDTO
{
}


/**
 * @property-read string               $id
 * @property-read string               $object
 * @property-read Carbon               $created
 * @property-read string               $model
 * @property-read ChatGptChoice[]      $choices
 * @property-read ChatGptResponseUsage $usage
 * @property-read string               $service_tier
 * @property-read string               $system_fingerprint
 */
class ChatGptChatResponse extends NestedDTO
{
    public function __construct(array              $input,
                                array              $DTOs = [],
                                ?array             $options = null,
                                ?DataTypeValidator $validator = null
    )
    {
        // Convert from epoch to Carbon.
        $input['created'] = Carbon::createFromTimestamp($input['created']);

        parent::__construct($input, $DTOs, $options, $validator);
    }
    /*
     * {\n
  "id": "chatcmpl-BC3I840D7nvYtoERC8CnYoXWHHNQV",\n
  "object": "chat.completion",\n
  "created": 1742212288,\n
  "model": "gpt-4o-mini-2024-07-18",\n
  "choices": [\n
    {\n
      "index": 0,\n
      "message": {\n
        "role": "assistant",\n
        "content": "```json\n{\n  \"php_versions\": }\n  ]\n}\n```",\n
        "refusal": null,\n
        "annotations": []\n
      },\n
      "logprobs": null,\n
      "finish_reason": "stop"\n
    }\n
  ],\n
  "usage": {\n
    "prompt_tokens": 68,\n
    "completion_tokens": 609,\n
    "total_tokens": 677,\n
    "prompt_tokens_details": {\n
      "cached_tokens": 0,\n
      "audio_tokens": 0\n
    },\n
    "completion_tokens_details": {\n
      "reasoning_tokens": 0,\n
      "audio_tokens": 0,\n
      "accepted_prediction_tokens": 0,\n
      "rejected_prediction_tokens": 0\n
    }\n
  },\n
  "service_tier": "default",\n
  "system_fingerprint": "fp_06737a9306"\n
}
     */
}

function stripFirstAndLastLines(string $string): string
{
    $lines = explode("\n", $string);

    // Remove first and last lines (if there are at least 3 lines)
    if (count($lines) >= 3) {
        array_shift($lines); // Remove first line
        array_pop($lines);   // Remove last line
    }

    return implode("\n", $lines);
}
