<?php
namespace Module\OAuth2\Actions\Events;

class EventHeap
    extends \Poirot\Events\EventHeap
{
    const EVENT_APP_ERROR            = 'app.error';


    /**
     * Initialize
     *
     */
    function __init()
    {
        // attach default event names:

    }
}
