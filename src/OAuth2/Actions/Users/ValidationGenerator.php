<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\ValidationCode;

/**
 * Generate and Persist Validation Codes
 *
 */
class ValidationGenerator
    extends aAction
{
    function __invoke($uid = null, array $identifiers = null, $continue = null)
    {
        /** @var iRepoValidationCodes $repoValidationCodes */
        $repoValidationCodes = $this->IoC()->get('services/repository/ValidationCodes');

        $validationCode = new ValidationCode;
        $validationCode
            ->setUserIdentifier($uid)
            ->setAuthCodes($identifiers)
            ->setContinueFollowRedirection($continue) // used by oauth registration follow
        ;

        $v    = $repoValidationCodes->insert($validationCode);

        // TODO send validation codes as messages

        $code = $v->getValidationCode();
        return $code;
    }
}
