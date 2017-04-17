<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
use Poirot\OAuth2\Model\AccessToken as BaseAccessToken;


class AccessTokens
    extends aRepository
    implements iRepoAccessTokens
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new AccessToken);
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
        $accToken = new AccessToken;
        $accToken
            ->setIdentifier(\Poirot\OAuth2\generateUniqueIdentifier(20))
            ->setClientIdentifier($token->getClientIdentifier())
            ->setDateTimeExpiration($token->getDateTimeExpiration())
            ->setScopes($token->getScopes())
            ->setOwnerIdentifier($token->getOwnerIdentifier())
        ;

        $r = $this->_query()->insertOne($accToken);

        $return = new BaseAccessToken;
        $return
            ->setIdentifier($accToken->getIdentifier())
            ->setClientIdentifier($accToken->getClientIdentifier())
            ->setDateTimeExpiration($accToken->getDateTimeExpiration())
            ->setScopes($accToken->getScopes())
            ->setOwnerIdentifier($accToken->getOwnerIdentifier())
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
        /** @var AccessToken $r */
        $r = $this->_query()->findOne([
            'identifier' => $tokenIdentifier,
        ]);

        # check expire time
        if ( $r && \Poirot\OAuth2\checkExpiry($r->getDateTimeExpiration()) ) {
            // enforce delete expired token entity!!
            $this->removeByIdentifier($tokenIdentifier);
            return false;
        }

        return $r ? $r : false;
    }

    /**
     * Remove Token From Persistence
     * used to revoke token!
     *
     * @param string $tokenIdentifier
     *
     * @return int
     */
    function removeByIdentifier($tokenIdentifier)
    {
        $r = $this->_query()->deleteMany([
            'identifier' => $tokenIdentifier
        ]);

        return $r->getDeletedCount();
    }
}
