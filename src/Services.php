<?php
namespace Module\OAuth2;

use Module\OAuth2\Events\OAuthEventHeap;
use Module\OAuth2\Services\GrantPlugins;

/**
 * @method static OAuthEventHeap Events();
 * @method static GrantPlugins GrantPlugins();
 */
class Services extends \IOC
{
    const EventsHeap   = 'Events';
    const GrantPlugins = 'GrantPlugins';
}
