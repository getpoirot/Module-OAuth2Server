<?php
namespace Module\OAuth2\Model\Authenticate;

use Poirot\AuthSystem\Authenticate\Interfaces\iProviderIdentityData;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthUser;


class IdentityFulfillmentLazy
    extends \Poirot\AuthSystem\Authenticate\Identity\IdentityFulfillmentLazy
    implements iOAuthUser
{
    protected $uid;
    protected $credential;


    /**
     * AbstractStruct constructor.
     *
     * @param iProviderIdentityData   $provider
     */
    function __construct(iProviderIdentityData $provider)
    {
        // Use "uid" property to retrieve data from Provider
        parent::__construct($provider, 'uid');
    }

    /**
     * @param string $uid
     * @return $this
     */
    function setUID($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Unique User Identifier
     *
     * !! Identifier Must Be Unique
     *
     * @return mixed
     */
    function getUID()
    {
        return $this->uid;
    }

    /**
     * Get Credential
     *
     * @return string
     */
    function getPassword()
    {
        if (!$this->_isDataLoaded())
            // load data to represent all properties
            $this->_loadData();
        
        return $this->credential;
    }

    // Map Data Setter:

    /**
     * Username Unique
     *
     * @return string
     */
    function getUsername()
    {
        // TODO: Implement getUsername() method.
    }
}
