<?php
namespace Module\OAuth2\Actions\Validation;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Entity\ValidationEntity;


/**
 * Make Validation Hash For User Identifiers Need To Validate
 * by Generating Auth Code And an Endpoint To Receive Code Back
 *
 */
class MadeUserIdentifierValidationState
    extends aAction
{
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/ValidationCodes
     */
    function __construct(iRepoValidationCodes $validationCodes)
    {
        $this->repoValidationCodes = $validationCodes;
    }


    /**
     * Generate Validation Code For Given Identifiers
     *
     * - Persist Validation
     * - Send Auth Code To Validate Device
     *
     *
     * @param iOAuthUser $user
     * @param null       $continue
     *
     * @return ValidationEntity|null Validation code identifier
     */
    function __invoke(iOAuthUser $user = null, $continue = null)
    {
        # Create Auth Codes for each Identifier:
        $authCodes = [];
        $identifiers = $user->getIdentifiers();
        /** @var iUserIdentifierObject $ident */
        foreach ($identifiers as $ident) {
            if ($ident->isValidated())
                // validated identifiers don't need auth code such as username
                continue;

            $authCodes[] = $this->GenIdentifierAuthCode($ident);
        }

        if (empty($authCodes))
            // User Identifiers All Is Validated!
            return null;


        $repoValidationCodes = $this->repoValidationCodes;

        $validationCode = new ValidationEntity;
        $validationCode
            ->setValidationCode( \Poirot\Std\generateUniqueIdentifier(30) )
            ->setUserIdentifier($user->getUid())
            ->setAuthCodes($authCodes)
            ->setContinueFollowRedirection($continue) // used by oauth registration follow
        ;

        $persistValidation = $repoValidationCodes->insert($validationCode);
        return $persistValidation;
    }
}
