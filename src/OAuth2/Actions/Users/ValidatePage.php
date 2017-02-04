<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Module\AuthenticatorFacade;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Module\OAuth2\Interfaces\Model\iEntityValidationCodeAuthObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Module;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\AuthSystem\Authenticate\Identity\IdentityOpen;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;

// TODO implement commit/rollback; maybe momento|aggregate design pattern or something is useful here
class ValidatePage
    extends aAction
{
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;

    /**
     * ValidatePage constructor.
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoValidationCodes $validationCodes, iRepoUsers $users)
    {
        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;
    }


    function __invoke($validation_code = null, iHttpRequest $request = null)
    {
        $repoValidationCodes = $this->repoValidationCodes;
        if (!$vc = $repoValidationCodes->findOneByValidationCode($validation_code))
            throw new exRouteNotMatch();


        # Handle Params Sent With Request Message If Has!!!
        $this->_handleValidate($vc, $request);


        # Prepare Output Values:

        /** @var iEntityValidationCodeAuthObject $ac */
        $vAuthCodes = []; $isAllValidated = true;
        foreach ($vc->getAuthCodes() as $ac) {
            $isAllValidated &= $isValid = $ac->isValidated();
            $vAuthCodes[$ac->getType()]['is_validated'] = $isValid;
            $v = \Module\OAuth2\truncateIdentifierValue($ac->getValue(), $ac->getType());
            $vAuthCodes[$ac->getType()]['truncated']    = $v;
        }


        # All Is Validated? Handle Login
        if ($isAllValidated)
            if ($r = $this->_handleLogin($vc, $request))
                return $r;


        # Build View Params
        return [
            // TODO almost the params that build this request will present as self to response !!!
            'self' => [
                'validation_code' => $validation_code,
            ],
            'is_validated'  => (boolean) $isAllValidated,
            'verifications' => $vAuthCodes,
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

                if ($ac->getType() == $requestAuthType && $ac->getCode() == $requestAuthCode) {
                    // Given Code Match; Update To Validated!!!
                    $this->repoValidationCodes->updateAuthCodeAsValidated(
                        $validationCode->getValidationCode()
                        , $ac->getType()
                    );

                    // Mark As Validated; So Display Latest Status When Code Execution Follows.
                    $ac->setValidated();

                    ## Validate User Collection Identifier
                    $repoUsers = $this->repoUsers;
                    $repoUsers->setUserIdentifier(
                        $validationCode->getUserIdentifier()
                        , $ac->getType()
                        , $ac->getValue()
                        , true
                    );
                }
            }
        }
    }


    // ..


    /**
     * Handle Login When Verification is Complete
     *
     * @param iEntityValidationCode $validationCode
     * @param iHttpRequest          $request
     * @return ResponseRedirect|null
     */
    protected function _handleLogin(iEntityValidationCode $validationCode, iHttpRequest $request)
    {
        if (!MethodType::_($request)->isPost())
            // Nothing To Do !!!
            return null;

        $reqParams = ParseRequestData::_($request)->parseBody();
        if (!isset($reqParams['login']) || $reqParams['login'] !== 'login')
            // It's not login button!!
            return null;


        ## Sign-in User, Then Redirect To Login Page
        /** @var Users $repoUsers */
        $repoUsers = $this->repoUsers;
        $user      = $repoUsers->findOneByUID($validationCode->getUserIdentifier());
        // Identity From Credential Authenticator
        /** @see RepoUserPassCredential::doFindIdentityMatch */
        $user      = __( new IdentityOpen() )->setUID($user->getUID());

        /** @var AuthenticatorFacade $authenticator */
        $authenticator = $this->withModule('authorization')->Facade();
        $identifier    = $authenticator->authenticator(Module::AUTHENTICATOR)->authenticate($user);
        $identifier->signIn();


        ## Continue Follow:
        $continue = ($validationCode->getContinueFollowRedirection())
            ? $validationCode->getContinueFollowRedirection()
            : (string) $this->withModule('foundation')->url('main/oauth/login')
        ;


        ## Delete Validation Entity From Repo
        $this->repoValidationCodes->deleteByValidationCode($validationCode->getValidationCode());

        return new ResponseRedirect($continue);
    }
}
