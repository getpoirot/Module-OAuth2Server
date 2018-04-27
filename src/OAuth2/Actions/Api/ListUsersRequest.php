<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Type\StdTravers;


class ListUsersRequest
    extends aApiAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = array(

    );

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Construct
     *
     * @param iRepoUsers           $users       @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     * Retrieve Users Info For Bunch Of Given IDs
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


        ## Parse Request Query params
        #
        $q      = ParseRequestData::_($this->request)->parseQueryParams();
        // offset is Mongo ObjectID "58e107fa6c6b7a00136318e3"
        $offset = (isset($q['offset'])) ? $q['offset']       : null;
        $limit  = (isset($q['limit']))  ? (int) $q['limit']  : 30;


        # Build Response
        #
        $r = []; $c = 0;
        $users = $this->repoUsers->findAll([], $offset, $limit);
        foreach ($users as $userEntity)
        {
            /** @var iOAuthUser $userEntity */
            $userInfo = [];

            $isValidAll = true; $validated = array();
            foreach ($userEntity->getIdentifiers() as $identifier) {
                // embed identifiers validity
                $isValidAll &= $identifier->isValidated();
                $validated[$identifier->getType()] = (boolean) $identifier->isValidated();

                $val = $identifier->getValue();
                if ($val instanceof \Traversable)
                    $userInfo[$identifier->getType()] = StdTravers::of($identifier->getValue())->toArray();
                else
                    $userInfo[$identifier->getType()] = $identifier->getValue();
            }

            $userInfo['meta'] = $userEntity->getMeta();

            $userInfo['datetime_created']  = [
                'datetime'  => $userEntity->getDateCreated(),
                'timestamp' => $userEntity->getDateCreated()->getTimestamp(),
            ];

            $r[] = [
                'user' => [
                        'uid'      => (string) $userEntity->getUid(),
                        'fullname' => $userEntity->getFullName(),
                    ] + $userInfo,
                'is_valid'      => (boolean) $isValidAll,
                'is_valid_more' => $validated,
            ];

            $c++;
        }


        # Build Response:

        // Check whether to display fetch more link in response?
        $linkMore = null;
        if (count($r) > $limit) {
            array_pop($r);                     // skip augmented content to determine has more?
            $nextOffset = $r[count($r)-1]; // retrieve the next from this offset (less than this)
            $linkMore   = \Module\HttpFoundation\Actions::url(null);
            $linkMore   = (string) $linkMore->uri()->withQuery('offset='.($nextOffset['uid']).'&limit='.$limit);
        }


        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'count' => $c,
                'items' => $r,
                '_link_more' => $linkMore,
                '_self' => [
                    'offset' => $offset,
                    'limit'  => $limit,
                ],
            ],
        ];
    }
}
