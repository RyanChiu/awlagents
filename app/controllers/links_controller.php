<?php
App::import('vendor', 'ExtraKits', array('file' => 'extrakits.inc.php'));
App::import('vendor', 'magpierss', array('file' => 'magpierss/rss_fetch.inc'));
?>
<?php
class LinksController extends AppController {
	/*properties*/
	var $name = 'Links';
	var $uses = array(
		'TransLink', 'TransSite', 'TransType', 'TransFee', 'TransClickout', 'TransCompany',
		'TransViewLink', 'TransViewSite', 'TransViewType', 'TransViewClickout',
		'TransViewCompany', 'TransViewAgent',
		'AgentSiteMapping', 'ViewMapping',
		'SiteExcluding'
	);
	var $helpers = array(
		'Form', 'Html', 'Javascript',
		'ExPaginator'
	);
	
	var $curuser = null;
	var $__limit = 50;
	
	/*callbacks*/
	function beforeFilter() {
		$this->pageTitle = 'AMERICAN WEB LINK.[LINKS]';
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
		
		/*check if the user could visit some actions*/
		$this->__handleAccess();
		
		parent::beforeFilter();
	}
	
	function __accessDenied() {
		$this->Session->setFlash('Sorry, you are not authorized to visit that location, so you\'ve been relocated here.');
		$this->redirect(array('controller' => 'trans', 'action' => 'index'));
	}
	
	function __handleAccess() {
		if ($this->curuser == null) {
			$this->__accessDenied();
			return;
		}
		
		if ($this->curuser['role'] == 0) {//means an administrator
			return;
		}
		if ($this->curuser['role'] != 0) {//means an office or an agent
			switch ($this->params['action']) {
				case 'lstsites':
				case 'addsite':
				case 'updsite':
				case 'activatesite':
				case 'suspendsite':
				case 'lsttypes':
				case 'updtype':
				case 'lstcampaigns':
					$this->__accessDenied();
					return;
			}
		}
	}
	
	function __getlinks_4cam4_only($url) {
		$scrape_ch = curl_init();
		curl_setopt($scrape_ch, CURLOPT_URL, $url);
		curl_setopt($scrape_ch, CURLOPT_RETURNTRANSFER, true); 
		$scrape = curl_exec( $scrape_ch );
		$scrape = str_replace( "&", "&#x26;", $scrape );//# & encoding
		curl_close($scrape_ch);
		$rss = @new MagpieRSS($scrape);
		return $rss->items;
	}
	
	function lsttypes($id = -1) {
		$this->layout = "defaultlayout";
		
		$conditions = array('1' => '1');
		if ($id != -1) {
			array_push($conditions, array('siteid' => $id));
		}
		$this->paginate = array(
			'TransViewType' => array(
				'conditions' => $conditions
				//'limit' => $this->__limit
			)
		);
		$this->set('rs', $this->paginate('TransViewType'));
	}
	
	function updtype($id = -1) {
		$this->layout = "defaultlayout";
		
		if (empty($this->data)) {
			$this->TransType->id = $id;
			$this->data = $this->TransType->read();
			if (empty($this->data)) {
				$this->Session->setFlash('Sorry, no such type.');
				$this->redirect(array('controller' => 'links', 'action' => 'lsttypes'));
			}
		} else {
			if ($this->TransType->save($this->data)) {
				$this->TransViewType->id = $this->data['TransType']['id'];
				$data = $this->TransViewType->read();
				$this->Session->setFlash('Type saved.');
				$this->redirect(array('controller' => 'links', 'action' => 'lsttypes', 'id' => $data['TransViewType']['siteid']));
			}
		}
	}
	
	function lstsites() {
		$this->layout = "defaultlayout";
		
		/*
		$this->paginate = array(
			'TransViewSite' => array(
				'limit' => $this->__limit
			)
		);
		*/
		$this->set('status', $this->TransSite->status);
		$this->set('rs', $this->paginate('TransViewSite'));
	}
	
	function addsite() {
		$this->layout = "defaultlayout";
		
		if (!empty($this->data)) {
			if ($this->TransSite->save($this->data)) {
				$this->Session->setFlash('Site added.');
				$this->redirect(array('controller' => 'links', 'action' => 'lstsites'));
			}
		}
		$this->set('types', $this->TransSite->types);
	}
	
	function updsite($id = -1) {
		$this->layout = "defaultlayout";
		
		$rs = array();
		if (empty($this->data)) {
			$this->TransSite->id = $id;
			$this->data = $this->TransSite->read();
			$rs = $this->data;
			if (empty($this->data)) {
				$this->Session->setFlash('Sorry, no such site.');
				$this->redirect(array('controller' => 'links', 'action' => 'lstsites'));
			}
		} else {
			if ($this->TransSite->save($this->data)) {
				$this->Session->setFlash('Site updated.');
				$this->redirect(array('controller' => 'links', 'action' => 'lstsites'));
			}
		}
		$this->set('rs', $rs);
		$this->set('types', $this->TransSite->types);
	}
	
	function activatesite($id = -1) {
		if ($this->TransSite->updateAll(array('status' => 1), array('id' => $id))) {
			$this->Session->setFlash('Site activated.');
		}
		$this->redirect(array('controller' => 'links', 'action' => 'lstsites'));
	}
	
	function suspendsite($id = -1) {
		if ($this->TransSite->updateAll(array('status' => 0), array('id' => $id))) {
			$this->Session->setFlash('Site suspended.');
		}
		$this->redirect(array('controller' => 'links', 'action' => 'lstsites'));
	}
	
	function lstlinks($id = -1) {
		$this->layout = "defaultlayout";
		
		/*prepare the agents for this view from DB*/
		/*prepare the sites for the view from DB*/
		$ags = array();
		$sites = array();
		//$exsites = array();
		$suspsites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array(
					'status' => 0//,
					//'id !=' => 5/*hard code here, try to put test site away*/
				),
				'order' => 'sitename'
			)
		);
		if ($this->curuser['role'] == 0) {//means an administrator
			$ags = $this->TransViewAgent->find('list',
				array(
					'fields' =>	array(
						'TransViewAgent.id',
						'TransViewAgent.username'
					),
					'order' => 'username4m'
				)
			);
			$sites = $this->TransViewSite->find('list',
				array(
					'fields' => array('id', 'sitenametype'),
					'order' => 'id'
				)
			);
		} else if ($this->curuser['role'] == 1) {//means an office
			$ags = $this->TransViewAgent->find('list',
				array(
					'fields' =>	array(
						'TransViewAgent.id',
						'TransViewAgent.username'
					),
					'conditions' => array('companyid' => $this->curuser['id']),
					'order' => 'username4m'
				)
			);
			$__exsites = $this->SiteExcluding->find('list',
				array(
					'fields' => array('siteid'),
					'conditions' => array('companyid' => $this->curuser['id']),
					'group' => 'siteid'
				)
			);
			$sites = $this->TransViewSite->find('list',
				array(
					'fields' => array('id', 'sitenametype'),
					'conditions' => array('status' => 1, 'not' => array('id' => $__exsites)),
					'order' => 'sitename'
				)
			);
			/*
			$exsites = $this->TransSite->find('list',
				array(
					'fields' => array('id', 'sitename'),
					'conditions' => array('id' => $__exsites),
					'order' => 'sitename'
				)
			);
			*/
		} else if ($this->curuser['role'] == 2) {//means an agent
			$ags = $this->TransViewAgent->find('list',
				array(
					'fields' =>	array(
						'TransViewAgent.id',
						'TransViewAgent.username'
					),
					'conditions' => array('id' => $this->curuser['id']),
					'order' => 'username4m'
				)
			);
			$agcp = $this->TransViewAgent->find('first',
				array(
					'conditions' => array('id' => $this->curuser['id'])
				)
			);
			$__exsites = $this->SiteExcluding->find('list',
				array(
					'fields' => array('siteid'),
					'conditions' => array('companyid' => $agcp['TransViewAgent']['companyid']),
					'group' => 'siteid'
				)
			);
			$__exsites += $this->SiteExcluding->find('list',
				array(
					'fields' => array('siteid'),
					'conditions' => array('agentid' => $this->curuser['id']),
					'group' => 'siteid'
				)
			);
			$__exsites = array_unique($__exsites);
			$sites = $this->TransSite->find('list',
				array(
					'fields' => array('id', 'sitename'),
					'conditions' => array('status' => 1, 'not' => array('id' => $__exsites)),
					'order' => 'sitename'
				)
			);
			/*
			$exsites = $this->TransSite->find('list',
				array(
					'fields' => array('id', 'sitename'),
					'conditions' => array('id' => $__exsites),
					'order' => 'sitename'
				)
			);
			*/
		}
		
		//$sites = array(0 => 'All') + $sites;
				
		$this->set('ags', $ags);
		$this->set('sites', $sites);
		//$this->set('exsites', $exsites);
		$this->set('suspsites', $suspsites);
		
		$this->set('rs', array());
		if (!empty($this->data)) {
			if ($this->data['TransSite']['id'] == -1) {//REMARK IT FROM CCI:site CAM4 is the 1st & special one
				$agent = $this->TransViewAgent->find('first',
					array(
						'conditions' => array('id' => $this->data['TransViewAgent']['id'])
					)
				);
				/*read the rss into $items*/
				$url = sprintf(
					'http://webmasters.cams4pleasure.com/custom/campaigns2.php?username=bvlgari2010&password=dreaming&campaign=%s',
					$agent['TransViewAgent']['username']
				);//this url is just for type 1 ~ type 3
				$items = $this->__getlinks_4cam4_only($url);
				$url = sprintf(
					'http://webmasters.cams4pleasure.com/custom/campaigns3.php?username=bvlgari2010&password=dreaming&campaign=%s',
					$agent['TransViewAgent']['username']
				);//this url is just for type 7
				$__items = $this->__getlinks_4cam4_only($url);
				$items[0] += array('link_type7' => $__items[0]['link_type7']);
	
				foreach ($items as $item) {
					if (strtolower($item['name']) == strtolower($agent['TransViewAgent']['username'])) {//if $item['name'] equals to POST username
						$typedata = $this->TransType->find('all',
							array(
								'conditions' => array(
									'siteid' => $this->data['TransSite']['id'],
								),
								'order' => 'id'
							)
						);
						
						//must matches the sequences in $items
						$hardtypes = array('link_type1', 'link_type2', 'link_type3', 'link_type7');
						$msg = '';
						for ($i = 0; $i < count($typedata); $i++) {
							$r = $this->TransLink->find('first',
								array(
									'conditions' => array(
										'agentid' => $agent['TransViewAgent']['id'],
										'typeid' => $typedata[$i]['TransType']['id']
									)
								)
							);
							if (empty($r)) {//if not exist, insert it
								$this->data['TransLink']['id'] = null;
								$this->data['TransLink']['agentid'] = $agent['TransViewAgent']['id'];
								$this->data['TransLink']['siteid'] = $this->data['TransSite']['id'];//??
								//$this->data['TransLink']['typeid'] = '1';
								$this->data['TransLink']['typeid'] = $typedata[$i]['TransType']['id'];
								$this->data['TransLink']['status'] = $typedata[$i]['TransType']['status'];
								$this->data['TransLink']['url'] = $item[$hardtypes[$i]];
								$this->TransLink->create();
								if ($this->TransLink->save($this->data)) {
									//$this->Session->setFlash('link_type1 inserted.');
									if ($typedata[$i]['TransType']['status'] == 0) {
										$msg .= ('[-]');
									} else {
										$msg .= ('[' . $hardtypes[$i] . ' inserted.]');
									}
									$this->data['TransLink']['id'] = $this->TransLink->id;
								} else {
									//$this->Session->setFlash('link_type1 insert wrong');
									$msg .= ('[Something wrong when DB inserting.(' . $i . ')]' . str_replace("\n", "<br/>", print_r($items, true)));
								}
							} else {//if exist, update it
								$this->data['TransLink']['id'] = $r['TransLink']['id'];
								$this->data['TransLink']['status'] = $typedata[$i]['TransType']['status'];
								$this->data['TransLink']['url'] = $item[$hardtypes[$i]];
								$this->TransLink->create();
								if ($this->TransLink->save($this->data)) {
									//$this->Session->setFlash('link_type1 updated.');
									if ($typedata[$i]['TransType']['status'] == 0) {
										$msg .= ('[-]');
									} else {
										$msg .= ('[' . $hardtypes[$i] . ' updated.]');
									}
									$this->data['TransLink']['id'] = $this->TransLink->id;
								} else {
									//$this->Session->setFlash('link_type1 update wrong');
									$msg .= ('[Something wrong when DB updating.(' . $i . ')]');
								}
							}
						}
						$this->Session->setFlash($msg);
						
						$this->set('rs',
							$this->TransViewLink->find('all',
								array(
									'conditions' => array(
										'agentid' => $agent['TransViewAgent']['id'],
										'status' => '1'
									)
								)
							)
						);
					}
				}
			} else {
				/*
				 * for the way with new driver commdrv_links.php...processing...
				 * this part will checkout table agent_site_mappings and generate the link from it:
				 * step 1, find out all the campaign ids with "siteid = $this->data['TransStie']['id']
				 * and agentid = $this->data['TransViewAgent']['id']" from agent_site_mappings
				 * step2, generate the links with all the types from trans_types in the same site 
				 * (siteid = $this->data['TransStie']['id']), and with campaign id one by one.
				 */
				$rs = $this->AgentSiteMapping->find('all',
					array(
						'conditions' => array(
							'siteid' => $this->data['TransSite']['id'],
							'agentid' => $this->data['TransViewAgent']['id'],
							'flag' => 1
						)
					)
				);
				$types = $this->TransType->find('all',
					array(
						'conditions' => array(
							'siteid' => $this->data['TransSite']['id'],
							'status' => '1'
						)
					)
				);
				$this->set(compact('rs'));
				$this->set(compact('types'));
			}
		}
	}
	
	function lstcampaigns($id = null) {
		$this->layout = 'defaultlayout';
		
		$rs = array();
		if (!empty($id)) {
			$this->paginate = array(
				'ViewMapping' => array(
					'conditions' => array('agentid' => $id)
				)
			);
			$rs = $this->paginate('ViewMapping');
		}
		$this->set(compact('rs'));
	}
	
	function lstclickouts() {
		$this->layout = 'defaultlayout';
		
		$startdate = date('Y-m-d', mktime (0,0,0,date("m"), date("d") - 6 ,date("Y")));
		$enddate = date('Y-m-d');

		$selcom = $selagent = $selsite = 0;
		if ($this->curuser['role'] == 1) {
			$selcom = $this->curuser['id'];
		} else if ($this->curuser['role'] == 2) {
			$selagent = $this->curuser['id'];
			$rs = $this->TransAgent->find('first',
				array(
					'fields' => array('companyid'),
					'conditions' => array('id' => $selagent)
				)
			);
			if (!empty($rs)) {
				$selcom = $rs['TransAgent']['companyid'];
			}
		}
		
		$coms = $this->TransCompany->find('list',
			array(
				'fields' => array('id', 'officename'),
				'conditions' => ($selcom == 0 ? array('1' => '1') : array('id' => $selcom)),
				'order' => array('officename')
			)
		);
		$coms = array('0' => 'All') + $coms;
		$ags = $this->TransViewAgent->find('list',
			array(
				'fields' => array('id', 'username'),
				'conditions' => ($selcom == 0 ? array('1' => '1') : array('companyid' => $selcom)),
				'order' => array('username4m')
			)
		);
		$ags = array('0' => 'All') + $ags;
		$sites = $this->TransViewSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => ($this->curuser['role'] == 0) ? array('1' => '1') : array('status' => '1'),
				'order' => array('sitename')
			)
		);
		$sites = array('0' => 'All') + $sites;
		
		if (empty($this->data)) {
			if ($this->Session->check('conditions_clickouts')) {
				$conditions = $this->Session->read('conditions_clickouts');
				$condv = array_values($conditions);
				$startdate = $condv[0];
				$enddate = $condv[1];
				$selcom = count($condv) > 2 ? $condv[2][1] : 0;
				$selagent = count($condv) > 3 ? $condv[3] : 0;
				$selsite = count($condv) > 4 ? $condv[4] : 0;
			} else {
				$conditions = array(
					'convert(clicktime, date) >=' => $startdate,
					'convert(clicktime, date) <=' => $enddate
				);
			}
		} else {
			$startdate = $this->data['TransViewClickout']['startdate'];
			$enddate = $this->data['TransViewClickout']['enddate'];
			$selcom = $this->data['Stats']['companyid'];
			$selagent = $this->data['Stats']['agentid'];
			$selsite = $this->data['Stats']['siteid'];
			$conditions = array(
				'convert(clicktime, date) >=' => $startdate,
				'convert(clicktime, date) <=' => $enddate
			);
			if ($selcom != 0) {
				$conditions['companyid'] = array(0, $selcom);//!!!Very important!!!If not put this way "array(0, $selcom)", the paginating will show wrong with officename.
			}
			if ($selagent != 0) {
				$conditions['agentid'] = $selagent;
			}
			if ($selsite != 0) {
				$conditions['siteid'] = $selsite;
			}
			$this->Session->write('conditions_clickouts', $conditions);
		}
		
		if ($selcom != 0) $conditions['companyid'] = array(-1, $selcom);
		if ($selagent != 0) $conditions['agentid'] = array(-1, $selagent);
		if ($selsite != 0) $conditions['siteid'] = array(-1, $selsite);
		
		$this->set(compact('startdate'));
		$this->set(compact('enddate'));
		$this->set(compact('coms'));
		$this->set(compact('ags'));
		$this->set(compact('sites'));
		$this->set(compact('selcom'));
		$this->set(compact('selagent'));
		$this->set(compact('selsite'));
		
		$this->paginate = array(
			'TransViewClickout' => array(
				'conditions' => $conditions,
				'order' => 'clicktime desc',
				'limit' => $this->__limit
			)
		);
		$this->set('rs', $this->paginate('TransViewClickout'));
	}
}
?>