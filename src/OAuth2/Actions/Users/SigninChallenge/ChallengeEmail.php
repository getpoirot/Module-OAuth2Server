<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


// TODO resend validation code as button
class ChallengeEmail
    extends aChallengeBase
{
    const CHALLENGE_TYPE = 'email';
    const FLASH_MESSAGE_ID = 'ChallengeEmail';


    /**
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    function doInvoke(iHttpRequest $request = null)
    {
        $_request_params = ParseRequestData::_($request)->parse();
        if (isset($_request_params['a']) && $_request_params['a'] == 'start')
            // Create Validation and Send Email Code
            // Redirect to Input Given Code From User
            return $this->_handleStartAction();

        if (isset($_request_params['a']) && $_request_params['a'] == 'confirm')
            // Display Confirm Dialog
            return $this->_handleConfirm($request);


        # Build View
        $v = $this->_getChallengeIdentifierObject()->getValue();
        return $this->viewModel
            ->setTemplate('main/oauth/members/challenge/email')
            ->setVariables([
                'url_next_challenge' => (string) $this->getNextUserChallengeUrl(),
                'email_truncate'     => \Module\OAuth2\truncateIdentifierValue($v),
            ])
        ;
    }
}
