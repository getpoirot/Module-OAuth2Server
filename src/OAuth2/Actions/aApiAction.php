<?php
namespace Module\OAuth2\Actions;

use Poirot\AuthSystem\Authenticate\Exceptions\exAccessDenied;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


abstract class aApiAction
    extends aAction
    implements iEventProvider
{
    protected $tokenMustHaveOwner  = true;
    protected $tokenMustHaveScopes = array(

    );


    // ...

    /**
     * Assert Token
     *
     * @param iEntityAccessToken $token
     *
     * @throws exAccessDenied
     */
    protected function assertTokenByOwnerAndScope($token)
    {
        # Validate Access Token
        \Module\OAuth2Client\Assertion\validateAccessToken(
            $token
            , (object) ['mustHaveOwner' => $this->tokenMustHaveOwner, 'scopes' => $this->tokenMustHaveScopes ]
        );
    }
}
