<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Poirot\Std\Struct\DataOptionsOpen;


class UserIdentifierObject
    extends DataOptionsOpen
    implements iEntityUserIdentifierObject
{
    const IDENTITY_EMAIL    = 'email';
    const IDENTITY_MOBILE   = 'mobile';
    const IDENTITY_USERNAME = 'username';


    protected $type;
    protected $value;
    protected $is_validated;


    /**
     * Set Type
     * 
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
     * 
     * @required
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
     * 
     * @required
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
     * @return boolean|null
     */
    function isValidated()
    {
        return $this->is_validated;
    }


    // ..

    static function newIdentifierByName($name, $value, $validated = null)
    {
        switch ($name) {
            case self::IDENTITY_EMAIL:
                return self::newEmailIdentifier($value, $validated);
            case self::IDENTITY_MOBILE:
                return self::newMobileIdentifier($value[1], $value[0], $validated);
            case self::IDENTITY_USERNAME:
                return self::newUsernameIdentifier($value);
        }

        throw new \Exception(sprintf(
            'Unknown Identifier (%s).'
            , $name
        ));
    }

    static function newEmailIdentifier($value, $validated = null)
    {
        $self = new self;
        $self->setType(self::IDENTITY_EMAIL);
        $self->setValue($value);
        
        if ($validated !== null)
            $self->setValidated($validated);

        return $self;
    }

    static function newMobileIdentifier($number, $countryCode = null, $validated = null)
    {
        $self = new self;
        $self->setType(self::IDENTITY_MOBILE);
        $self->setValue([
            $countryCode,
            $number
        ]);
        
        if ($validated !== null)
            $self->setValidated($validated);
        
        return $self;
    }

    static function newUsernameIdentifier($value)
    {
        $self = new self;
        $self->setType(self::IDENTITY_USERNAME);
        $self->setValue($value);
        
        return $self;
    }
}