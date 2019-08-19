<?php
namespace Module\OAuth2\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;
use Poirot\Http\HttpMessage\Response\BuildHttpResponse;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\Http\Psr\ResponseBridgeInPsr;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;
use Poirot\OAuth2\Server\Exception\exOAuthServer;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;
use Psr\Http\Message\ServerRequestInterface;


class AuthorizePage
    extends aAction
{
    /** @var GrantAggregateGrants */
    protected $grantResponder;
    /** @var iRepoUsersApprovedClients */
    protected $repoApprovedClients;

    protected $requestPsr;
    protected $response;


    /**
     * Authorize constructor.
     *
     * @param iRepoUsersApprovedClients $repoApprovedClients @IoC /module/oauth2/services/repository/Users.ApprovedClients
     * @param iHttpRequest              $httpRequest         @IoC /HttpRequest
     * @param ServerRequestInterface    $httpRequestPsr      @IoC /HttpRequestPsr
     * @param iHttpResponse             $response            @IoC /HttpResponse
     */
    function __construct(
        iRepoUsersApprovedClients $repoApprovedClients
        , iHttpRequest $httpRequest
        , ServerRequestInterface $httpRequestPsr
        , iHttpResponse $response

    ) {
        die('dsf');

        $this->response   = $response;
        $this->requestPsr = $httpRequestPsr;

        $this->repoApprovedClients = $repoApprovedClients;

        parent::__construct($httpRequest);
    }


    function __invoke()
    {
        $request  = $this->request;
        $response = $this->response;


        # Check whether we can respond to request grant?
        #
        $grant = $this->GrantResponder()
            ->canRespondToRequest( $this->requestPsr );

        if (! $grant )
            throw exOAuthServer::unsupportedGrantType();



        /** @var iOAuthClient $client */
        $client = $grant->assertClient(false);
        list($scopeRequested, $scopes) = $grant->assertScopes( $client->getScope() );

        // check whether to display approve page or not?
        if (! $approveNotRequire = $client->isResidentClient() ) {
            $RepoApprovedClients = $this->repoApprovedClients;
            $User = $this->RetrieveAuthenticatedUser();

            //// also maybe client approve the client in the past
            $approveNotRequire = $RepoApprovedClients->isUserApprovedClient($User, $client);
        }


        $_post = Plugin\PhpServer::_($request)->getPost();

        if (false == $approveNotRequire)
        {
            if (Plugin\MethodType::_($request)->isPost() && $_post->get('deny_access', null) !== null) {
                // Get Deny Result Back To The Client
                $responsePsr = new ResponseBridgeInPsr($response);
                $exception   = exOAuthServer::accessDenied($grant->newGrantResponse());
                $responsePsr = $exception->buildResponse($responsePsr);
                $response    = new HttpResponse(
                    new BuildHttpResponse( BuildHttpResponse::parseWith($responsePsr) )
                );
                return [
                    ListenerDispatch::RESULT_DISPATCH => $response
                ];

            } elseif (Plugin\MethodType::_($request)->isPost() && $_post->get('allow_access', null) !== null) {
                // Allow Access The Client
                $RepoApprovedClients = $this->repoApprovedClients;
                $User = $this->RetrieveAuthenticatedUser();
                $RepoApprovedClients->approveClient($User, $client);
            } else {
                ## display approve page
                return [
                    ListenerDispatch::RESULT_DISPATCH => [
                        'client' => [
                            'name'        => $client->getName(),
                            'description' => $client->getDescription(),
                            'image_url'   => $client->getImage(),
                        ],
                        'scopes' => $scopes,
                    ]
                ];
            }
        }

        // Client is resident or approved by user
        $responsePsr = $grant->respond( new ResponseBridgeInPsr($this->response) );
        $response    = new HttpResponse(
            new BuildHttpResponse(BuildHttpResponse::parseWith($responsePsr))
        );

        return [
            ListenerDispatch::RESULT_DISPATCH => $response
        ];
    }
}
