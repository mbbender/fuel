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
    public $template = 'template';  //todo: Make configurable

    /**
     * Controller method preparations
     *
     * @return  void
     */
    public function before()
    {
        // already logged in?
        if (\Auth::check())
        {
            //\Messages::error('You are already logged in');
            //\Response::redirect(\Input::post('redirect_to', '/'));
            \Session::set_flash('success','You are already logged in');
            \Response::redirect('admin');    //todo: make configurable
        }

        parent::before();
    }
}