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

namespace Fusio\Impl\Repository;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Service\Connection as ConnectionService;
use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
use PSX\Sql\Condition;

/**
 * ConnectionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ConnectionDatabase implements Repository\ConnectionInterface
{
    private Connection $connection;
    private ConfigInterface $config;

    public function __construct(Connection $connection, ConfigInterface $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    public function getAll(): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_TENANT_ID, $this->getTenantId());
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_STATUS, Table\Connection::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\ConnectionTable::COLUMN_ID,
                Table\Generated\ConnectionTable::COLUMN_NAME,
                Table\Generated\ConnectionTable::COLUMN_CLASS,
            ])
            ->from('fusio_connection', 'connection')
            ->orderBy(Table\Generated\ConnectionTable::COLUMN_NAME, 'ASC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        $connections = [];
        foreach ($result as $row) {
            $connections[] = $this->newConnection($row);
        }

        return $connections;
    }

    public function get(string|int $id): ?Model\ConnectionInterface
    {
        if (is_numeric($id)) {
            $column = Table\Generated\ConnectionTable::COLUMN_ID;
        } else {
            $column = Table\Generated\ConnectionTable::COLUMN_NAME;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_TENANT_ID, $this->getTenantId());
        $condition->equals($column, $id);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\ConnectionTable::COLUMN_ID,
                Table\Generated\ConnectionTable::COLUMN_NAME,
                Table\Generated\ConnectionTable::COLUMN_CLASS,
                Table\Generated\ConnectionTable::COLUMN_CONFIG,
            ])
            ->from('fusio_connection', 'connection')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $row = $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        if (!empty($row)) {
            return $this->newConnection($row);
        } else {
            return null;
        }
    }

    private function newConnection(array $row): Model\ConnectionInterface
    {
        $config = !empty($row[Table\Generated\ConnectionTable::COLUMN_CONFIG]) ? ConnectionService\Encrypter::decrypt($row[Table\Generated\ConnectionTable::COLUMN_CONFIG], $this->config->get('fusio_project_key')) : [];

        return new Model\Connection(
            $row[Table\Generated\ConnectionTable::COLUMN_ID],
            $row[Table\Generated\ConnectionTable::COLUMN_NAME],
            $row[Table\Generated\ConnectionTable::COLUMN_CLASS],
            $config
        );
    }

    private function getTenantId(): ?string
    {
        $tenantId = $this->config->get('fusio_tenant_id');
        if (empty($tenantId)) {
            return null;
        }

        return $tenantId;
    }
}
