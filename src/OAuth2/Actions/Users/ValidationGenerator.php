<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\Foundation\Actions\IOC;
use Module\OAuth2\Actions\aAction;
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
    /**
     * @param string                     $uid
     * @param []ValidationCodeAuthObject $identifiers
     * @param null|string                $continue    Registration from oauth partners
     *
     * @return string
     */
    function __invoke($uid = null, array $identifiers = null, $continue = null)
    {
        /** @var iRepoValidationCodes $repoValidationCodes */
        $repoValidationCodes = $this->IoC()->get('services/repository/ValidationCodes');

        $validationCode = new ValidationCode;
        $validationCode
            ->setUserIdentifier($uid)
            ->setAuthCodes($identifiers)
            ->setContinueFollowRedirection($continue) // used by oauth registration follow
        ;

        $v    = $repoValidationCodes->insert($validationCode);
        $code = $v->getValidationCode();

        /** @var ValidationCodeAuthObject $id */
        foreach ($identifiers as $id)
            $this->_sendValidation($code, $id);

        return $code;
    }


    // ..

    // TODO Improve on send messages and separate from here!!!

    function _sendValidation($validationCode, ValidationCodeAuthObject $authCode)
    {
        switch ($authCode->getType()) {
            case 'email':  $this->_sendEmailValidation($validationCode, $authCode);  break;
            case 'mobile': $this->_sendMobileValidation($validationCode, $authCode); break;
        }
    }

    function _sendMobileValidation($validationCode, ValidationCodeAuthObject $authCode)
    {
        /*
         * [ "+98", "9355497674" ]
         */
        $mobileNo = $authCode->getValue();
        $this->_postData('/sms', array(
            'to'   => '0'.$mobileNo[1],
            'body' => sprintf(
                'کد فعال سازی شما %s'
                , $authCode->getCode()
            )
        ));
    }

    function _sendEmailValidation($validationCode, ValidationCodeAuthObject $authCode)
    {
        /** @var UrlAction $validationUrl */
        $validationUrl = $this->withModule('foundation')->url(
            'main/oauth/validate'
            , array('validation_code' => $validationCode)
        );

        $urlString = (string) $validationUrl->uri()->withQuery(http_build_query(array(
            'email' => $authCode->getCode()
        )));

        $this->_postData('/email', array(
            'subject' => 'کد فعال سازی دیجی پیک',
            'to'   => $authCode->getValue(),
            'body' => sprintf(
                '<h4><a href="%s">برای فعال سازی اینجا کلیک کنید</a></h4>'
                , IOC::path('$serverUrl').$urlString
            )
        ));
    }


    protected function _postData($path, array $data)
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
