<?php
namespace Module\OAuth2\Interfaces\Model;

interface iEntityValidationCodeAuthObject
{
    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    function setType($type);

    /**
     * Get Auth Code Type
     * @return string
     */
    function getType();

    /**
     * Set Value
     * @param mixed $value
     * @return $this
     */
    function setValue($value);

    /**
     * Get Value
     * @return mixed
     */
    function getValue();

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

    /**
     * Set Validated
     * @param bool $validated
     * @return $this
     */
    function setValidated($validated = true);

    /**
     * Is Validated?
     * !! default false
     * @return boolean
     */
    function isValidated();
}
