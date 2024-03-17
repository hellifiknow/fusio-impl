<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Consumer\Api\Token;

use Fusio\Impl\Table\App;
use Fusio\Impl\Table\Token;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use Monolog\DateTimeImmutable;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/token', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 6,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 6,
            "status": 1,
            "name": "Backend\/Developer",
            "scope": [
                "backend"
            ],
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": 5,
            "status": 1,
            "name": "Developer\/Consumer",
            "scope": [
                "consumer"
            ],
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "name": "Foo-App\/Developer",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Foo-App\/Consumer",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "status": 1,
            "name": "Developer\/Consumer",
            "scope": [
                "consumer",
                "authorization"
            ],
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "status": 1,
            "name": "Backend\/Administrator",
            "scope": [
                "backend",
                "authorization"
            ],
            "ip": "127.0.0.1",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetUnauthorized()
    {
        $response = $this->sendRequest('/consumer/token', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Missing authorization header', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/token', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'name' => 'Foo',
            'expire' => (new \DateTime())->add(new \DateInterval('P4D'))->format(\DateTimeInterface::RFC3339),
            'scope' => ['foo', 'bar']
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertNotEmpty($data->access_token);
        $this->assertEquals('bearer', $data->token_type);
        $this->assertEquals(345600, $data->expires_in);
        $this->assertNotEmpty($data->refresh_token);
        $this->assertEquals('bar,foo,authorization', $data->scope);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'app_id', 'user_id', 'name', 'token', 'refresh', 'scope')
            ->from('fusio_token')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(8, $row['id']);
        $this->assertEquals(Token::STATUS_ACTIVE, $row['status']);
        $this->assertEquals(null, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals('Foo', $row['name']);
        $this->assertNotEmpty($row['token']);
        $this->assertNotEmpty($row['refresh']);
        $this->assertEquals('bar,foo,authorization', $row['scope']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/token', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/token', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
