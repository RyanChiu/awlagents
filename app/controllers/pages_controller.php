<?php
/*
 * this controller is based on cake/libs/controller/pages_controller.php
 */
class PagesController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
	var $name = 'Pages';
/**
 * Default helper
 *
 * @var array
 * @access public
 */
	var $helpers = array('Html');
/**
 * This controller does not use a model
 *
 * @var array
 * @access public
 */
	var $uses = array();
	
	/*callbacks*/
	function beforeFilter() {
		$this->layout = 'emptylayout';
		if (!$this->Session->check("Auth")) {
			$this->redirect(array('controller' => 'trans', 'action' => 'index'));
		} else {
			$u = $this->Session->read("Auth");
			if (!array_key_exists("TransAccount", $u)) {
				$this->redirect(array('controller' => 'trans', 'action' => 'index'));
			}
		}
		
		parent::beforeFilter();
	}
	
/**
 * Displays a view
 *
 * @param mixed What page to display
 * @access public
 */
	function display() {
		$path = func_get_args();

		$count = count($path);
		if (!$count) {
			$this->redirect('/');
		}
		$page = $subpage = $title = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title'));
		$this->render(join('/', $path));
	}
}
?>