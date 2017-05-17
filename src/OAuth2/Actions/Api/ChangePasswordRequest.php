<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Exception\exPasswordNotMatch;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class ChangePasswordRequest
    extends aApiAction
{
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * @param iRepoUsers           $users       @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     * Change Password Grant Credential For Current User Determined By Token
     *
     * @param iEntityAccessToken $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Retrieve User With OwnerID
        #
        /** @var iOAuthUser $userEntity */
        if (! $userEntity = $this->repoUsers->findOneByUID( $token->getOwnerIdentifier() ))
            throw new \Exception('User not Found.', 500);


        # Validate Sent Data:
        $post = ParseRequestData::_($this->request)->parse();
        $post = $this->_assertValidData($post);


        # Current password must match
        #
        if ( $this->repoUsers->makeCredentialHash($post['currpass']) !== $userEntity->getPassword() )
            throw new exPasswordNotMatch('Current Password Does not match!');

        $r = $this->repoUsers->updateGrantTypeValue($token->getOwnerIdentifier(), 'password', $post['newpass']);

        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'stat' => ($r) ? 'changed' : 'unchanged'
            ],
        ];
    }


    // ..

    /**
     * Assert Validated Change Password Post Data
     *
     * Array (
     *   [credential] => e10adc3949ba59abbe56e057f20f883e
     * )
     *
     * @param array $post
     *
     * @return array
     */
    protected function _assertValidData(array $post)
    {
        # Validate Data:
        if (! isset($post['newpass']) || !isset($post['currpass']) )
            throw new exUnexpectedValue('Arguments "newpass" & "currpass" is required.');

        # Sanitize Data:
        $post['newpass']  = trim($post['newpass']);
        $post['currpass'] = trim($post['currpass']);

        return $post;
    }
}
