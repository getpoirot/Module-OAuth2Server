<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;

class ChallengeFine
    extends aChallenge
{
    /**
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    function doInvoke(iHttpRequest $request = null)
    {
        return $this->viewModel->setTemplate('main/oauth/members/challenge/fine');
    }
}
