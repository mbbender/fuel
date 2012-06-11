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



class InfusedAuthException extends \FuelException
{
    public function __construct($message='',$code='',$user_id='')
    {
        parent::__construct($message,$code);
        $this->user_id = $user_id;
    }
}
class SimpleUserValidationException extends InfusedAuthException {}


class Auth_Login_InfusedAuth extends \Auth\Auth_Login_SimpleAuth
{
    public static function _init()
    {
        parent::_init();
        \Config::load('infusedauth', true, true, true);
    }

    /**
     *
     * This function will create a new user and send validation notification to the user if account validation is
     * enabled. Account validation is always disabled for third party sources (registrations from NinjAuth) regardless
     * of config file settings.
     *
     * @param $username
     * @param $password
     * @param $email
     * @param int $group
     * @param array $profile_fields
     * @param bool $thirdparty_source
     * @return bool
     * @throws \SimpleUserValidationException
     * @throws InfusedAuthException
     */
    public function create_user($username, $password, $email, $group = 1, Array $profile_fields = array(), $thirdparty_source = false)
    {
        $password = trim($password);
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (empty($username) or empty($password) or empty($email))
        {
            throw new InfusedAuthException('Username, password and email address can\'t be empty.', 1);
        }

        $same_users = \DB::select_array(\Config::get('simpleauth.table_columns', array('*')))
            ->where('username', '=', $username)
            ->or_where('email', '=', $email)
            ->from(\Config::get('simpleauth.table_name'))
            ->execute(\Config::get('simpleauth.db_connection'));

        if ($same_users->count() > 0)
        {
            if (in_array(strtolower($email), array_map('strtolower', $same_users->current())))
            {
                throw new InfusedAuthException('Email address already exists', 2, $same_users[0]['id']);
            }
            else
            {
                throw new InfusedAuthException('Username already exists', 3, $same_users[0]['id']);
            }
        }

        $user = array(
            'username'        => (string) $username,
            'password'        => $this->hash_password((string) $password),
            'email'           => $email,
            'group'           => (int) $group,
            'profile_fields'  => serialize($profile_fields),
            'created_at'      => \Date::forge()->get_timestamp()
        );


        if(\Config::get('infusedauth.account_validation',false) and !$thirdparty_source)
        {
            if(\Config::get('infusedauth.temp_table_name',false) === FALSE) throw new \SimpleUserValidationException('Must set temp_table_name configuration in infusedauth config file.', 1);

            $same_users = \DB::select_array(\Config::get('infusedauth.temp_table_columns', array('*')))
                ->where('username', '=', $username)
                ->or_where('email', '=', $email)
                ->from(\Config::get('infusedauth.temp_table_name'))
                ->execute(\Config::get('infusedauth.db_connection'));

            if ($same_users->count() > 0)
            {
                if (in_array(strtolower($email), array_map('strtolower', $same_users->current())))
                {
                    throw new \SimpleUserValidationException('Email address already exists', 2, $same_users[0]['id']);
                }
                else
                {
                    throw new \SimpleUserValidationException('Username already exists', 3, $same_users[0]['id']);
                }
            }


            $user['validation_code1'] = \Str::random('alnum', 10);
            $user['validation_code2'] = \Str::random('alnum', 10);

            $result = \DB::insert(\Config::get('infusedauth.temp_table_name'))
                ->set($user)
                ->execute(\Config::get('infusedauth.db_connection'));
            if($result[1] > 0)
            {
                try{
                    $this->send_validation($result[0]);
                }

                catch(\SimpleUserValidationException $e)
                {
                    Log::error('Unable to send email to user for account validation. '.json_encode($user));
                }
            }
        }

        else
        {
            $result = \DB::insert(\Config::get('simpleauth.table_name'))
                ->set($user)
                ->execute(\Config::get('simpleauth.db_connection'));
        }

        return ($result[1] > 0) ? $result[0] : false;
    }

    /**
     * Send validation code to user
     */
    public function send_validation($temp_user_id)
    {
        $user_temp = \Model_Temp_User::find($temp_user_id);
        if(empty($user_temp)) throw new \SimpleUserValidationException('Can not find user with id '.$temp_user_id,0);

        $send_method = \Config::get('infusedauth.notification.method','email');

        switch($send_method)
        {
            case 'email':

                $validation_link = \Uri::create(\Config::get('account_validation_route','auth/validate').'/'.$user_temp['validation_code1'].'/'.$user_temp['validation_code2']);

                \Package::load('email');
                $email = \Email::forge();
                $email->from(\Config::get('email.defaults.from.email',false),\Config::get('email.deafaults.from.name',false));
                $email->to($user_temp['email']);
                $email->subject(\Config::get('infusedauth.notification.subject','ACTION REQUIRED! Validate your account.'));
                $email->html_body(\View::forge('account_confirmation_email',array('user'=>$user_temp,'validation_link'=>$validation_link)));
                try{
                    $email->send();
                }

                catch(\EmailValidationFailedException $e){
                    throw new \SimpleUserValidationException('Unable to send email notification. '.$e->getMessage());
                }
                catch(\EmailSendingFailedException $e){
                    throw new \SimpleUserValidationException('Unable to send email notification. '.$e->getMessage());
                }
                break;

            default:
                throw new \SimpleUserValidationException('Unsupported notification method specified in configuration file.');
        }

        return TRUE;
    }

    public function validate($code1,$code2)
    {
        //todo: Add a user check to make sure user doesn't already exists
        //todo: A new user ID is created when we validate the user. This means that we need to migrate any added authentications as well

        //$temp_user = self::find('first',array('where'=>array(array('validation_code1'=>$code1),array('validation_code2'=>$code2))));
        $temp_user = \DB::select()->from('users_temp')->where_open()
            ->where('validation_code1',$code1)
            ->and_where('validation_code2',$code2)->where_close()
            ->as_assoc()->execute()->as_array();
        if(!empty($temp_user[0]))
        {
            $temp_user_id = $temp_user[0]['id'];
            unset($temp_user[0]['id']);
            unset($temp_user[0]['validation_code1']);
            unset($temp_user[0]['validation_code2']);

            $temp_user[0]['last_login'] = '';
            $temp_user[0]['login_hash'] = '';


            $user = new \Model_User($temp_user[0]);
            if($user->save())
            {
                \Model_Temp_User::find($temp_user_id)->delete();
                return $user;
            }
        }

        return false;
    }

    /**
     * Check the user exists before logging in
     *
     * @return  bool
     */
    public function validate_temp_user($username_or_email = '', $password = '')
    {
        $username_or_email = trim($username_or_email) ?: trim(\Input::post(\Config::get('simpleauth.username_post_key', 'username')));
        $password = trim($password) ?: trim(\Input::post(\Config::get('simpleauth.password_post_key', 'password')));

        if (empty($username_or_email) or empty($password))
        {
            return false;
        }

        $password = $this->hash_password($password);
        $this->user = \DB::select_array(\Config::get('infusedauth.table_columns', array('*')))
            ->where_open()
            ->where('username', '=', $username_or_email)
            ->or_where('email', '=', $username_or_email)
            ->where_close()
            ->where('password', '=', $password)
            ->from(\Config::get('infusedauth.temp_table_name'))
            ->execute(\Config::get('infusedauth.db_connection'))->current();

        return $this->user ?: false;
    }

}
