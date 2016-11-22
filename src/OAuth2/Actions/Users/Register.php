<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Mongo\User;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\ValidationCode;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
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
        $post = ParseRequestData::_($request)->parseBody();
        $post = $this->_assertValidData($post);

        # Map Given Data Of API Protocol and Map To Entity Model:
        $identifiers   = [];
        $identifiers[] = ['type' => 'email', 'value' => $post['username']];
        if (isset($post['mobile'])) {
            $identifiers[] = [ 'type' => 'mobile', 'value' => [$post['mobile']['country'], $post['mobile']['number']] ];
        }

        $entity = new \Module\OAuth2\Model\User;
        $entity
            ->setFullName($post['full_name'])
            # ->setIdentifier() // Allow Entity/Persistence Storage Choose Identifier
            ->setUsername($post['username'])
            ->setPassword(md5($post['credential'])) // Add Grant Password
            ->setIdentifiers($identifiers)
        ;


        # Persist Data:
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');

        ## validate existence identifier
        #- email or mobile not given before
        if ($repoUsers->isIdentifiersRegistered($entity->getIdentifiers()))
            throw new exIdentifierExists('Identifier Is Given To Another User.', 400);

        ## do not persist duplicated data for none validated users
        if ($user = $repoUsers->findOneByIdentifiers($entity->getIdentifiers(), false)) {
            // delete old one and lets registration follow
            $repoUsers->deleteByUID($user->getUID(), false);
            $entity->setUID($user->getUID()); // don't change UID; continue with old validations
        }

        /** @var User|iEntityUser $user */
        $user = $repoUsers->insert($entity);
        $code = $this->_giveUserValidationCode($user->getUID());

        return array(
            ListenerDispatch::RESULT_DISPATCH => array(
                'url_validation' => (string) $this->withModule('foundation')->url(
                    'main/oauth/validate'
                    , array('validation_code' => $code)
                ),
            )
        );
    }

    /**
     * Generate And Persist Validation Code For User
     *
     * @param string $uid
     *
     * @return string
     */
    protected function _giveUserValidationCode($uid)
    {
        /** @var iRepoValidationCodes $repoValidationCodes */
        $repoValidationCodes = $this->IoC()->get('services/repository/ValidationCodes');

        if ($r = $repoValidationCodes->findOneByUserIdentifier($uid))
            // User has active validation code before!!
            return $r->getValidationCode();

        $validationCode = new ValidationCode();

        $validationCode
            ->setUserIdentifier($uid)
            ->setAuthCodes(array(
                new ValidationCodeAuthObject('email', 10, \Module\OAuth2\GENERATE_CODE_NUMBERS | \Module\OAuth2\GENERATE_CODE_STRINGS_LOWER),
                new ValidationCodeAuthObject('mobile', 4, \Module\OAuth2\GENERATE_CODE_NUMBERS),
            ))
        ;
        $v    = $repoValidationCodes->insert($validationCode);
        $code = $v->getValidationCode();

        return $code;
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
        # Validate Data:

        # Sanitize Data:

        return $post;
    }
}
