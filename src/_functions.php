<?php
namespace Module\OAuth2
{
    use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
    use Poirot\Storage\Http\SessionStore;


    const GENERATE_CODE_NUMBERS       = 2;
    const GENERATE_CODE_STRINGS_LOWER = 4;
    const GENERATE_CODE_STRINGS_UPPER = 8;
    const GENERATE_CODE_STRINGS = GENERATE_CODE_STRINGS_LOWER | GENERATE_CODE_STRINGS_UPPER;


    /**
     * Check The Given Token, Validation Code Pair is Valid
     * by check the session storage equality
     *
     * @param string $hash
     *
     * @return bool
     */
    function hasTokenBind($hash, $token = null)
    {
        $storage = new SessionStore( 'SESSION_REALM_TOKEN_BIND' );
        $vToken  = $storage->get($hash);

        if ($token !== null)
            return $token === $vToken;

        return $vToken;
    }

    /**
     * Generate Token and store to session as bind with given
     * validation code
     *
     * - it will gather in pages for valid requests assertion
     *
     * @param string $hash
     *
     * @return string
     */
    function generateAndRememberToken($hash)
    {
        $token   = \Poirot\Std\generateShuffleCode(16);
        $storage = new SessionStore( 'SESSION_REALM_TOKEN_BIND' );
        $storage->set($hash, $token);
        return $token;
    }

    /**
     * Generate Random Strings
     *
     * @param int $length
     * @param int $contains
     *
     * @return string
     */
    function generateCode($length = 8, $contains = GENERATE_CODE_NUMBERS | GENERATE_CODE_STRINGS)
    {
        return \Poirot\Std\generateShuffleCode($length, $contains);
    }

    /**
     * Get Specific Identifier From Identifiers List
     *
     * @param string                  $type
     * @param iUserIdentifierObject[] $identifiers
     *
     * @return iUserIdentifierObject|null
     * @throws \Exception
     */
    function getIdentifierFromList($type, $identifiers)
    {
        foreach ($identifiers as $identifier) {
            if ($identifier->getType() === $type)
                return $identifier;
        }

        return null;
    }

    /**
     * Truncate Identifier Value To Something That Hard To Predict For Strangers
     *
     * exp.
     *  naderi.payam@gmail.com ----> na------am@gmail.com
     *
     * @param string $value
     * @param string $type
     *
     * @return string
     */
    function truncateIdentifierValue($value, $type = null, $threshold = 4)
    {
        $value = (string) $value;

        switch ($type) {
            case 'mobile':
                return truncateIdentifierValue($value);
                break;
            default:
        }

        if (false !== $pos = strpos($value, '@')) {
            // maybe its email
            $username = truncateIdentifierValue(substr($value, 0, $pos));
            return $username.substr($value, $pos);
        }

        $len    = strlen($value);
        $chrNum = round($len / $threshold);

        $return = '';
        $return .= substr($value, 0, $chrNum);
        $return .= str_repeat('*', $len - ($chrNum * 2));
        $return .= substr($value, -1*($chrNum));

        return $return;
    }


    /**
     * Is Valid Mobile Number?
     *
     * @param string $mobileNumber
     * @param null   $matches
     *
     * @return bool
     */
    function isValidMobileNum($mobileNumber, &$matches = null)
    {
        if ($matches === null)
            $matches = [];

        $pattern = '/^[- .\(\)]?((?P<country_code>(98)|(\+98)|(0098)|0){1}[- .\(\)]{0,3})(?P<number>((90)|(91)|(92)|(93)|(99)){1}[0-9]{8})$/';
        return preg_match($pattern, (string) $mobileNumber, $matches);
    }

    /**
     * Is Valid Email Address?
     *
     * @param string $emailAddress
     *
     * @return bool
     */
    function isEmailAddress($emailAddress)
    {
        return filter_var( (string) $emailAddress, FILTER_VALIDATE_EMAIL );
    }
}
