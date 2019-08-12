<?php
namespace Module\OAuth2\Model\Entity\User;

use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\Std\Struct\DataOptionsOpen;


class IdentifierObject
    extends DataOptionsOpen
    implements iUserIdentifierObject
{
    const IDENTITY_EMAIL    = 'email';
    const IDENTITY_MOBILE   = 'mobile';
    const IDENTITY_USERNAME = 'username';


    protected $type;
    protected $value;
    protected $is_validated = false;


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

    function __toString()
    {
        return (string) $this->getValue();
    }

    // ..

    /**
     * @param $value
     * @param null $validated
     *
     * @return static
     */
    static function newIdentifier($value, $validated = null)
    {
        if ( \Module\OAuth2\isEmailAddress($value) )
            return self::newEmailIdentifier($value, $validated);

        $matches = [];
        if ( \Module\OAuth2\isValidMobileNum($value, $matches) )
            return self::newMobileIdentifier([
                $matches['country_code'],
                $matches['number'],
            ], $validated);


        return self::newUsernameIdentifier($value);
    }

    static function newIdentifierByType($name, $value, $validated = null)
    {
        switch ($name) {
            case self::IDENTITY_EMAIL:
                return static::newEmailIdentifier($value, $validated);
            case self::IDENTITY_MOBILE:
                return static::newMobileIdentifier($value, $validated);
            case self::IDENTITY_USERNAME:
                return static::newUsernameIdentifier($value);
        }

        throw new \Exception(sprintf(
            'Unknown Identifier (%s).'
            , $name
        ));
    }


    /**
     * New Email Identifier Instance
     *
     * @param string $value
     * @param null   $validated
     *
     * @return static
     */
    static function newEmailIdentifier($value, $validated = null)
    {
        $self = new static;
        $self->setType(self::IDENTITY_EMAIL);
        $self->setValue( (string) $value);
        
        if ($validated !== null)
            $self->setValidated($validated);

        return $self;
    }

    /**
     * New Mobile Identifier Instance
     *
     * @param MobileObject $value
     * @param null         $validated
     *
     * @return static
     */
    static function newMobileIdentifier($value, $validated = null)
    {
        $self = new static;
        $self->setType(self::IDENTITY_MOBILE);

        if ( $value && !empty($value) ) {
            $self->setValue( new MobileObject($value) );
        }

        if ($validated !== null)
            $self->setValidated($validated);
        
        return $self;
    }

    /**
     * New Username Identifier Instance
     *
     * @param string $value
     *
     * @return static
     */
    static function newUsernameIdentifier($value)
    {
        if (! preg_match('/^[a-zA-Z0-9._-]{3,}$/', $value) )
            throw new exUnexpectedValue(sprintf(
                'Username "%s" Invalid.'
                , $value
            ));


        $self = new static();
        $self->setType(self::IDENTITY_USERNAME);
        $self->setValue( strtolower( (string) $value) );
        $self->setValidated(); // username is always validated and not require validation send
        
        return $self;
    }
}