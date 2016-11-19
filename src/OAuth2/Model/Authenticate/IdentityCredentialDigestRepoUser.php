<?php
namespace Module\OAuth2\Model\Authenticate;

use Poirot\AuthSystem\Authenticate\Exceptions\exMissingCredential;
use Poirot\AuthSystem\Authenticate\Identity\IdentityHttpDigest;
use Poirot\AuthSystem\Authenticate\Identity\IdentityUsername;
use Poirot\AuthSystem\Authenticate\Interfaces\iIdentity;

use Poirot\AuthSystem\Authenticate\RepoIdentityCredential\aIdentityCredentialAdapter;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUsers;

/**
 * Authenticate Digest Credential From Repo Users
 *
 */
class IdentityCredentialDigestRepoUser
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
        $username = $this->getUsername();
        $password = $this->getPassword();
        if (!isset($username))
            throw new exMissingCredential('Adapter Credential not contains Username.');


        $identity = false;
        
        if ($password === null) {
            // Digest authenticate
            $user = $this->repoUsers->findOneByIdentifier($username);
            if ($user) {
                # Digest Identity
                $username = $user->getIdentifier();
                $realm    = $user->getRealm();
                if ($realm !== $this->getRealm())
                    return false;

                $a1       = $user->getA1();
                // digest http authorization need secret key (A1)
                $identity = new IdentityHttpDigest;
                $identity->setUsername($username);
                $identity->setA1($a1);
            }
        } else {
            $user = $this->repoUsers->findOneByUserPass($username, $password);
            if ($user) {
                $identity = new IdentityUsername();
                $identity->setUsername($username);
            }
        }

        return $identity;
    }


    // Credentials as Options:

    /**
     * @required
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
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