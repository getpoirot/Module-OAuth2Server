<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Mongo\User;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\ValidationCodeAuthObject;


class Register
    extends aAction
{
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
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');

        ## validate existence identifier
        #- email or mobile not given before
        if ($repoUsers->isIdentifiersRegistered($entity->getIdentifiers()))
            throw new exIdentifierExists('Identifier Is Given To Another User.', 400);

        ## do not persist duplicated data for none validated users
        if ($user = $repoUsers->findOneMatchByIdentifiers($entity->getIdentifiers(), false)) {
            // delete old one and lets registration follow
            $repoUsers->deleteByUID($user->getUID(), false);
            $entity->setUID($user->getUID()); // don't change UID; continue with old validations
        }

        /** @var User|iEntityUser $user */
        $user = $repoUsers->insert($entity);
        return $user;
    }

    /**
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
        /** @var iRepoValidationCodes $repoValidationCodes */
        $repoValidationCodes = $this->IoC()->get('services/repository/ValidationCodes');

        if ($r = $repoValidationCodes->findOneByUserIdentifier($user->getUID()))
            // User has active validation code before!!
            return $r->getValidationCode();


        # Create Auth Codes for each Identifier:
        $authCodes = [];
        $identifiers = $user->getIdentifiers();
        /** @var iEntityUserIdentifierObject $ident */
        foreach ($identifiers as $ident) {
            if ($ident->isValidated()) continue; // validated identifiers don't need auth code such as username
            $authCodes[] = ValidationCodeAuthObject::newByIdentifier($ident);
        }

        $code = $this->ValidationGenerator($user->getUID(), $authCodes, $continue);
        return $code;
    }
}
