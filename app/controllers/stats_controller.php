<?php
class StatsController extends AppController {
	/*properties*/
	var $name = 'Stats';
	var $uses = array(
		'TransViewCompany', 'TransViewAgent', 'TransSite', 'TransType',
		'TransStats', 'TransSite', 'TransType',
		'TransViewStats', 'TransTmpStats', 'TransRunStats'
	);
	var $components = array('RequestHandler');
	var $helpers = array(
		'Form', 'Html', 'Javascript',
		'ExPaginator'
	);
	
	var $curuser = null;
	var $__limit = 100;
	var $__runid = -1;
		
	/*callbacks*/
	function beforeFilter() {
		$this->pageTitle = 'AMERICAN WEB LINK.[STATS]';
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
		
		/*set $this->__runid*/
		if ($this->curuser) {
			if ($this->Session->valid()) {
				if (!$this->Session->check('runid')) {
					$runid = array('TransRunStats' => array('id' => null, 'runtime' => date('Y-m-d H:i:s')));
					$this->TransRunStats->create();
					$tmp = $this->TransRunStats->saveAll($runid);//generate the runid
					$this->Session->write('runid', $this->TransRunStats->id);
				}
				$this->__runid = $this->Session->read('runid');
			}
		}
		
		parent::beforeFilter();
	}
	
	function ___getcond_4statsby_only() {
		$conditions = array('1' => '0');
		if ($this->curuser) {}
		else return $conditions;
		$conditions = array(
			'trxtime >=' => ($this->data['Stats']['startdate'] . ' 00:00:00'),
			'trxtime <=' => ($this->data['Stats']['enddate'] . ' 23:59:59')
		);
		if ($this->data['Stats']['siteid'] != 0) {
			$conditions += array('siteid' => $this->data['Stats']['siteid']);
		}
		if ($this->data['Stats']['typeid'] != 0) {
			$conditions += array('typeid' => $this->data['Stats']['typeid']);
		}
		if ($this->curuser['role'] == 0) {//means an administrator
			if (!empty($this->data['Stats']['companyid'])
				&& !in_array('0', $this->data['Stats']['companyid'])) {
				$conditions += array('companyid' => $this->data['Stats']['companyid']);
			}
			if ($this->data['Stats']['agentid'] != 0) {
				$conditions += array('agentid' => $this->data['Stats']['agentid']);
			}
		} else if ($this->curuser['role'] == 1) {//means a company
			$conditions += array('companyid' => $this->curuser['id']);
			if ($this->data['Stats']['agentid'] != 0) {
				$conditions += array('agentid' => $this->data['Stats']['agentid']);
			}
		} else if ($this->curuser['role'] == 2) {//means an agent
			$conditions += array('agentid' => $this->curuser['id']);
		}
		return $conditions;
	}
	
	function ___prepconstparms_4statsby_only(
		&$sites, &$types, &$coms, &$ags, &$periods
	) {
		$periods = array('0' => '-CHOOSE PAYOUT PERIOD-');
		$periods += array(date('Y-m-d') . ',' . date('Y-m-d') => 'TODAY');
		$periods += array(
			date('Y-m-d', mktime(0,0,0,date("m"), date("d") - 1, date("Y")))
			. ','
			. date('Y-m-d', mktime(0,0,0,date("m"), date("d") - 1, date("Y")))
			=> 'YESTERDAY'
		);
		
		$halfmons = array();
		for ($i = 0; $i < 12; $i++) {
			$halfmon = mktime(0, 0, 0, date('m') - 1 - $i, date('d'), date('Y'));
			$halfmons += array(
				date('Y-m-01,Y-m-15', $halfmon) 
					=> date('M 1-15, Y', $halfmon)
			);
			$lastday = date('d', mktime(0, 0, 0, date('m') - $i, 0, date('Y')));
			$halfmon = mktime(0, 0, 0, date('m') - 1 - $i, date('d'), date('Y'));
			$halfmons += array(
				date('Y-m-16,Y-m-' . $lastday, $halfmon) => date('M 16-' . $lastday . ', Y', $halfmon)
			);
		}
		if (date('Y-m-d') <= date('Y-m-15')) {
			$halfmons = array(date('Y-m-01,Y-m-' . date('d')) => date('M 1-' .date('j') . ', Y')) + $halfmons;
		} else {
			$lastday = date('d');
			$halfmons = array(date('Y-m-16,Y-m-' . $lastday) => date('M 16-' . $lastday . ', Y')) + $halfmons;
			$halfmons = array(date('Y-m-01,Y-m-15') => date('M 1-15, Y')) + $halfmons;
		}
		$periods += $halfmons;
		
		$lastday = date("Y-m-d", strtotime(date('Y-m-d') . " Sunday"));
		$periods += array(
			date("Y-m-d", strtotime($lastday . " - 6 days")) . ',' . $lastday
			=> 'THIS WEEK'
		);
		$periods += array(
			date("Y-m-d", strtotime($lastday . " - 13 days"))
			. ','
			. date("Y-m-d", strtotime($lastday . " - 7 days"))
			=> 'LAST WEEK'
		);
		$periods += array(
			date("Y-m-d", strtotime($lastday . " - 20 days"))
			. ','
			. date("Y-m-d", strtotime($lastday . " - 14 days"))
			=> 'TWO WEEKS AGO'
		);
		$periods += array(
			date('Y-m', mktime(0,0,0,date("m"), date("d"), date("Y"))) . '-01'
			. ','
			. date('Y-m-d', mktime(0,0,0,date("m") + 1, 0, date("Y")))
			=> 'THIS MONTH'
		);
		$periods += array(
			date('Y-m', mktime(0,0,0,date("m") - 1, date("d"), date("Y"))) . '-01'
			. ','
			. date('Y-m-d', mktime(0,0,0,date("m"), 0, date("Y")))
			=> 'LAST MONTH'
		);
		for ($i = 2; $i < 12; $i++) {
			$periods += array(
				date('Y-m', mktime(0,0,0,date("m") - $i, date("d"), date("Y"))) . '-01'
				. ','
				. date('Y-m-d', mktime(0,0,0,date("m") - $i + 1, 0, date("Y")))
				=> 'MONTH ' . date('Y-m', mktime(0,0,0,date("m") - $i, date("d"), date("Y")))
			);
		}
		$periods += array(
			date('Y-m-d', mktime(0,0,0, 1, 1, date("Y")))
			. ','
			. date('Y-m-d', mktime(0,0,0, 12, 31, date("Y")))
			=> 'THIS YEAR'
		);
		$periods += array(
			date('Y-m-d', mktime(0,0,0, 1, 1, date("Y") - 1))
			. ','
			. date('Y-m-d', mktime(0,0,0, 12, 31, date("Y") - 1))
			=> 'LAST YEAR'
		);
		
		$this->___prepparms_4statsby_only(
			$tmp0, $tmp1, $selsite, $tmp2, $selcoms, $tmp3
		);
		
		$sites = array();
		$types = array();
		$coms = array();
		$ags = array();
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1),
				'order' => 'sitename'
			)
		);
		$sites = array('-1' => '-CHOOSE A SITE-') + $sites;
		
		$types = $this->TransType->find('list',
			array(
				'fields' => array('id', 'typename'),
				'conditions' => array('1' => '1')
					+ ($selsite == -1 ? array('1' => '1') : array('siteid' => $selsite)),
				'order' => 'typename'
			)
		);
		$types = array('0' => 'All') + $types;

		if ($this->curuser['role'] == 0) {//means an administrator
			$coms = $this->TransViewCompany->find('list',
				array(
					'fields' => array('companyid', 'officename'),
					//'conditions' => array('status' => 1),
					'order' => 'officename'
				)
			);
		}
		$coms = array('0' => 'All') + $coms;

		if ($this->curuser['role'] == 0) {//means an administrator
			$ags = $this->TransViewAgent->find('list',
				array(
					'fields' => array('id', 'username'),
					'conditions' => array(/*'status' => 1*/)
						+ (empty($selcoms) || in_array('0', $selcoms) ? array('1' => '1') : array('companyid' => $selcoms)),
					'order' => 'username4m'
				)
			);
		} else if ($this->curuser['role'] == 1) {//means an office
			$ags = $this->TransViewAgent->find('list',
				array(
					'fields' => array('id', 'username'),
					'conditions' => array(
						'companyid' => $this->curuser['id']/*,  
						'status' => 1*/
					),
					'order' => 'username4m'
				)
			);
		} else if ($this->curuser['role'] == 2) {//means an agent
			$ags = $this->TransViewAgent->find('list',
				array(
					'fields' => array('id', 'username'),
					'conditions' => array(
						'id' => $this->curuser['id']/*,  
						'status' => 1*/
					),
					'order' => 'username4m'
				)
			);
		}
		$ags = array('0' => 'All') + $ags;
		
		$this->set(compact('sites'));
		$this->set(compact('types'));
		$this->set(compact('coms'));
		$this->set(compact('ags'));
		$this->set(compact('periods'));
	}
	
	function ___prepparms_4statsby_only(
		&$startdate, &$enddate, &$selsite, &$seltype, &$selcoms, &$selagent
	) {
		$selsite = -1;
		if (!empty($this->data)) {
			$selsite = $this->data['Stats']['siteid'];
		} else if (array_key_exists('siteid', $this->passedArgs)) {
			$selsite = $this->passedArgs['siteid'];
		} else if (array_key_exists('page', $this->passedArgs)) {
			if ($this->Session->check('conditions_stats')) {
				$conds = $this->Session->read('conditions_stats');
				$selsite = $conds['selsite'];
			}
		}
		$seltype = 0;
		if (!empty($this->data)) {
			$seltype = $this->data['Stats']['typeid'];
		} else if (array_key_exists('typeid', $this->passedArgs)) {
			$seltype = $this->passedArgs['typeid'];
		} else if (array_key_exists('page', $this->passedArgs)) {
			if ($this->Session->check('conditions_stats')) {
				$conds = $this->Session->read('conditions_stats');
				$seltype = $conds['seltype'];
			}
		}
		$selcoms = array();
		if (!empty($this->data)) {
			$__selcoms = $this->data['Stats']['companyid'];
			$selcoms = is_array($__selcoms) ? $__selcoms : array($__selcoms);
		} else if (array_key_exists('companyid', $this->passedArgs)) {
			$selcoms = explode(',', $this->passedArgs['companyid']);
		} else if (array_key_exists('page', $this->passedArgs)) {
			if ($this->Session->check('conditions_stats')) {
				$conds = $this->Session->read('conditions_stats');
				$selcoms = $conds['selcoms'];
			}
		}
		$selagent = 0;
		if (!empty($this->data)) {
			$selagent = $this->data['Stats']['agentid'];
		} else if (array_key_exists('agentid', $this->passedArgs)) {
			$selagent = $this->passedArgs['agentid'];
		} else if (array_key_exists('page', $this->passedArgs)) {
			if ($this->Session->check('conditions_stats')) {
				$conds = $this->Session->read('conditions_stats');
				$selagent = $conds['selagent'];
			}
		}
		$startdate = $enddate = date('Y-m-d');
		if (date('Y-m-d') <= date('Y-m-15')) {
			$startdate = date('Y-m-01');
			$enddate = date('Y-m-' . date('d'));
		} else {
			$lastday = date('d');
			$startdate = date('Y-m-16');
			$enddate = date('Y-m-' . $lastday);
		}
		if (!empty($this->data)) {
			$startdate = $this->data['Stats']['startdate'];
		} else if (array_key_exists('startdate', $this->passedArgs)) {
			$startdate = $this->passedArgs['startdate'];
		} else if (array_key_exists('page', $this->passedArgs)) {
			if ($this->Session->check('conditions_stats')) {
				$conds = $this->Session->read('conditions_stats');
				$startdate = $conds['startdate'];
			}
		}
		if (!empty($this->data)) {
			$enddate = $this->data['Stats']['enddate'];
		} else if (array_key_exists('enddate', $this->passedArgs)) {
			$enddate = $this->passedArgs['enddate'];
		} else if (array_key_exists('page', $this->passedArgs)) {
			if ($this->Session->check('conditions_stats')) {
				$conds = $this->Session->read('conditions_stats');
				$enddate = $conds['enddate'];
			}
		}
	}
	
	function __statsby($group) {
		$this->layout = "defaultlayout";
		
		/*if $this->__runid is N/A, then redirect to the home page*/
		if ($this->__runid == -1) {
			$this->redirect(array('controller' => 'trans', 'action' => 'index'));
		}
		
		/*prepare for the parameters*/
		$this->___prepconstparms_4statsby_only($sites, $types, $coms, $ags, $periods);
		$this->___prepparms_4statsby_only($startdate, $enddate, $selsite, $seltype, $selcoms, $selagent);

		if (array_key_exists('clear', $this->passedArgs)) {
			$this->set(compact("startdate"));
			$this->set(compact("enddate"));
			$this->set(compact('selsite'));
			$this->set(compact('seltype'));
			$this->set(compact('selcoms'));
			$this->set(compact('selagent'));
			return;
		}
				
		/*prepare addons for the conditions & group by*/
		$gbaddons = '';
		$order = 'trxtime desc';
		switch ($group) {
			case 1:
				$gbaddons = 'convert(trxtime, date)';
				$order = 'trxtime desc, username4m asc';
				break;
			case 2:
				$gbaddons = 'companyid';
				$order = 'officename asc, trxtime desc';
				break;
			case 3:
				$gbaddons = 'agentid';
				$order = 'username4m asc, trxtime desc';
				break;
			case 4:
				$gbaddons = 'convert(trxtime, date), companyid, agentid';
				$order = 'trxtime desc, username4m asc';
				break;
		}	
			
		if (empty($this->data)) {
			/* see if there is already a result which was grouped by some group inserted. 
			 * but do nothing when it's about paginating. */
			
			/*prepare the data*/
			if (!array_key_exists('page', $this->passedArgs)) {
				//if it's not paginating, then it should be drilling down
				if ($this->Session->check('conditions_stats')) {
					$conditions['trxtime >='] = $startdate . ' 00:00:00';
					$conditions['trxtime <='] = $enddate . ' 23:59:59';
					if ($selsite != -1) $conditions['siteid'] = $selsite;
					if ($seltype != 0) $conditions['typeid'] = $seltype;
					if (!empty($selcoms) && !in_array('0', $selcoms)) $conditions['companyid'] = $selcoms;
					if ($selagent != 0) $conditions['agentid'] = $selagent;
					if ($this->curuser['role'] == 1) {//if it's an office
						$conditions['companyid'] = $this->curuser['id'];
					}
					if ($this->curuser['role'] == 2) {//if it's an agent
						$conditions['agentid'] = $this->curuser['id'];
					}
					$rs = $this->TransViewStats->find('all',
						array(
							'fields' => array(
								'convert(trxtime, date) as day',
								'companyid',
								'officename',
								'agentid',
								'username',
								'username4m',
								'siteid',
								'sitename',
								'typeid',
								'typename',
								'sum(raws) as raws',
								'sum(uniques) as uniques',
								'sum(chargebacks) as chargebacks',
								'sum(signups) as signups',
								'sum(frauds) as frauds',
								'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 0, 1), sales_number, 0)) as sales_type1',
								'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 1, 1), sales_number, 0)) as sales_type2',
								'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 2, 1), sales_number, 0)) as sales_type3',
								'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 3, 1), sales_number, 0)) as sales_type4',
								//'sum(sales_number) as sales_number',
								'sum(payouts) as payouts',
								'sum(earnings) as earnings'
							),
							'conditions' => $conditions,
							'group' => 'siteid' . ', ' . $gbaddons
						)
					);
					$data = array('TransTmpStats' => array());
					foreach ($rs as $r) {
						array_push(
							$data['TransTmpStats'],
							array(
								'runid' => $this->__runid,
								'bygroup' => $group,
								'trxtime' => $r[0]['day'],
								'companyid' => $r['TransViewStats']['companyid'],
								'officename' => $r['TransViewStats']['officename'],
								'agentid' => $r['TransViewStats']['agentid'],
								'username' => $r['TransViewStats']['username'],
								'username4m' => $r['TransViewStats']['username4m'],
								'siteid' => $r['TransViewStats']['siteid'],
								'sitename' => $r['TransViewStats']['sitename'],
								'typeid' => $r['TransViewStats']['typeid'],
								'typename' => $r['TransViewStats']['typename'],
								'raws' => $r[0]['raws'],
								'uniques' => $r[0]['uniques'],
								'chargebacks' => $r[0]['chargebacks'],
								'signups' => $r[0]['signups'],
								'frauds' => $r[0]['frauds'],
								//'frauds' => ($r[0]['frauds'] + $r[0]['chargebacks']),
								'sales_type1' => $r[0]['sales_type1'],
								'sales_type2' => $r[0]['sales_type2'],
								'sales_type3' => $r[0]['sales_type3'],
								'sales_type4' => $r[0]['sales_type4'],
								//'sales_number' => $r[0]['sales_number'],
								'payouts' => $r[0]['payouts'],
								'earnings' => $r[0]['earnings']
							)
						);
					}
					$this->TransTmpStats->deleteAll(array('runid' => $this->__runid, 'bygroup' => $group));
					$this->TransTmpStats->saveAll($data['TransTmpStats']);
					
					$this->Session->write('conditions_stats',
						array(
							'startdate' => $startdate,
							'enddate' => $enddate,
							'selsite' => $selsite,
							'seltype' => $seltype,
							'selcoms' => $selcoms,
							'selagent' => $selagent
						)
					);
					
					if ($this->Session->check('crumbs_stats')) {
						$crumbs = $this->Session->read('crumbs_stats'); 
						$cururl = array(
							'controller' => 'stats',
							'action' => $group == 1 ? 'statsdate' : ($group == 2 ? 'statscompany' : ($group == 3 ? 'statsagent' : '')),
							'startdate' => $startdate, 'enddate' => $enddate,
							'siteid' => $selsite, 'typeid' => $seltype,
							'companyid' => empty($selcoms) || in_array('0', $selcoms) ? implode(',', array_keys($coms)) : implode(',', $selcoms),
							'agentid' => $selagent
						);
						$isin = false;
						$i = 0;
						foreach ($crumbs as $k => $v) {
							$diff = array_diff_assoc($v, $cururl);
							if (empty($diff)) {
								$isin = true;
								array_splice($crumbs, $i + 1);
								break;
							}
							$i++;
						}
						if (!$isin) {
							$crumbs[$group == 1 ? 'Day' : ($group == 2 ? 'Office' : ($group == 3 ? 'Agent' : ''))] = $cururl;
						}
						$this->Session->write('crumbs_stats', $crumbs);
					} else {
						$this->Session->setFlash('Illegal drilldown(1).');
						$this->redirect(array('controller' => 'trans', 'action' => 'index'));
					}
				} else {
					$this->Session->setFlash('Illegal drilldown(2).');
					$this->redirect(array('controller' => 'trans', 'action' => 'index'));
				}
			}
		} else {
			$conditions = $this->___getcond_4statsby_only();
			$rs = $this->TransViewStats->find('all',
				array(
					'fields' => array(
						'convert(trxtime, date) as day',
						'companyid',
						'officename',
						'agentid',
						'username',
						'username4m',
						'siteid',
						'sitename',
						'typeid',
						'typename',
						'sum(raws) as raws',
						'sum(uniques) as uniques',
						'sum(chargebacks) as chargebacks',
						'sum(signups) as signups',
						'sum(frauds) as frauds',
						'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 0, 1), sales_number, 0)) as sales_type1',
						'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 1, 1), sales_number, 0)) as sales_type2',
						'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 2, 1), sales_number, 0)) as sales_type3',
						'sum(if(typeid = (SELECT id FROM trans_types WHERE siteid = ' . $selsite . ' order by id limit 3, 1), sales_number, 0)) as sales_type4',
						//'sum(sales_number) as sales_number',
						'sum(payouts) as payouts',
						'sum(earnings) as earnings'
					),
					'conditions' => $conditions,
					'group' => 'siteid' . ', ' . $gbaddons
				)
			);
			$data = array('TransTmpStats' => array());
			foreach ($rs as $r) {
				array_push(
					$data['TransTmpStats'],
					array(
						'runid' => $this->__runid,
						'bygroup' => $group,
						'trxtime' => $r[0]['day'],
						'companyid' => $r['TransViewStats']['companyid'],
						'officename' => $r['TransViewStats']['officename'],
						'agentid' => $r['TransViewStats']['agentid'],
						'username' => $r['TransViewStats']['username'],
						'username4m' => $r['TransViewStats']['username4m'],
						'siteid' => $r['TransViewStats']['siteid'],
						'sitename' => $r['TransViewStats']['sitename'],
						'typeid' => $r['TransViewStats']['typeid'],
						'typename' => $r['TransViewStats']['typename'],
						'raws' => $r[0]['raws'],
						'uniques' => $r[0]['uniques'],
						'chargebacks' => $r[0]['chargebacks'],
						'signups' => $r[0]['signups'],
						'frauds' => $r[0]['frauds'],
						//'frauds' => ($r[0]['frauds'] + $r[0]['chargebacks']),
						'sales_type1' => $r[0]['sales_type1'],
						'sales_type2' => $r[0]['sales_type2'],
						'sales_type3' => $r[0]['sales_type3'],
						'sales_type4' => $r[0]['sales_type4'],
						//'sales_number' => $r[0]['sales_number'],
						'payouts' => $r[0]['payouts'],
						'earnings' => $r[0]['earnings']
					)
				);
			}
			$this->TransTmpStats->deleteAll(array('runid' => $this->__runid, 'bygroup' => $group));
			$this->TransTmpStats->saveAll($data['TransTmpStats']);
			$startdate = $this->data['Stats']['startdate'];
			$enddate = $this->data['Stats']['enddate'];
			$selsite = $this->data['Stats']['siteid'];
			$seltype = $this->data['Stats']['typeid'];
			$__selcoms = $this->data['Stats']['companyid'];
			$selcoms = is_array($__selcoms) ? $__selcoms : array($__selcoms);
			$selagent = $this->data['Stats']['agentid'];
			$this->Session->write('conditions_stats',
				array(
					'startdate' => $startdate,
					'enddate' => $enddate,
					'selsite' => $selsite,
					'seltype' => $seltype,
					'selcoms' => $selcoms,
					'selagent' => $selagent
				)
			);
			$this->Session->write('crumbs_stats',
				array(
					$group == 1 ? 'Day' : ($group == 2 ? 'Office' : ($group == 3 ? 'Agent' : '')) => array(
						'controller' => 'stats',
						'action' => $group == 1 ? 'statsdate' : ($group == 2 ? 'statscompany' : ($group == 3 ? 'statsagent' : '')),
						'startdate' => $startdate, 'enddate' => $enddate,
						'siteid' => $selsite, 'typeid' => $seltype,
						'companyid' => empty($selcoms) || in_array('0', $selcoms) ? implode(',', array_keys($coms)) : implode(',', $selcoms),
						'agentid' => $selagent
					)
				)
			);
		}
		
		/*set the vriables for the view*/
		$this->set(compact("startdate"));
		$this->set(compact("enddate"));
		$this->set(compact('selsite'));
		$this->set(compact('seltype'));
		$this->set(compact('selcoms'));
		$this->set(compact('selagent'));
		
		/*prepare totals*/
		$totals = array(
			'raws' => 0, 'uniques' => 0, 'chargebacks' => 0, 'signups' => 0, 'frauds' => 0,
			'sales_type1' => 0, 'sales_type2' => 0, 'sales_type3' => 0, 'sales_type4' => 0,
			'payouts' => 0, 'earnings' => 0
		);
		$rs = $this->TransTmpStats->find('all',
			array(
				'fields' => array(
					'runid',
					'sum(raws) as raws',
					'sum(uniques) as uniques',
					'sum(chargebacks) as chargebacks',
					'sum(signups) as signups',
					'sum(frauds) as frauds',
					'sum(sales_type1) as sales_type1',
					'sum(sales_type2) as sales_type2',
					'sum(sales_type3) as sales_type3',
					'sum(sales_type4) as sales_type4',
					'sum(payouts) as payouts',
					'sum(earnings) as earnings'
				),
				'conditions' => array('runid' => $this->__runid, 'bygroup' => $group),
				'group' => 'runid'
			)
		);
		if (!empty($rs)) {
			$totals['raws'] = $rs[0][0]['raws'];
			$totals['uniques'] = $rs[0][0]['uniques'];
			$totals['chargebacks'] = $rs[0][0]['chargebacks'];
			$totals['signups'] = $rs[0][0]['signups'];
			$totals['frauds'] = $rs[0][0]['frauds'];
			$totals['sales_type1'] = $rs[0][0]['sales_type1'];
			$totals['sales_type2'] = $rs[0][0]['sales_type2'];
			$totals['sales_type3'] = $rs[0][0]['sales_type3'];
			$totals['sales_type4'] = $rs[0][0]['sales_type4'];
			$totals['payouts'] = $rs[0][0]['payouts'];
			$totals['earnings'] = $rs[0][0]['earnings'];
		}
		$this->set('totals', $totals);
		/*pagination things*/
		$this->paginate = array(
			'TransTmpStats' => array(
				'conditions' => array('runid' => $this->__runid, 'bygroup' => $group),
				'order' => $order,
				'limit' => $this->__limit
			)
		);		
		$this->set('rs',
			$this->paginate('TransTmpStats')
		);
	}
	
	function statsdate() {
		$this->__statsby(1);
		
		$this->set('bywhat', 0);
		$this->render('statsem');
	}
	
	function statscompany() {
		$this->__statsby(2);
		
		$this->set('bywhat', 1);
		$this->render('statsem');
	}
	
	function statsagent() {
		$this->__statsby(3);
		
		$this->set('bywhat', 2);
		$this->render('statsem');
	}
	
	function statsagdetail() {
		$this->__statsby(4);
		
		$this->set('bywhat', 3);
		$this->render('statsem');
	}
	
	function switchtype() {
		$this->layout = "emptylayout";
		Configure::write('debug', '0');
		
		$options = array('0' => 'All');
		if($this->data['Stats']['siteid'] != -1) {
		    $options = $options + $this->TransType->find('list',
		    	array(
		    		'fields' => array('id', 'typename'),
		    		'conditions' => array('siteid' => $this->data['Stats']['siteid'])
		    	)
		    );
		} else {
			$options = array('-1' => '--------');
		}
		$this->set(compact('options'));
		$this->render('switchem');
	}
	
	function switchagent() {
		$this->layout = "emptylayout";
		Configure::write('debug', 0);
		
		$options = array('0' => 'All');
		if(!empty($this->data['Stats']['companyid'])
			&& !in_array('0', $this->data['Stats']['companyid'])) {
		    $options = $options + $this->TransViewAgent->find('list',
		    	array(
		    		'fields' => array('id', 'username'),
		    		'conditions' => array('companyid' => $this->data['Stats']['companyid']),
		    		'order' => 'username4m'
		    	)
		    );
		} else {
			$options = $options + $this->TransViewAgent->find('list',
		    	array(
		    		'fields' => array('id', 'username'),
		    		'order' => 'username4m'
		    	)
		    );
		}
		$this->set(compact('options'));
		$this->render('switchem');
	}
	
	function updfrauds() {
		$this->layout = "emptylayout";
		Configure::write('debug', 0);
		
		$frauds = intval($_REQUEST['value']);
		if ($this->__runid != -1 && $frauds >= 0
			&& array_key_exists('date', $this->passedArgs)
			&& array_key_exists('agentid', $this->passedArgs)
			&& array_key_exists('siteid', $this->passedArgs)
			&& array_key_exists('typeid', $this->passedArgs)
		) {
			if ($this->TransStats->updateAll(
					array('frauds' => $frauds),
					array(
						'convert(trxtime, date)' => $this->passedArgs['date'],
						'agentid' => $this->passedArgs['agentid'],
						'siteid' => $this->passedArgs['siteid'],
						'typeid' => $this->passedArgs['typeid']
					)
				)
				&&
				$this->TransTmpStats->updateAll(
					array('frauds' => $frauds),
					array(
						'runid' => $this->__runid,
						'convert(trxtime, date)' => $this->passedArgs['date'],
						'agentid' => $this->passedArgs['agentid'],
						'siteid' => $this->passedArgs['siteid'],
						'typeid' => $this->passedArgs['typeid']
					)
				)
			) {
			} else {
				$frauds = -1;
			}
			
		}
		$this->set(compact('frauds'));
	}
}
?>