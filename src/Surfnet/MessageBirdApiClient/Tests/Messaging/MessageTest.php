<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\MessageBirdApiClient\Tests\Messaging;

use Surfnet\MessageBirdApiClient\Messaging\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidRecipientTypes
     * @param mixed $recipient
     */
    public function testThrowsAnInvalidArgumentExceptionWhenGivenANonStringRecipient($recipient)
    {
        $this->setExpectedException('Surfnet\MessageBirdApiClient\Exception\InvalidArgumentException');

        new Message($recipient, 'body');
    }

    /**
     * @dataProvider invalidRecipientFormats
     * @param string $recipient
     */
    public function testItThrowsOnIncorrectlyFormattedRecipient($recipient)
    {
        $this->setExpectedException('Surfnet\MessageBirdApiClient\Exception\DomainException');

        new Message($recipient, 'body');
    }

    /**
     * @dataProvider invalidBodyTypes
     * @param mixed $body
     */
    public function testItDetectsInvalidBody($body)
    {
        $this->setExpectedException('Surfnet\MessageBirdApiClient\Exception\InvalidArgumentException');

        new Message('31612345678', $body);
    }

    public function testItHasARecipient()
    {
        $message = new Message('31612345678', 'body');
        $this->assertEquals('31612345678', $message->getRecipient());
    }

    public function testItHasABody()
    {
        $message = new Message('31612345678', 'body');
        $this->assertEquals('body', $message->getBody());
    }

    public function invalidRecipientTypes()
    {
        return [
            'Not a phone number, but NULL' => [null],
            'Not a phone number, but an object' => [new \stdClass],
        ];
    }

    public function invalidRecipientFormats()
    {
        return [
            'Empty phone number' => [''],
            'Non-numeric phone number' => ['8d98ap'],
        ];
    }

    public function invalidBodyTypes()
    {
        return [
            'Not a string, but NULL' => [null],
            'Not a string, but an object' => [new \stdClass],
            'Not a string, but an integer' => [3],
        ];
    }
}