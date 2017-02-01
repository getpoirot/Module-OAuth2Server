<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module\OAuth2\Interfaces\Model\iEntityUser;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;

abstract class aChallenge
{
    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;


    /**
     * Constructor.
     * @param iViewModelPermutation $viewModel @IoC /
     */
    function __construct(iViewModelPermutation $viewModel)
    {
        $this->viewModel = $viewModel;
    }


    /**
     * @param iEntityUser  $user
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    abstract function __invoke(iEntityUser $user = null, iHttpRequest $request = null);
}
