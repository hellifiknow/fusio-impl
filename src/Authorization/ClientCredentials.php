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

namespace Fusio\Impl\Authorization;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Impl\Table\App;
use PSX\Framework\Oauth2\Credentials;
use PSX\Framework\Oauth2\GrantType\ClientCredentialsAbstract;
use PSX\Oauth2\Authorization\Exception\InvalidClientException;
use PSX\Oauth2\Authorization\Exception\InvalidGrantException;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;
use PSX\Sql\Condition;

/**
 * ClientCredentials
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ClientCredentials extends ClientCredentialsAbstract
{
    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    private $appTokenService;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    private $scopeService;

    /**
     * @var \Fusio\Impl\Service\User
     */
    private $userService;

    /**
     * @var \Fusio\Impl\Table\App
     */
    private $appTable;

    /**
     * @var string
     */
    private $expireToken;

    /**
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Table\App $appTable
     * @param string $expireToken
     */
    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Service\User $userService, Table\App $appTable, string $expireToken)
    {
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->userService     = $userService;
        $this->appTable        = $appTable;
        $this->expireToken     = $expireToken;
    }

    /**
     * @param \PSX\Framework\Oauth2\Credentials $credentials
     * @param string $scope
     * @return \PSX\Oauth2\AccessToken
     */
    protected function generate(Credentials $credentials, $scope)
    {
        // check whether the credentials contain an app key and secret
        $app = $this->getApp($credentials->getClientId(), $credentials->getClientSecret());
        if (!empty($app)) {
            $appId  = $app['id'];
            $userId = $app['user_id'];
        } else {
            // otherwise try to authenticate the user credentials
            $appId  = null;
            $userId = $this->userService->authenticateUser($credentials->getClientId(), $credentials->getClientSecret());
        }

        if (empty($userId)) {
            throw new InvalidClientException('Unknown credentials');
        }

        if (empty($scope)) {
            // as fallback simply use all scopes assigned to the user
            $scope = implode(',', $this->userService->getAvailableScopes($userId));
        }

        // validate scopes
        $scopes = $this->scopeService->getValidScopes($scope, $appId, $userId);
        if (empty($scopes)) {
            throw new InvalidScopeException('No valid scope given');
        }

        // generate access token
        return $this->appTokenService->generateAccessToken(
            $appId === null ? 1 : $appId,
            $userId,
            $scopes,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new \DateInterval($this->expireToken)
        );
    }

    private function getApp(string $appKey, string $appSecret)
    {
        $condition = new Condition();
        $condition->equals('app_key', $appKey);
        $condition->equals('app_secret', $appSecret);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->getOneBy($condition);
        if (empty($app)) {
            return null;
        }

        return $app;
    }
}
