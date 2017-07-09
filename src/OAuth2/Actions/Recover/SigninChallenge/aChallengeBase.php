<?php
namespace Module\OAuth2\Actions\Recover\SigninChallenge;

use Module;
use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Response\ResponseRedirect;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\AuthSystem\Authenticate\Identity\IdentityOpen;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModel;
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
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     * @param iViewModel           $viewModel       @IoC /ViewModel
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoValidationCodes $validationCodes, iViewModel $viewModel, iRepoUsers $users)
    {
        parent::__construct($viewModel);

        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;
    }


    // ...

    /**
     * When User Send Start Challenge Request
     *
     * @param iHttpRequest $request
     *
     * @return ResponseRedirect
     */
    protected function _handleStartAction(iHttpRequest $request)
    {
        $user = $this->user;

        // Generate validation code
        $_get     = ParseRequestData::_($request)->parseQueryParams();
        $continue = isset($_get['continue']) ? $_get['continue'] : null;


        # Create Auth Codes Based On Identifier:

        $identifierObject = $this->_getChallengeIdentifierObject();
        $identifierObject->setValidated(false); // ensure validation made it!!


        $validationEntity = \Module\OAuth2\Actions\IOC::Validation()
            ->madeValidationChallenge($user, [ $identifierObject ], $continue);


        # Send Auth Code To Medium
        \Module\OAuth2\Actions\IOC::Validation()
            ->sendAuthCodeByMediumType($validationEntity, static::CHALLENGE_TYPE);


        $redirect = \Module\HttpFoundation\Actions::url();
        $redirect = $redirect->uri()
            ->withQuery( http_build_query(['a'=>'confirm', 'vc'=> $validationEntity->getValidationCode() ]) );

        return new ResponseRedirect((string) $redirect);
    }

    /**
     * Confirm Auth Code Sent To User For Validation
     *
     * @param iHttpRequest $request
     *
     * @return ResponseRedirect|iViewModelPermutation
     */
    protected function _handleConfirm(iHttpRequest $request)
    {
        $_request_params = ParseRequestData::_($request)->parse();
        $validationCode  = $_request_params['vc'];

        if ( false === ( $validationEntity = $this->repoValidationCodes->findOneByValidationCode($validationCode)) )
            throw new exRouteNotMatch('Validation Code Is Expired.');

        if ( (string) $validationEntity->getUserUid() !==  (string) $this->user->getUid())
            throw new \RuntimeException('Invalid Request.');


        # Confirm Code

        if (MethodType::_($request)->isPost()) {
            // Code Confirmation Sent
            $_post = ParseRequestData::_($request)->parseBody();
            if (isset($_post['confirm_code']))
            {
                \Module\OAuth2\Actions\IOC::Validation()
                    ->validateAuthCodes(
                        $validationEntity
                        , [ static::CHALLENGE_TYPE => trim($_post['confirm_code']) ]
                    );


                // check whether auth code is validated from validation entity?

                /** @var AuthObject $ac */
                foreach ($validationEntity->getAuthCodes() as $ac)
                {
                    if ($ac->getType() !== static::CHALLENGE_TYPE)
                        // Try next identifier ...
                        continue;

                    if (! $ac->isValidated() ) {
                        // Invalid code provided!
                        \Module\HttpFoundation\Actions::flashMessage(static::FLASH_MESSAGE_ID)
                            ->error('کد ارسال شده صحیح نیست.');
                        ;

                        $redirect = \Module\HttpFoundation\Actions::url(null, [], Url::DEFAULT_INSTRUCT|Url::APPEND_CURRENT_REQUEST_QUERY);
                        return new ResponseRedirect((string) $redirect);
                    }


                    // Determine that user itself has validate code on page, used to login user automatically
                    $token = Module\OAuth2\generateAndRememberToken( $validationEntity->getValidationCode() );

                    $done = true;
                    break;
                }

                if (! isset($done) )
                    // Given Challenge is not as same as challenge type!!
                    throw new \RuntimeException('Invalid Request.');


                ## Login User, Generate Hash Session Token, Redirect To Pick New Password

                $this->__loginUser( $validationEntity->getUserUid() );


                ## redirect to change password

                $redirect = \Module\HttpFoundation\Actions::url(
                    'main/oauth/recover/pick_new_password'
                    , [ 'validation_code' => $validationCode, 'token' => $token]
                );

                return new ResponseRedirect((string) $redirect);
            } // end post
        }

        
        # Build View
        
        $v = $this->_getChallengeIdentifierObject()->getValue();
        $v = (is_array($v)) ? $v[1] : $v; // maybe cell phone number ['+98', '9355497674']
        return $this->viewModel
            ->setTemplate('main/oauth/recover/challenge/'.static::CHALLENGE_TYPE.'_confirm')
            ->setVariables([
                'self' => [
                    'validation_code' => $validationCode,
                ],
                'value_truncate' => \Module\OAuth2\truncateIdentifierValue($v, null, 6),
            ])
        ;
    }

    protected function __loginUser($userUID)
    {
        ## Sign-in User, Then Redirect To Login Page
        /** @var Module\OAuth2\Model\Driver\Mongo\UserRepo $repoUsers */
        $repoUsers = $this->repoUsers;
        $user      = $repoUsers->findOneByUID($userUID);
        // Identity From Credential Authenticator
        /** @see RepoUserPassCredential::doFindIdentityMatch */
        $user      = __( new IdentityOpen() )->setUid((string) $userUID);

        $authenticator = \Module\Authorization\Actions::Authenticator();
        $identifier    = $authenticator->authenticator(Module\OAuth2\Module::REALM)
            ->authenticate($user);

        $identifier->signIn();
    }
}
