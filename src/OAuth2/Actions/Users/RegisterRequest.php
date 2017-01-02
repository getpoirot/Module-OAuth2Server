<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterRequest
    extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if ($request === null)
            // Method inside can be used by others
            return $this;

        return array(
            ListenerDispatch::RESULT_DISPATCH => $this->handleRegisterRequest($request, true),
        );
    }

    /**
     * Handle Register Post Request
     *
     * @param iHttpRequest $request
     * @param bool         $allowNoEmail Allow Api Partners Application To Register Without Email
     *
     * @return array|null
     */
    function handleRegisterRequest(iHttpRequest $request, $allowNoEmail = false)
    {
        if (MethodType::_($request)->isPost())
        {
            // TODO implement commit/rollback; maybe momento design pattern or something is useful here

            $user = $this->Register()->persistUser(
                $this->attainUserFromRequest($request, $allowNoEmail)
            );

            // Continue Used to OAuth Registration Follow!!!
            $queryParams = ParseRequestData::_($request)->parseQueryParams();
            $continue    = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

            $code = $this->Register()->giveUserValidationCode($user, $continue);

            return array(
                'url_validation' => (string) $this->withModule('foundation')->url(
                    'main/oauth/validate'
                    , array('validation_code' => $code)
                ),
            );
        }

        return null;
    }

    function attainUserFromRequest(iHttpRequest $request, $allowNoEmail = false)
    {
        # Validate Sent Data:
        $post = ParseRequestData::_($request)->parseBody();
        $post = $this->_assertValidData($post, $allowNoEmail);

        # Map Given Data Of API Protocol and Map To Entity Model:
        $identifiers   = [];
        if (isset($post['email']))
            $identifiers[] = ['type' => 'email', 'value' => $post['email'], 'validated' => false];
        if (isset($post['mobile']))
            $identifiers[] = [ 'type' => 'mobile', 'value' => [$post['mobile']['country'], $post['mobile']['number']], 'validated' => false ];

        $entity = new \Module\OAuth2\Model\User;
        $entity
            ->setFullName($post['fullname'])
            ->setUsername($this->_attainUsernameFromFullname($post['fullname']))
            ->setPassword($post['credential']) // Add Grant Password
            ->setIdentifiers($identifiers)
        ;

        return $entity;
    }


    // ..

    /**
     * Assert Validated Registration Post Data
     *
     * Array (
     *   [full_name] => Payam Naderi
     *   [username] => naderi.payam@gmail.com
     *   [credential] => e10adc3949ba59abbe56e057f20f883e
     *   [mobile] => Array (
     *     [country] => +98
     *     [number] => 9355497674
     *   )
     * )
     *
     * @param array $post
     *
     * @return array
     */
    protected function _assertValidData(array $post, $allowNoEmail = false)
    {
        # Sanitize Data:
        if (isset($post['mobile']) && is_array($post['mobile']))
            $post['mobile']['number'] = ltrim('0', preg_replace('/\s+/', '', $post['mobile']['number']));

        # Validate Data:
        if (!$allowNoEmail && !isset($post['email']))
            throw new exRegistration('Email is Required.');

        return $post;
    }

    protected function _attainUsernameFromFullname($fullname)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');
        return $repoUsers->attainNextUsername($fullname);
    }
}
