<?php
namespace Module\OAuth2\Actions\Users\SigninChallenge;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class ChallengeMobile
    extends aChallenge
{
    const CHALLENGE_TYPE = 'mobile';

    /** @var iRepoValidationCodes */
    protected $repoValidationCodes;


    /**
     * Constructor.
     * @param iRepoValidationCodes  $validationCodes @IoC /module/oauth2/services/repository/
     * @param iViewModelPermutation $viewModel       @IoC /
     */
    function __construct(iRepoValidationCodes $validationCodes, iViewModelPermutation $viewModel)
    {
        $this->repoValidationCodes = $validationCodes;
        parent::__construct($viewModel);
    }

    /**
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate|iHttpResponse
     */
    function doInvoke(iHttpRequest $request = null)
    {
        $_request_params = ParseRequestData::_($request)->parse();
        if (isset($_request_params['a']) && $_request_params['a'] == 'start')
            // Create Validation and Send SMS Code
            // Redirect to Input Given Code From User
            return $this->_handleStartAction();

        if (isset($_request_params['a']) && $_request_params['a'] == 'confirm')
            // Display Confirm Dialog
            return $this->_handleConfirm($request);


        # Build View
        $v = $this->getChallengeIdentifierObject()->getValue();
        $v = $v[1];
        return $this->viewModel
            ->setTemplate('main/oauth/members/challenge/mobile')
            ->setVariables([
                'url_next_challenge' => (string) $this->getNextUserChallengeUrl(),
                'mobile_truncate'     => \Module\OAuth2\truncateIdentifierValue($v, null, 6),
            ])
        ;
    }


    // ...

    protected function _handleStartAction()
    {
        # Create Auth Codes Based On Identifier:
        $authCodes = [
            ValidationCodeAuthObject::newByIdentifier( $this->getChallengeIdentifierObject() )
        ];

        $validationCode = \Module\OAuth2\Actions\Users\IOC::validationGenerator($this->user->getUID(), $authCodes);

        $redirect = \Module\Foundation\Actions\IOC::url();
        $redirect = $redirect->uri()->withQuery( http_build_query(['a'=>'confirm', 'vc'=> $validationCode ]) );
        return new ResponseRedirect($redirect);
    }

    protected function _handleConfirm(iHttpRequest $request)
    {
        $_request_params = ParseRequestData::_($request)->parse();
        $validationCode  = $_request_params['vc'];

        if (false === ( $r = $this->repoValidationCodes->findOneByValidationCode($validationCode)) )
            throw new \RuntimeException('Validation Code Is Expired');

        if ( $r->getUserIdentifier() !== $this->user->getUID() )
            throw new \RuntimeException('Invalid Request.');


        return $this->viewModel
            ->setTemplate('main/oauth/members/challenge/mobile_confirm')
            ->setVariables([
                'url_next_challenge' => (string) $this->getNextUserChallengeUrl(),
            ])
        ;
    }
}
