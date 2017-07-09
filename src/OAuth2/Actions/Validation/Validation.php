<?php
namespace Module\OAuth2\Actions\Validation;

use Module;

use Module\HttpFoundation\Actions\Url;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\iValidation;
use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use Module\OAuth2\Model\Entity\ValidationEntity;
use Poirot\Sms\Entity\SMSMessage;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iSentMessage;


class Validation
    extends Module\OAuth2\Actions\aAction
{
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var iClientOfSMS */
    protected $sms;

    /**
     * ValidatePage constructor.
     *
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     * @param iClientOfSMS         $sms             @IoC /module/smsclients/services/Sms
     */
    function __construct(iRepoValidationCodes $validationCodes, iRepoUsers $users, iClientOfSMS $sms)
    {
        $this->repoValidationCodes = $validationCodes;
        $this->repoUsers = $users;
        $this->sms = $sms;
    }


    /**
     * Validation
     *
     * @return $this
     */
    function __invoke()
    {
        return $this;
    }

    /**
     * Generate Validation Code For All Given Identifiers
     *
     * - Persist Validation
     *
     * @param iOAuthUser              $user
     * @param null                    $continue
     *
     * @return ValidationEntity|null Validation code identifier
     */
    function madeUserValidationState(iOAuthUser $user = null, $continue = null)
    {
        # Create Auth Codes for each Identifier:
        $identifiers = $user->getIdentifiers();

        return $this->madeValidationChallenge($user, $identifiers, $continue);
    }

    /**
     * Generate Validation Code For Given Identifiers
     *
     * - Persist Validation
     *
     *
     * @param iOAuthUser              $user
     * @param iUserIdentifierObject[] $identifiers
     * @param null                    $continue
     *
     * @return ValidationEntity|null Validation code identifier
     */
    function madeValidationChallenge(iOAuthUser $user = null, array $identifiers, $continue = null)
    {
        # Create Auth Codes for each Identifier:
        $authCodes = [];
        /** @var iUserIdentifierObject $ident */
        foreach ($identifiers as $ident) {
            if ($ident->isValidated())
                // validated identifiers don't need auth code such as username
                continue;

            $authCodes[] = $this->GenIdentifierAuthCode($ident);
        }

        if (empty($authCodes))
            // User Identifiers All Is Validated!
            return null;


        $repoValidationCodes = $this->repoValidationCodes;

        $validationCode = new ValidationEntity;
        $validationCode
            ->setValidationCode( \Poirot\Std\generateUniqueIdentifier(30) )
            ->setUserUid($user->getUid())
            ->setAuthCodes($authCodes)
            ->setContinueFollowRedirection($continue) // used by oauth registration follow
        ;

        $persistValidation = $repoValidationCodes->insert($validationCode);
        return $persistValidation;
    }

    /**
     * Validate a Validation Entity Against Given Auth Codes
     *
     * $validateAgainst:
     * [
     *   'email' => 4567
     *   ...
     * ]
     *
     * @param iValidation $validationEntity
     * @param array       $validateAgainst
     *
     *
     * @return boolean True if all codes is validated
     */
    function validateAuthCodes(iValidation $validationEntity, array $validateAgainst)
    {
        $rIsValidated = true;


        /** @var iValidationAuthCodeObject $ac */
        $authCodes = $validationEntity->getAuthCodes();
        foreach ($authCodes as $ac)
        {
            if ($ac->isValidated())
                // This Auth Code is Validated.
                continue;

            $mediumType = $ac->getType();
            if (! isset($validateAgainst[$mediumType]) ) {
                // Auth code not validated but code not given too.
                $rIsValidated &= false;

                continue;
            }

            if ( $ac->getCode() != $validateAgainst[$mediumType] ) {
                // Given code does not match
                $rIsValidated &= false;

                continue;
            }


            // Given Code Match; Update To Validated!!!
            // TODO implement commit/rollback; maybe momento|aggregate design pattern or something is useful here

            ## Update User Identifier To Validated With Current Value
            $this->repoUsers->setUserIdentifier(
                $validationEntity->getUserUid()
                , $ac->getType()
                , $ac->getValue()
                , true
            );

            ## Update Validation Entity
            $this->repoValidationCodes->updateAuthAsValidated(
                $validationEntity->getValidationCode()
                , $ac->getType()
            );

            // Mark As Validated; So Display Latest Status When Code Execution Follows.
            $ac->setValidated();


            // Determine that user itself has validate code on page, used to login user automatically
            Module\OAuth2\generateAndRememberToken( $validationEntity->getValidationCode() );
        }


        return $rIsValidated;
    }


    /**
     * Send Auth Code Of Specific Medium From Validation Entity
     *
     * - deliver auth code of specific medium type to owner
     *   exp. send 0745 as a code to mobile of user
     *
     *
     * @param iValidation $validation
     * @param string|null $mediumType Identifier type to send. exp. "email" | "sms"
     *
     * @return int Sent Message Interval
     */
    function sendAuthCodeByMediumType(iValidation $validation, $mediumType)
    {
        $authToSend = null;
        /** @var AuthObject $authCode */
        foreach ($validation->getAuthCodes() as $authCode) {
            if ($authCode->getType() === $mediumType) {
                $authToSend = $authCode;
                break;
            }
        }

        if ($authToSend === null)
            throw new \InvalidArgumentException(sprintf(
                'Identifier (%s) not embed within Validation Code Object.'
                , $mediumType
            ));


        switch (strtolower($mediumType))
        {
            case IdentifierObject::IDENTITY_EMAIL:
                $sendInterval = $this->_sendEmailValidation($validation, $authToSend);
                break;

            case IdentifierObject::IDENTITY_MOBILE:
                $sendInterval = $this->_sendMobileValidation($validation, $authToSend);
                break;

            default: throw new \InvalidArgumentException(sprintf(
                'Identifier (%s) is unknown.'
                , $mediumType
            ));
        }

        return $sendInterval;
    }


    // ...

    /**
     * Send SMS To Mobile Medium
     *
     * @param iValidation               $validationCode
     * @param AuthObject  $authCode
     *
     * @return int
     */
    protected function _sendMobileValidation(iValidation $validationCode, AuthObject $authCode)
    {
        if ( $lastTimeStampSent = $authCode->getTimestampSent() ) {
            // TODO configurable time interval
            $expiry = $this->__getTimeExpiryInterval( $lastTimeStampSent, new \DateInterval('PT2M') );

            # Check last sent datetime to avoid attacks
            if ( 0 < $expiry )
                // SMS is sent currently; wait to expire last time sent...
                return $expiry;
        }


        /*
         * [ "+98", "9355497674" ]
         */
        $mobileNo    = (string) $authCode->getValue();

        if (method_exists($this->sms, 'sendVerificationTo')) {
            // Currently our sms provider support for sending verification codes; with higher priority and delivery!
            $sentMessage = $this->sms->sendVerificationTo((string) $mobileNo, 'papionVerify', ['token' => $authCode->getCode()]);

        } else {
            $messageBody = $this->sapi()->config()->get(\Module\OAuth2\Module::CONF_KEY);
            $messageBody = $messageBody['mediums']['mobile']['message_verification'];

            $sentMessage = $this->sms->sendTo(
                [ (string) $mobileNo ]
                , new SMSMessage( sprintf($messageBody, $authCode->getCode()) )
            );
        }


        $sentMessage = current($sentMessage);

        if ($sentMessage->getStatus() === iSentMessage::STATUS_BANNED)
            throw new exRegistration(
                'سیستم قادر به ارسال پیامک فعال سازی نیست، دریافت سرویس های پیامکی توسط شما لغو شده است.'
            );

        # Update Last Sent Validation Code Datetime
        $this->repoValidationCodes->updateAuthTimestampSent(
            $validationCode->getValidationCode()
            , $authCode->getType()
        );

        return $this->__getTimeExpiryInterval(time(), new \DateInterval('PT2M'));
    }


    /**
     * Send Email
     *
     * @param iValidation $validationCode
     * @param AuthObject  $authCode
     *
     * @return int
     */
    protected function _sendEmailValidation(iValidation $validationCode, AuthObject $authCode)
    {
        if ( $lastTimeStampSent = $authCode->getTimestampSent() ) {
            // TODO Configurable time interval
            $expiry = $this->__getTimeExpiryInterval($lastTimeStampSent, new \DateInterval('PT1M'));

            # Check last sent datetime to avoid attacks
            if ( 0 < $expiry )
                // SMS is sent currently; wait to expire last time sent...
                return $expiry;
        }


        /** @var Url $validationUrl */
        $validationUrl = \Module\HttpFoundation\Actions::url(
            'main/oauth/recover/validate'
            , array('validation_code' => $validationCode->getValidationCode())
        );

        $urlString = (string) $validationUrl->uri()->withQuery(http_build_query(array(
            'email' => $authCode->getCode()
        )));


        // TODO send verification email
        /*
        $this->__postData('/email', array(
            'subject' => '.....',
            'to'   => $authCode->getValue(),
            'body' => sprintf(
                '<h4><a href="%s">برای فعال سازی اینجا کلیک کنید</a></h4>'
                // TODO base url prefixed within \Module\HttpFoundation\Actions::url() helper
                , \Module\Foundation\Actions::path('$serverUrl').$urlString
            )
        ));
        */


        # Update Last Sent Validation Code Datetime
        $this->repoValidationCodes->updateAuthTimestampSent(
            $validationCode->getValidationCode()
            , $authCode->getType()
        );

        return $this->__getTimeExpiryInterval(time(), new \DateInterval('PT1M'));
    }

    /**
     * Check Expiry Of Given Timestamp In an Interval
     *
     * @param $timestamp
     * @param \DateInterval $dateInterval
     *
     * @return int Negative int mean the time is past
     */
    protected function __getTimeExpiryInterval($timestamp, \DateInterval $dateInterval)
    {
        $exprTime = new \DateTime();
        $exprTime->setTimestamp($timestamp);
        $exprTime = $exprTime->add($dateInterval);

        return $exprTime->getTimestamp() - time();
    }
}
