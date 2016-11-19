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
        $characters = null;

        if (($contains & GENERATE_CODE_NUMBERS) == GENERATE_CODE_NUMBERS)
            $characters .= '0123456789';

        if (($contains & GENERATE_CODE_STRINGS_LOWER) == GENERATE_CODE_STRINGS_LOWER)
            $characters .= 'abcdefghijklmnopqrstuvwxyz';

        if (($contains & GENERATE_CODE_STRINGS_UPPER) == GENERATE_CODE_STRINGS_UPPER)
            $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($characters === null)
            throw new \InvalidArgumentException('Invalid Contains Argument Provided; Does Not Match Any Condition.');


        $randomString = '';
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, strlen($characters) - 1)];

        return $randomString;
    }
}
