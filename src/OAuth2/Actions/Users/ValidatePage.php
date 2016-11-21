<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Module\OAuth2\Interfaces\Model\iEntityValidationCodeAuthObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class ValidatePage extends aAction
{
    /** @var iRepoValidationCodes $repoValidationCodes */
    protected $repoValidationCodes;


    function __invoke($validation_code = null, iHttpRequest $request = null)
    {
        $repoValidationCodes = $this->_getRepoValidationCode();
        if (!$vc = $repoValidationCodes->findOneByValidationCode($validation_code))
            throw new exRouteNotMatch();


        # Handle Params Sent With Request Message If Has!!!
        $this->_handleValidate($vc, $request);


        # Prepare Output Values:

        /** @var iEntityValidationCodeAuthObject $ac */
        $vAuthCodes = []; $isAllValidated = true;
        foreach ($vc->getAuthCodes() as $ac) {
            $isAllValidated &= $isValid = $ac->isValidated();
            $vAuthCodes[$ac->getType()] = $isValid;
        }

        # All Is Validated?
        if ($isAllValidated) {
            // TODO Response From Ajax Calls

            ## Delete Validation Entity From Repo
            $repoValidationCodes->deleteByValidationCode($validation_code);

            ## Sign-in User, Then Redirect To Login Page
            // TODO sign-in user
            $urlLogin = $this->withModule('foundation')->url('main/oauth/login');
            return new ResponseRedirect($urlLogin);
        }

        return [
            'verified' => $vAuthCodes,
        ];
    }

    /**
     * Handle Validate Request
     *
     * - the auth-code types must exists as parameter
     *   carried with http message.
     *   exp. email: x134code
     *
     * @param iEntityValidationCode $validationCode
     * @param iHttpRequest          $request
     */
    protected function _handleValidate(iEntityValidationCode $validationCode, iHttpRequest $request)
    {
        $data  = ParseRequestData::_($request)->parse();

        $authCodes = $validationCode->getAuthCodes();
        foreach ($data as $requestAuthType => $requestAuthCode) {
            /** @var iEntityValidationCodeAuthObject $ac */
            foreach ($authCodes as $ac) {
                if ($ac->isValidated())
                    // This Auth Code is Validated.
                    continue;

                if ($ac->getType() == $requestAuthType && $ac->getValue() == $requestAuthCode) {
                    // Given Code Match; Update To Validated!!!
                    $this->_getRepoValidationCode()->updateAuthCodeAsValidated(
                        $validationCode->getValidationCode()
                        , $ac->getType()
                    );

                    // Mark As Validated; So Display Latest Status When Code Execution Follows.
                    $ac->setValidated();

                    // TODO Validate User Collection Identifier
                }
            }
        }

    }


    // ..

    protected function _getRepoValidationCode()
    {
        if (!$this->repoValidationCodes)
            $this->repoValidationCodes = $this->IoC()->get('services/repository/ValidationCodes');

        return $this->repoValidationCodes;
    }
}
