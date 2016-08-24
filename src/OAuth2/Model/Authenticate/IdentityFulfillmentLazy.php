<?php
namespace Module\OAuth2\Model\Authenticate;

use Poirot\AuthSystem\Authenticate\Interfaces\iProviderIdentityData;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;

class IdentityFulfillmentLazy
    extends \Poirot\AuthSystem\Authenticate\Identity\IdentityFulfillmentLazy
    implements iEntityUser
{
    protected $identifier;
    protected $credential;


    /**
     * AbstractStruct constructor.
     *
     * @param iProviderIdentityData   $provider
     */
    function __construct(iProviderIdentityData $provider)
    {
        // Use "identifier" property to retrieve data from Provider
        parent::__construct($provider, 'identifier');
    }
    
    /**
     * @param string $identifier
     */
    function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
    }

    /**
     * Unique User Identifier (username)
     *
     * @return string|int
     */
    function getIdentifier()
    {
        if (!$this->_isDataLoaded())
            // load data to represent all properties
            $this->_loadData();
        
        return $this->identifier;
    }


    /**
     * Get Credential
     *
     * @return string
     */
    function getCredential()
    {
        if (!$this->_isDataLoaded())
            // load data to represent all properties
            $this->_loadData();
        
        return $this->credential;
    }

    function setCredential($credential)
    {
        $this->credential = (string) $credential;
        return $this;
    }


    // Map Data Setter:

    /**
     * Proxy Map For Digest Authentication
     * @param $username
     * @return $this
     */
    function setUsername($username)
    {
        $this->setIdentifier($username);
        return $this;
    }
}
