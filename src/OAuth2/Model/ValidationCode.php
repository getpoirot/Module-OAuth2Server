<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Poirot\Std\Struct\DataOptionsOpen;


class ValidationCode extends DataOptionsOpen
    implements iEntityValidationCode
{
    protected $userIdentifier;
    protected $validationCode;
    protected $authCodes = [];
    protected $expirationDateTime;


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
     *  'email' => '#1234code',
     *  'cell'  => '@2345code',
     *
     * @param array|\Traversable $authCodes
     *
     * @return $this
     */
    function setAuthCodes($authCodes)
    {
        if ($authCodes instanceof \Traversable)
            $authCodes = \Poirot\Std\cast($authCodes)->toArray(null, true);

        if (array_values($authCodes) === $authCodes)
            throw new \InvalidArgumentException('Map Provided For Auth Codes Must be an Associative Array.');

        $this->authCodes = $authCodes;
        return $this;
    }

    /**
     * Get Authorization Codes
     *
     * @return array
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
    function setExpirationDateTime(\DateTime $dateTime)
    {
        $this->expirationDateTime = $dateTime;
        return $this;
    }

    /**
     * Expiration DateTime
     *
     * @return \DateTime
     */
    function getExpirationDateTime()
    {
        if (!$this->expirationDateTime) {
            $dt = new \DateTime();
            $dt->add(new \DateInterval('P1D'));
            $this->setExpirationDateTime($dt);
        }

        return $this->expirationDateTime;
    }
}
