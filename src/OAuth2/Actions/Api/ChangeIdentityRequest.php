<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class ChangeIdentityRequest
    extends aApiAction
{
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * @param iRepoUsers           $users        @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest  @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     *
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


        # Parse and Validate Sent Data
        #
        $post = ParseRequestData::_($this->request)->parse();
        $post = self::_assertValidData($post);

        $identifiersToChange = $post['identifiers_changed'];


        # Check Identifier Uniqueness:
        #
        /** @var iUserIdentifierObject $ident */
        if ($identifiers = $this->repoUsers->hasAnyIdentifiersRegistered($identifiersToChange)) {
            // Check weather current user is owner of this identifier?!!
            $userEntity = $this->repoUsers->findOneMatchByIdentifiers($identifiers);

            if ((string) $userEntity->getUid() !== (string) $token->getOwnerIdentifier() )
                // Identifier given to another user !!
                throw new exIdentifierExists($identifiers);


            // Check which identifier has not to changed
            /** @var iUserIdentifierObject $identifier */
            foreach ($userEntity->getIdentifiers() as $identifier) {
                /** @var iUserIdentifierObject $id */
                foreach ($identifiersToChange as $i => $id) {
                    if ($id->getType() == $identifier->getType())
                        if ($id->getValue() == $identifier->getValue())
                            unset($identifiersToChange[$i]);
                }
            }
        }


        # Retrieve User Identifiers:
        #
        if (! isset($userEntity) ) {
            // User May Retrieved Above!! (From Existence Identifier)
            /** @var iOAuthUser $userEntity */
            if ( false === $userEntity = $this->repoUsers->findOneByUID($token->getOwnerIdentifier()) )
                throw new \Exception('Invalid Token Provided, User ID Not Match.', 500);
        }


        # Update User Identifiers With New Values
        #
        $userIdentifiers = $userEntity->getIdentifiers();
        $newIdentifiers  = []; $rIdentifiers = []; $changedIdentifiers = [];
        /** @var iUserIdentifierObject $id */
        foreach ($userIdentifiers as $id) {
            /** @var iUserIdentifierObject $nid */
            foreach ($identifiersToChange as $i => $nid) {
                if ($nid->getType() === $id->getType()) {
                    $id = $nid;
                    $rIdentifiers[$id->getType()] = (boolean) $id->isValidated();

                    $changedIdentifiers[] = $nid;

                    unset($identifiersToChange[$i]);
                    break;
                }
            }

            $newIdentifiers[] = $id;
        }

        $userEntity->setIdentifiers($newIdentifiers);


        # re-Set user identifiers with given value
        #
        $validations = [];
        /** @var iUserIdentifierObject $id */
        foreach ($changedIdentifiers as $ident)
        {
            $this->repoUsers->setUserIdentifier(
                $token->getOwnerIdentifier()
                , $ident->getType()
                , $ident->getValue()
                , $ident->isValidated()
            );


            $validations[ $ident->getType() ] = [
                '_link' =>
                    (string) \Module\HttpFoundation\Actions::url(
                        'main/oauth/api/members/delegate/validate'
                        , [
                            'userid' => (string) $userEntity->getUid(),
                            'identifier' => $ident->getType(),
                        ]
                    )
            ];
        }


        # Build Response
        $r = array();
        $r['validated']   = $rIdentifiers;
        $r['validations'] = $validations;

        if (! empty($validations) )
            $r['validations']['_link'] = (string) \Module\HttpFoundation\Actions::url(
                'main/oauth/api/members/delegate/validate'
                , [
                    'userid' => (string) $userEntity->getUid(),
                ]
            );

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
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
     * @return iUserIdentifierObject[]
     */
    protected static function _assertValidData(array $post)
    {
        # Validate Data:

        # Sanitize Data:
        $identifiers = [];
        foreach ($post as $k => $v) {
            try {
                $identifiers[] = IdentifierObject::newIdentifierByType($k, $v);
            } catch (\Exception $e) {
                throw new exUnexpectedValue(sprintf(
                    'Identifier type (%s) can`t fulfilled.'
                    , $k
                ));
            }
        }

        if ( empty($identifiers) )
            throw new \InvalidArgumentException('No Argument Provided', 400);

        return ['identifiers_changed' => $identifiers];
    }
}
