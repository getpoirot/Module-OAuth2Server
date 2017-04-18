<?php
namespace Module\OAuth2\Model\Entity;

use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserGrantObject;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Std\Hydrator\aHydrateEntity;


class UserHydrate
    extends aHydrateEntity
    implements \IteratorAggregate
    , iOAuthUser
{
    const FIELD_FULLNAME   = 'fullname';
    const FIELD_CREDENTIAL = 'credential';
    const FIELD_EMAIL      = 'email';
    const FIELD_MOBILE     = 'mobile';
    const FIELD_USERNAME   = 'username';

    protected $fullname;
    protected $credential;
    protected $mobileIdentifier;
    protected $emailIdentifier;
    protected $usernameIdentifier;


    // Hydrate Setter

    function setFullname($fullname)
    {
        $this->fullname = (string) $fullname;
    }

    function setCredential($credential)
    {
        $this->credential = (string) $credential;
    }

    // Mobile Identifier
    function setMobile($mobile)
    {
        if (is_array($mobile) && isset($mobile['number']) && !$mobile['number'] == '')
            $this->mobileIdentifier = $mobile;
    }

    // Email Identifier
    function setEmail($email)
    {
        $this->emailIdentifier = (string) $email;
    }

    function setUsername($username)
    {
        $username = strtolower( preg_replace('/\s+/', '.', (string) $username) );
        $this->usernameIdentifier = $username;
    }


    // Hydrate Getters

    /**
     * Unique User Identifier
     *
     * !! Identifier Must Be Unique
     *
     * @return mixed
     */
    function getUid()
    {
        // Not Implemented
    }

    /**
     * Get Password Credential
     * note: plain password not hashed
     *
     * @return string
     */
    function getPassword()
    {
        return $this->credential;
    }

    /**
     * Get FullName
     *
     * @return string
     */
    function getFullName()
    {
        return $this->fullname;
    }

    /**
     * Get Contacts
     *
     * @return iUserIdentifierObject[]
     */
    function getIdentifiers()
    {
        # Map Given Data Of API Protocol and Map To Entity Model:
        $identifiers   = [];
        if ( $this->usernameIdentifier )
            $identifiers[] = IdentifierObject::newUsernameIdentifier( $this->usernameIdentifier );

        if ( $this->emailIdentifier )
            $identifiers[] = IdentifierObject::newEmailIdentifier( $this->emailIdentifier );

        if ( $this->mobileIdentifier )
            $identifiers[] = IdentifierObject::newMobileIdentifier($this->mobileIdentifier);


        return $identifiers;
    }

    /**
     * Get Grants
     *
     * @return iUserGrantObject[]
     */
    function getGrants()
    {
        // Not Implemented (Only Password Implemented)
    }

    /**
     * Get Created Date
     *
     * @return \DateTime
     */
    function getDateCreated()
    {
        // Not Implemented
    }

    /**
     * Username Unique
     *
     * @return string
     */
    function getUsername()
    {
        $this->usernameIdentifier;
    }
}
