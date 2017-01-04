<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityRefreshToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens;
use Poirot\OAuth2\Model\RefreshToken as BaseRefreshToken;


class RefreshTokens
    extends aRepository
    implements iRepoRefreshTokens
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new RefreshToken);
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
        $rToken = new RefreshToken;
        $rToken
            ->setIdentifier(\Poirot\OAuth2\generateUniqueIdentifier(30))
            ->setAccessTokenIdentifier($token->getAccessTokenIdentifier())
            ->setClientIdentifier($token->getClientIdentifier())
            ->setDateTimeExpiration($token->getDateTimeExpiration())
            ->setScopes($token->getScopes())
            ->setOwnerIdentifier($token->getOwnerIdentifier())
        ;

        $r = $this->_query()->insertOne($rToken);

        $return = new BaseRefreshToken;
        $return
            ->setIdentifier($rToken->getIdentifier())
            ->setAccessTokenIdentifier($rToken->getAccessTokenIdentifier())
            ->setClientIdentifier($rToken->getClientIdentifier())
            ->setDateTimeExpiration($rToken->getDateTimeExpiration())
            ->setScopes($rToken->getScopes())
            ->setOwnerIdentifier($rToken->getOwnerIdentifier())
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
     * @return iEntityRefreshToken|false
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