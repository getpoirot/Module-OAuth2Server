<?php
namespace Module\OAuth2\Model;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\Std\Struct\DataOptionsOpen;


class Client extends DataOptionsOpen
    implements iEntityClient
{
    const CLIENT_TYPE_CONFIDENTIAL = 'confidential';
    const CLIENT_TYPE_PUBLIC       = 'public';

    protected $identifier;
    protected $clientType;
    protected $name;
    protected $description;
    protected $image;
    protected $secretKey;
    protected $ownerIdentifier;
    protected $scope = array();
    protected $redirectUri = array();
    protected $residentClient = false;


    // ..

    /**
     * Unique ClientID
     * @ignore we wont this on mongo persist
     *
     * @return string|int
     */
    function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return $this
     */
    function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
        return $this;
    }

    /**
     * Client Type Mostly Used To Restrict Client From
     * Some Authorization Grant
     *
     * @link https://tools.ietf.org/html/rfc6749#section-2.1
     *
     * @return string
     */
    function getClientType()
    {
        return $this->clientType;
    }

    /**
     * @param mixed $clientType
     * @return $this
     */
    function setClientType($clientType)
    {
        $this->clientType = $clientType;
        return $this;
    }

    /**
     * Get Client Name
     * this is informational data that showed into user
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Description About Client
     * this is informational data that showed into user
     *
     * @return string|null
     */
    function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get Http Address Of Client Logo Image
     * this is informational data that showed into user
     *
     * @return string
     */
    function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Client Secret Key
     *
     * @return string
     */
    function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param mixed $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * Owner Of Client
     * this the user that register client in panel
     *
     * @return string|int|null
     */
    function getOwnerIdentifier()
    {
        return $this->ownerIdentifier;
    }

    /**
     * @param mixed $ownerIdentifier
     * @return $this
     */
    public function setOwnerIdentifier($ownerIdentifier)
    {
        $this->ownerIdentifier = $ownerIdentifier;
        return $this;
    }

    /**
     * Default Client Scopes
     *
     * !! the grant request scopes must equal or less from defaults
     *
     * @return string[]
     */
    function getScope()
    {
        return $this->scope;
    }

    /**
     * @param array $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Returns the registered redirect URI
     *
     * @return string[]
     */
    function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param array $redirectUri
     * @return $this
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    /**
     * residents is company clients(such as server as a service) that
     * in follow don't need display of approve page.
     *
     * @return boolean
     */
    function isResidentClient()
    {
        return $this->residentClient;
    }

    /**
     * @param boolean $residentClient
     * @return $this
     */
    public function setResidentClient($residentClient)
    {
        $this->residentClient = (boolean) $residentClient;
        return $this;
    }


}
