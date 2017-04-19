<?php
namespace Module\OAuth2\Actions\Validation;

use Module;
use Poirot\Http\HttpMessage\Request\Plugin;

use Module\Authorization\Actions\AuthenticatorAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iValidation;
use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\AuthSystem\Authenticate\Identity\IdentityOpen;
use Poirot\Http\Interfaces\iHttpRequest;


class ValidatePage
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
     * @param iHttpRequest         $request         @IoC /
     */
    function __construct(
        iRepoValidationCodes $validationCodes
        , iRepoUsers $users
        , iHttpRequest $request
    ) {
        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;

        parent::__construct($request);
    }


    /**
     * Validation Page
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
        foreach ($validationEntity->getAuthCodes() as $ac) {
            $vAuthCodes[$ac->getType()]['is_validated'] = $ac->isValidated();

            $v = \Module\OAuth2\truncateIdentifierValue($ac->getValue(), $ac->getType());
            $vAuthCodes[$ac->getType()]['truncated']    = $v;
        }


        # All Is Validated? Handle Login
        if ($isAllValidated && Plugin\MethodType::_($this->request)->isPost() ) {
            if ($r = $this->_handleLogin($validationEntity))
                return [
                    // Login User ....
                    ListenerDispatch::RESULT_DISPATCH => $r
                ];
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


    // ..

    /**
     * Handle Login When Verification is Complete
     *
     * @param iValidation $validationCode
     *
     * @return ResponseRedirect|null
     */
    protected function _handleLogin(iValidation $validationCode)
    {
        $request = $this->request;

        $reqParams = Plugin\ParseRequestData::_($request)->parseBody();
        if (! isset($reqParams['login']) || $reqParams['login'] !== 'login' )
            // It's not login button!!
            return null;


        ## User must redirect to Login Page to Authenticate then Continue
        $continue = $validationCode->getContinueFollowRedirection();
        $redirect = $this->withModule('foundation')->url('main/oauth/login');
        (!$continue) ?: $redirect = $redirect->uri()->withQuery(sprintf('continue=%s', $continue));


        if ( Module\OAuth2\hasTokenBind($validationCode->getValidationCode()) )
        {
            // User Itself Validated this Validation Auth Codes
            // @see self::_handleValidate
            ## Sign-in User, Then Redirect To Login Page

            /** @var Module\OAuth2\Model\Driver\Mongo\UserRepo $repoUsers */
            $repoUsers = $this->repoUsers;
            $user      = $repoUsers->findOneByUID( $validationCode->getUserUid() );
            // Identity From Credential Authenticator
            /** @see RepoUserPassCredential::doFindIdentityMatch */
            // (string) because serialize of mongodb ObjectID not allowed!!!
            $user      = __( new IdentityOpen )->setUID( (string) $user->getUid() );

            // Then Login User Manually
            /** @var AuthenticatorAction $authenticator */
            $authenticator = \Module\Authorization\Actions\IOC::Authenticator();
            $identifier    = $authenticator->authenticator(Module\OAuth2\Module::AUTHENTICATOR)
                ->authenticate($user);
            $identifier->signIn();


            ## Continue Follow Directly:
            (!$continue) ?: $redirect = $continue;
        }


        return new ResponseRedirect( (string) $redirect );
    }
}
