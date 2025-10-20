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

use Exception;
use GuzzleHttp\Exception\ClientException;
use PHPExperts\RESTSpeaker\RESTSpeaker;

class AnthropicSpeaker implements IsLLMSpeaker
{
    use ModelTrait;

    private bool $returnJSON = false;

    protected ?Database $db;
    protected string $anthropicVersion;

    public RESTSpeaker $api;

    public function __construct(?RESTSpeaker $api = null)
    {
        $this->model = env('ANTHROPIC_MODEL');
        $this->anthropicVersion = env('ANTHROPIC_VERSION');

        if (!$this->model) {
            throw new \RuntimeException('Missing _ENV["ANTHROPIC_MODEL].');
        }

        if (!$this->anthropicVersion) {
            throw new \RuntimeException('Missing _ENV["ANTHROPIC_VERSION].');
        }

        if (!$api) {
            $api = new RESTSpeaker(new AnthropicAuth(), 'https://api.anthropic.com/v1');
        }

        $this->api = $api;

        if (extension_loaded('pdo_sqlite')) {
//            $this->db = new Database();
        }
    }

    public function returnText(): void
    {
        $this->returnJSON = false;
    }

    public function returnJSON(): void
    {
        $this->returnJSON = true;
    }

    /**
     * Sends a chat request to the Anthropic API using native PHP cURL functions.
     *
     * @param array $messages Array of messages as per the API chat format:
     *  [
     *    ['role' => 'user', 'content' => 'Hello!']
     *  ]
     * @return array The decoded JSON response from the API as an associative array.
     * @throws Exception if the cURL request fails or the API returns an error.
     */
    public function chat(array $messages, string $systemPrompt = ''): array
    {
        // Retrieve the API key from environment variables.
        // Make sure you have this set in your .env file or server configuration.
        $apiKey = env('ANTHROPIC_API_KEY');
//        throw new Exception("Bad ANTHROPIC Model: $this->model");
        if (!$apiKey) {
            throw new Exception('ANTHROPIC_API_KEY environment variable not set.');
        }

        // The API endpoint
        $apiUrl = 'https://api.anthropic.com/v1/messages';

        // Use the class property to determine if a JSON response is requested.
        if ($this->returnJSON) {
            $messages[] = ['role' => 'user', 'content' => 'format responses in JSON'];
        }

        // Construct the payload using class properties for model and version.
        $payload = [
            'model'      => $this->model,
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => $messages,
        ];
        file_put_contents('/srv/http/waha/payload-' . time() . '.json', json_encode($payload, JSON_PRETTY_PRINT));

        // Encode the payload into a JSON string.
        $jsonPayload = json_encode($payload);

        // Prepare the required HTTP headers.
        $headers = [
            'Content-Type: application/json',
            'anthropic-version: ' . $this->anthropicVersion,
            'x-api-key: ' . $apiKey,
        ];

        // 1. Initialize cURL session
        $ch = curl_init();

        // 2. Set cURL options
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string instead of outputting it
        curl_setopt($ch, CURLOPT_POST, true);           // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload); // Set the body of the request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  // Set the custom headers

        // 3. Execute the cURL session
        $responseBody = curl_exec($ch);

        // 4. Check for cURL errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error_msg);
        }

        // 5. Check for a non-successful HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            curl_close($ch);
            // The response body often contains useful error details from the API
            throw new Exception("HTTP Error {$httpCode}: " . $responseBody);
        }

        // 6. Close the cURL session
        curl_close($ch);

        // 7. Decode the JSON response into an associative array and return it
        $responseData = json_decode($responseBody, true);

        return $responseData;
    }

    /**
     * [Original RESTSpeaker implementation]
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
    public function chat_php(array $messages): array
    {
        if ($this->returnJSON) {
            $messages[] = ['role' => 'user', 'content' => 'format responses in JSON'];
        }
        $systemPrompt = <<<TXT
You are a friendly concierge in an apartment building. A male tenant who is contacting you is named
Maizen Eltawil. He lives in apartment number 4502. You are to help him facilitate repair requests.
His phone is 971543998492

For now, just make up an Indian-sounding repair man and give a made-up Dubai cell number. Tell him
the request has been logged and when to expect (make the times within business hours).

A plumber corp is Thomas Services UAE, at Al Saef - 1st St - Al Thanyah Third - Barsha Heights - Dubai,
phone +971 585-36-0247

Theodore R. Smith is a male tenant who lives in apartment 3602 of Sulafa Tower, Dubai Marina. His phone
is 18323039477.

Arshad Iqbal is a male tenant who lives in the Abdullah Meheirah building in Barsha Heights, Apt 402.
His phone is 919874022772.

Richard Stalwart, a male tenant in Marina Tower, Dubai Harbor, in apartment 6105, near Barsha Heights.
His phone is 923338809541.
TXT;
//        dd($messages);

        $payload = [
//            'model'    => env('ANTHROPIC_MODEL'),
            'model'    => 'claude-3-5-haiku-20241022',
            'max_tokens' => 1024,
            'system' => $systemPrompt,
            'messages' => $messages,
        ];

//        dump($payload);
        file_put_contents('/srv/http/waha/0.llmspeaker-' . time(), print_r($payload, true) . "\n", FILE_APPEND);


        file_put_contents('/srv/http/waha/llmspeaker-' . time(), 'hit: ' . __LINE__);


        // echo json_encode($payload, JSON_PRETTY_PRINT); exit;
        $response = $this->api->post('/v1/messages', $payload, [
//            'headers' => ['anthropic-version' => env('ANTHROPIC_VERSION')]
            'headers' => ['anthropic-version' => '2023-06-01']
        ]);

        $result = $payload;
//        if ($this->returnJSON) {
//            $result['messages'] += ['role' => 'assistant', 'content' => json_decode($response->choices[0]->message->content)];
//        } else {
//            $result = $payload['messages'][0]['content'];
//        }
        file_put_contents('/srv/http/waha/ai_speaker-' . time() . '.log', print_r($response, true) . "\n", FILE_APPEND);

        return (array) $response;
    }

    public function concisePrompt(string $prompt, array $chatHistory = [], array $systemPrompts = [], bool $returnJson = true): object
    {
        try {
            $concisePrompt = [['role' => 'user', 'content' => 'concise answers']];

            return $this->prompt($prompt, array_merge($chatHistory, $concisePrompt), $systemPrompts, $returnJson);
        } catch (ClientException $e) {
            echo __LINE__;
            dd((string)$e->getResponse()->getBody());
        }
    }

    public function useExactPromptPayload(array $promptPayload): object
    {
        $response = $this->api->post('/v1/chat/completions', $promptPayload);

        return (object) $response;
    }
}
