<?php
namespace Module\OAuth2\Model;

use Module\MongoDriver\Model\aPersistable;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;


class Client extends aPersistable
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


    # proxy calls to work with mongo persist

    function get_Id()
    {
        return $this->getIdentifier();
    }

    function set_Id($identifier)
    {
        return $this->setIdentifier($identifier);
    }


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
     */
    function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
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
     */
    function setClientType($clientType)
    {
        $this->clientType = $clientType;
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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     */
    public function setImage($image)
    {
        $this->image = $image;
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
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
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
     */
    public function setOwnerIdentifier($ownerIdentifier)
    {
        $this->ownerIdentifier = $ownerIdentifier;
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
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
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
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
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
     */
    public function setResidentClient($residentClient)
    {
        $this->residentClient = $residentClient;
    }


}
