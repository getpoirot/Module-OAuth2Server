<?php
namespace Module\OAuth2\Exception;

use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;


class exIdentifierExists
    extends exRegistration
{
    const MESSAGE = 'The Identifier Given To Another User.';
    
    protected $identifiers;

    
    /**
     * exIdentifierExists constructor.
     * 
     * @param iEntityUserIdentifierObject[] $identifiers
     * @param string $message
     * @param \Exception|null $previous
     */
    function __construct($identifiers = array(), $message = self::MESSAGE, \Exception $previous = null)
    {
        $this->identifiers = $identifiers;
        parent::__construct($message, 403, $previous);
    }

    /**
     * Get List Of Identifiers
     * 
     * @return array|\Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject[]
     */
    function listIdentifiers()
    {
        return $this->identifiers;
    }
}
