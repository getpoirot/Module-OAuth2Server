<?php
namespace Module\OAuth2\Services;

use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Interfaces\Repository\iRepoUsersApprovedClients;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCodes;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens;


/**
 * @method static iRepoAccessTokens         AccessTokens(array $options=null)
 * @method static iRepoRefreshTokens        RefreshTokens(array $options=null)
 * @method static iRepoAuthCodes            AuthCodes(array $options=null)
 * @method static iRepoClients              Clients(array $options=null)
 * @method static iRepoUsers                Users(array $options=null)
 * @method static iRepoUsersApprovedClients UsersApprovedClients(array $options=null)
 * @method static iRepoValidationCodes      ValidationCodes(array $options=null)
 */
class Repositories extends \IOC
{
    const AccessTokens         = 'AccessTokens';
    const RefreshTokens        = 'RefreshTokens';
    const AuthCodes            = 'AuthCodes';
    const Clients              = 'Clients';
    const Users                = 'Users';
    const UsersApprovedClients = 'Users.ApprovedClients';
    const ValidationCodes      = 'ValidationCodes';
}
