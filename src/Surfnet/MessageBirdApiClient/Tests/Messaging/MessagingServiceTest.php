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

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use Surfnet\MessageBirdApiClient\Messaging\Message;
use Surfnet\MessageBirdApiClient\Messaging\MessagingService;

class MessagingServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testItSendsAMessage()
    {
        $http = new Client;
        $http->getEmitter()->attach(new Mock([__DIR__ . '/fixtures/it-sends-a-message.txt']));

        $messaging = new MessagingService($http);
        $result = $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));

        $this->assertTrue($result->isSuccess());
    }

    public function testHandlesUnprocessableEntities()
    {
        $http = new Client;
        $http->getEmitter()->attach(new Mock([__DIR__ . '/fixtures/it-handles-unprocessable-entities.txt']));

        $messaging = new MessagingService($http);
        $result = $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));

        $this->assertTrue($result->isMessageInvalid());
    }

    public function testHandlesInvalidAccessKey()
    {
        $http = new Client;
        $http->getEmitter()->attach(new Mock([__DIR__ . '/fixtures/it-handles-invalid-access-key.txt']));

        $messaging = new MessagingService($http);
        $result = $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));

        $this->assertTrue($result->isAccessKeyInvalid());
        $this->assertEquals(
            '(#2) Request not allowed (incorrect access_key); (#9) no (correct) recipients found; '
                . '(#10) originator is invalid',
            $result->getErrorsAsString()
        );
    }

    /**
     * @dataProvider other4xxStatusCodes
     * @param string $fixture Filename to HTTP response fixture.
     * @param string $errorString
     */
    public function testThrowsApiDomainExceptionsOnOther4xxStatusCodes($fixture, $errorString)
    {
        $this->setExpectedException('Surfnet\MessageBirdApiClient\Exception\ApiRuntimeException', $errorString);

        $http = new Client;

        $http->getEmitter()->attach(new Mock([$fixture]));

        $messaging = new MessagingService($http);
        $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));
    }

    /**
     * @dataProvider other5xxStatusCodes
     * @param string $fixture Filename to HTTP response fixture.
     * @param string $errorString
     */
    public function testHandlesOther5xxStatusCodes($fixture, $errorString)
    {
        $http = new Client;
        $http->getEmitter()->attach(new Mock([$fixture]));

        $messaging = new MessagingService($http);
        $result = $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));

        $this->assertFalse($result->isSuccess());
        $this->assertEquals($errorString, $result->getErrorsAsString());
    }

    public function testThrowsApiRuntimeExceptionsOnBrokenJson()
    {
        $this->setExpectedException('Surfnet\MessageBirdApiClient\Exception\ApiRuntimeException', 'valid JSON');

        $http = new Client;
        $http->getEmitter()->attach(new Mock([__DIR__ . '/fixtures/201-json-error.txt']));

        $messaging = new MessagingService($http);
        $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));
    }

    public function testThrowsApiRuntimeExceptionWhenUnknownStatusCode()
    {
        $this->setExpectedException(
            'Surfnet\MessageBirdApiClient\Exception\ApiRuntimeException',
            'Unexpected MessageBird server behaviour'
        );

        $http = new Client;
        $http->getEmitter()->attach(new Mock([__DIR__ . '/fixtures/101-switching-protocols.txt']));

        $messaging = new MessagingService($http);
        $messaging->send(new Message('SURFnet', '31612345678', 'This is a text message.'));
    }

    public function other4xxStatusCodes()
    {
        return [
            [__DIR__ . '/fixtures/other-4xx-406.txt', '(#20) message not found'],
            [__DIR__ . '/fixtures/other-4xx-415.txt', ''],
        ];
    }

    public function other5xxStatusCodes()
    {
        return [
            [__DIR__ . '/fixtures/502-bad-gateway.txt', '(#9) no (correct) recipients found'],
            [__DIR__ . '/fixtures/503-service-unavailable.txt', ''],
        ];
    }
}
