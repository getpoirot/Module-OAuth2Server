<?php
namespace Module\OAuth2\Actions\Validation;

use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Model\Entity\User\MobileObject;
use Module\OAuth2\Model\ValidationAuthCodeObject;


/**
 * Generate Validation Code For Given User Identifier
 *
 * exp.
 * Mobile: 7364
 *
 */
class GenIdentifierAuthCode
{
    /**
     * // TODO Merged Config How To Generate Codes Settings
     *
     * Generate Validation Code
     *
     * @param iUserIdentifierObject $ident
     *
     * @return ValidationAuthCodeObject Self when no arguments passed
     * @throws \Exception
     */
    function __invoke(iUserIdentifierObject $ident = null)
    {
        if ($ident == null)
            return $this;


        switch ( $ident->getType() ) {
            case 'email':
                return $this->newEmailAuthCode($ident->getValue());
            case 'mobile':
                return $this->newMobileAuthCode($ident->getValue());
            default:
                throw new \Exception(sprintf(
                    'Auth Code Cant Be Generated; Unknown Identifier (%s).'
                    , $ident->getType()
                ));
        }
    }


    /**
     * Email Auth Code
     *
     * @param string $value
     * @param bool   $validated
     *
     * @return ValidationAuthCodeObject
     */
    function newEmailAuthCode($value, $validated = false)
    {
        $authCode = new ValidationAuthCodeObject;
        $authCode->setType('email');
        $authCode->setCode(\Module\OAuth2\generateCode(
            5,
            \Module\OAuth2\GENERATE_CODE_NUMBERS
        ));
        $authCode->setValue($value);
        $authCode->setValidated($validated);

        return $authCode;
    }

    /**
     * Mobile Auth Code
     *
     * @param MobileObject $value
     * @param bool         $validated
     *
     * @return ValidationAuthCodeObject
     */
    function newMobileAuthCode(MobileObject $value, $validated = false)
    {
        $authCode = new ValidationAuthCodeObject;
        $authCode->setType('mobile');
        $authCode->setCode(\Module\OAuth2\generateCode(
            4, // length is used somewhere else like validation; be aware
            \Module\OAuth2\GENERATE_CODE_NUMBERS
        ));
        $authCode->setValue($value);
        $authCode->setValidated($validated);

        return $authCode;
    }
}
