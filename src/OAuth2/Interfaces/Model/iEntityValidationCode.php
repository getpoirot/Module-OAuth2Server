<?php
namespace Module\OAuth2\Interfaces\Model;

interface iEntityValidationCode
{
    /**
     * Set User Identifier That Validation Belong To
     *
     * @param string $identifier
     *
     * @return $this
     */
    function setUserIdentifier($identifier);

    /**
     * Get User Identifier That Validation Is Belong To
     *
     * @return string
     */
    function getUserIdentifier();

    /**
     * Set Validation Code
     *
     * @param string $code
     *
     * @return $this
     */
    function setValidationCode($code);

    /**
     * Validation Code
     *
     * @return string
     */
    function getValidationCode();

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
    function setAuthCodes($authCodes);

    /**
     * Get Authorization Codes
     *
     * @return array
     */
    function getAuthCodes();

    /**
     * Set Expiration DateTime
     *
     * @param \DateTime $dateTime
     *
     * @return $this
     */
    function setExpirationDateTime(\DateTime $dateTime);

    /**
     * Expiration DateTime
     *
     * @return \DateTime
     */
    function getExpirationDateTime();
}
