<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Mongo\User;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class Register
    extends aAction
{
    // TODO Register user as action with params not request object
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
        $identifiers[] = ['type' => 'email', 'value' => $post['username'], 'validated' => false];
        if (isset($post['mobile'])) {
            $identifiers[] = [ 'type' => 'mobile', 'value' => [$post['mobile']['country'], $post['mobile']['number']], 'validated' => false ];
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
        if ($user = $repoUsers->findOneMatchByIdentifiers($entity->getIdentifiers(), false)) {
            // delete old one and lets registration follow
            $repoUsers->deleteByUID($user->getUID(), false);
            $entity->setUID($user->getUID()); // don't change UID; continue with old validations
        }

        /** @var User|iEntityUser $user */
        $user = $repoUsers->insert($entity);
        $code = $this->_giveUserValidationCode($user, $request);

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
     * @param iEntityUser  $user
     * @param iHttpRequest $request
     *
     * @return string Validation code
     * @throws \Exception
     */
    protected function _giveUserValidationCode(iEntityUser $user, iHttpRequest $request)
    {
        // Continue Used to OAuth Registration Follow!!!
        $queryParams = ParseRequestData::_($request)->parseQueryParams();
        $continue    = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

        /** @var iRepoValidationCodes $repoValidationCodes */
        $repoValidationCodes = $this->IoC()->get('services/repository/ValidationCodes');

        if ($r = $repoValidationCodes->findOneByUserIdentifier($user->getUID()))
            // User has active validation code before!!
            return $r->getValidationCode();


        # Create Auth Codes for each Identifier:
        $authCodes = [];
        $identifiers = $user->getIdentifiers();
        /** @var iEntityUserIdentifierObject $ident */
        foreach ($identifiers as $ident)
            $authCodes[] = ValidationCodeAuthObject::newByIdentifier($ident);

        $code = $this->ValidationGenerator($user->getUID(), $authCodes, $continue);
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
        # Sanitize Data:
        $post['mobile']['number'] = preg_replace('/\s+/', '', $post['mobile']['number']);

        # Validate Data:

        return $post;
    }
}
