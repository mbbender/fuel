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

class Controller_Register extends Controller_Infusedauth
{
    public static $registered_redirect = 'admin';

    /**
     * The module index
     *
     * @return  Response
     */
    public function action_index()
    {
        // If a user clicked on a Login with Third Party button, redirect them appropriately.
        if(\Input::post('btnSubmit','Register') != 'Register' ){
            $provider = strtolower(\Input::post('btnSubmit'));
            \Package::load('ninjauth');
            \Response::redirect(\Uri::create('session/'.$provider));
        }

        // create the form fieldset, do not add an {open}, a closing ul and a {close}, we have a custom form layout!
        $fieldset = \Fieldset::forge('register');
        $fieldset->add('username', 'Username', array('maxlength' => 50), array(array('required')))
            ->add('full_name', 'Full Name', array('maxlength' => 150), array(array('required')))
            ->add('email', 'Email', array('maxlength' => 255), array(array('required'), array('valid_email')))
            ->add('password', 'Password', array('type' => 'password', 'maxlength' => 255), array(array('required'), array('min_length', 8)))
            ->add('btnSubmit', '', array('value' => 'Register', 'type' => 'submit', 'tag' => 'button'));

        // see if we have a registration via a third-party provider
        $user_hash = \Session::get('ninjauth.user', false);
        $authentication = \Session::get('ninjauth.authentication');
        $third_party = false;
        if($user_hash AND $authentication)
        {
            $third_party = true;
            // set required values for registration
            $full_name = \Input::post('full_name') ?: \Arr::get($user_hash, 'name');
            $username = \Input::post('username') ?: \Arr::get($user_hash, 'nickname');
            $email = \Input::post('email') ?: \Arr::get($user_hash, 'email');
            $password = \Input::post('password') ?: \Arr::get($authentication, 'uid');
        }
        else
        {
            // set required values for registration
            $full_name = \Input::post('full_name');
            $username = \Input::post('username');
            $email = \Input::post('email');
            $password = \Input::post('password');
        }

        $user_id = false;


        // Do we have enough info to register a new user?
        if($fieldset->validation()->run(array('full_name'=>$full_name,'username'=>$username,'email'=>$email,'password'=>$password)))
        {
            //Create the new user
            try
            {
                $user_id = \Auth::create_user(
                    $username,
                    $password,
                    $email,
                    \Config::get('infusedauth.default_group'),
                    array(
                        'full_name' => $full_name
                    ),
                    $third_party
                );
            }
            catch(SimpleUserValidationException $e)
            {
                if($e->user_id != '') $user_id = $e->user_id;
                else Session::set_flash('error',$e->getMessage());
            }

            catch(InfusedAuthException $e)
            {
                if($e->user_id != '') $user_id = $e->user_id;
                else Session::set_flash('error',$e->getMessage());
            }

            if($user_id !== false)
            {
                // User was created successfully

                // If this was a third party registration lets add it to the user
                if($user_hash and $authentication)
                {
                    Model_Authentication::forge(array(
                        'user_id' => $user_id,
                        'provider' => $authentication['provider'],
                        'uid' => $authentication['uid'],
                        'access_token' => $authentication['access_token'],
                        'secret' => $authentication['secret'],
                        'refresh_token' => $authentication['refresh_token'],
                        'expires' => $authentication['expires'],
                        'created_at' => time(),
                    ))->save();
                }

                // Redirect based on account validation requirements
                if(\Config::get('infusedauth.account_validation',false))
                {
                    \Response::redirect(\Config::get('infusedauth.urls.acccount_validation_required').'/'.$user_id);
                }

                else
                {
                    \Response::redirect(\Config::get('infusedauth.urls.registered'));
                }
            }
        }

        // Load registration form
        $fieldset->populate(\Input::post());
        $this->template->title = "Register";
        $this->template->content = \View::forge("register",array('fieldset'=>$fieldset));
    }

    public function action_verify($id)
    {
        $this->template->title = null;
        $this->template->content = \View::forge('registration_success', array('user_id' => $id));
    }

}