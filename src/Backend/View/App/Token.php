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

namespace Fusio\Impl\Backend\View\App;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\App\Token\TokenQueryFilter;
use Fusio\Impl\Backend\Filter\DateQueryFilter;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token extends ViewAbstract
{
    public function getCollection(TokenQueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\AppTokenTable::COLUMN_IP, DateQueryFilter::COLUMN_DATE => Table\Generated\AppTokenTable::COLUMN_DATE], 'token');
        $condition->equals('app.' . Table\Generated\AppTable::COLUMN_TENANT_ID, $context->getTenantId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'token.' . Table\Generated\AppTokenTable::COLUMN_ID,
                'token.' . Table\Generated\AppTokenTable::COLUMN_APP_ID,
                'token.' . Table\Generated\AppTokenTable::COLUMN_USER_ID,
                'token.' . Table\Generated\AppTokenTable::COLUMN_STATUS,
                'token.' . Table\Generated\AppTokenTable::COLUMN_SCOPE,
                'token.' . Table\Generated\AppTokenTable::COLUMN_IP,
                'token.' . Table\Generated\AppTokenTable::COLUMN_DATE,
            ])
            ->from('fusio_app_token', 'token')
            ->innerJoin('token', 'fusio_app', 'app', 'token.' . Table\Generated\AppTokenTable::COLUMN_APP_ID . ' = app.' . Table\Generated\AppTable::COLUMN_ID)
            ->orderBy('token.' . Table\Generated\AppTokenTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues())
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        $countBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from('fusio_app_token', 'token')
            ->innerJoin('token', 'fusio_app', 'app', 'token.' . Table\Generated\AppTokenTable::COLUMN_APP_ID . ' = app.' . Table\Generated\AppTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countBuilder->getSQL(), $countBuilder->getParameters(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'id' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_ID),
                'appId' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_APP_ID),
                'userId' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_USER_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_STATUS),
                'scope' => $builder->fieldCsv(Table\Generated\AppTokenTable::COLUMN_SCOPE),
                'ip' => Table\Generated\AppTokenTable::COLUMN_IP,
                'date' => $builder->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $id, ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals('token.' . Table\Generated\AppTokenTable::COLUMN_ID, $id);
        $condition->equals('app.' . Table\Generated\AppTable::COLUMN_TENANT_ID, $context->getTenantId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'token.' . Table\Generated\AppTokenTable::COLUMN_ID,
                'token.' . Table\Generated\AppTokenTable::COLUMN_APP_ID,
                'token.' . Table\Generated\AppTokenTable::COLUMN_USER_ID,
                'token.' . Table\Generated\AppTokenTable::COLUMN_STATUS,
                'token.' . Table\Generated\AppTokenTable::COLUMN_TOKEN,
                'token.' . Table\Generated\AppTokenTable::COLUMN_SCOPE,
                'token.' . Table\Generated\AppTokenTable::COLUMN_IP,
                'token.' . Table\Generated\AppTokenTable::COLUMN_EXPIRE,
                'token.' . Table\Generated\AppTokenTable::COLUMN_DATE,
            ])
            ->from('fusio_app_token', 'token')
            ->innerJoin('token', 'fusio_app', 'app', 'token.' . Table\Generated\AppTokenTable::COLUMN_APP_ID . ' = app.' . Table\Generated\AppTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
            'id' => Table\Generated\AppTokenTable::COLUMN_ID,
            'app' => $builder->doEntity([$this->getTable(Table\App::class), 'find'], [new Reference('app_id')], [
                'id' => Table\Generated\AppTable::COLUMN_ID,
                'userId' => Table\Generated\AppTable::COLUMN_USER_ID,
                'status' => Table\Generated\AppTable::COLUMN_STATUS,
                'name' => Table\Generated\AppTable::COLUMN_NAME,
            ]),
            'user' => $builder->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                'id' => Table\Generated\UserTable::COLUMN_ID,
                'status' => Table\Generated\UserTable::COLUMN_STATUS,
                'name' => Table\Generated\UserTable::COLUMN_NAME,
            ]),
            'status' => Table\Generated\AppTokenTable::COLUMN_STATUS,
            'token' => Table\Generated\AppTokenTable::COLUMN_TOKEN,
            'scope' => $builder->fieldCsv(Table\Generated\AppTokenTable::COLUMN_SCOPE),
            'ip' => Table\Generated\AppTokenTable::COLUMN_IP,
            'expire' => $builder->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_EXPIRE),
            'date' => $builder->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }
}
