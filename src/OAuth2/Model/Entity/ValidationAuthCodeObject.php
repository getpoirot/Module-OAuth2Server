<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Poirot\Std\Struct\DataOptionsOpen;


class ValidationAuthCodeObject
    extends DataOptionsOpen
    implements iValidationAuthCodeObject
{
    protected $type;
    protected $authCode;
    protected $value;
    protected $is_validated;

    protected $_valueLength;
    protected $_valueType;


    protected $timestampSent;


    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    function setType($type)
    {
        $this->type = (string) $type;
        return $this;
    }

    /**
     * Get Contact Type
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Set Value
     * @param mixed $authCode
     * @return $this
     */
    function setValue($authCode)
    {
        $this->value = $authCode;
        return $this;
    }

    /**
     * Get Value
     * @return mixed
     */
    function getValue()
    {
        return $this->value;
    }

    function setCode($authCode)
    {
        $this->authCode = (string) $authCode;
        return $this;
    }

    function getCode()
    {
        return $this->authCode;
    }

    /**
     * Set Validated
     * @param bool $validated
     * @return $this
     */
    function setValidated($validated = true)
    {
        $this->is_validated = (boolean) $validated;
        return $this;
    }

    /**
     * Is Validated?
     * !! default false
     * @return boolean
     */
    function isValidated()
    {
        return $this->is_validated;
    }


    // ...

    /**
     * Get Sent Time Stamp When Send To Owner Medium
     *
     *
     */
    function getTimestampSent()
    {
        return $this->timestampSent;
    }

    function setTimestampSent($timestamp)
    {
        $this->timestampSent = $timestamp;
    }
}
