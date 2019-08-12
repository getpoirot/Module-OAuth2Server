<?php
namespace Module\OAuth2\Interfaces\Model;


interface iValidationAuthCodeObject
    extends iUserIdentifierObject
{
    /**
     * Set Auth Code Bind With This Value Type
     * @param string $authCode
     * @return $this
     */
    function setCode($authCode);

    /**
     * Get Auth Code Bind With This Value Type
     * @return string
     */
    function getCode();


    function setTimestampSent($timestamp);


    function getTimestampSent();
}
