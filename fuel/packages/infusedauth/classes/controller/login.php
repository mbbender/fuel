<?php
/**
 * InfusedAuth is an add on to SimpleAuth
 * @package    InfusedAuth
 * @version    1.0
 * @author     Michael Bneder
 * @license    Commercial License
 * @copyright  2012 Infused Industries, Inc.
 * @link       http://sociablegroup.com
 */

namespace InfusedAuth;

use Auth;

class Controller_Login extends Controller_Infusedauth
{
    /**
     * The module index
     *
     * @return  Response
     */
    public function action_index()
    {
        // create the form fieldset, do not add an {open}, a closing ul and a {close}, we have a custom form layout!
        $fieldset = \Fieldset::forge('login');
        $fieldset->add('username', 'Username', array('maxlength' => 50), array(array('required')))
            ->add('password', 'Password', array('type' => 'password', 'maxlength' => 255), array(array('required'), array('min_length', 8)));

        // was the login form posted?
        if (\Input::post())
        {
            // deal with the login type
            switch (\Input::post('btnSubmit', false))   // Turn btnSubmit into a configuration setting
            {
                case 'Login':
                    // run the form validation
                    if ( ! $fieldset->validation()->run())
                    {
                        // set any error messages we need to display
                        foreach ($fieldset->validation()->error() as $error)
                        {
                            \Session::set_flash('error','Please fix errors in the form.');
                        }
                    }
                    else
                    {
                        // create an Auth instance
                        $auth = \Auth::instance();

                        // check the credentials.
                        if ($auth->login(\Input::param('username'), \Input::param('password')))
                        {
                            \Response::redirect('admin');    //todo:make configurable
                        }
                        else
                        {
                            if(\Config::get('infusedauth.account_validation',false))
                            {
                                if($auth->validate_temp_user(\Input::param('username'),\Input::param('password')) !== false){
                                    \Repsonse::redirect(\Config::get('infusedauth.urls.acccount_validation_required'));
                                }
                            }
                            \Session::set_flash('error','Username and/or password is incorrect');
                        }
                    }
                    break;

                default:
                    $provider = strtolower(\Input::post('btnSubmit'));
                    \Package::load('ninjauth');
                    \Response::redirect(\Uri::create('session/'.$provider));
                    break;
            }
        }

        // set the login page content partial
        //\Theme::instance()->set_partial('content', 'users/login/index')->set('fieldset', $fieldset, false);
        $fieldset->add('btnSubmit','',array('type'=>'submit', 'class'=>'btn', 'colspan'=>2, 'value'=>'Login'));
        $this->template->title = 'Login';
        $this->template->content = \View::forge('login',array('fieldset'=>$fieldset));

    }

    /**
     * Alias for NinjAuth function. Doing this instead of extending NinjAuth controller so that NinjAuth
     * is not required for InfusedAuth to function.
     */
    public function action_session($provider = null)
    {
        \NinjAuth\Controller::action_session($provider);
    }

    /**
     * Alias for NinjAuth function. Doing this instead of extending NinjAuth controller so that NinjAuth
     * is not required for InfusedAuth to function.
     */
    public function action_callback($provider)
    {
        \NinjAuth\Controller::action_callback($provider);
    }
}
 