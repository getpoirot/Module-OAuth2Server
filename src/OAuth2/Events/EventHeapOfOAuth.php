<?php
namespace Module\OAuth2\Events;


class EventHeapOfOAuth
    extends \Poirot\Events\EventHeap
{
    const EVENT_USER_REGISTER = 'oauth2.user.register';


    /**
     * Initialize
     *
     */
    function __init()
    {
        // attach default event names:

    }
}
