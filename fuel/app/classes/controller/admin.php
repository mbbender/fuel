<?php

class Controller_Admin extends Controller_Base {

	public $template = 'admin/template';

	public function before()
	{
		parent::before();

		if ( ! Auth::check() and Request::active()->action != 'login')
		{
			Response::redirect('auth/login');
		}
	}

	/**
	 * The index action.
	 *
	 * @access  public
	 * @return  void
	 */
	public function action_index()
	{
		$this->template->title = 'Dashboard';
		$this->template->content = View::forge('admin/dashboard');
	}

}

/* End of file admin.php */
