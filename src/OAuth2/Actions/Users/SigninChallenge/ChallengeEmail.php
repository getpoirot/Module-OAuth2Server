<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module\OAuth2\Interfaces\Model\iEntityUser;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class ChallengeEmail
    extends aChallenge
{
    const CHALLENGE_TYPE = 'email';


    /**
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    function doInvoke(iHttpRequest $request = null)
    {


        # Build View
        $v = $this->getChallengeIdentifierObject()->getValue();
        return $this->viewModel
            ->setTemplate('main/oauth/members/challenge/email')
            ->setVariables([
                'url_next_challenge' => (string) $this->getNextUserChallengeUrl(),
                'email_truncate'     => \Module\OAuth2\truncateIdentifierValue($v),
            ])
        ;
    }
}
