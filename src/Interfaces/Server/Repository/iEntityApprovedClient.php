<?php
namespace Module\OAuth2\Interfaces\Server\Repository;

interface iEntityApprovedClient
{
    /**
     * Unique Client Identifier
     *
     * @return string|int
     */
    function getIdentifier();

    /**
     * Get Client Name
     * this is informational data that showed into user
     *
     * @return string
     */
    function getName();

    /**
     * Get Http Address Of Client Logo Image
     * this is informational data that showed into user
     *
     * @return string
     */
    function getImage();
}
