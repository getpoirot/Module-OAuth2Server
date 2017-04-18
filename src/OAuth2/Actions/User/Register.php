<?php
namespace Module\OAuth2\Actions\User;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iValidation;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\OAuth2\Model\Entity\UserEntity;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use Poirot\Sms\Entity\SMSMessage;
use Poirot\Sms\Interfaces\iClientOfSMS;
use Poirot\Sms\Interfaces\iSentMessage;


class Register
    extends aAction
{
    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;
    /** @var iClientOfSMS */
    protected $sms;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/Users
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/ValidationCodes
     * @param iClientOfSMS         $sms             @IoC /module/smsclients/services/Sms
     */
    function __construct(
        iRepoUsers $users
        , iRepoValidationCodes $validationCodes
        , iClientOfSMS $sms
    ) {
        $this->repoUsers = $users;
        $this->repoValidationCodes = $validationCodes;
        $this->sms = $sms;
    }


    /**
     * Allow Access To Methods Within
     *
     * @return $this
     */
    function __invoke()
    {
        return $this;
    }

    /**
     * Persist User (Register)
     *
     * - check that given identifier(s) for User
     *   not registered before
     *
     * - generate authentication code for identifiers and send it to medium
     *
     * - return validation hashed endpoint for validating codes by send it back
     *
     * @param iOAuthUser  $entity
     * @param string|null $continue Continue used by oauth partners registration follows
     *
     * @return array [user, validationHash|null] null when user has no identifier that need validation
     * @throws exIdentifierExists
     */
    function persistUser(iOAuthUser $entity, $continue = null)
    {
        # Persist Data:
        $repoUsers = $this->repoUsers;

        ## validate existence identifier
        #- email or mobile not given before
        $identifiers = $repoUsers->hasAnyIdentifiersRegistered( $entity->getIdentifiers() );
        if (!empty($identifiers))
            throw new exIdentifierExists($identifiers);


        if (! $entity->getUid() )
            // User must have identifier when validation code generated
            $entity->setUid( $repoUsers->attainNextIdentifier() );

        // TODO implement commit/rollback; maybe momento/aggregate design pattern or something is useful here

        # Give User Validation Code:
        $validationHash = $this->giveUserValidationCode($entity, $continue);

        # Then Persist User Entity:
        /** @var UserEntity|iOAuthUser $user */
        $user = $repoUsers->insert($entity);

        return array( $user, $validationHash );
    }

    /**
     * Generate And Persist Validation Code For User
     *
     * - generate authentication code for identifiers and send it to medium
     *
     * - return validation hashed endpoint for validating codes by send it back
     *
     *
     * @param iOAuthUser  $user
     * @param string|null $continue Continue used by oauth partners registration follows
     *
     * @return string|null Validation code, or null when user has no identifier that need validation
     * @throws \Exception
     */
    function giveUserValidationCode(iOAuthUser $user, $continue = null)
    {
        $validationHash = null;

        if ( $validationEntity = $this->MadeUserIdentifierValidationState($user, $continue) ) {
            /** @var AuthObject $authCodeObject */
            foreach ($validationEntity->getAuthCodes() as $authCodeObject)
                $_ = $this->sendAuthCodeByMediumType($validationEntity, $authCodeObject->getType());

            $validationHash = $validationEntity->getValidationCode();
        }

        return $validationHash;
    }


    // ..

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
        $messageBody = $this->sapi()->config()->get(\Module\OAuth2\Module::CONF_KEY);
        $messageBody = $messageBody['mediums']['mobile']['message_verification'];

        $sentMessage = $this->sms->sendTo(
            [ (string) $mobileNo ]
            , new SMSMessage( sprintf($messageBody, $authCode->getCode()) )
        );

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
     * @param iValidation              $validationCode
     * @param AuthObject $authCode
     *
     * @return int
     */
    protected function _sendEmailValidation(iValidation $validationCode, AuthObject $authCode)
    {
        if ( $lastTimeStampSent = $authCode->getTimestampSent() ) {
            $expiry = $this->__getTimeExpiryInterval($lastTimeStampSent, new \DateInterval('PT1M'));

            # Check last sent datetime to avoid attacks
            if ( 0 < $expiry )
                // SMS is sent currently; wait to expire last time sent...
                return $expiry;
        }


        /** @var UrlAction $validationUrl */
        $validationUrl = $this->withModule('foundation')->url(
            'main/oauth/members/validate'
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
                // TODO base url prefixed within ->url() helper
                , $this->withModule('foundation')->path('$serverUrl').$urlString
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
