<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Actions\AuthenticatorAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iValidation;
use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Module;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\AuthSystem\Authenticate\Identity\IdentityOpen;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Storage\Gateway\DataStorageSession;

// TODO implement commit/rollback; maybe momento|aggregate design pattern or something is useful here
class ValidatePage
    extends aAction
{
    const SESSION_REALM = 'ValidatePage';

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

        /** @var iValidationAuthCodeObject $ac */
        $vAuthCodes = []; $isAllValidated = true;
        foreach ($vc->getAuthCodes() as $ac) {
            $isAllValidated &= $isValid = $ac->isValidated();
            $vAuthCodes[$ac->getType()]['is_validated'] = $isValid;
            $v = \Module\OAuth2\truncateIdentifierValue($ac->getValue(), $ac->getType());
            $vAuthCodes[$ac->getType()]['truncated']    = $v;
        }


        # All Is Validated? Handle Login
        if ($isAllValidated) {
            if ($r = $this->_handleLogin($vc, $request))
                return $r;
        }


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
     * @param iValidation $validationCode
     * @param iHttpRequest          $request
     *
     * @return string|false Token if valid something
     */
    protected function _handleValidate(iValidation $validationCode, iHttpRequest $request)
    {
        $data  = ParseRequestData::_($request)->parse();

        $authCodes = $validationCode->getAuthCodes();
        foreach ($data as $requestAuthType => $requestAuthCode) {
            /** @var iValidationAuthCodeObject $ac */
            foreach ($authCodes as $ac) {
                if ($ac->isValidated())
                    // This Auth Code is Validated.
                    continue;

                if ($ac->getType() == $requestAuthType && $ac->getCode() == $requestAuthCode) {
                    // Given Code Match; Update To Validated!!!
                    $this->repoValidationCodes->updateAuthAsValidated(
                        $validationCode->getValidationCode()
                        , $ac->getType()
                    );

                    // Mark As Validated; So Display Latest Status When Code Execution Follows.
                    $ac->setValidated();

                    ## VALIDATE_USER_IDENTIFIER
                    ## Update User Identifier To Validated With Current Value
                    $this->repoUsers->setUserIdentifier(
                        $validationCode->getUserIdentifier()
                        , $ac->getType()
                        , $ac->getValue()
                        , true
                    );


                    ## Remember Token For This Validation; It Used To Access User For Login Directly
                    return self::generateAndRememberToken($validationCode->getValidationCode());
                }
            }
        }

        return false;
    }


    // ..


    /**
     * Handle Login When Verification is Complete
     *
     * @param iValidation $validationCode
     * @param iHttpRequest          $request
     * @return ResponseRedirect|null
     */
    protected function _handleLogin(iValidation $validationCode, iHttpRequest $request)
    {
        if (!MethodType::_($request)->isPost())
            // Nothing To Do !!!
            return null;

        $reqParams = ParseRequestData::_($request)->parseBody();
        if (!isset($reqParams['login']) || $reqParams['login'] !== 'login')
            // It's not login button!!
            return null;


        ## User must redirect to Login Page to Authenticate then Continue
        $continue = $validationCode->getContinueFollowRedirection();
        $redirect = \Module\Foundation\Actions\IOC::url('main/oauth/login');
        (!$continue) ?: $redirect = $redirect->uri()->withQuery(sprintf('continue=%s', $continue));


        if ( self::hasTokenBind($validationCode->getValidationCode()) ) {
            // User Itself Validated this Validation Auth Codes
            // @see self::_handleValidate
            ## Sign-in User, Then Redirect To Login Page
            /** @var Users $repoUsers */
            $repoUsers = $this->repoUsers;
            $user      = $repoUsers->findOneByUID($validationCode->getUserIdentifier());
            // Identity From Credential Authenticator
            /** @see RepoUserPassCredential::doFindIdentityMatch */
            $user      = __( new IdentityOpen() )->setUID($user->getUID());

            /** @var AuthenticatorAction $authenticator */
            $authenticator = \Module\Authorization\Actions\IOC::Authenticator();
            $identifier    = $authenticator->authenticator(Module\OAuth2\Module::AUTHENTICATOR)->authenticate($user);
            $identifier->signIn();


            ## Continue Follow Directly:
            (!$continue) ?: $redirect = $continue;
        }


        ## Delete Validation Entity From Repo
        // $this->repoValidationCodes->deleteByValidationCode($validationCode->getValidationCode());

        return new ResponseRedirect( (string) $redirect );
    }


    // Helpers:

    /**
     * Check The Given Token, Validation Code Pair is Valid
     * by check the session storage equality
     *
     * @param string $validationCode
     *
     * @return bool
     */
    static function hasTokenBind($validationCode)
    {
        $storage = new DataStorageSession(self::SESSION_REALM);
        $vToken  = $storage->get($validationCode);

        return $vToken;
    }

    /**
     * Generate Token and store to session as bind with given
     * validation code
     *
     * - it will gather in pages for valid requests assertion
     *
     * @param string $validationCode
     *
     * @return string
     */
    static function generateAndRememberToken($validationCode)
    {
        $token   = \Poirot\Std\generateShuffleCode(16);
        $storage = new DataStorageSession(self::SESSION_REALM);
        $storage->set($validationCode, $token);
        return $token;
    }

    static function prepareApiResultClosure()
    {
        return function ($self = null, $is_validated = null, $verifications = null) {
            return [ 
                ListenerDispatch::RESULT_DISPATCH => [
                    'self' => $self,
                    'is_validated' => $is_validated,
                    'verifications' => $verifications,
                ] 
            ];
        };
    }
}
