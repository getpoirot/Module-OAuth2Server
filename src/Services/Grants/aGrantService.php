<?php
namespace Module\OAuth2\Services\Grants;

use Module\OAuth2\Module;
use Module\OAuth2\Services;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Interfaces\Server\iGrant;
use Poirot\OAuth2\Server\Grant\aGrant;


abstract class aGrantService
    extends aServiceContainer
{
    protected $defaultSettings = [];


    /**
     * @inheritDoc
     */
    function __construct($nameOsetter = null, $setter = array())
    {
        parent::__construct($nameOsetter, $setter);

        $this->__init();
    }

    function __init()
    {
        // Implement if needed.
    }


    /**
     * Get Grant Classname
     *
     * @return string
     */
    abstract function getGrantClassname();


    /**
     * @inheritDoc
     * @return iGrant
     */
    final function newService()
    {
        $grantType = $this->getGrantClassname();
        if (! class_exists($grantType) )
            throw new \RuntimeException(sprintf('Grant "%s" not found.', $grantType));


        /** @var aGrant $grantType */
        $grantType = $this->_setGrantSettingsFromMergedConf(
            $this->_injectDefaultDependencies(new $grantType));

        return $grantType;
    }

    // ..

    /**
     * Set Grant Default Settings
     *
     * @param aGrant|iGrant $grant
     *
     * @return iGrant
     */
    protected function _setGrantSettingsFromMergedConf(iGrant $grant)
    {
        $grantType  = $grant->getGrantType();

        $moduleConf = \Poirot\config(Module::class, 'grants', 'settings');
        $defConf    = array_merge($moduleConf['default'] ?? [], $this->defaultSettings);
        if ( isset($moduleConf[$grantType]) )
            $defConf = array_merge($defConf, $moduleConf[$grantType]);


        $grant->with($defConf, false);
        return $grant;
    }

    /**
     * Inject Default Dependencies
     *
     * @param aGrant|iGrant $grant
     *
     * @return iGrant
     */
    protected function _injectDefaultDependencies($grant)
    {
        switch (1) {
            case method_exists($grant, 'setTtlAuthCode'):
                $grant->setTtlAuthCode(new \DateInterval('PT5M'));

            case method_exists($grant, 'setTtlAccessToken'):
                $grant->setTtlAccessToken(new \DateInterval('PT1H'));

            case method_exists($grant, 'setTtlRefreshToken'):
                $grant->setTtlRefreshToken(new \DateInterval('P1M'));

            case method_exists($grant, 'setRepoUser'):
                $grant->setRepoUser(Services\Repositories::Users());

            case method_exists($grant, 'setRepoClient'):
                $grant->setRepoClient(Services\Repositories::Clients());

            case method_exists($grant, 'setRepoAuthCode'):
                $grant->setRepoAuthCode(Services\Repositories::AuthCodes());

            case method_exists($grant, 'setRepoAccessToken'):
                $grant->setRepoAccessToken(Services\Repositories::AccessTokens());

            case method_exists($grant, 'setRepoRefreshToken'):
                $grant->setRepoRefreshToken(Services\Repositories::RefreshTokens());
        }

        return $grant;
    }
}
