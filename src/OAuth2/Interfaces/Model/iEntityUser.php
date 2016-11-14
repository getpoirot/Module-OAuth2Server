<?php
namespace Module\OAuth2\Interfaces\Model;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser as iBaseEntityUser;

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

interface iEntityUser
    extends iBaseEntityUser
{
    /**
     * Set FullName
     *
     * @param string $fullName
     *
     * @return $this
     */
    function setFullName($fullName);

    /**
     * Get FullName
     *
     * @return string
     */
    function getFullName();

    /**
     * Set Contacts; Replace Old Ones
     * note: use [] empty array to delete contacts
     *
     * @param []iEntityUserContactObject $contacts
     *
     * @return $this
     */
    function setIdentifiers(array $identifiers);

    /**
     * Get Contacts
     *
     * @return []iEntityUserContactObject
     */
    function getIdentifiers();

    /**
     * Add Contact
     *
     * @param iEntityUserIdentifierObject $identifier
     *
     * @return $this
     */
    function addIdentifier(iEntityUserIdentifierObject $identifier);

    /**
     * Set Given Grants Accounts
     * note: use [] empty array to delete grants
     *
     * @param []iEntityUserGrantObject $grants
     *
     * @return $this
     */
    function setGrants(array $grants);

    /**
     * Get Grants
     *
     * @return []iEntityUserGrantObject
     */
    function getGrants();

    /**
     * Add Grant
     *
     * @param iEntityUserGrantObject $grant
     *
     * @return $this
     */
    function addGrant(iEntityUserGrantObject $grant);

    /**
     * Set Created Date
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    function setDateCreated(\DateTime $date);

    /**
     * Get Created Date
     *
     * @return \DateTime
     */
    function getDateCreated();
}
