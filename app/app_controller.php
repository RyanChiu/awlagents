<?php
class AppController extends Controller {
	var $uses = array('TransAdmin', 'TransAgent', 'TransSite', 'SiteExcluding');
	var $curuser = null;
	
	/*
	 * callbacks
	 */
	function beforeFilter() {
		if ($this->Session->check("Auth")) {
			$u = $this->Session->read("Auth");
			$u = array_values($u);
			if (count($u) == 0) {
				$this->curuser = null;
			} else {
				$this->curuser = $u[0];
			}
		} else {
			$this->curuser = null;
		}
		
		$excludedsites = array();
		if ($this->curuser != null && $this->curuser['role'] == 2) {
			$aginfo = $this->TransAgent->find('first',
				array('conditions' => array('id' => $this->curuser['id']))
			);
			$excludedsites = $this->SiteExcluding->find('list',
				array(
					'fields' => array('id', 'siteid'),
					'conditions' => array(
						'OR' => array(
							'companyid' => array(-1, $aginfo['TransAgent']['companyid']),
							'agentid' => $this->curuser['id']
						)
					)
				)
			);
			$excludedsites = array_unique($excludedsites);
			$excludedsites = $this->TransSite->find('list',
				array(
					'fields' => array('id', 'sitename'),
					'conditions' => array(
						'id' => $excludedsites
					)
				)
			);
		}
		$this->set(compact("excludedsites"));
		
		$popupmsg = $this->TransAdmin->field('notes', array('id' => 1));//HARD CODE: we put popup msg here
		$this->set(compact('popupmsg'));
		
		parent::beforeFilter();
	}
}
?>