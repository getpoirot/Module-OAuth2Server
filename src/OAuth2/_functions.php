<?php
namespace Module\OAuth2
{
    use Poirot\Http\Interfaces\iHttpRequest;
    use Poirot\Http\Psr\ServerRequestBridgeInPsr;
    use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
    use Poirot\OAuth2\Resource\Validation\AuthorizeByInternalServer;


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

        // pass token as collector result chain to other action
        return $validator->hasValidated($requestPsr);
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
}
