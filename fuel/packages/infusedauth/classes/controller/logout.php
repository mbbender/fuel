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

class Controller_Logout extends \Controller
{
    /**
     * The module index
     */
    public function action_index()
    {
        $auth = \Auth::instance()->logout();
        \Response::redirect('auth/login');
    }
}
 