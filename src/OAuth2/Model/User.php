<?php
namespace Module\OAuth2\Model;

use Module\MongoDriver\Model\aPersistable;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;


class User extends aPersistable
    implements iEntityUser
{
    protected $identifier;
    protected $credential;


    /**
     * Unique User Identifier (username)
     *
     * @return string|int
     */
    function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
    }

    /**
     * Get Credential
     *
     * @return string
     */
    function getCredential()
    {
        return $this->credential;
    }
    
    function setCredential($credential)
    {
        $this->credential = (string) $credential;
        return $this;
    }
}
