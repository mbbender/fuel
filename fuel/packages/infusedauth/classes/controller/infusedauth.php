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

class Controller_Infusedauth extends \Controller_Hybrid
{
    public static $linked_redirect = 'admin';
    public static $login_redirect = 'admin';
    public static $register_redirect = 'auth/register';
    public static $registered_redirect = 'admin';

    public $template = 'template';  //todo: Make configurable

    /**
     * Controller method preparations
     *
     * @return  void
     */
    public function before()
    {
        // already logged in?
        if (\Auth::check() and \Request::active()->action != 'logout')
        {
            //\Messages::error('You are already logged in');
            //\Response::redirect(\Input::post('redirect_to', '/'));
            \Session::set_flash('success','You are already logged in');
            \Response::redirect('admin');    //todo: make configurable
        }

        parent::before();
    }

    public function action_login()
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
                                if($user = $auth->validate_temp_user(\Input::param('username'),\Input::param('password'))){
                                    return $this->action_verification_required($user['id']);
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
        //$fieldset->add('btnSubmit','',array('type'=>'submit', 'class'=>'btn', 'colspan'=>2, 'value'=>'Login'));
        $this->template->title = 'Login';
        $this->template->content = \View::forge('login',array('fieldset'=>$fieldset));

    }

    public function action_logout()
    {
        $auth = \Auth::instance()->logout();
        \Session::delete('ninjauth');
        \Response::redirect('auth/login');
    }

    public function action_register()
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
            ->add('password', 'Password', array('type' => 'password', 'maxlength' => 255), array(array('required'), array('min_length', 8)));

        // see if we have a registration via a third-party provider
        $user_hash = \Session::get('ninjauth.user', false);
        $authentication = \Session::get('ninjauth.authentication');
        $third_party = false;
        if($user_hash AND $authentication)
        {
            $third_party = true;
            // set required values for registration
            $full_name = \Input::post('full_name') ?: \Arr::get($user_hash, 'name');
            $username = \Input::post('username',\Arr::get($user_hash, 'nickname')) ?: $user_hash['name'];
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
                $user_id = \Auth::instance()->create_user(
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
                if($e->user_id != ''){
                    $user_id = $e->user_id;

                }
                \Session::set_flash('error',$e->getMessage());
            }

            catch(InfusedAuthException $e)
            {
                if($e->user_id != ''){
                    $user_id = $e->user_id;

                }
                \Session::set_flash('error',$e->getMessage());
            }

            if($user_id !== false)
            {
                // User was created successfully

                // If this was a third party registration lets add it to the user
                if($user_hash and $authentication)
                {
                    try{
                        \Auth::instance()->add_authentication($user_id,$user_hash,$authentication);
                    }
                    catch(InfusedAuthException $e)
                    {
                        \Session::set_flash('error',$e->getMessage());
                    }

                }

                // Redirect based on account validation requirements
                if(\Config::get('infusedauth.account_validation',false) and (\Auth::instance()->validate_temp_user($username,$password) !== false))
                {
                    return $this->action_verification_required($user_id);
                }

                else
                {
                    if(\Auth::login($username,$password)){
                        \Response::redirect(\Config::get('infusedauth.urls.registered'));
                    }
                }
            }
        }

        // Load registration form
        $fieldset->populate(\Input::post());
        $this->template->title = "Register";
        $this->template->content = \View::forge("register",array('fieldset'=>$fieldset));
    }

    public function action_verification_required($user_id)
    {
        $this->template->title = null;
        $this->template->content = \View::forge(
            'registration_success',
            array('user_id' => $user_id,
                'resend_action' => \Config::get('infusedauth.urls.resend_verification_request')
            )
        );
    }

    public function action_verify($code1,$code2)
    {
        if($user = \Auth::instance()->verify($code1,$code2))
        {
            \Auth::instance()->force_login($user->id);
            \Session::set_flash('success','Thank you for verifying your account.');
            \Response::redirect(\Config::get('infusedauth.urls.registered','admin'));
        }

        $this->template->title = "Verification error";
        $this->template->content = \View::forge('verification_failed');
    }

    public function action_resend_verification_request()
    {
        if(\Input::is_ajax())
        {
            $user_id = \Input::post('user_id',false);
            if(empty($user_id)) exit(json_encode(array('success'=>0)));

            $user = Model_Temp_User::find($user_id);
            if(empty($user)) exit(json_encode(array('success'=>0)));

            try{
                \Auth::instance()->send_validation($user_id);
            }

            catch(\FuelException $e)
            {
                exit(json_encode(array('success'=>0)));
            }

            exit(json_encode(array('success'=>1,'email'=>$user->email)));
        }

        return "AJAX only";
    }

    /**
     * Alias for NinjAuth function. Doing this instead of extending NinjAuth controller so that NinjAuth
     * is not required for InfusedAuth to function.
     */
    public function action_session($provider = null)
    {
        \Config::load('ninjauth',true,true,true);
        //\NinjAuth\Controller::action_session($provider);
        $url = \NinjAuth\Strategy::forge($provider)->authenticate();
        \Response::redirect($url);
    }

    /**
     * Alias for NinjAuth function. Doing this instead of extending NinjAuth controller so that NinjAuth
     * is not required for InfusedAuth to function.
     */
    public function action_callback($provider)
    {
        \Config::load('ninjauth',true,true,true);
        try
        {
            // Whatever happens, we're sending somebody somewhere
            $status = \NinjAuth\Strategy::forge($provider)->login_or_register();
            \Log::error($status);
            // Stuff should go with each type of response
            switch ($status)
            {
                case 'linked':
                    $message = 'You have linked '.$provider.' to your account.';
                    $url = static::$linked_redirect;
                    break;

                case 'logged_in':
                    $message = 'You have logged in.';
                    $url = static::$login_redirect;
                    break;

                case 'registered':
                    $message = 'You have logged in with your new account.';
                    $url = static::$registered_redirect;
                    break;

                case 'register':
                    $message = 'Please fill in any missing details and add a password.';
                    $url = static::$register_redirect;
                    break;

                default:
                    exit('Strategy::login_or_register() has come up with a result that we dont know how to handle.');
            }

            \Response::redirect($url);
        }

        catch (AuthException $e)
        {
            exit($e->getMessage());
        }
    }
}