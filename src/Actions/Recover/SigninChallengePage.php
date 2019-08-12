<?php
namespace Module\OAuth2\Actions\Recover;

use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\HttpFoundation\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Actions\Recover\SigninChallenge\aChallenge;
use Module\OAuth2\Actions\Recover\SigninChallenge\ChallengeEmail;
use Module\OAuth2\Actions\Recover\SigninChallenge\ChallengeFine;
use Module\OAuth2\Actions\Recover\SigninChallenge\ChallengeMobile;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Ioc\instance;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class SigninChallengePage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninChallengePage';

    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;


    /**
     * Constructor.
     *
     * @param iRepoUsers   $users       @IoC /module/oauth2/services/repository/Users
     * @param iViewModel   $viewModel   @IoC /ViewModel
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iViewModel $viewModel, iHttpRequest $httpRequest)
    {
        parent::__construct($httpRequest);

        $this->repoUsers = $users;
        $this->viewModel = $viewModel;
    }


    /**
     * @param string       $uid        User UID
     * @param string       $identifier Identifier medium type; exp. "email"
     *
     * @return ResponseRedirect[]|iViewModelPermutation[]
     */
    function __invoke($uid = null, $identifier = null)
    {
        /** @var iOAuthUser $user */
        $user = $this->repoUsers->findOneByUID($uid);
        if (! $user )
            throw new exRouteNotMatch;


        if ($identifier === null)
            // Identifier not given try to pick one !!
            return $this->_pickAChallengeForUser($user);


        # Issue Challenge
        /** @var aChallenge $challenge */
        $challenge = $this->_newChallenge($identifier, $user);
        if (! $challenge )
            // Given Challenge is not valid pick another!
            return $this->_pickAChallengeForUser($user);


        // Run challenge
        return [
            ListenerDispatch::RESULT_DISPATCH => call_user_func($challenge, $user, $this->request)
        ];
    }


    // ..

    /**
     * Pick a Challenge for user from user`s identifiers
     *
     * @param iOAuthUser $user
     *
     * @return array
     */
    protected function _pickAChallengeForUser($user)
    {
        $userIdentifiers = $user->getIdentifiers();

        $challengeType = 'fine';

        /** @var IdentifierObject $idnt */
        foreach ($userIdentifiers as $idnt) {
            if ($this->_canHandleChallengeForIdentifier( $idnt->getType() )) {
                $challengeType = $idnt->getType();
                break;
            }
        }


        # build redirect uri point to challenge
        $redirect = \Module\HttpFoundation\Actions::url(
            'main/oauth/recover/signin_challenge'
            , ['uid' => $user->getUid(), 'identifier' => $challengeType]
            , Url::DEFAULT_INSTRUCT|Url::APPEND_CURRENT_REQUEST_QUERY
        );

        return [
            ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect($redirect)
        ];
    }

    /**
     * @param string      $identifier_type
     * @param iOAuthUser $user
     *
     * @return callable
     * @throws \Exception
     */
    protected function _newChallenge($identifier_type, $user)
    {
        switch ($identifier_type) {
            case 'fine':
                $challenge = \Poirot\Ioc\newInitIns(new instance(ChallengeFine::class));
                break;
            case 'email':
                $challenge = \Poirot\Ioc\newInitIns(new instance(ChallengeEmail::class));
                break;
            case 'mobile':
                $challenge = \Poirot\Ioc\newInitIns(new instance(ChallengeMobile::class));
                break;

            default: return null;
        }


        if (!$challenge instanceof aChallenge)
            throw new \Exception(sprintf(
                'Challenge (%s) is requested but (%s) is instanced.'
                , $identifier_type, \Poirot\Std\flatten($challenge)
            ));


        // Generate next challenge link and inject to challenge abstract

        // attain next identifier and create link to challenge it!
        /** @var IdentifierObject $idnt */
        $nextChallengeType = 'fine';
        $userIdentifiers = $user->getIdentifiers();
        do {
            /** @var IdentifierObject $currIdentifier */
            $currIdentifier = current($userIdentifiers);
            if ($currIdentifier->getType() === $identifier_type) {
                // achieve self challenge try next
                $tryNext = true;
                continue;
            }

            if ( isset($tryNext) && $this->_canHandleChallengeForIdentifier($currIdentifier->getType()) ) {
                $nextChallengeType = $currIdentifier->getType();
                break;
            }

        } while( next($userIdentifiers) );


        /** @var Url $nextUrl */
        $uid = $user->getUid();
        $nextUrl = \Module\HttpFoundation\Actions::url(
            'main/oauth/recover/signin_challenge'
            , ['uid' => $uid, 'identifier' => $nextChallengeType]
            , Url::DEFAULT_INSTRUCT|Url::APPEND_CURRENT_REQUEST_QUERY
        );

        /** @var aChallenge $challenge */
        $challenge->setNextUserChallengeUrl( $nextUrl->uri() );
        return $challenge;
    }

    protected function _canHandleChallengeForIdentifier($challengeType)
    {
        return in_array($challengeType, ['email', 'mobile']);
    }
}
