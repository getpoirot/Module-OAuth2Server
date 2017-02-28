<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\UserIdentifierObject;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class ChangeIdentity
    extends aAction
{
    /** @var Users */
    protected $repoUsers;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoUsers $users @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoUsers $users)
    {
        $this->repoUsers = $users;
    }

    /**
     * @param string $uid
     * @param array $changeIds
     * 
     * @return array
     * @throws \Exception
     */
    function __invoke($uid = null, $changeIds = null)
    {
        # Check Identifier Uniqueness:
        /** @var iEntityUserIdentifierObject $ident */
        if ($identifiers = $this->repoUsers->hasAnyIdentifiersRegistered($changeIds))
            throw new exIdentifierExists($identifiers);

        if (false === $user = $this->repoUsers->findOneByUID($uid))
            throw new \Exception('User not found.', 500);


        # Update User Identifiers With New Values
        $userIdentifiers = $user->getIdentifiers();
        $newIdentifiers  = []; $rIdentifiers = [];
        /** @var iEntityUserIdentifierObject $id */
        foreach ($userIdentifiers as $id) {
            foreach ($changeIds as $i => $nid) {
                if ($nid->getType() === $id->getType()) {
                    $id = $nid;
                    $rIdentifiers[$id->getType()] = (boolean) $id->isValidated();

                    unset($changeIds[$i]);
                    break;
                }
            }

            $newIdentifiers[] = $id;
        }

        $user->setIdentifiers($newIdentifiers);


        # Send Validation Code
        $validationCode = $this->register()->giveUserValidationCode($user);

        # re-Set user identifiers with given value
        /** @var iEntityUserIdentifierObject $id */
        foreach ($user->getIdentifiers() as $id)
            $this->repoUsers->setUserIdentifier($uid, $id->getType(), $id->getValue(), $id->isValidated());


        # Build Response
        $r = array();
        $r['validated'] = $rIdentifiers;

        (!$validationCode)
            ?: $r['_link'] = array(
                'next_validate' => (string) $this->withModule('foundation')->url(
                    'main/oauth/members/validate'
                    , array('validation_code' => $validationCode)
                ),
                'next_validate_alter' => (string) $this->withModule('foundation')->url(
                    'main/oauth/members/validate'
                    , array('validation_code' => $validationCode)
                ),
            );

        return [ListenerDispatch::RESULT_DISPATCH => $r];
    }


    // Statical Route Chain Helpers:

    /**
     * Used With Chained Actions To Extract Data From Request
     *
     * note: currently with dispatcher listener we cant retrieve both
     *       services and chained result together
     *
     * @return callable
     */
    static function getParsedRequestDataClosure()
    {
        /**
         * @param iHttpRequest $request
         * @return array
         */
        return function (iHttpRequest $request = null) {
            # Validate Sent Data:
            $post = ParseRequestData::_($request)->parse();
            $post = self::_assertValidData($post);

            return $post;
        };
    }

    static function getParsedUIDFromTokenClosure()
    {
        /**
         * note: currently with dispatcher listener we cant retrieve both
         *       services and chained result together
         *
         * @param iEntityAccessToken $token
         * @return array
         */
        return function ($token = null) {
            // Retrieve from token
            $uid = $token->getOwnerIdentifier();
            return ['uid' => $uid];
        };
    }


    // ..

    protected function _changeValidatedIdentity($uid, iEntityUserIdentifierObject $ident)
    {
        if ($ident->getType() !== UserIdentifierObject::IDENTITY_USERNAME)
            throw new \Exception(
                sprintf( 'Identifier %s is invalid.', $ident->getType() )
            );

        ## Validate User Collection Identifier
        /** @var Users $repoUsers */
        $repoUsers = $this->repoUsers;

        if ($repoUsers->hasAnyIdentifiersRegistered(array($ident)))
            throw new exIdentifierExists(array($ident), sprintf(
                'Identifier "%s" exists.', $ident->getValue()
            ));

        $repoUsers->setUserIdentifier(
            $uid
            , $ident->getType()
            , $ident->getValue()
            , true
        );
    }

    /**
     * Assert Validated Change Identifier
     *
     * Array (
     *   [username] => 'payam.naderi'
     * )
     *
     * @param array $post
     *
     * @return iEntityUserIdentifierObject[]
     */
    protected static function _assertValidData(array $post)
    {
        # Validate Data:

        # Sanitize Data:
        $identifiers = [];
        foreach ($post as $k => $v)
            $identifiers[] = UserIdentifierObject::newIdentifierByName($k, $v);

        return ['changeIds' => $identifiers];
    }
}
