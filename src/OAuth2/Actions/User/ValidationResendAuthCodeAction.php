<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Poirot\Application\Exception\exRouteNotMatch;


class ValidationResendAuthCodeAction
    extends aAction
{
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;

    /**
     * ValidatePage constructor.
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoValidationCodes $validationCodes)
    {
        $this->repoValidationCodes = $validationCodes;
    }


    function __invoke($validation_code = null, $identifier_type = null)
    {
        $repoValidationCodes = $this->repoValidationCodes;
        if (!$vc = $repoValidationCodes->findOneByValidationCode($validation_code))
            throw new exRouteNotMatch();


        if (empty($identifier_type))
            return false;

        $expiry = $this->ValidationGenerator()->sendValidation($vc, $identifier_type);
        return ['resend' => $expiry];
    }
}
