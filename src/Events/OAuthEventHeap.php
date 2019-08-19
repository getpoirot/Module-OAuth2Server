<?php
namespace Module\OAuth2\Events;

use Poirot\Events\Event;


class OAuthEventHeap
    extends \Poirot\Events\EventHeap
{
    const USER_REGISTER_BEFORE = 'oauth2.user.register.before';
    const USER_REGISTER        = 'oauth2.user.register';


    /**
     * Initialize
     *
     */
    function __init()
    {
        $this->collector = new OAuthDataTransfer;

        // attach default event names:
        $this->bind( new Event(self::USER_REGISTER_BEFORE) );
        $this->bind( new Event(self::USER_REGISTER) );
    }

    /**
     * @override ide auto info
     * @inheritdoc
     *
     * @return OAuthDataTransfer
     */
    function collector($options = null)
    {
        return parent::collector($options);
    }
}
