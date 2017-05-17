<?php
namespace Module\OAuth2\Actions\Recover;

use Module\HttpFoundation\Actions\UrlAction;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\HttpFoundation\Response\ResponseRedirect;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class SigninNewPassPage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninNewPassPage';

    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;

    protected $_validationCode;


    /**
     * Constructor.
     *
     * @param iRepoValidationCodes  $validationCodes @IoC /module/oauth2/services/repository/
     * @param iRepoUsers            $users           @IoC /module/oauth2/services/repository/
     * @param iViewModelPermutation $viewModel       @IoC /
     * @param iHttpRequest $request @IoC /
     */
    function __construct(
        iRepoValidationCodes $validationCodes
        , iRepoUsers $users
        , iViewModelPermutation $viewModel
        , iHttpRequest $request
    ) {
        parent::__construct($request);

        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;
        $this->viewModel = $viewModel;
    }


    /**
     * @param string       $validation_code
     * @param string       $token
     *
     * @return array
     */
    function __invoke($validation_code = null, $token = null)
    {
        # Assert Session Token, ValidationCode Bind with given as Arguments
        if (!\Module\OAuth2\hasTokenBind($validation_code, $token))
            // Request is invalid!!
            throw new exRouteNotMatch;


        $request = $this->request;

        $this->_validationCode = $validation_code;

        if ( Plugin\MethodType::_($request)->isPost() )
            return $this->_handleChangePassword($request);

        $_query = Plugin\ParseRequestData::_($request)->parseQueryParams();
        if ( array_key_exists('skip', $_query) )
            return $this->_handleSkip();


        # Build Response View:

        return [
            ListenerDispatch::RESULT_DISPATCH => $this->viewModel
                ->setTemplate('main/oauth/recover/pick_new_password')
        ];
    }


    // ..

    function _handleChangePassword($request)
    {
        /** @var UrlAction $url */
        $url = \Module\HttpFoundation\Module::url(null, null, true);

        $_post = Plugin\ParseRequestData::_($request)->parseBody();
        if (! isset($_post['newpassword']) ) {
            \Module\HttpFoundation\Module::flashMessage(self::FLASH_MESSAGE_ID)
                ->error('پارامتر های مورد نیاز ارسال نشده است.');
            ;

            return [
                ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect((string) $url)
            ];
        }


        # Change User Password Credential Of Validation Owner
        $newPass = trim($_post['newpassword']);
        // TODO what if it expired before change password
        $vc  = $this->repoValidationCodes->findOneByValidationCode($this->_validationCode);
        if (! $vc )
            throw new exRouteNotMatch;
        
        $uid = $vc->getUserUid();
        $this->repoUsers->updateGrantTypeValue($uid, 'password', $newPass);

        ## Continue Follow:
        $continue = ( $vc->getContinueFollowRedirection() )
            ? $vc->getContinueFollowRedirection()
            : (string) \Module\HttpFoundation\Module::url('main/oauth/login')
        ;

        ## Delete Validation Entity From Repo
        $this->repoValidationCodes->deleteByValidationCode( $vc->getValidationCode() );
        return [
            ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect($continue)
        ];
    }

    function _handleSkip()
    {
        // TODO what if it expired before change password
        $vc  = $this->repoValidationCodes->findOneByValidationCode($this->_validationCode);
        if (! $vc )
            throw new exRouteNotMatch;
        
        ## Continue Follow:
        $continue = ($vc->getContinueFollowRedirection())
            ? $vc->getContinueFollowRedirection()
            : (string) \Module\HttpFoundation\Module::url('main/oauth/login')
        ;

        ## Delete Validation Entity From Repo
        $this->repoValidationCodes->deleteByValidationCode($vc->getValidationCode());
        return [
            ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect($continue)
        ];
    }
}
