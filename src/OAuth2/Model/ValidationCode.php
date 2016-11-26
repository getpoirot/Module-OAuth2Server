<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Module\OAuth2\Interfaces\Model\iEntityValidationCodeAuthObject;
use Poirot\Std\Struct\DataOptionsOpen;


class ValidationCode extends DataOptionsOpen
    implements iEntityValidationCode
{
    protected $userIdentifier;
    protected $validationCode;
    protected $authCodes = [];
    protected $expirationDateTime;

    protected $continueFollowRedirection;


    /**
     * Set User Identifier That Validation Belong To
     *
     * @param string $identifier
     *
     * @return $this
     */
    function setUserIdentifier($identifier)
    {
        $this->userIdentifier = (string) $identifier;
        return $this;
    }

    /**
     * Get User Identifier That Validation Is Belong To
     *
     * @return string
     */
    function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * Set Validation Code
     *
     * @param string $code
     *
     * @return $this
     */
    function setValidationCode($code)
    {
        $this->validationCode = (string) $code;
        return $this;
    }

    /**
     * Validation Code
     *
     * @return string
     */
    function getValidationCode()
    {
        if (!$this->validationCode) {
            $validationCode = md5(uniqid());
            $this->setValidationCode($validationCode);
        }

        return $this->validationCode;
    }

    /**
     * Set Authorization codes
     *
     * $authCodes: [
     *  iEntityValidationCodeAuthObject,
     *
     * @param array|\Traversable $authCodes
     *
     * @return $this
     */
    function setAuthCodes($authCodes)
    {
        $this->authCodes = [];
        foreach ($authCodes as $code) {
            if (!$code instanceof iEntityValidationCodeAuthObject)
                // Usually constructed from bson unseriallized objects
                $code = new ValidationCodeAuthObject($code);

            $this->addAuthCode($code);
        }

        return $this;
    }

    /**
     * Add Authorization Code
     *
     * @param iEntityValidationCodeAuthObject $authCode
     *
     * @return $this
     */
    function addAuthCode(iEntityValidationCodeAuthObject $authCode)
    {
        $this->authCodes[] = $authCode;
        return $this;
    }

    /**
     * Get Authorization Codes
     *
     * @return []iEntityValidationCodeAuthObject
     */
    function getAuthCodes()
    {
        return $this->authCodes;
    }

    /**
     * Set Expiration DateTime
     *
     * @param \DateTime $dateTime
     *
     * @return $this
     */
    function setDateTimeExpiration(\DateTime $dateTime)
    {
        $this->expirationDateTime = $dateTime;
        return $this;
    }

    /**
     * Expiration DateTime
     *
     * @return \DateTime
     */
    function getDateTimeExpiration()
    {
        if (!$this->expirationDateTime) {
            $dt = new \DateTime();
            $dt->add(new \DateInterval('P1D'));
            $this->setDateTimeExpiration($dt);
        }

        return $this->expirationDateTime;
    }

    /**
     * Set Continue Follow Redirection
     * !! Implement By OAuth Registration
     *
     * @param string $url
     *
     * @return $this
     */
    function setContinueFollowRedirection($url)
    {
        $this->continueFollowRedirection = (string) $url;
        return $this;
    }

    /**
     * Get Continue Follow Redirection
     * !! Implement By OAuth Registration
     *
     * @return string|null
     */
    function getContinueFollowRedirection()
    {
        return $this->continueFollowRedirection;
    }
}
