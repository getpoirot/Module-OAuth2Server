<?php
namespace Module\OAuth2\Exception;

use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;


class exIdentifierExists
    extends exRegistration
{
    const MESSAGE = 'The Identifier(s) [%s] Is Given To Another User.';
    
    protected $identifiers;

    
    /**
     * exIdentifierExists constructor.
     * 
     * @param iEntityUserIdentifierObject[] $identifiers
     * @param string $message
     * @param \Exception|null $previous
     */
    function __construct($identifiers = array(), $message = null, \Exception $previous = null)
    {
        $this->identifiers = $identifiers;
        if ($message === null) {
            $idTypes = array();
            foreach ($identifiers as $id)
                $idTypes[] = $id->getType();

            $message = sprintf(self::MESSAGE, implode(', ', $idTypes));
        }

        parent::__construct($message, 400, $previous);
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
