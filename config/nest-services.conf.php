<?php
/** @see \Poirot\Ioc\Container\BuildContainer */
return array(
    'nested' 
        => array(
            'repository' => array(
                'services' => array(
                    \Module\OAuth2\Services\Repository\ServiceRepoClients::class,
                ),
            ),
        ),
);
