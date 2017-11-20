<?php
namespace Module\OAuth2\Model\Driver\Redis;

use Module\OAuth2\Model\Driver\Mongo\AccessTokenEntity;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
use Poirot\OAuth2\Model\AccessToken as BaseAccessToken;
use Poirot\OAuth2\Model\AccessToken;
use Poirot\Storage\Interchange\SerializeInterchange;
use Predis;


class AccessTokenRepo
    implements iRepoAccessTokens
{
    const PREFIX = 'oauth.access_token.';
    const DIVIDER = '.';

    /** @var Predis\Client  */
    protected $client;

    /** @var SerializeInterchange */
    private $_interchangable;


    /**
     * Initialize Object
     *
     * @param Predis\Client $client
     */
    function __construct(Predis\Client $client)
    {
        $this->client = $client;

        $this->_interchangable = new SerializeInterchange;
    }


    /**
     * Insert New Token
     *
     * @param iEntityAccessToken $token
     *
     * @return iEntityAccessToken|boolean include insert id
     */
    function insert(iEntityAccessToken $token)
    {
        $accToken   = \Poirot\Std\generateUniqueIdentifier(20);
        $expiredAt  = ( $token->getDateTimeExpiration() )
            ? $token->getDateTimeExpiration()->getTimestamp() - time()
            : null;


        $result = $this->client->set(
            self::PREFIX . $accToken
            , $this->_interchangable->makeForward($token)
        );

        if (! is_null($expiredAt) ) {
            $this->client->expire(
                self::PREFIX . $accToken
                , $expiredAt
            );
        }

        if ( is_null($result) )
            return false;

        $return = new BaseAccessToken;
        $return
            ->setIdentifier($accToken)
            ->setClientIdentifier($token->getClientIdentifier())
            ->setDateTimeExpiration($token->getDateTimeExpiration())
            ->setScopes($token->getScopes())
            ->setOwnerIdentifier($token->getOwnerIdentifier())
        ;

        return $return;
    }

    /**
     * Find Token Match By Identifier
     *
     * note: it must not gather tokens that expired by time
     *
     * @param string $tokenIdentifier
     *
     * @return iEntityAccessToken|false
     */
    function findByIdentifier($tokenIdentifier)
    {
        $result = $this->client->get(self::PREFIX.$tokenIdentifier);

        if (! $result)
            return false;


        /** @var AccessTokenEntity $e */
        $e = $this->_interchangable->retrieveBackward($result);

        // Check For Expiration
        //
        if (time() > $e->getDateTimeExpiration()->getTimestamp() )
            return false;


        $accessToken = new AccessToken();
        $accessToken
            ->setIdentifier( $e->getIdentifier() )
            ->setClientIdentifier( $e->getClientIdentifier() )
            ->setDateTimeExpiration( $e->getDateTimeExpiration() )
            ->setScopes( $e->getScopes() )
            ->setOwnerIdentifier( (string) $e->getOwnerIdentifier() )
        ;

        $r = $accessToken;

        return $r ? $r : false;
    }

    /**
     * Remove Token
     * used to revoke token!
     *
     * @param string $tokenIdentifier
     *
     * @return int|false
     */
    function removeByIdentifier($tokenIdentifier)
    {
        $delete = $this->client->del([self::PREFIX.$tokenIdentifier]);
        return (is_null($delete)) ? false :  $delete;
    }
}
