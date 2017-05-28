<?php
/**
 * @var ViewModelRenderer $this
 * @var array             $user { 'fullname' => string(19) "پیام نادری" }
 */
use Module\Foundation\ServiceManager\ViewModelRenderer;

if (isset($user) && is_array($user))
    $template = 'signin_recognize-success';
elseif (isset($user) && $user === false)
    $template = 'signin_recognize-input';
else
    $template = 'signin_recognize-input';

/**
 * @param Poirot\View\ViewModelTemplate $parent
 * @param $_
 */
return function ($parent, $_) use ($template) {
    $parent->variables()->set('template', $template);
};
