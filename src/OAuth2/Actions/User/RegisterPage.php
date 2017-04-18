<?php
namespace Module\OAuth2\Actions\User;

use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Std\Exceptions\exUnexpectedValue;


class RegisterPage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'message.register';

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $request         @IoC /
     */
    function __construct(iRepoUsers $users, iHttpRequest $request)
    {
        $this->repoUsers = $users;

        parent::__construct($request);
    }


    function __invoke()
    {
        $request = $this->request;

        # Persist Registration Request:
        if ( Plugin\MethodType::_($request)->isPost() ) {
            $r = $this->_handleRegisterRequest($request);
            goto response;
        }


        # Is Exists Account Identifier When Registering?
        # prepare recovery link
        #
        $catchedExceptions = $this->withModule('foundation')
            ->flashMessage(self::FLASH_MESSAGE_ID)->hasObject('flash_exception');

        if ($catchedExceptions) {
            $existsIdentifiers = \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID)
                ->fetchObjects('flash_exception');
            /*
            [meta => NULL
            type String(5) => catch
            value Object => stdClass] // (object) type:$uidn->getType(), value: $uidn->getValue()
            */
            $existsIdentifiers = current($existsIdentifiers); // use once to recognize user
            $u = $this->repoUsers->findOneHasIdentifierWithValue($existsIdentifiers['value']->value);
        }
        
        
        # Build View

        $r = [];
        if (isset($u)) {
            $r['recovery_flow'] = [
                'user' => [
                    'uid'      => $u->getUid(),
                    'fullname' => $u->getFullName(),
                    #'avatar'  => $userAvatarUrl,
                    'identifier_exists' => $existsIdentifiers['value']->value,
                ],
                '_link' => \Module\Foundation\Actions\IOC::url(
                    'main/oauth/members/signin_challenge'
                    , [ 'uid' => $u->getUid() ]
                ),
            ];
        }


response:

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }

    /**
     * POST: Handle Register Request
     *
     * @param iHttpRequest $request
     *
     * @return ResponseRedirect
     */
    protected function _handleRegisterRequest(iHttpRequest $request)
    {
        # Create User Entity From Http Request
        $hydrateUser = new Entity\UserHydrate(
            Entity\UserHydrate::parseWith($this->request) );

        try
        {
            $entityUser  = new Entity\UserEntity($hydrateUser);

            // check allow server to pick a username automatically if not given!!
            $config  = $this->sapi()->config()->get(\Module\OAuth2\Module::CONF_KEY);
            $isAllow = (boolean) $config['allow_server_pick_username'];

            if (! $entityUser->getUsername() && $isAllow) {
                // Give Registered User Default Username On Registration
                $username = $this->AttainUsername($entityUser);
                $entityUser->setUsername($username);
            }

            __( new Entity\UserValidate($entityUser, ['must_have_username' => true]) )
                ->assertValidate();

            # Register User:

            // Continue Used to OAuth Registration Follow!!!
            $queryParams    = Plugin\ParseRequestData::_($request)->parseQueryParams();
            $continue       = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

            list($_, $validationHash) = $this->Register()->persistUser($entityUser, $continue);

            // Redirect To Validation Page
            $r = $this->withModule('foundation')->url(
                'main/oauth/members/validate'
                , ['validation_code' => $validationHash]
            );

        }
        catch (exUnexpectedValue $e)
        {
            // TODO Handle Validation ...
            throw $e;
        }
        catch (exIdentifierExists $e) {
            $flash = \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID);
            $flash->error(sprintf('این مشخصه قبلا توسط کاربر دیگری ثبت شده است.'));

            ## tell page that exception catches here; show user account recovery follow
            $uIdentifiers = $e->listIdentifiers();
            /** @var iUserIdentifierObject $uIdentifiers */
            $uIdentifiers = current($uIdentifiers);
            $flash->addObject(
                (object) array('type' => $uIdentifiers->getType(), 'value' => $uIdentifiers->getValue())
                , 'flash_exception'
            );
        }
        catch (exRegistration $e) {
            $flash = \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID);
            $flash->error( $e->getMessage() );
        }
        catch (\Exception $e) {
            \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID)
                ->error('سرور در حال حاضر قادر به انجام درخواست شما نیست. لطفا مجدد تلاش کنید.');
            ;
        }


        if (!isset($r)) {
            // Redirect Refresh
            $r = (string) \Module\Foundation\Actions\IOC::url(null, null, true);
        }


        return new ResponseRedirect( $r );
    }
}
