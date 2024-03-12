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

namespace Fusio\Impl\Service\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Fusio\Impl\Service\System\FrameworkConfig;

/**
 * JsonWebToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class JsonWebToken
{
    private const ALGO = 'HS256';

    private FrameworkConfig $frameworkConfig;

    public function __construct(FrameworkConfig $frameworkConfig)
    {
        $this->frameworkConfig = $frameworkConfig;
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->frameworkConfig->getProjectKey(), self::ALGO);
    }

    public function decode(string $jwt): \stdClass
    {
        return JWT::decode($jwt, new Key($this->frameworkConfig->getProjectKey(), self::ALGO));
    }
}
