<?php
namespace Module\OAuth2\Model;

use Module\MongoDriver\Model\aPersistable;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;


class User extends aPersistable
    implements iEntityUser
{
    protected $_id;
    protected $identifier;


    # proxy calls to work with mongo persist

    function get_Id()
    {
        return $this->_id;
    }

    function set_Id($_id)
    {
        $this->_id = $_id;
        return $this;
    }


    // ..

    /**
     * Unique User Identifier (username)
     *
     * @return string|int
     */
    function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
    }
}
