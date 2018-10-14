<?php
namespace Module\OAuth2\Actions\Api;

use Module;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\HttpFoundation\Response\ResponseRedirect;
use Poirot\Http\HttpMessage\Request\Plugin;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;


class ConfirmValidationRequest
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
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     * @param iHttpRequest         $httpRequest     @IoC /HttpRequest
     */
    function __construct(
        iRepoValidationCodes $validationCodes
        , iRepoUsers $users
        , iHttpRequest $httpRequest
    ) {
        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     * Validation Page
     *
     * - Delete Validation Entity When All Is Validated
     *
     *
     * @param null $validation_code Validation hash from uri
     *
     * @return array|ResponseRedirect|null
     */
    function __invoke($validation_code = null)
    {
        $repoValidationCodes = $this->repoValidationCodes;
        if (! $validationEntity = $repoValidationCodes->findOneByValidationCode($validation_code) )
            // Validation By Given Hash Not Found Or Expired!!
            throw new exRouteNotMatch;


        # Handle Params Sent With Request Message If Has!!!
        $req = Plugin\ParseRequestData::_($this->request)->parse();
        $isAllValidated = $this->Validation()->validateAuthCodes($validationEntity, $req);


        # Prepare Output Values:

        /** @var iValidationAuthCodeObject $ac */
        $vAuthCodes = [];
        foreach ($validationEntity->getAuthCodes() as $ac)
            $vAuthCodes[$ac->getType()]['is_validated'] = $ac->isValidated();


        # All Is Validated? Delete Validation Entity
        if ($isAllValidated)
        {
            // Change Password Request
            //
            if ($validationEntity->getReason() === ChangePasswordRequest::REASON_CHANGE_PASSWORD)
            {
                $password = $validationEntity->getMeta('password_change');

                $this->repoUsers->updateGrantTypeValue(
                    $validationEntity->getUserUid()
                    , 'password'
                    , $password
                );
            }


            $this->repoValidationCodes->deleteByValidationCode($validation_code);
        }


        # Build View Params
        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'is_validated'  => (boolean) $isAllValidated,
                'verifications' => $vAuthCodes,

                '_self' => [
                    'validation_code' => $validation_code,
                ],
            ],
        ];
    }
}
