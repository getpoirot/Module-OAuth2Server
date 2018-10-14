<?php
namespace Module\OAuth2\Model\Entity;

use Module\OAuth2\Interfaces\Model\iValidation;
use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Poirot\Std\Struct\DataOptionsOpen;
use Poirot\Std\Type\StdTravers;


class ValidationEntity
    extends DataOptionsOpen
    implements iValidation
{
    protected $userIdentifier;
    protected $validationCode;
    protected $authCodes = [];
    protected $reason;
    protected $meta = null;
    protected $expirationDateTime;

    protected $continueFollowRedirection;


    /**
     * Set User Identifier That Validation Belong To
     *
     * @param string $identifier
     *
     * @return $this
     */
    function setUserUid($identifier)
    {
        $this->userIdentifier = $identifier;
        return $this;
    }

    /**
     * Get User Identifier That Validation Is Belong To
     *
     * @return string
     */
    function getUserUid()
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
        return $this->validationCode;
    }

    /**
     * Reason
     *
     * @param string $reason
     *
     * @return $this
     */
    function setReason($reason)
    {
        $this->reason = (string) $reason;
        return $this;
    }

    /**
     * Reason
     *
     * @return string|null
     */
    function getReason()
    {
        return $this->reason;
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
        foreach ($authCodes as $code)
            $this->addAuthCode($code);

        return $this;
    }

    /**
     * Add Authorization Code
     *
     * @param iValidationAuthCodeObject $authCode
     *
     * @return $this
     */
    function addAuthCode(iValidationAuthCodeObject $authCode)
    {
        $this->authCodes[$authCode->getType()] = $authCode;
        return $this;
    }

    /**
     * Get Authorization Codes
     *
     * @param null $authType
     *
     * @return \Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject[]
     */
    function getAuthCodes($authType=null)
    {
        if ($authType !== null)
            return ( isset($this->authCodes[$authType]) )
                ? $this->authCodes[$authType]
                : null;

        return array_values($this->authCodes); // must persist as array
    }

    /**
     * Set Meta Data
     *
     * @param null|array $meta
     *
     * @return $this
     */
    function setMeta($meta)
    {
        if ($meta instanceof \Traversable)
            $meta = StdTravers::of($meta)->toArray();

        if (null !== $meta && !is_array($meta))
            throw new \InvalidArgumentException(sprintf(
                'Meta must be Array or instance of Traversable; given: (%s).'
                , \Poirot\Std\flatten($meta)
            ));


        $this->meta = $meta;
        return $this;
    }

    /**
     * Get Persist Meta
     *
     * @param null|string $key
     *
     * @return array|null
     */
    function getMeta($key = null)
    {
        $meta = $this->meta;
        if ( $key && is_array($this->meta) )
            $meta = (isset($this->meta[$key])) ? $this->meta[$key] : null;

        return $meta;
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
