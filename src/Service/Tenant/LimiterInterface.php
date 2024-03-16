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

namespace Fusio\Impl\Service\Tenant;

/**
 * LimiterInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
interface LimiterInterface
{
    public function getActionCount(): int;
    public function getAppCount(): int;
    public function getCategoryCount(): int;
    public function getConnectionCount(): int;
    public function getCronjobCount(): int;
    public function getEventCount(): int;
    public function getIdentityCount(): int;
    public function getOperationCount(): int;
    public function getPageCount(): int;
    public function getPlanCount(): int;
    public function getRateCount(): int;
    public function getRoleCount(): int;
    public function getSchemaCount(): int;
    public function getScopeCount(): int;
    public function getUserCount(): int;
    public function getWebhookCount(): int;
}
