<?php
namespace Module\OAuth2\Services\Repository;

use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCodes;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUsers;

/**
 * Usage:
 *   to ease access to IoC nested containers
 *   Module\Places\Services\Repository\IOC::places()
 * 
 * @method static iRepoClients       Clients(array $options=null)
 * @method static iRepoUsers         Users(array $options=null)
 * @method static iRepoAccessTokens  AccessTokens(array $options=null)
 * @method static iRepoRefreshTokens RefreshTokens(array $options=null)
 * @method static iRepoAuthCodes     AuthCodes(array $options=null)
 *
 */
class IOC extends \IOC
{ }
