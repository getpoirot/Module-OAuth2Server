<?php
namespace Module\OAuth2\Services\Repository;

use Module\OAuth2\Model\Repo\Mongo\Clients;

/**
 * Usage:
 *   to ease access to IoC nested containers
 *   Module\Places\Services\Repository\IOC::places()
 * 
 * @method static Clients clients(array $options=null)
 * @see ServiceRepoClients
 */
class IOC extends \IOC
{ }
