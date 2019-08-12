<?php
namespace Module\OAuth2\Exception;


class exPasswordNotMatch
    extends \RuntimeException
{
    protected $code = 400;
}
