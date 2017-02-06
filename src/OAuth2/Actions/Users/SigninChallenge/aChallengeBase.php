<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module;
use Module\Authorization\Module\AuthenticatorFacade;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\AuthSystem\Authenticate\Identity\IdentityOpen;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;


abstract class aChallengeBase
    extends aChallenge
{
    const CHALLENGE_TYPE = 'mobile';
    const FLASH_MESSAGE_ID = 'ChallengeMobile';

    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Constructor.
     * @param iRepoValidationCodes  $validationCodes @IoC /module/oauth2/services/repository/
     * @param iViewModelPermutation $viewModel       @IoC /
     * @param iRepoUsers            $users           @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoValidationCodes $validationCodes, iViewModelPermutation $viewModel, iRepoUsers $users)
    {
        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;
        parent::__construct($viewModel);
    }


    // ...

    protected function _handleStartAction(iHttpRequest $request)
    {
        $userUID = $this->user->getUID();

        # Check that user may have currently active validation code generated for this challenge!!
        if ($r = $this->repoValidationCodes->findOneByUserHasIdentifierValidation($userUID, static::CHALLENGE_TYPE)) {
            $validationCode = $r->getValidationCode();
            // TODO what if continue follow has changed??
        } else {
            // Generate validation code
            $_get     = ParseRequestData::_($request)->parseQueryParams();
            $continue = isset($_get['continue']) ? $_get['continue'] : null;

            # Create Auth Codes Based On Identifier:
            $authCodes = [
                ValidationCodeAuthObject::newByIdentifier( $this->_getChallengeIdentifierObject() )
            ];

            $validationCode = \Module\OAuth2\Actions\Users\IOC::validationGenerator($userUID, $authCodes, $continue);
        }

        $redirect = \Module\Foundation\Actions\IOC::url();
        $redirect = $redirect->uri()->withQuery( http_build_query(['a'=>'confirm', 'vc'=> $validationCode ]) );
        return new ResponseRedirect($redirect);
    }

    protected function _handleConfirm(iHttpRequest $request)
    {
        $_request_params = ParseRequestData::_($request)->parse();
        $validationCode  = $_request_params['vc'];

        if (false === ( $r = $this->repoValidationCodes->findOneByValidationCode($validationCode)) )
            throw new exRouteNotMatch('Validation Code Is Expired.');

        if ( $r->getUserIdentifier() !== $this->user->getUID())
            throw new \RuntimeException('Invalid Request.');


        # Confirm Code

        if (MethodType::_($request)->isPost()) {
            // Code Confirmation Sent
            $_post = ParseRequestData::_($request)->parseBody();
            if (isset($_post['confirm_code']))
            {
                // check whether codes are equal?!!
                /** @var ValidationCodeAuthObject $ac */
                foreach ($r->getAuthCodes() as $ac) {
                    if ($ac->getType() !== static::CHALLENGE_TYPE)
                        continue;

                    if ($ac->getCode() != trim($_post['confirm_code']))
                    {
                        // Invalid code provided!
                        \Module\Foundation\Actions\IOC::flashMessage(static::FLASH_MESSAGE_ID)
                            ->error('کد ارسال شده صحیح نیست.');
                        ;

                        $redirect = \Module\Foundation\Actions\IOC::url(null, null, true);
                        return new ResponseRedirect((string) $redirect);
                    }

                    $done = true;
                    break;
                }

                if (!isset($done))
                    // Given Challenge is not as same as challenge type!!
                    throw new \RuntimeException('Invalid Request.');



                $response = $this->__loginUser($r->getUserIdentifier(), $r->getContinueFollowRedirection());

                ## Delete Validation Entity From Repo
                $this->repoValidationCodes->deleteByValidationCode($validationCode);

                return $response;
            } // end post
        }


        return $this->viewModel
            ->setTemplate('main/oauth/members/challenge/'.static::CHALLENGE_TYPE.'_confirm')
            ->setVariables([

            ])
        ;
    }

    protected function __loginUser($userUID, $continue = null)
    {
        ## Sign-in User, Then Redirect To Login Page
        /** @var Users $repoUsers */
        $repoUsers = $this->repoUsers;
        $user      = $repoUsers->findOneByUID($userUID);
        // Identity From Credential Authenticator
        /** @see RepoUserPassCredential::doFindIdentityMatch */
        $user      = __( new IdentityOpen() )->setUID($userUID);

        /** @var AuthenticatorFacade $authenticator */
        $authenticator = \IOC::GetIoC()->get('/module/authorization');
        $identifier    = $authenticator->authenticator(Module\OAuth2\Module::AUTHENTICATOR)->authenticate($user);
        $identifier->signIn();

        ## Continue Follow:
        $continue = ($continue) ? $continue :(string) \Module\Foundation\Actions\IOC::url('main/oauth/login');
        return new ResponseRedirect($continue);
    }
}
