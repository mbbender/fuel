<?php


/**
 * Created by JetBrains PhpStorm.
 * User: michael
 * Date: 6/7/12
 * Time: 1:49 AM
 * To change this template use File | Settings | File Templates.
 */
class Controller_Auth extends \NinjAuth\Controller
{
    public static $login_redirect = '/admin';
    public static $linked_redirect = '/admin';

    public function action_register()
    {
        // Already logged in
        Auth::check() and Response::redirect('admin');

        $user_hash = Session::get('ninjauth.user');
        $authentication = Session::get('ninjauth.authentication');

        // Working with what?
        $strategy = NinjAuth\Strategy::forge($authentication['provider']);

        $full_name = Input::post('full_name') ?: Arr::get($user_hash, 'name');
        $username = Input::post('username') ?: Arr::get($user_hash, 'nickname');
        $email = Input::post('email') ?: Arr::get($user_hash, 'email');
        $password = Input::post('password') ?: Arr::get($user_hash, 'uid');
        $password_verify = Input::post('password2') ?: Arr::get($user_hash, 'uid');

        $val = Validation::forge('create');

        $val->add('username', 'Username')
            ->add_rule('required');
        $val->add('email', 'Email')
            ->add_rule('required');
        $val->add('password', 'Password')
            ->add_rule('required');
        $val->add('password2', 'Password Verification')
            ->add_rule('required')
            ->add_rule('match_field','password');

        if ($val->run(array('username'=>$username,'email'=>$email,'password'=>$password,'password2'=>$password_verify)))
        {

            //Check if user already exists and if so attempte to log them in.
            Auth::login($username,$password);
            Auth::check() and Response::redirect('admin');

            try{
                $user_id = $strategy->adapter->create_user(array(
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $full_name,
                    'password' => $password,
                ));
            }

            catch(\SimpleUserUpdateException $e)
            {
                Session::set_flash('error',$e->getMessage());
                $this->template->title = null;
                $this->template->content = View::forge('auth/registration_success', array('user' => (object) compact('username', 'full_name', 'email', 'password')));
                return;
            }

            if (isset($user_id) && $user_id !== FALSE)
            {
                NinjAuth\Model_Authentication::forge(array(
                    'user_id' => $user_id,
                    'provider' => $authentication['provider'],
                    'uid' => $authentication['uid'],
                    'access_token' => $authentication['access_token'],
                    'secret' => $authentication['secret'],
                    'refresh_token' => $authentication['refresh_token'],
                    'expires' => $authentication['expires'],
                    'created_at' => time(),
                ))->save();

                //Response::redirect(static::$registered_redirect);
                $this->template->title = null;
                $this->template->content = View::forge('auth/registration_success', array('user' => (object) compact('username', 'full_name', 'email', 'password')));
                return;
            }
        }

        $this->template->title = 'Register';
        $this->template->content = View::forge('auth/register', array('val' => $val, 'user' => (object) compact('username', 'full_name', 'email', 'password')));
        return;
    }

    public function action_register1()
    {
        // Already logged in
        Auth::check() and Response::redirect('admin');

        $val = Validation::forge();

        if (Input::method() == 'POST')
        {
            $val->add('username', 'Username')
                ->add_rule('required');
            $val->add('email', 'Email')
                ->add_rule('required');
            $val->add('password', 'Password')
                ->add_rule('required');
            $val->add('password2', 'Password Verification')
                ->add_rule('required')
                ->add_rule('match_field','password');

            if ($val->run())
            {
                $auth = Auth::instance();

                // check the credentials. This assumes that you have the previous table created
                if (Auth::check() or $auth->create_user(Input::post('username'), Input::post('password'), Input::post('email')))
                {
                    // credentials ok, go right in
                    $current_user = Model_User::find_by_username(Auth::get_screen_name());
                    //Session::set_flash('success', 'Welcome, '.$current_user->username);
                    //Response::redirect('admin');
                    $this->template->title = null;
                    $this->template->content = View::forge('auth/registration_success');
                }
                else
                {
                    $this->template->set_global('login_error', 'Fail');
                }
            }
        }

        $this->template->title = 'Register';
        $this->template->content = View::forge('auth/register', array('val' => $val), false);
    }


}
