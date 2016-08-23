<?php
namespace Module\OAuth2\Model\Repo\Stateless;

use Poirot\OAuth2\Interfaces\iEncrypt;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityRefreshToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshToken;
use Poirot\OAuth2\Model\AccessToken;
use Poirot\OAuth2\Model\RefreshToken;


class RefreshTokens
    implements iRepoRefreshToken
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
     * @param iEntityRefreshToken $token
     *
     * @return iEntityAccessToken include insert id
     */
    function insert(iEntityRefreshToken $token)
    {
        $tokenData = array(
            ## this identifier give back when unserialize token
            #- it can be the used as id on other persistence
            'identifier'              => $token->getIdentifier(), 
            'access_token_identifier' => $token->getAccessTokenIdentifier(),
            'client_identifier'       => $token->getClientIdentifier(),
            'expiry_date_time'        => $token->getExpiryDateTime(),
            'scopes'                  => $token->getScopes(),
            'owner_identifier'        => $token->getOwnerIdentifier(),
        );

        // Identifier will give back to user as token
        $identifier = serialize($tokenData);
        $identifier = $this->encryption->encrypt($identifier);

        $newToken = new RefreshToken($tokenData);
        $newToken->setIdentifier($identifier);
        return $newToken;
    }

    /**
     * Find Token Match By Identifier
     *
     * @param string $tokenIdentifier
     *
     * @return iEntityRefreshToken|false
     */
    function findByIdentifier($tokenIdentifier)
    {
        $tokenData = $this->encryption->decrypt($tokenIdentifier);
        $tokenData = unserialize($tokenData);

        $token = new RefreshToken($tokenData);
        $token->setIdentifier($tokenIdentifier); // replace identifier to stateless one
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
