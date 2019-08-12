<?php
namespace Module\OAuth2\Model\Driver\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
use Poirot\OAuth2\Model\AccessToken as BaseAccessToken;
use Poirot\OAuth2\Model\AccessToken;


class AccessTokenRepo
    extends aRepository
    implements iRepoAccessTokens
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new AccessTokenEntity);
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
        $accToken = new AccessTokenEntity;
        $accToken
            ->setIdentifier(\Poirot\Std\generateUniqueIdentifier(20))
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
        /** @var AccessTokenEntity $r */
        $r = $this->_query()->findOne([
            'identifier' => $tokenIdentifier,
        ]);

        # check expire time
        if ( $r && \Poirot\OAuth2\checkExpiry($r->getDateTimeExpiration()) ) {
            // enforce delete expired token entity!!
            $this->removeByIdentifier($tokenIdentifier);
            return false;
        }

        if ( $r ) {
            $accessToken = new AccessToken();
            $accessToken
                ->setIdentifier( $r->getIdentifier() )
                ->setClientIdentifier( $r->getClientIdentifier() )
                ->setDateTimeExpiration( $r->getDateTimeExpiration() )
                ->setScopes( $r->getScopes() )
                ->setOwnerIdentifier( (string) $r->getOwnerIdentifier() )
            ;

            $r = $accessToken;
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
