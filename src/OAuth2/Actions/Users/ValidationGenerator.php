<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\Foundation\Actions\IOC;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\ValidationCode;
use Module\OAuth2\Model\ValidationCodeAuthObject;


/**
 * Generate and Persist Validation Codes
 *
 */
class ValidationGenerator
    extends aAction
{
    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;


    /**
     * ValidatePage constructor.
     * @param iRepoValidationCodes $validationCodes @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoValidationCodes $validationCodes)
    {
        $this->repoValidationCodes = $validationCodes;
    }

    /**
     * Generate Validation Code For Given Identifiers
     * - Persist Validation
     * - Send Auth Code To Validate Device
     *
     * @param string                     $uid
     * @param []ValidationCodeAuthObject $identifiers
     * @param null|string                $continue    Registration from oauth partners
     *
     * @return string Validation code identifier
     */
    function __invoke($uid = null, array $authCodes = null, $continue = null)
    {
        if ($uid === null)
            // allow access to other methods
            return $this;

        $repoValidationCodes = $this->repoValidationCodes;

        $validationCode = new ValidationCode;
        $validationCode
            ->setUserIdentifier($uid)
            ->setAuthCodes($authCodes)
            ->setContinueFollowRedirection($continue) // used by oauth registration follow
        ;

        $v = $repoValidationCodes->insert($validationCode);

        /** @var ValidationCodeAuthObject $id */
        foreach ($authCodes as $id)
            $this->sendValidation($v, $id->getType());

        return $v->getValidationCode();
    }


    // ..

    // TODO Improve on send messages and separate from here!!!

    /**
     * Send Auth Code For Validation
     *
     * @param iEntityValidationCode $validationCode
     * @param string|null           $authIdentifier Identifier type to send. exp. "email" | "sms"
     *
     * @return int Sent Message Interval
     */
    function sendValidation(iEntityValidationCode $validationCode, $authIdentifier)
    {
        $authToSend = null;
        /** @var ValidationCodeAuthObject $authCode */
        foreach ($validationCode->getAuthCodes() as $authCode) {
            if ($authCode->getType() === $authIdentifier) {
                $authToSend = $authCode;
                break;
            }
        }

        if ($authToSend === null)
            throw new \InvalidArgumentException(sprintf(
                'Identifier (%s) not embed within Validation Code Object.'
                , $authIdentifier
            ));


        switch (strtolower($authIdentifier)) {
            case 'email':  $sendInterval = $this->_sendEmailValidation($validationCode, $authToSend);  break;
            case 'mobile': $sendInterval = $this->_sendMobileValidation($validationCode, $authToSend); break;

            default: throw new \InvalidArgumentException(sprintf(
                'Identifier (%s) is unknown.'
                , $authIdentifier
            ));
        }

        return $sendInterval;
    }

    /**
     * @param iEntityValidationCode    $validationCode
     * @param ValidationCodeAuthObject $authCode
     *
     * @return int
     */
    function _sendMobileValidation(iEntityValidationCode $validationCode, ValidationCodeAuthObject $authCode)
    {
        if ($lastTimeStampSent = $authCode->getTimestampSent()) {
            $expiry = $this->__getTimeExpiryInterval($lastTimeStampSent, new \DateInterval('PT2M'));

            # Check last sent datetime to avoid attacks
            if ( 0 < $expiry )
                // SMS is sent currently; wait to expire last time sent...
                return $expiry;
        }


        /*
         * [ "+98", "9355497674" ]
         */
        $mobileNo = $authCode->getValue();
        $this->__postData('/sms', array(
            'to'   => '0'.$mobileNo[1],
            'body' => sprintf(
                'کد فعال سازی شما %s'
                , $authCode->getCode()
            )
        ));


        # Update Last Sent Validation Code Datetime
        $this->repoValidationCodes->updateAuthTimestampSent(
            $validationCode->getValidationCode()
            , $authCode->getType()
        );

        return $this->__getTimeExpiryInterval(time(), new \DateInterval('PT2M'));
    }

    /**
     * @param iEntityValidationCode    $validationCode
     * @param ValidationCodeAuthObject $authCode
     *
     * @return int
     */
    function _sendEmailValidation(iEntityValidationCode $validationCode, ValidationCodeAuthObject $authCode)
    {
        if ($lastTimeStampSent = $authCode->getTimestampSent()) {
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

        $this->__postData('/email', array(
            'subject' => 'کد فعال سازی دیجی پیک',
            'to'   => $authCode->getValue(),
            'body' => sprintf(
                '<h4><a href="%s">برای فعال سازی اینجا کلیک کنید</a></h4>'
                , IOC::path('$serverUrl').$urlString
            )
        ));


        # Update Last Sent Validation Code Datetime
        $this->repoValidationCodes->updateAuthTimestampSent(
            $validationCode->getValidationCode()
            , $authCode->getType()
        );

        return $this->__getTimeExpiryInterval(time(), new \DateInterval('PT1M'));
    }

    /**
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


    protected function __postData($path, array $data)
    {
        $url = 'http://91.239.55.157:8060/';
        $url .= ltrim($path, '/');
        $ch  = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS
            , http_build_query($data)
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec ($ch);
        if ($err = curl_error($ch))
            throw new \Exception('Wire Data Error While Sent To Messaging.');

        curl_close ($ch);
        return $result;
    }
}
