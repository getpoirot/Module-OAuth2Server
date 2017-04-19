<?php
namespace Module\OAuth2
{
    use Poirot\Http\Interfaces\iHttpRequest;
    use Poirot\Http\Psr\ServerRequestBridgeInPsr;
    use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
    use Poirot\OAuth2\Resource\Validation\AuthorizeByInternalServer;
    use Poirot\Storage\Gateway\DataStorageSession;


    const GENERATE_CODE_NUMBERS       = 2;
    const GENERATE_CODE_STRINGS_LOWER = 4;
    const GENERATE_CODE_STRINGS_UPPER = 8;
    const GENERATE_CODE_STRINGS = GENERATE_CODE_STRINGS_LOWER | GENERATE_CODE_STRINGS_UPPER;


    /**
     * Assert Authorization Token From Request
     *
     * @param iHttpRequest $request
     *
     * @return iEntityAccessToken
     */
    function assertAuthToken(iHttpRequest $request)
    {
        $requestPsr = new ServerRequestBridgeInPsr($request);

        $repoAccessTokens = \Module\OAuth2\Services\Repository\IOC::AccessTokens();
        $validator        = new AuthorizeByInternalServer($repoAccessTokens);

        $token = $validator->parseTokenFromRequest($requestPsr);
        // pass token as collector result chain to other action
        return $validator->assertToken($token);
    }

    // Helpers:

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
        $storage = new DataStorageSession( 'SESSION_REALM_TOKEN_BIND' );
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
        $storage = new DataStorageSession( 'SESSION_REALM_TOKEN_BIND' );
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
        $pattern = '/^[- .\(\)]?((?P<country_code>(98)|(\+98)|(0098)|0){1}[- .\(\)]{0,3})(?P<number>((91)|(93)){1}[0-9]{8})$/';
        return preg_match($pattern, (string) $mobileNumber, $matches);
    }
}
