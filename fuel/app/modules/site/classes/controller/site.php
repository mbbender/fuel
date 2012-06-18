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

namespace site;

use view;

class Controller_Site extends \Controller_Hybrid
{
    public $template = "template";

    public function action_index()
    {
        $this->template->content = View::forge('index');
    }
}
