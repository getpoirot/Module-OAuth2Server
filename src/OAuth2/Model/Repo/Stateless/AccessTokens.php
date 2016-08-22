<?php
namespace Module\OAuth2\Model\Repo\Stateless;

use Poirot\OAuth2\Interfaces\iEncrypt;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessToken;
use Poirot\OAuth2\Model\AccessToken;


class AccessTokens
    implements iRepoAccessToken
{
    /** @var iEncrypt */
    protected $encryption;


    /**
     * AccessTokens constructor.
     *
     * @param iEncrypt $encryption
     */
    function __construct(iEncrypt $encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * Insert New Token
     *
     * @param iEntityAccessToken $token
     *
     * @return iEntityAccessToken include insert id
     */
    function insert(iEntityAccessToken $token)
    {
        $tokenArray = \Poirot\Std\cast($token)->toArray();

        // Identifier will give back to user as token
        $identifier = serialize($tokenArray);
        $identifier = $this->encryption->encrypt($identifier);

        $newToken = new AccessToken($tokenArray);
        $newToken->setIdentifier($identifier);

        return $newToken;
    }

    /**
     * Find Token Match By Identifier
     *
     * @param string $tokenIdentifier
     *
     * @return iEntityAccessToken|false
     */
    function findByIdentifier($tokenIdentifier)
    {
        $token = $this->encryption->decrypt($tokenIdentifier);
        $token = unserialize($token);
        
        $token = new AccessToken($token);
        return $token;
    }

    /**
     * Remove Token From Persistence
     * used to revoke token!
     *
     * @param string $tokenIdentifier
     *
     * @return void
     */
    function removeByIdentifier($tokenIdentifier)
    {
        // Stateless Access Tokens Revoke Not Implemented!
        // ..

    }
}
