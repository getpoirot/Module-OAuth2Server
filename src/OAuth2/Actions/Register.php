<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Model\Mongo\User;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestBody;
use Poirot\Http\Interfaces\iHttpRequest;


class Register extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if (!$request instanceof iHttpRequest)
            throw new \InvalidArgumentException(sprintf(
                'Request Http Must Instance of iHttpRequest; given: (%s).'
                , \Poirot\Std\flatten($request)
            ));


        if (MethodType::_($request)->isPost()) {
            return $this->_persistRegistration($request);
        }

        return null;
    }

    protected function _persistRegistration(iHttpRequest $request)
    {
        # Validate Sent Data:
        $post = ParseRequestBody::_($request)->parseData();
        $post = $this->_assertValidData($post);

        # Map Given Data Of API Protocol and Map To Entity Model:
        $contacts   = [];
        $contacts[] = ['type' => 'email', 'value' => $post['username']];
        if (isset($post['mobile'])) {
            $contacts[] = [ 'type' => 'mobile', 'value' => [$post['mobile']['country'], $post['mobile']['number']] ];
        }

        $entity = new \Module\OAuth2\Model\User;
        $entity
            ->setFullName($post['full_name'])
            ->setIdentifier($post['username'])
            ->setPassword(md5($post['credential']))
            ->setIdentifiers($contacts)
            /*->setGrants([
                ['type' => 'password', 'value' => $post['credential']]
            ])*/
        ;


        # Persist Data:
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');

        ## validate existence identifier
        #- email or mobile not given before
        if ($repoUsers->isExistsIdentifiers($entity->getIdentifiers()))
            throw new exIdentifierExists('Identifier Is Given To Another User.', 400);

        /** @var User|iEntityUser $r */
        $r = $repoUsers->insert($entity);


        return array(
            ListenerDispatch::RESULT_DISPATCH => $r
        );
    }

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
    protected function _assertValidData(array $post)
    {
        return $post;
    }
}
