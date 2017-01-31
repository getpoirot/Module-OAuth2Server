<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class SigninRecognizePage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninRecognizePage';

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Constructor.
     * @param iRepoUsers $users @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoUsers $users)
    {
        $this->repoUsers = $users;
    }

    function __invoke(iHttpRequest $request = null)
    {
        if (MethodType::_($request)->isPost())
            return $this->_handleRecognizeIdentifier($request);


        $_query = ParseRequestData::_($request)->parseQueryParams();
        if (!isset($_query['u']))
            ## Render Input Page
            return [];

        $u = $_query['u'];
        /** @var iEntityUser $user */
        $user = $this->repoUsers->findOneByUID($u);

        return [
            // Tell Template View To Display Recognition.
            'user' => [
                'uid'      => $user->getUID(),
                'fullname' => $user->getFullName(),
                #'avatar' => $userAvatarUrl
            ]
        ];
    }

    /**
     * Handle Recognize User From Identifier That Given
     *
     * @param iHttpRequest $request
     *
     * @return array|ResponseRedirect
     */
    protected function _handleRecognizeIdentifier(iHttpRequest $request)
    {
        /** @var UrlAction $url */
        $url = $this->withModule('foundation')->url(null, null);

        $_post = ParseRequestData::_($request)->parseBody();
        if (!isset($_post['identifier'])) {
            $this->withModule('foundation')->flashMessage(self::FLASH_MESSAGE_ID)
                ->error('پارامتر های مورد نیاز ارسال نشده است.');
            ;

            return new ResponseRedirect((string) $url);
        }


        $identifier = $_post['identifier'];
        if (false === $u = $this->repoUsers->findOneHasIdentifierWithValue($identifier)) {
            $this->withModule('foundation')->flashMessage(self::FLASH_MESSAGE_ID)
                ->error('کاربر با این مشخصه پیدا نشد.');
            ;

            return new ResponseRedirect((string) $url);
        }


        $url = $url->uri()->withQuery(\Poirot\Psr7\buildQuery( ['u' => $u->getUID() ]));
        return new ResponseRedirect((string) $url);
    }
}
