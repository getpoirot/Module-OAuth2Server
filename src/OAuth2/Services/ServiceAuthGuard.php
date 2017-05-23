<?php
namespace Module\OAuth2\Services;

use Module\Authorization\Guard\GuardRoute;
use Poirot\Ioc\Container\Service\aServiceContainer;


class ServiceAuthGuard
    extends aServiceContainer
{
    /**
     * Create Service
     *
     * @return GuardRoute
     */
    function newService()
    {
        $guard = new GuardRoute;
        $auth  = \Module\Authorization\Actions::Authenticator( \Module\OAuth2\Module::REALM );
        $guard->setAuthenticator( $auth );
        $guard->setRoutesDenied([
            'main/oauth/authorize',
            'main/oauth/me/*',
        ]);

        return $guard;
    }
}
