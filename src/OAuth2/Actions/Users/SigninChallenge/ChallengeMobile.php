<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module\OAuth2\Interfaces\Model\iEntityUser;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class ChallengeMobile
    extends aChallenge
{
    /**
     * @param iEntityUser  $user
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    function __invoke(iEntityUser $user = null, iHttpRequest $request = null)
    {
        // TODO: Implement __invoke() method.
    }
}
