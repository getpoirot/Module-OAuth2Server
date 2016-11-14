<?php
namespace Module\OAuth2\Model;


use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Poirot\Std\Struct\DataOptionsOpen;

class UserIdentifierObject extends DataOptionsOpen
    implements iEntityUserIdentifierObject
{
    protected $type;
    protected $value;
    protected $is_validated = false;

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
     * @param mixed $value
     * @return $this
     */
    function setValue($value)
    {
        $this->value = $value;
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

    /**
     * Set Validated
     * @param bool $validated
     * @return $this
     */
    function setValidated($validated = true)
    {
        $this->is_validated = $validated;
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
}