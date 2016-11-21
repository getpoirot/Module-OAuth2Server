<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iEntityValidationCodeAuthObject;
use Poirot\Std\Struct\DataOptionsOpen;


class ValidationCodeAuthObject
    extends DataOptionsOpen
    implements iEntityValidationCodeAuthObject
{
    protected $type;
    protected $value;
    protected $is_validated;

    protected $_valueLength;
    protected $_valueType;


    /**
     * ValidationCodeAuthObject constructor.
     *
     * @param string| array|\Traversable $type
     * @param int                        $codeLength
     * @param int                        $codeType
     * @param bool                       $validated
     */
    function __construct($type
        , $codeLength = 10
        , $codeType = \Module\OAuth2\GENERATE_CODE_STRINGS | \Module\OAuth2\GENERATE_CODE_NUMBERS
        , $validated = false
    ) {
        if (is_array($type) || $type instanceof \Traversable)
            // Options Given As First Argument
            return parent::__construct($type);

        $this->setType($type);
        $this->setValidated($validated);

        $this->_valueLength = (int) $codeLength;
        $this->_valueType   = $codeType;

        return parent::__construct();
    }

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
        $this->value = (string) $value;
        return $this;
    }

    /**
     * Get Value
     * @return mixed
     */
    function getValue()
    {
        if (!$this->value)
            $this->setValue(\Module\OAuth2\generateCode(
                $this->_valueLength,
                $this->_valueType
            ));

        return $this->value;
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
}
