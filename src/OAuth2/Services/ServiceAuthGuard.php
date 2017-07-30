<?php
namespace Module\OAuth2\Services;

use Module\Authorization\Guard\GuardRoute;
use Module\OAuth2\Services\Guards\aServiceGuard;


class ServiceAuthGuard
    extends aServiceGuard
{
    protected $authenticatorName = \Module\OAuth2\Module::REALM;


    /**
     * Create Service
     *
     * @return GuardRoute
     */
    function newService()
    {
        $guard = new GuardRoute;
        $auth  = $this->_attainAuthenticatorByName($this->authenticatorName);
        $guard->setAuthenticator( $auth );
        $guard->setRoutesDenied([
            'main/oauth/authorize',
            'main/oauth/me/*',
        ]);

        return $guard;
    }
}
