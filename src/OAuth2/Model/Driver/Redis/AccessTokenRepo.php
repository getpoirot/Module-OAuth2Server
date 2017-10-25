<?php
namespace Module\OAuth2\Model\Driver\Redis;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
use Poirot\OAuth2\Model\AccessToken as BaseAccessToken;
use Poirot\OAuth2\Model\AccessToken;
use Poirot\Storage\Interchange\SerializeInterchange;
use Predis;


class AccessTokenRepo
    implements iRepoAccessTokens
{
    const REDIS_SERVER = 'db-master-redis';
    const PREFIX = 'oauth.access_token.';
    const DIVIDER = '.';

    /** @var Predis\Client  */
    protected $client;

    /** @var SerializeInterchange */
    private $_interchangable;

    /**
     * Initialize Object
     *
     */
    protected function __construct()
    {
        $this->client = new predis\Client([
            'schema' => 'tcp',
            'host'   => self::REDIS_SERVER,
            'port'   => 6379
        ]);

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
        $accToken = \Poirot\Std\generateUniqueIdentifier(20);
        $expiredAt    = ($token->getDatetimeExpiration())
            ? strtotime($token->getDatetimeExpiration()) - time()
            : null;

        $result = $this->client->set(
            self::PREFIX . $accToken,
            $this->_interchangable->makeForward($token)
        );

        if(!is_null($expiredAt)) {
            $this->client->expire(
                self::PREFIX . $accToken,
                $expiredAt
            );
        }

        if(is_null($result))
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
            return null;

        $e = $this->_interchangable->retrieveBackward($result);


        $accessToken = new AccessToken();
        $accessToken
            ->setIdentifier( $e['identifier'] )
            ->setClientIdentifier( $e['clientidentifier'] )
            ->setDateTimeExpiration( $e['datetimeexpiration'] )
            ->setScopes( $e['scopes'] )
            ->setOwnerIdentifier( (string) $e['owneridentifier'] )
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
