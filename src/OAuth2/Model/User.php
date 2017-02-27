<?php
namespace Module\OAuth2\Model;

use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\iEntityUserGrantObject;
use Poirot\Std\Struct\DataOptionsOpen;


class User extends DataOptionsOpen
    implements iEntityUser
{
    protected $identifier;
    protected $fullname;
    protected $identifiers = array(
        # iEntityUserContactObject
    );
    protected $grants = array(
        # iEntityUserGrantObject
    );
    /** @var \DateTime */
    protected $date_created;



    /**
     * TODO get unique id from repository nextUID
     * 
     * Unique User Identifier (username)
     *
     * @return string|int
     */
    function getUID()
    {
        if (!$this->identifier)
            // TODO UUID
            $this->identifier = uniqid();

        return $this->identifier;
    }

    /**
     * Set Identifier
     * @param string $identifier
     * @return $this
     */
    function setUID($identifier)
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
        /** @var UserIdentifierObject $c */
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
        /** @var UserIdentifierObject $c */
        foreach ($this->getIdentifiers() as $i => $c)
            if ($c->getType() == 'username')
                unset($this->identifiers[$i]);

        $co = new UserIdentifierObject;
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
        /** @var UserGrantObject $g */
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
        /** @var UserGrantObject $g */
        foreach ($this->getGrants() as $i => $g)
            if ($g->getType() == 'password')
                unset($this->grants[$i]);

        $go = new UserGrantObject();
        $go->setType('password')->setValue($credential);
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

        foreach ($identifiers as $c) {
            if (!$c instanceof iEntityUserIdentifierObject)
                // TODO when get from persistence object will unserialize this and remove from here
                $c = new UserIdentifierObject($c);

            $this->addIdentifier($c);
        }

        return $this;
    }

    /**
     * Get Contacts
     *
     * @return []iEntityUserContactObject
     */
    function getIdentifiers()
    {
        return array_values($this->identifiers);
    }

    /**
     * Add Contact
     *
     * @param iEntityUserIdentifierObject $identifier
     *
     * @return $this
     */
    function addIdentifier(iEntityUserIdentifierObject $identifier)
    {
        $this->identifiers[] = $identifier;
        return $this;
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
            if (!$g instanceof UserGrantObject)
                $g = new UserGrantObject($g);

            $this->addGrant($g);
        }

        return $this;
    }

    /**
     * Get Grants
     *
     * @return []iEntityUserGrantObject
     */
    function getGrants()
    {
        return array_values($this->grants);
    }

    /**
     * Add Grant
     *
     * @param iEntityUserGrantObject $grant
     *
     * @return $this
     */
    function addGrant(iEntityUserGrantObject $grant)
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
