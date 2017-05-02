<?php
namespace Module\OAuth2\Model\Entity;

use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Std\aValidator;
use Poirot\Std\Exceptions\exUnexpectedValue;


class UserValidate
    extends aValidator
{
    /** @var iOAuthUser */
    protected $entity;

    // Allow Api Partners Application To Register Without Email When Value is False
    protected $must_have_email    = true;     // user must provide email address
    protected $must_have_username = false;    // user must provide username


    /**
     * Construct
     *
     * $options:
     * [
     *    'must_have_email'    => true,
     *    'must_have_username' => false,
     * ]
     *
     * @param iOAuthUser $entity
     * @param array      $options
     *
     */
    function __construct(iOAuthUser $entity = null, array $options = null)
    {
        $this->entity = $entity;

        foreach ($options as $key => $val)
            $this->{$key} = $val;
    }


    /**
     * Do Assertion Validate and Return An Array Of Errors
     *
     * @return exUnexpectedValue[]
     */
    function doAssertValidate()
    {
        $exceptions = [];

        if (!$this->entity->getFullName())
            $exceptions[] = exUnexpectedValue::paramIsRequired('fullname');

        if (!$this->entity->getPassword())
            $exceptions[] = exUnexpectedValue::paramIsRequired('password');



        $identifiers = $this->entity->getIdentifiers();

        if (empty($identifiers))
            $exceptions[] = new exUnexpectedValue('No Valid Identifier Is Given.');


        if ($this->must_have_email) {
            $emailIdentifier = \Module\OAuth2\getIdentifierFromList(
                IdentifierObject::IDENTITY_EMAIL
                , $identifiers
            );

            if ($emailIdentifier === null || $emailIdentifier->getValue() == '')
                $exceptions[] = exUnexpectedValue::paramIsRequired('email');
        }


        if ($this->must_have_username) {
            $usernameIdentifier = $this->entity->getUsername();
            if ($usernameIdentifier == '')
                $exceptions[] = exUnexpectedValue::paramIsRequired('username');
        }


        return $exceptions;
    }
}
