<?php
namespace Module\OAuth2\Actions\User;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iValidation;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\OAuth2\Model\Entity\UserEntity;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use Poirot\Sms\Entity\SMSMessage;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iSentMessage;


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
     * @param string|null $continue Continue used by oauth partners registration follows
     *
     * @return array [user, validationHash|null] null when user has no identifier that need validation
     * @throws exIdentifierExists
     */
    function persistUser(iOAuthUser $entity, $continue = null)
    {
        # Persist Data:
        $repoUsers = $this->repoUsers;

        ## validate existence identifier
        #- email or mobile not given before
        $identifiers = $repoUsers->hasAnyIdentifiersRegistered( $entity->getIdentifiers() );
        if (!empty($identifiers))
            throw new exIdentifierExists($identifiers);


        if (! $entity->getUid() )
            // User must have identifier when validation code generated
            $entity->setUid( $repoUsers->attainNextIdentifier() );

        // TODO implement commit/rollback; maybe momento/aggregate design pattern or something is useful here

        # Give User Validation Code:
        $validationHash = $this->giveUserValidationCode($entity, $continue);

        # Then Persist User Entity:
        /** @var UserEntity|iOAuthUser $user */
        $user = $repoUsers->insert($entity);

        return array( $user, $validationHash );
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
