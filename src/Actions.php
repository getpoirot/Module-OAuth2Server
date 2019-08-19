<?php
namespace Module\OAuth2;

use Module\OAuth2\Actions\Recover\SigninChallengePage;
use Module\OAuth2\Actions\Recover\SigninNewPassPage;
use Module\OAuth2\Actions\Recover\SigninRecognizePage;
use Module\OAuth2\Actions\User\LoginPage;
use Module\OAuth2\Actions\User\LogoutPage;
use Module\OAuth2\Actions\User\Register;
use Module\OAuth2\Actions\User\RegisterPage;
use Module\OAuth2\Actions\User\RetrieveAuthenticatedUser;
use Module\OAuth2\Actions\Validation\ValidatePage;
use Module\OAuth2\Actions\Validation\ResendAuthCodeRequest;
use Module\OAuth2\Actions\Validation\Validation;
use Module\OAuth2\Model\Entity\UserEntity;


/**
 * @property RegisterPage                   $RegisterPage
 * @property LoginPage                      $LoginPage
 * @property LogoutPage                     $LogoutPage
 * @property ValidatePage                   $ValidatePage
 * @property SigninRecognizePage            $SigninRecognizePage
 * @property SigninChallengePage            $SigninChallengePage
 * @property SigninNewPassPage              $SigninNewPassPage
 * @property ResendAuthCodeRequest          $ResendAuthCodeRequest
 * @property RetrieveAuthenticatedUser      $RetrieveAuthenticatedUser
 *
 * @method static UserEntity  RetrieveAuthenticatedUser()
 * @method static Validation  Validation()
 * @method static Register    Register()
 */
class Actions extends \IOC
{ }
