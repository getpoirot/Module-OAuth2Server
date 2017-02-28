<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Mongo\User;
use Module\OAuth2\Model\ValidationCodeAuthObject;


class Register
    extends aAction
{
    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;


    /**
     * ValidatePage constructor.
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoUsers $users, iRepoValidationCodes $validationCodes)
    {
        $this->repoUsers = $users;
        $this->repoValidationCodes = $validationCodes;
    }

    function __invoke()
    {
        return $this;
    }

    /**
     * Persist User (Register)
     *
     * @param iEntityUser $entity
     *
     * @return iEntityUser
     */
    function persistUser(iEntityUser $entity)
    {
        # Persist Data:
        $repoUsers = $this->repoUsers;

        ## validate existence identifier
        #- email or mobile not given before
        $identifiers = $repoUsers->hasAnyIdentifiersRegistered( $entity->getIdentifiers() );
        if (!empty($identifiers))
            throw new exIdentifierExists($identifiers);
        
        /** @var User|iEntityUser $user */
        $user = $repoUsers->insert($entity);
        return $user;
    }

    /**
     * TODO some actions used this feature so it can be moved somewhere with more uniqueness
     * 
     * Generate And Persist Validation Code For User
     *
     * @param iEntityUser  $user
     * @param string|null  $continue Continue used by oauth partners registration follows
     *
     * @return string Validation code
     * @throws \Exception
     */
    function giveUserValidationCode(iEntityUser $user, $continue = null)
    {
        # Create Auth Codes for each Identifier:
        $authCodes = [];
        $identifiers = $user->getIdentifiers();
        /** @var iEntityUserIdentifierObject $ident */
        foreach ($identifiers as $ident) {
            if ($ident->isValidated())
                // validated identifiers don't need auth code such as username
                continue; 
            
            // TODO Merged Config or defined service To Generate Codes Settings
            $authCodes[] = ValidationCodeAuthObject::newByIdentifier($ident);
        }

        $code = null;
        if (!empty($authCodes))
            $code = $this->ValidationGenerator($user->getUID(), $authCodes, $continue);

        return $code;
    }
}
