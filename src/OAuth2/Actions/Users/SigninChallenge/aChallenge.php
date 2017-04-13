<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module\Authorization\Module\AuthenticatorAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;
use Psr\Http\Message\UriInterface;


abstract class aChallenge
{
    const CHALLENGE_TYPE = VOID;


    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;
    /** @var UriInterface */
    protected $nextChallengeUrl;
    /** @var iEntityUser */
    protected $user;


    /**
     * Constructor.
     * @param iViewModelPermutation $viewModel @IoC /
     */
    function __construct(iViewModelPermutation $viewModel)
    {
        $this->viewModel = $viewModel;
    }


    /**
     * @param iEntityUser  $user
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate|iHttpResponse
     */
    function __invoke(iEntityUser $user = null, iHttpRequest $request = null)
    {
        $this->user = $user;

        if ($redirectResponse = $this->_assertUser($user))
            // Check Whether Attained User Is Same As Current Logged in User?!
            // if so redirect to login page
            return $redirectResponse;

        return $this->doInvoke($request);
    }

    /**
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    abstract function doInvoke(iHttpRequest $request);


    /**
     * Check Whether User That want to recover Account is not currently Logged in,
     * if so redirect it to dashboard
     *
     * @param iEntityUser $user
     *
     * @return ResponseRedirect
     */
    protected function _assertUser($user)
    {
        /** @var AuthenticatorAction $authorization */
        $authorization = \IOC::GetIoC()->get('/module/authorization');
        $identifier    = $authorization->authenticator(\Module\OAuth2\Module::AUTHENTICATOR)->hasAuthenticated();
        if (false !== $identifier) {
            // Some user is logged in
            if ( $identifier->withIdentity()->getUID() == $user->getUID() ) {
                // The Same User is found
                $continue = (string) \Module\Foundation\Actions\IOC::url('main/oauth/login');
                return new ResponseRedirect($continue);
            }
        }

        return null;
    }

    /**
     * Get Current Challenge Identifier Object
     *
     * @return UserIdentifierObject
     * @throws \Exception
     */
    protected function _getChallengeIdentifierObject()
    {
        $user = $this->user;

        /** @var UserIdentifierObject $idnt */
        foreach ($user->getIdentifiers() as $idnt) {
            if ($idnt->getType() === static::CHALLENGE_TYPE) {
                $find = $idnt;
                break;
            }
        }

        if (!isset($find))
            throw new \Exception(sprintf(
                'Identifier Object For Challenge (%s) not found.'
                , static::CHALLENGE_TYPE
            ));

        return $find;
    }


    // Options

    /**
     * Set Next User Challenge Url
     *
     * @param UriInterface $url
     *
     * @return $this
     */
    function setNextUserChallengeUrl(UriInterface $url)
    {
        $this->nextChallengeUrl = $url;
        return $this;
    }

    /**
     * Get Next User Challenge Url
     *
     * @return UriInterface|null
     */
    function getNextUserChallengeUrl()
    {
        return $this->nextChallengeUrl;
    }
}
