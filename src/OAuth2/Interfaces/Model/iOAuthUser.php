<?php
namespace Module\OAuth2\Interfaces\Model;

use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthUser as iBaseOAuthUser;

/*
{
  "full_name": "Payam Naderi",
  "contacts": [
    {
      "type": "mobile",
      "value": [
        "+98",
        "9355497674"
      ],
      "validated": true
    },
    {
      "type": "email",
      "value": "naderi.payam@gmail.com",
      "validated": true
    }
  ]
  "grants": [
    {
      "type": "password",
      "value": "e10adc3949ba59abbe56e057f20f883e"
    }
  ],
}
 */

interface iOAuthUser
    extends iBaseOAuthUser
{
    /**
     * Get FullName
     *
     * @return string
     */
    function getFullName();

    /**
     * Get Contacts
     *
     * @return iUserIdentifierObject[]
     */
    function getIdentifiers();

    /**
     * Get Grants
     *
     * @return iUserGrantObject[]
     */
    function getGrants();

    /**
     * Get Created Date
     *
     * @return \DateTime
     */
    function getDateCreated();
}
