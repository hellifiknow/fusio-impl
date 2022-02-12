<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\View\App\Token;

use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View\QueryFilterAbstract;
use PSX\Sql\Condition;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class QueryFilter extends QueryFilterAbstract
{
    protected ?int $appId = null;
    protected ?int $userId = null;
    protected ?int $status = null;
    protected ?string $scope = null;
    protected ?string $ip = null;

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getCondition(?string $alias = null): Condition
    {
        $condition = parent::getCondition($alias);
        $alias     = $alias !== null ? $alias . '.' : '';

        if (!empty($this->appId)) {
            $condition->equals($alias . 'app_id', $this->appId);
        }

        if (!empty($this->userId)) {
            $condition->equals($alias . 'user_id', $this->userId);
        }

        if (!empty($this->status)) {
            $condition->equals($alias . 'status', $this->status);
        }

        if (!empty($this->scope)) {
            $condition->like($alias . 'scope', '%' . $this->scope . '%');
        }

        if (!empty($this->ip)) {
            $condition->like($alias . 'ip', $this->ip);
        }

        return $condition;
    }

    public static function create(RequestInterface $request): static
    {
        $filter = parent::create($request);
        $appId  = $request->get('appId');
        $userId = $request->get('userId');
        $status = $request->get('status');
        $scope  = $request->get('scope');
        $ip     = $request->get('ip');
        $search = $request->get('search');

        // parse search if available
        if (!empty($search)) {
            $parts = explode(',', $search);
            foreach ($parts as $part) {
                $part = trim($part);
                if (filter_var($part, FILTER_VALIDATE_IP) !== false) {
                    $ip = $part;
                } else {
                    $scope = $part;
                }
            }
        }

        if ($filter instanceof self) {
            $filter->appId  = $appId;
            $filter->userId = $userId;
            $filter->status = $status;
            $filter->scope  = $scope;
            $filter->ip     = $ip;
        }

        return $filter;
    }
}
