<?php
namespace Module\OAuth2\Exception;


class exUserNotFound
    extends \RuntimeException
{
    protected $code = 400;
}
