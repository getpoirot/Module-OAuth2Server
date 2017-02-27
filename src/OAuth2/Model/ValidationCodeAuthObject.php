<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\iEntityValidationCodeAuthObject;
use Poirot\Std\Struct\DataOptionsOpen;


class ValidationCodeAuthObject
    extends DataOptionsOpen
    implements iEntityValidationCodeAuthObject
{
    protected $type;
    protected $authCode;
    protected $value;
    protected $is_validated;

    protected $_valueLength;
    protected $_valueType;



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


    // ..

    static function newByIdentifier(iEntityUserIdentifierObject $ident)
    {
        switch ($ident->getType()) {
            case 'email':
                return ValidationCodeAuthObject::newEmailAuthCode($ident->getValue());
            case 'mobile':
                return ValidationCodeAuthObject::newMobileAuthCode($ident->getValue());
            default:
                throw new \Exception(sprintf(
                    'Auth Code Cant Be Generated; Unknown Identifier (%s).'
                    , $ident->getType()
                ));
        }
    }

    static function newEmailAuthCode($value, $validated = false)
    {
        $self = new self;
        $self->setType('email');
        $self->setCode(\Module\OAuth2\generateCode(
            5,
            \Module\OAuth2\GENERATE_CODE_NUMBERS
        ));
        $self->setValue($value);
        $self->setValidated($validated);

        return $self;
    }

    static function newMobileAuthCode($value, $validated = false)
    {
        $self = new self;
        $self->setType('mobile');
        $self->setCode(\Module\OAuth2\generateCode(
            4, // length is used somewhere else like validation; be aware
            \Module\OAuth2\GENERATE_CODE_NUMBERS
        ));
        $self->setValue($value);
        $self->setValidated($validated);

        return $self;
    }
}
