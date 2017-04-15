<?php
namespace Module\OAuth2\Actions
{
    use Module\OAuth2\Actions\Users\RetrieveAuthenticatedUser;
    use Module\OAuth2\Model\User;


    /**
     * @property  RetrieveAuthenticatedUser $RetrieveAuthenticatedUser
     *
     * @method static User  RetrieveAuthenticatedUser()
     * @method static mixed ValidationGenerator($uid = null, array $authCodes = null, $continue = null)
     */
    class IOC extends \IOC
    { }
}


namespace Module\OAuth2\Services
{
    /**
     */
    class IOC extends \IOC
    { }
}

namespace Module\OAuth2\Services\Repository
{
    use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCodes;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens;


    /**
     * @method static iRepoClients       Clients(array $options=null)
     * @method static iRepoUsers         Users(array $options=null)
     * @method static iRepoAccessTokens  AccessTokens(array $options=null)
     * @method static iRepoRefreshTokens RefreshTokens(array $options=null)
     * @method static iRepoAuthCodes     AuthCodes(array $options=null)
     */
    class IOC extends \IOC
    { }
}
