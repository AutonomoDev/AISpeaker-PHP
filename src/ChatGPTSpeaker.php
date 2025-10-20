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

use GuzzleHttp\Exception\ClientException;
use Autonomo\AiSpeaker\DTO\ChatGptChatResponse;
use Autonomo\AiSpeaker\DTO\ChatGptChoice;
use Autonomo\AiSpeaker\DTO\ChatGptChoiceMessage;
use Autonomo\AiSpeaker\DTO\ChatGptResponseUsage;
use Autonomo\AiSpeaker\DTO\CompletionTokensDetails;
use Autonomo\AiSpeaker\DTO\PromptTokensDetails;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\RESTSpeaker\RESTSpeaker;
use PHPExperts\SimpleDTO\SimpleDTO;

class ChatGPTSpeaker implements IsLLMSpeaker
{
    use ModelTrait;

    private bool $returnJSON = true;

    protected ?Database $db;

    public RESTSpeaker $api;

    public function __construct(RESTSpeaker $api = null)
    {
        if (!$api) {
            $api = new RESTSpeaker(new OpenAiAuth(), 'https://api.openai.com');
        }

        $this->api = $api;

        if (extension_loaded('pdo_sqlite')) {
//            $this->db = new Database();
        }

        $this->model = env('OPENAI_GPT_MODEL');
    }

    /**
     * Tells ChatGPT to return future responses in text format.
     *
     * @return void
     */
    public function returnText(): void
    {
        $this->returnJSON = false;
    }

    /**
     * Tells ChatGPT to return future responses in JSON format.
     *
     * @return void
     */
    public function returnJSON(): void
    {
        $this->returnJSON = true;
    }

    /**
     * Send chat messages to xAI Grok chat completions endpoint.
     *
     * @param array $messages Array of messages as per OpenAI chat format:
     *  [
     *    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
     *    ['role' => 'user', 'content' => 'Hello!']
     *  ]
     * @param string $systemPrompt
     * @return array[string, object] The LLM's API response
     */
    public function chat(array $messages, string $systemPrompt = ''): array
    {
        $payload = [
            'model'    => $this->model,
            'messages' => $messages,
        ];

        $response = $this->useExactPromptPayload($payload);

        return [$response->choices[0]->message->content, $response];
    }

    /**
     * Prompt the ChatGPT model with a given prompt and optional parameters.
     *
     * @param string $prompt The input text to generate a response for.
     * @param array $chatHistory An array of previous conversation messages. Default is an empty array.
     * @param array $systemPrompts An array of system prompts to set the behavior of the model. Default is an empty array.
     * @param bool $useCache Whether to use cached responses or not. Default is true.
     * @return ChatGptChatResponse The response from the ChatGPT model.
     */
    public function prompt(
        string $prompt,
        array $chatHistory = [],
        array $systemPrompts = [],
        bool $useCache = true): ChatGptChatResponse
    {
        $t = <<<OEM_MOTOROLA
3A55751231442504#5A59323244585A513954006D6F746F726F6C0000#17BB8F98FC87113C897CF4198CD034A6AE158A86#F881E760000000000000000000000000
OEM_MOTOROLA;

        if ($this->returnJSON) {
            $systemPrompts[] = ['role' => 'user', 'content' => 'format responses in JSON'];
            $systemPrompts[] = ['role' => 'user', 'content' => 'Just the ask. No description or extra words.'];
        }

        $payload = [
            'model'    => env('OPENAI_GPT_MODEL'),
            'messages' => [
                ...$systemPrompts,
                ...$chatHistory,
                ['role' => 'user', 'content' => $prompt],
            ]
        ];
        dump($payload);
        $cacheKey = md5(json_encode($payload));
        $cacheFile = "/srv/http/waha/chatgpt-speaker.$cacheKey.json";
        if ($useCache && (!file_exists($cacheFile) && filemtime($cacheFile) < time() + 86400)) {
            //echo json_encode($payload, JSON_PRETTY_PRINT); exit;
            $response = $this->api->post('/v1/chat/completions', $payload);
            // An error occurred. Return the response directly, as-is (probably a
            // GuzzleHttp\Response object).
            if ($this->api->getLastResponse()->getStatusCode() !== 200) {
                throw new \RuntimeException((string) $this->api->getLastResponse()->getBody());
            }

            //dd((string) $this->api->getLastResponse()->getBody());
            file_put_contents(
                $cacheFile,
                (string)$this->api->getLastResponse()->getBody()
            );
        } else {
            $jsonData = file_get_contents($cacheFile);
            $response = json_decode($jsonData);
            unset($jsonData);
        }

        dump($response);
        try {
            // Build the ChatGptResponse DTO.
            // Trick the autoloader
            if (class_exists(ChatGptChatResponse::class)) {

            }
            foreach ($response->choices as $i => $choice) {
                $message = new ChatGptChoiceMessage((array) $choice->message, $this->returnJSON);
                $choice->message = $message;
                $response->choices[$i] = new ChatGptChoice((array) $choice);
            }

            $response->usage->prompt_tokens_details = new PromptTokensDetails((array) $response->usage->prompt_tokens_details);
            $response->usage->completion_tokens_details = new CompletionTokensDetails((array) $response->usage->completion_tokens_details);

            $response->usage = new ChatGptResponseUsage((array) $response->usage);

            //$response = ChatGptChatResponse::fromJson($response);
            $dto = new ChatGptChatResponse((array) $response);

//            $gptResponse = $llm->prompt($text);
            // Get the AI and person responses, as an array...
            $dto->choices[0]->nessage;

            // Get the usage stats
            $usedTokens = $dto->usage->prompt_tokens;
            $dto->usage->prompt_token_details->audio_tokens;
            $dto->usage->prompt_token_details->cached_tokens;
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }


        if ($this->returnJSON) {
//            dd($dto->)
            //dd($response->choices[0]);
        }

        return $dto;
    }

    /**
     * Prompt the ChatGPT model with a given prompt and optional parameters for concise answers.
     *
     * @param string $prompt The input text to generate a response for.
     * @param array $chatHistory An array of previous conversation messages. Default is an empty array.
     * @param array $systemPrompts An array of system prompts to set the behavior of the model. Default is an empty array.
     * @param bool $returnJson Whether to return the response in JSON format or not. Default is true.
     * @return object The concise response from the ChatGPT model.
     */
    public function concisePrompt(string $prompt, array $chatHistory = [], array $systemPrompts = [], bool $returnJson = true): object
    {
        $concisePrompt = [['role' => 'user', 'content' => 'concise answers']];

        return $this->prompt($prompt, array_merge($chatHistory, $concisePrompt), $systemPrompts, $returnJson);
    }

    /**
     * Send an exact prompt payload to the ChatGPT API and return the response.
     *
     * @param array $promptPayload The exact prompt payload to send to the API.
     * @return object The response from the ChatGPT API.
     */
    public function useExactPromptPayload(array $promptPayload): object
    {
        $response = $this->api->post('/v1/chat/completions', $promptPayload);

        return (object) $response;
    }
}
