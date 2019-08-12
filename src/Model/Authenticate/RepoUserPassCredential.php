<?php
namespace Module\OAuth2\Model\Authenticate;

use Poirot\AuthSystem\Authenticate\Exceptions\exMissingCredential;
use Poirot\AuthSystem\Authenticate\Identity\IdentityOpen;
use Poirot\AuthSystem\Authenticate\Interfaces\iIdentity;

use Poirot\AuthSystem\Authenticate\RepoIdentityCredential\aIdentityCredentialAdapter;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUsers;


/**
 * Authenticate User/Pass Credential From Repo Users
 *
 */
class RepoUserPassCredential
    extends aIdentityCredentialAdapter
{
    protected $username;
    protected $password;
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Do Match Identity With Given Options/Credential
     *
     * @param array $credentials Include Credential Data
     *
     * @return iIdentity|false
     */
    protected function doFindIdentityMatch(array $credentials)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];
        if (!isset($username))
            throw new exMissingCredential('Adapter Credential not contains Username.');

        $identity = false;
        $user = $this->repoUsers->findOneByUserPass($username, $password);


        // TODO check if user is banned or not activated or .... here and throw exAuthentication exception

        if ($user) {
            $identity = new IdentityOpen;
            // (string) because Mongo ObjectID can't serialized
            $identity->setUid( (string) $user->getUid());
        }

        return $identity;
    }


    // Credentials as Options:

    /**
     * @required
     *
     * @return string
     */
    function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    function setUsername($username)
    {
        $this->username = (string) $username;
        return $this;
    }

    /**
     * @return string
     */
    function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    function setPassword($password)
    {
        $this->password = (string) $password;
        return $this;
    }


    // Options:

    /**
     * Set Users Repository
     *
     * @param iRepoUsers $repoUser
     *
     * @return $this
     */
    function setRepoUsers(iRepoUsers $repoUser)
    {
        $this->repoUsers = $repoUser;
        return $this;
    }
}