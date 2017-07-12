<?php
namespace Module\OAuth2\Model\Entity;

use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\iUserGrantObject;
use Module\OAuth2\Model\Entity\User\GrantObject;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Std\Struct\DataOptionsOpen;


class UserEntity
    extends DataOptionsOpen
    implements iOAuthUser
{
    protected $identifier;
    protected $fullname;
    protected $identifiers = array(
        # iEntityUserContactObject
    );
    protected $meta = array();
    protected $grants = array(
        # iEntityUserGrantObject
    );
    /** @var \DateTime */
    protected $date_created;



    /**
     * Unique User Identifier
     *
     * !! Identifier Must Be Unique
     *
     * @return mixed
     */
    function getUid()
    {
        return $this->identifier;
    }

    /**
     * Set Identifier
     *
     * @param mixed $identifier
     *
     * @return $this
     */
    function setUid($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get Username
     * @ignore identifier is presented by contacts (email)
     *
     * @return string|int
     */
    function getUsername()
    {
        /** @var IdentifierObject $c */
        foreach ($this->getIdentifiers() as $c)
            if ($c->getType() == 'username')
                return $c->getValue();

        return null;
    }

    /**
     * Set Username
     * @param string $identifier
     * @return $this
     */
    function setUsername($identifier)
    {
        /** @var IdentifierObject $c */
        foreach ($this->getIdentifiers() as $i => $c)
            if ($c->getType() == 'username')
                unset($this->identifiers[$i]);

        $co = new IdentifierObject;
        $co ->setType('username')
            ->setValue($identifier)
            ->setValidated(true) // given username is always has no validation
        ;

        $this->addIdentifier($co);
        return $this;
    }

    /**
     * Get Password Credential
     * @ignore password stored in grants
     *
     * @return string
     */
    function getPassword()
    {
        /** @var GrantObject $g */
        foreach ($this->getGrants() as $g)
            if ($g->getType() == 'password')
                return $g->getValue();

        return null;
    }

    /**
     * Set Password Credential
     * @param string $credential
     * @return $this
     */
    function setPassword($credential)
    {
        /** @var GrantObject $g */
        foreach ($this->getGrants() as $i => $g)
            if ($g->getType() == 'password')
                unset($this->grants[$i]);

        $go = new GrantObject();
        $go->setType('password')->setValue($credential)->addOption('checksum', 'md5');
        $this->addGrant($go);

        return $this;
    }

    /**
     * Set FullName
     *
     * @param string $fullName
     *
     * @return $this
     */
    function setFullName($fullName)
    {
        $this->fullname = (string) $fullName;
        return $this;
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
     * Set Contacts; Replace Old Ones
     * note: use [] empty array to delete contacts
     *
     * @param []iEntityUserContactObject $contacts
     *
     * @return $this
     */
    function setIdentifiers(array $identifiers)
    {
        // clear previous contacts
        $this->identifiers = array();

        foreach ($identifiers as $c)
            $this->addIdentifier($c);


        return $this;
    }

    /**
     * Get Contacts
     *
     * @return iUserIdentifierObject[]
     */
    function getIdentifiers()
    {
        return array_values($this->identifiers);
    }

    /**
     * Add Contact
     *
     * @param iUserIdentifierObject $identifier
     *
     * @return $this
     */
    function addIdentifier(iUserIdentifierObject $identifier)
    {
        $this->identifiers[] = $identifier;
        return $this;
    }

    /**
     * Set metadata, replacing old ones
     *
     * @param array $meta
     */
    function setMeta($meta){
        $this->meta = $meta;
    }

    /**
     * Get metadata
     *
     * @return array
     */
    function getMeta(){
        return $this->meta;
    }

    /**
     * Set Given Grants Accounts
     * note: use [] empty array to delete grants
     *
     * @param []iEntityUserGrantObject $grants
     *
     * @return $this
     */
    function setGrants(array $grants)
    {
        // clear previous grants
        $this->grants = array();

        foreach ($grants as $g) {
            if (!$g instanceof GrantObject)
                $g = new GrantObject($g);

            $this->addGrant($g);
        }

        return $this;
    }

    /**
     * Get Grants
     *
     * @return iUserGrantObject[]
     */
    function getGrants()
    {
        return array_values($this->grants);
    }

    /**
     * Add Grant
     *
     * @param iUserGrantObject $grant
     *
     * @return $this
     */
    function addGrant(iUserGrantObject $grant)
    {
        $this->grants[] = $grant;
        return $this;
    }

    /**
     * Set Created Date
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    function setDateCreated(\DateTime $date)
    {
        $this->date_created = $date;
        return $this;
    }

    /**
     * Get Created Date
     *
     * @return \DateTime
     */
    function getDateCreated()
    {
        if ($this->date_created === null)
            $this->setDateCreated(new \DateTime());

        return $this->date_created;
    }
}
