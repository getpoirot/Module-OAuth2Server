<?php
namespace Module\OAuth2\Actions\User;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Entity\UserEntity;
use Module\OAuth2\Model\Entity\UserValidate;
use Module\OAuth2\Model\Entity\Validation\AuthObject;


class Register
    extends aAction
{
    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/Users
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/ValidationCodes
     */
    function __construct(
        iRepoUsers $users
        , iRepoValidationCodes $validationCodes
    ) {
        $this->repoUsers = $users;
        $this->repoValidationCodes = $validationCodes;
    }


    /**
     * Allow Access To Methods Within
     *
     * @return $this
     */
    function __invoke()
    {
        return $this;
    }

    /**
     * Persist User (Register)
     *
     * - check that given identifier(s) for User
     *   not registered before
     *
     * - generate authentication code for identifiers and send it to medium
     *
     * - return validation hashed endpoint for validating codes by send it back
     *
     * @param iOAuthUser  $entity
     *
     * @return UserEntity
     * @throws exIdentifierExists
     */
    function persistUser(iOAuthUser $entity)
    {
        # Persist Data:
        $repoUsers = $this->repoUsers;

        ## validate existence identifier
        #- email or mobile not given before
        $identifiers = $repoUsers->hasAnyIdentifiersRegistered( $entity->getIdentifiers() );
        if (! empty($identifiers) )
            throw new exIdentifierExists($identifiers);

        if (! $entity->getUid() )
            // User must have identifier when validation code generated
            $entity->setUid( $repoUsers->attainNextIdentifier() );


        # Username
        #
        // check allow server to pick a username automatically if not given!!
        $config  = $this->sapi()->config()->get(\Module\OAuth2\Module::CONF_KEY);
        $isAllow = (boolean) $config['allow_server_pick_username'];

        if (! $entity->getUsername() && $isAllow) {
            // Give Registered User Default Username On Registration
            $username = $this->AttainUsername($entity);
            $entity->setUsername($username);
        }


        # Validate User Entity Object
        #
        __(new UserValidate($entity
            , [ 'must_have_username' => true,
                'is_onetime_code'    => true,
                'must_have_email'    => false, ] // registration through 3rd parties do not restrict email
        )) ->assertValidate();


        # Then Persist User Entity:
        #
        /** @var UserEntity|iOAuthUser $user */
        $user = $repoUsers->insert($entity);

        return $user;
    }

    /**
     * Generate And Persist Validation Code For User
     *
     * - generate authentication code for identifiers and send it to medium
     *
     * - return validation hashed endpoint for validating codes by send it back
     *
     *
     * @param iOAuthUser  $user
     * @param string|null $continue Continue used by oauth partners registration follows
     *
     * @return string|null Validation code, or null when user has no identifier that need validation
     * @throws \Exception
     */
    function giveUserValidationCode(iOAuthUser $user, $continue = null)
    {
        $validationHash = null;

        if ( $validationEntity = $this->Validation()->madeUserValidationState($user, $continue) ) {
            /** @var AuthObject $authCodeObject */
            foreach ($validationEntity->getAuthCodes() as $authCodeObject)
                $_ = $this->Validation()->sendAuthCodeByMediumType($validationEntity, $authCodeObject->getType());

            $validationHash = $validationEntity->getValidationCode();
        }

        return $validationHash;
    }
}
