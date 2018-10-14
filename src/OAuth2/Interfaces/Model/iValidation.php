<?php
namespace Module\OAuth2\Interfaces\Model;


interface iValidation
{
    /**
     * Set User Identifier That Validation Belong To
     *
     * @param string $identifier
     *
     * @return $this
     */
    function setUserUid($identifier);

    /**
     * Get User Identifier That Validation Is Belong To
     *
     * @return string
     */
    function getUserUid();

    /**
     * Set Validation Code
     *
     * @param string $code
     *
     * @return $this
     */
    function setValidationCode($code);

    /**
     * Reason
     *
     * @param string $reason
     *
     * @return $this
     */
    function setReason($reason);

    /**
     * Reason
     *
     * @return string|null
     */
    function getReason();

    /**
     * Validation Code
     *
     * @return string
     */
    function getValidationCode();

    /**
     * Set Authorization codes
     *
     * !! empty argument [] will clear all given
     *    authorization codes
     *
     * $authCodes: [
     *  iEntityValidationCodeAuthObject,
     *
     * @param array|\Traversable $authCodes
     *
     * @return $this
     */
    function setAuthCodes($authCodes);

    /**
     * Add Authorization Code
     *
     * @param iValidationAuthCodeObject $authCode
     *
     * @return $this
     */
    function addAuthCode(iValidationAuthCodeObject $authCode);

    /**
     * Get Authorization Codes
     *
     * @return []iEntityValidationCodeAuthObject
     */
    function getAuthCodes();

    /**
     * Set Meta Data
     *
     * @param null|array $meta
     *
     * @return $this
     */
    function setMeta($meta);

    /**
     * Get Persist Meta
     *
     * @param null|string $key
     *
     * @return array|null
     */
    function getMeta($key = null);

    /**
     * Set Expiration DateTime
     *
     * @param \DateTime $dateTime
     *
     * @return $this
     */
    function setDateTimeExpiration(\DateTime $dateTime);

    /**
     * Expiration DateTime
     *
     * @return \DateTime
     */
    function getDateTimeExpiration();

    /**
     * Set Continue Follow Redirection
     * !! Implement By OAuth Registration
     *
     * @param string $url
     *
     * @return $this
     */
    function setContinueFollowRedirection($url);

    /**
     * Get Continue Follow Redirection
     * !! Implement By OAuth Registration
     *
     * @return string|null
     */
    function getContinueFollowRedirection();
}
