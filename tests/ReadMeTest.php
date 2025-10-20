<?php declare(strict_types=1);

/**
 * This file is part of ChatGPT Speaker, a PHP Experts, Inc., Project.
 *
 * Copyright © 2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore.smith@autonomo.codes>
 *   GPG Fingerprint: 6CAC F838 454C 8912 8AA2  26DB 89DC D8F1 3BB9 33B3
 *   https://www.phpexperts.pro/
 *   https://github.com/AutonomoDev/ai-speaker
 *
 * This file is licensed under the Creative Commons No-Derivations v4.0 License.
 * Most rights are reserved.
 */

namespace Autonomo\AiSpeaker\Tests;

use Autonomo\AiSpeaker\ChatGPTSpeaker;
use PHPUnit\Framework\TestCase;

/** @testdox Tests for all of the README's examples */
class ReadMeTest extends TestCase
{
    public function testExample1()
    {
        $chatGPT = new ChatGPTSpeaker();

        $prompt = <<<PROMPT
        Please create a table of the PHP major-minor version releases (5.2, 7.4, 
        etc.) along with the date of release, starting with v1.0.
        PROMPT;
        $response = $chatGPT->prompt($prompt);
dd($response->toArray());
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    public function testExample2()
    {
        $chatGPT = new ChatGPTSpeaker();
        $chatGPT->returnText();

        $prompt = <<<PROMPT
        Please create a table of the PHP minor version releases along with the date of release.
        PROMPT;
        $response = $chatGPT->prompt($prompt);

//        var_dump((string)$chatGPT->api->getLastResponse()->getBody());
        //var_dump($chatGPT->api->http->testHandler->getRecords());
    }
}
