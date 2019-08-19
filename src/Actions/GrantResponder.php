<?php
namespace Module\OAuth2\Actions;

use Module\OAuth2\Services\GrantPlugins;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;


class GrantResponder
    extends GrantAggregateGrants
{
    protected $grantsContainer;

    /**
     * GrantResponder constructor.
     *
     * @param GrantPlugins $grantsContainer @IoC /module/oauth2/services/ContainerGrants
     */
    function __construct(GrantPlugins $grantsContainer)
    {
        $this->grantsContainer = $grantsContainer;


        // Attach Container Capped Grants To Aggregate Grant

        $grants = [];
        foreach ($grantsContainer->listServices() as $name)
            $grants[] = $grantsContainer->get($name);

        parent::__construct($grants);

    }

    /**
     * @return $this
     */
    function __invoke()
    {
        return $this;
    }
}
