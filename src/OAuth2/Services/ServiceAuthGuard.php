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
     * @return mixed
     */
    function newService()
    {
        $guard = new GuardRoute;
        $auth  = \Module\Authorization\Actions\IOC::Authenticator( \Module\OAuth2\Module::AUTHENTICATOR );
        $guard->setAuthenticator( $auth );
        $guard->setRoutesDenied([
            'main/oauth/authorize',
            'main/oauth/me/*',
        ]);

        return $guard;
    }
}
