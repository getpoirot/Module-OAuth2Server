<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module;
use Module\Authorization\Module\AuthenticatorAction;
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
use Poirot\Storage\Gateway\DataStorageSession;
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
        if ($r = $this->repoValidationCodes->findOneHasAuthCodeMatchUserType($userUID, static::CHALLENGE_TYPE)) {
            $validationCode = $r->getValidationCode();
            // TODO what if continue redirect attribute has changed??
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


                    ## update value to validated
                    $this->repoValidationCodes->updateAuthAsValidated($validationCode, $ac->getType());
                    $done = true;
                    break;
                }

                if (!isset($done))
                    // Given Challenge is not as same as challenge type!!
                    throw new \RuntimeException('Invalid Request.');


                ## VALIDATE_USER_IDENTIFIER
                ## Update User Identifier To Validated With Current Value
                $this->repoUsers->setUserIdentifier($r->getUserIdentifier(), $ac->getType(), $ac->getValue(), true);

                ## Login User, Generate Hash Session Token, Redirect To Pick New Password
                $this->__loginUser($r->getUserIdentifier(), $r->getContinueFollowRedirection());

                ### generate token hash session
                $token = Module\OAuth2\Actions\Users\SigninNewPassPage::generateAndRememberToken($validationCode);

                ### redirect to change password
                $redirect = \Module\Foundation\Actions\IOC::url(
                    'main/oauth/members/pick_new_password'
                    , [
                        'validation_code' => $validationCode,
                        'token'           => $token,
                    ]
                );
                return new ResponseRedirect($redirect);
            } // end post
        }

        
        # Build View
        
        $v = $this->_getChallengeIdentifierObject()->getValue();
        $v = (is_array($v)) ? $v[1] : $v; // maybe cell phone number ['+98', '9355497674']
        return $this->viewModel
            ->setTemplate('main/oauth/members/challenge/'.static::CHALLENGE_TYPE.'_confirm')
            ->setVariables([
                'self' => [
                    'validation_code' => $validationCode,
                ],
                'value_truncate' => \Module\OAuth2\truncateIdentifierValue($v, null, 6),
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

        /** @var AuthenticatorAction $authenticator */
        $authenticator = \IOC::GetIoC()->get('/module/authorization');
        $identifier    = $authenticator->authenticator(Module\OAuth2\Module::AUTHENTICATOR)->authenticate($user);
        $identifier->signIn();
    }
}
