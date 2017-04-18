<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Storage\Gateway\DataStorageSession;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class SigninNewPassPage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninNewPassPage';
    const SESSION_REALM    = 'SigninNewPassPage';

    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;

    protected $_validationCode;


    /**
     * Constructor.
     * @param iRepoValidationCodes  $validationCodes @IoC /module/oauth2/services/repository/
     * @param iRepoUsers            $users           @IoC /module/oauth2/services/repository/
     * @param iViewModelPermutation $viewModel       @IoC /
     */
    function __construct(iRepoValidationCodes $validationCodes, iRepoUsers $users, iViewModelPermutation $viewModel)
    {
        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;
        $this->viewModel = $viewModel;
    }

    /**
     * @param string       $validation_code
     * @param string       $token
     * @param iHttpRequest $request
     *
     * @return ResponseRedirect|iViewModelPermutation
     */
    function __invoke($validation_code = null, $token = null, iHttpRequest $request = null)
    {
        # Assert Session Token, ValidationCode Bind with given as Arguments
        if (!self::isTokenBindValid($validation_code, $token))
            // Request is invalid!!
            throw new exRouteNotMatch;


        $this->_validationCode = $validation_code;

        if (MethodType::_($request)->isPost())
            return $this->_handleChangePassword($request);

        $_query = ParseRequestData::_($request)->parseQueryParams();
        if (array_key_exists('skip', $_query))
            return $this->_handleSkip();

        return $this->viewModel
            ->setTemplate('main/oauth/members/pick_new_password')
        ;
    }


    // ..

    function _handleChangePassword($request)
    {
        /** @var UrlAction $url */
        $url = $this->withModule('foundation')->url(null, null, true);

        $_post = ParseRequestData::_($request)->parseBody();
        if (!isset($_post['newpassword'])) {
            $this->withModule('foundation')->flashMessage(self::FLASH_MESSAGE_ID)
                ->error('پارامتر های مورد نیاز ارسال نشده است.');
            ;

            return new ResponseRedirect((string) $url);
        }


        # Change User Password Credential Of Validation Owner
        $newPass = trim($_post['newpassword']);
        // TODO what if it expired before change password
        $vc  = $this->repoValidationCodes->findOneByValidationCode($this->_validationCode);
        if (!$vc) 
            throw new exRouteNotMatch;
        
        $uid = $vc->getUserUid();

        $this->repoUsers->updateGrantTypeValue($uid, 'password', $newPass);

        ## Continue Follow:
        $continue = ($vc->getContinueFollowRedirection())
            ? $vc->getContinueFollowRedirection()
            : (string) $this->withModule('foundation')->url('main/oauth/login')
        ;

        ## Delete Validation Entity From Repo
        $this->repoValidationCodes->deleteByValidationCode($vc->getValidationCode());
        return new ResponseRedirect($continue);
    }

    function _handleSkip()
    {
        // TODO what if it expired before change password
        $vc  = $this->repoValidationCodes->findOneByValidationCode($this->_validationCode);
        if (!$vc)
            throw new exRouteNotMatch;
        
        ## Continue Follow:
        $continue = ($vc->getContinueFollowRedirection())
            ? $vc->getContinueFollowRedirection()
            : (string) $this->withModule('foundation')->url('main/oauth/login')
        ;

        ## Delete Validation Entity From Repo
        $this->repoValidationCodes->deleteByValidationCode($vc->getValidationCode());
        return new ResponseRedirect($continue);
    }

    
    // Helpers:

    /**
     * Check The Given Token, Validation Code Pair is Valid
     * by check the session storage equality
     *
     * @param string $validationCode
     * @param string $token
     *
     * @return bool
     */
    static function isTokenBindValid($validationCode, $token)
    {
        $storage = new DataStorageSession(self::SESSION_REALM);
        $vToken  = $storage->get($validationCode);

        return $token === $vToken;
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
}
