<?php
namespace Module\OAuth2\Actions\Validation;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;


class ResendAuthCodeRequest
    extends aAction
{
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     * @param iHttpRequest         $httpRequest     @IoC /HttpRequest
     */
    function __construct(iRepoValidationCodes $validationCodes, iHttpRequest $httpRequest)
    {
        $this->repoValidationCodes = $validationCodes;
    }


    function __invoke($validation_code = null, $identifier_type = null)
    {
        $repoValidationCodes = $this->repoValidationCodes;
        if (!$validationEntity = $repoValidationCodes->findOneByValidationCode($validation_code))
            throw new exRouteNotMatch;


        if (empty($identifier_type))
            return false;


        # Build Response

        $expiry = $this->Validation()->sendAuthCodeByMediumType($validationEntity, $identifier_type);
        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'resend' => $expiry
            ],
        ];
    }
}
