<?php
namespace Module\OAuth2\Model\Entity\Validation;

use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Module\OAuth2\Model\Entity\User\IdentifierObject;


class AuthObject
    extends IdentifierObject
    implements iValidationAuthCodeObject
{
    protected $authCode;

    protected $_valueLength;
    protected $_valueType;

    protected $timestampSent;



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
