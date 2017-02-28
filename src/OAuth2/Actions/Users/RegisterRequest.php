<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterRequest
    extends aAction
{
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * ValidatePage constructor.
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoUsers $users)
    {
        $this->repoUsers = $users;
    }


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
        if (!MethodType::_($request)->isPost())
            return null;

        // TODO implement commit/rollback; maybe momento/aggregate design pattern or something is useful here

        $user = $this->Register()->persistUser(
            $this->makeUserEntityFromRequest($request, $allowNoEmail)
        );
        
        /** @var iEntityUserIdentifierObject $ident */
        $validate = []; $ids = [];
        foreach ($user->getIdentifiers() as $ident) 
        {
            $ids[$ident->getType()] = $ident->getValue();
            
            if ($ident->isValidated()) 
                continue;
            
            $validate[] = $ident->getType();
        }

        // Continue Used to OAuth Registration Follow!!!
        $queryParams    = ParseRequestData::_($request)->parseQueryParams();
        $continue       = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;
        
        $validationCode = $this->Register()->giveUserValidationCode($user, $continue);


        # make response data
        
        $r = array(
            'uid'           => $user->getUID(),
            'identifiers'   => $ids,
            'validate'      => $validate,
        );
        
        (!$validationCode) ?: $r = array_merge($r, array(
            '_link' => array(
                'next_validate' => (string) $this->withModule('foundation')->url(
                    'main/oauth/members/validate'
                    , array('validation_code' => $validationCode)
                ),
            ),
        )); 
        
        return $r;
    }

    function makeUserEntityFromRequest(iHttpRequest $request, $allowNoEmail = false)
    {
        # Validate Sent Data:
        $post = ParseRequestData::_($request)->parseBody();
        $post = $this->_assertValidData($post, $allowNoEmail);

        // TODO CONFIG to give users default username on registration
        $username = (isset($post['username'])) ? $post['username']
            : $this->_attainUsernameFromFullname($post['fullname']);

        $entity = new \Module\OAuth2\Model\User;
        $entity
            ->setFullName($post['fullname'])
            ->setIdentifiers($post['identifiers'])
            ->setUsername($username)
            ->setPassword($post['credential']) // Add Grant Password
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
        # Validate Data:
        if (! (isset($post['fullname']) && isset($post['credential'])) )
            throw new exRegistration('Fullname and Credential is Required.');

        if (!$allowNoEmail && !isset($post['email']))
            throw new exRegistration('Email is Required.');


        # Sanitize Data:
        if (isset($post['mobile']) && is_array($post['mobile'])) {
            $post['mobile']['number'] = ltrim(preg_replace('/\s+/', '', $post['mobile']['number']), '0');
            if (!isset($post['mobile']['country']))
                $post['mobile']['country'] = '+98';
        }

        if (isset($post['username']))
            $post['username'] = strtolower(preg_replace('/\s+/', '.', $post['username']));

        # Map Given Data Of API Protocol and Map To Entity Model:
        $identifiers   = [];
        if ( isset($post[ UserIdentifierObject::IDENTITY_USERNAME ]) )
            $identifiers[] = UserIdentifierObject::newUsernameIdentifier($post['username']);
        if ( isset($post[ UserIdentifierObject::IDENTITY_EMAIL ]) )
            $identifiers[] = UserIdentifierObject::newEmailIdentifier($post['email']);
        if ( isset($post[ UserIdentifierObject::IDENTITY_MOBILE ]) )
            $identifiers[] = UserIdentifierObject::newMobileIdentifier($post['mobile']['number'], $post['mobile']['country']);

        if (empty($identifiers))
            throw new exRegistration('No Valid Identifier Given.');

        $post['identifiers'] = $identifiers;
        return $post;
    }

    protected function _attainUsernameFromFullname($fullname)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->repoUsers;
        return $repoUsers->attainNextUsername($fullname);
    }
}
