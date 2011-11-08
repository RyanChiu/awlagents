<?php
App::import('vendor', 'ExtraKits', array('file' => 'extrakits.inc.php'));
?>
<?php
class TransController extends AppController {
	var $name = 'Trans';
	var $uses = array(
		'TransAccount', 'TransAdmin', 'TransCompany', 'TransAgent',
		'TransCountry', 'Bulletin', 'ChatLog', 'ViewChatLog',
		'OnlineLog', 'ViewOnlineLog',
		'TransLink', 'TransClickout', 'AgentSiteMapping', 'TransType',
		'TransSite', 'SiteExcluding', 'TransStats',
		'TransViewAdmin', 'TransViewCompany', 'TransViewAgent',
		'TransViewStats', 'ViewMapping',
		'FakeContactUs'
	);
	var $components = array(
		'Auth',
		'Email',
		'Captcha'
	);
	var $helpers = array(
		'Form', 'Html', 'Javascript',
		'ExPaginator'
	);
	var $__limit = 50;
	var $__svrtz = "America/Los_Angeles";
	var $__timeout = 21600;// in seconds, shoud be the same with the php timeout setting

	function beforeFilter() {
		//Configure::write('debug', 2);
		$this->pageTitle = 'AMERICAN WEB LINK';
		$this->Auth->authenticate = ClassRegistry::init('TransAccount'); 
		$this->Auth->userModel = 'TransAccount'; 
		$this->Auth->loginAction = array('controller' => 'trans', 'action' => 'login');
		$this->Auth->loginRedirect = array('controller' => 'trans', 'action' => 'index');
		$this->Auth->logoutRedirect = array('controller' => 'trans', 'action' => 'login');
		$this->Auth->loginError = 'Sorry, login failed, please try again.';
		$this->Auth->authError = 'Sorry, you are not authorized to access that location.';
		$this->Auth->userScope = array('TransAccount.status' => '1');
		$this->Auth->autoRedirect = false;
		$this->Auth->allow('login', 'logout', 'forgotpwd', 'contactus', 'captcha', 'index', 'golink', 'go');
		
		/*check if the user could visit some actions*/
		$this->__handleAccess();
		
		parent::beforeFilter();
	}
	
	function __accessDenied() {
		$this->Session->setFlash('Sorry, you are not authorized to visit that location, so you\'ve been relocated here.');
		$this->redirect(array('controller' => 'trans', 'action' => 'index'));
	}
	
	function __handleAccess() {		
		if ($this->Auth->user('role') == 0) {//means an administrator
			return;
		}
		if ($this->Auth->user('role') == 1) {//means an office
			switch ($this->params['action']) {
				case 'updadmin':
				case 'addnews':
				case 'updpopupmsg':
				case 'regcompany':
				case 'lstcompanies':
					$this->__accessDenied();
					return;
				case 'lstagents':
					if (count($this->params['pass']) == 1 
						&& $this->params['pass'][0] == $this->Auth->user('id')) {
						return;
					} else if (array_key_exists('page', $this->passedArgs)) {
						return;
					} else if (array_key_exists('TransViewAgent', $this->data)
						&& array_key_exists('AgentSiteMapping', $this->data)) {
						return;
					} else {
						$this->__accessDenied();
						return;	
					}
				case 'updcompany':
					if (count($this->params['pass']) == 1 
						&& $this->params['pass'][0] == $this->Auth->user('id')) {
						return;
					} else {
						$this->__accessDenied();
						return;	
					}
			}
		}
		if ($this->Auth->user('role') == 2) {//means an agent
			switch ($this->params['action']) {
				case 'updadmin':
				case 'addnews':
				case 'updpopupmsg':
				case 'regcompany':
				case 'regagent':
				case 'updcompany':
				case 'rptpayouts':
				case 'lstcompanies':
				case 'lstagents':
				case 'lstlogins':
					$this->__accessDenied();
					return;
				case 'updagent':
					if (count($this->params['pass']) == 1 
						&& $this->params['pass'][0] == $this->Auth->user('id')) {
						return;
					} else {
						$this->__accessDenied();
						return;	
					}
			}
		}
	}
	
	function __sendemail($subject = 'empty', $content = 'empty',
		$from = 'support@americanweblink.com',
		$mailto = 'support@americanweblink.com',
		$replyto = 'support@americanweblink.com') {
		/* SMTP Options */
		$this->Email->smtpOptions = array(
			//'request' => array('uri' => array('scheme' => 'https')),
			'port'=>'25',
			'timeout'=>'60',
			'host' => 'smtpout.secureserver.net',
			'username'=>'support@americanweblink.com',
			'password'=>'Otvori54321A'
		);
		$this->Email->from = '<' . $from . '>';
		$this->Email->to = '<' . $mailto . '>';
		$this->Email->replyTo = '<' . $replyto . '>';
		$this->Email->subject = $subject;
		/* Set delivery method */
		$this->Email->delivery = 'smtp';
		/* Send the email */
		$this->Email->send($content);
		/* Check for SMTP errors */ 
		if (!empty($this->Email->smtpError)) return $this->Email->smtpError;
		else return true;
	}
	
	function captcha() {
        Configure::write('debug', '0');
        $this->autoRender = false;
        $this->Captcha->render();
	}
    
	function __checkCaptcha($vcode) {
    	if ($this->Session->check('captcha')) {
    		$s_captcha = $this->Session->read('captcha');
			if (!empty($vcode) && $vcode == $s_captcha) {
				return true;
			}
		}
		return false;
	}
	
	function __top10($conds) {
		/*
		 * for the "selling contestants" stuff
		 */
		//avoid those data which are not in trans_types
		$tps = $this->TransType->find('list',
			array(
				'fields' => array('id', 'id')
			)
		);
		$rs = $this->TransStats->find('all',
			array(
				'fields' => array('agentid', 'sum(sales_number) as sales'),
				'conditions' => array(
					'convert(trxtime, date) <=' => $conds['enddate'],
					'convert(trxtime, date) >=' => $conds['startdate'],
					'typeid' => $tps,
					'agentid >' => '0'//avoid those data that don't belog to any agent
				),
				'group' => array('agentid'),
				'order' => array('sales desc'),
				'limit' => 10
			)
		);
		$i = 0;
		foreach ($rs as $r) {
			$topag = $this->TransAccount->find('first',
				array(
					'fields' => array('username'),
					'conditions' => array('id' => $r['TransStats']['agentid'])
				)
			);
			$topcom = $this->TransAgent->find('first',
				array(
					'fields' => array('companyid'),
					'conditions' => array('id' => $r['TransStats']['agentid'])
				)
			);
			$topcom = $this->TransCompany->find('first',
				array(
					'fields' => array('officename'),
					'conditions' => array('id' => $topcom['TransAgent']['companyid'])
				)
			);
			$rs[$i]['TransViewStats']['officename'] = $topcom['TransCompany']['officename'];
			$rs[$i]['TransViewStats']['username'] = $topag['TransAccount']['username'];
			$rs[$i]['TransViewStats']['sales'] = $r[0]['sales'];
			$i++;
		}
		return $rs;
	}
	
	function top10() {
		$this->layout = 'defaultlayout';
		
		$lastday = date("Y-m-d", strtotime(date('Y-m-d') . " Sunday"));
		$weekend = $lastday;
		$weekstart = date("Y-m-d", strtotime($lastday . " - 6 days"));
		$periods = array();
		for ($i = 0; $i < 52; $i++) {
			$oneweek = date("Y-m-d", strtotime($lastday . " - " . (7 * $i + 6) . " days"))
				. ','
				. date("Y-m-d", strtotime($lastday . " - " . (7 * $i) . " days"));
			$v = $oneweek;
			switch ($i) {
				case 0:
					$v = 'THIS WEEK';
					break;
				case 1:
					$v = 'LAST WEEK';
					break;
				default:
					break;
			}
			$periods += array($oneweek => $v);
		}
		
		$weekrs = array();
		if (!empty($this->data)) {
			$conds = array();
			$weekstart = $this->data['Top10']['weekstart'];
			$weekend = $this->data['Top10']['weekend'];
			$conds['startdate'] = $weekstart;
			$conds['enddate'] = $weekend;
			$weekrs = $this->__top10($conds);
		}
		$this->set(compact('weekrs'));
		$this->set(compact('weekstart'));
		$this->set(compact('weekend'));
		$this->set(compact('periods'));
	}
	
	function pass() {
		$this->Session->write('switch_pass', 1);
		$this->redirect(array('controller' => 'trans', 'action' => 'index'));
	}
	
	function index($id = null) {
		if (!$this->Auth->user()) $this->redirect(array('controller' => 'trans', 'action' => 'login'));
		$this->layout = 'defaultlayout';

		/*try to archive the bulletin*/
		if ($id == -1 && $this->Auth->user('role') == 0) {
			$this->Bulletin->updateAll(
				array('archdate' => "'" . date('Y-m-d h:i:s') . "'"),
				array('archdate' => null)
			);
			if ($this->Bulletin->getAffectedRows() > 0) {
				$this->Session->setFlash("Bulletin archived.");
			} else {
				$this->Session->setFlash("No current bulletin exists.");
			}
		}
		/*prepare the historical bulletins*/
		$archdata = $this->Bulletin->find('all',
			array(
				'fields' => array('id', 'title', 'archdate'),
				'conditions' => array('archdate not' => null),
				'order' => array('archdate desc')
			)
		);
		$this->set(compact('archdata'));
		/*prepare the ALERTS for the current logged-in user*/
		$info = array();
		if ($id == null) {
			$info = $this->Bulletin->find('first',
				array(
					'fields' => array('info'),
					'conditions' => array('archdate' => null)
				)
			);
		} else {
			$info = $this->Bulletin->find('first',
				array(
					'fields' => array('info'),
					'conditions' => array('id' => $id)
				)
			);
		}
		$this->set('topnotes',  empty($info) ? '...' : $info['Bulletin']['info']);
		if ($this->Auth->user('role') == 0) {//means an administrator
			$this->set('notes', '');//set the additional notes here
		} else if ($this->Auth->user('role') == 1) {//means a company
			$cominfo = $this->TransCompany->find('first',
				array(
					'fields' => array('agentnotes'),
					'conditions' => array('id' => $this->Auth->user('id'))
				)
			);
			$this->set('notes', '');//set the additional notes here
		} else {//means an agent
			$aginfo = $this->TransAgent->find('first',
				array(
					'fields' => array('companyid'),
					'conditions' => array('id' => $this->Auth->user('id'))
				)
			);
			$cominfo = $this->TransCompany->find('first',
				array(
					'fileds' => array('agentnotes'),
					'conditions' => array('id' => $aginfo['TransAgent']['companyid'])
				)
			);
			$this->set('notes', '<font size="3"><b>Office news&nbsp;&nbsp;</b></font>' . $cominfo['TransCompany']['agentnotes']);
			
			
		}
		
		/*
		 * for the "selling contestants" stuff
		 */
		//avoid those data which are not in trans_types
		$conds['startdate'] = '0000-00-00';
		$conds['enddate'] = date('Y-m-d');
		$rs = $this->__top10($conds);
		$this->set(compact('rs'));
		$weekend = date("Y-m-d", strtotime(date('Y-m-d') . " Saturday"));
		$weekstart = date("Y-m-d", strtotime($weekend . " - 6 days"));
		$conds['startdate'] = $weekstart;
		$conds['enddate'] = $weekend;
		$weekrs = $this->__top10($conds);
		$this->set(compact('weekrs'));
		$this->set(compact('weekstart'));
		$this->set(compact('weekend'));
				
		/*prepare the totals demo data*/
		/*## for accounts overview*/
		/*
		$totals = array('cpofflines' => 0, 'cponlines' => 0, 'agofflines' => 0, 'agonlines' => 0,
			'cpacts' => 0, 'cpsusps' => 0, 'agacts' => 0, 'agsusps' => 0);
		$totals['cpofflines'] = 
			$this->TransViewCompany->find('count',
				array('conditions' => array('online' => 0))
			);
		$totals['cponlines'] = 
			$this->TransViewCompany->find('count',
				array('conditions' => array('online' => 1))
			);
		
		$totals['agofflines'] =
			$this->TransViewAgent->find('count',
				array('conditions' => array('online' => 0))
			);
		$totals['agonlines'] =
			$this->TransViewAgent->find('count',
				array('conditions' => array('online' => 1))
			);
		
		$totals['cpsusps'] =
			$this->TransViewCompany->find('count',
				array('conditions' => array('status' => 0))
			);
		$totals['cpacts'] =
			$this->TransViewCompany->find('count',
				array('conditions' => array('status' => 1))
			);
		
		$totals['agsusps'] =
			$this->TransViewAgent->find('count',
				array('conditions' => array('status' => 0))
			);
		$totals['agacts'] =
			$this->TransViewAgent->find('count',
				array('conditions' => array('status' => 1))
			);
		
		$this->set('totals', $totals);
		*/
		
		/*prepare the online demo data*/
		/*
		$this->set('cprs',
			$this->TransViewCompany->find('all',
				array(
					'fields' => array('username', 'officename', 'contactname', 'regtime'),
					'conditions' => array('online' => 1)
				)
			)
		);
		
		$this->set('agrs',
			$this->TransViewAgent->find('all',
				array(
					'fields' => array('username', 'officename', 'name', 'regtime'),
					'conditions' => array('online' => 1)
				)
			)
		);
		*/
	}
	
	function login() {
		$this->layout = 'loginlayout';
		
		if (!empty($this->data)) {//if there are any POST data
			
			/*try to find the account info from DB who try to login*/
			$userinfo = $this->TransAccount->find('first',
				array('conditions' => array('lower(username)' => strtolower($this->data['TransAccount']['username'])))
			);
			
				/*the follwing codes are just in case of "agent name changed" situation-start*/
				if (empty($userinfo)) {
					$asmrs = $this->AgentSiteMapping->find('first',
						array('conditions' => array('lower(campaignid)' => strtolower($this->data['TransAccount']['username'])))
					);
					if (!empty($asmrs)) {
						$userinfo = $this->TransAccount->find('first',
							array('conditions' => array('id' => $asmrs['AgentSiteMapping']['agentid']))
						);
						
					}
				}
				/*the up block codes are just in case of "agent name changed" situation-end*/
				
				/*try to judge if the office which the agent belongs to is suspended*/
				if ($userinfo['TransAccount']['role'] == 2) {
					$aginfo = $this->TransAgent->find('first',
						array('conditions' => array('id' => $userinfo['TransAccount']['id']))
					);
					$cpinfo = $this->TransAccount->find('first',
						array('conditions' => array('id' => $aginfo['TransAgent']['companyid']))
					);
					if ($cpinfo['TransAccount']['status'] == 0) {
						$this->Session->setFlash(
							"(Your office is suspended right now, please contact your administrator.)",
							'default',
							array('class' => 'suspended-warning')
						);
						$this->data['TransAccount']['password'] = '';
						$this->Auth->logout();
						return;
					}
				}
				
			$vcode = $this->data['TransAccount']['vcode'];
			if ($this->__checkCaptcha($vcode)) {//if captcha code is correct
				/*login part*/
				if ($this->Auth->user()) {//means username/password/status are all correct, login succeeded
					$this->Auth->login();
					/*
					 * before login redirect, we try to log the login time of the user. 
					 */
					$gonnalog = true;
					$now = new DateTime("now", new DateTimeZone($this->__svrtz));
					if ($this->Auth->user('online') != -1) {
						$ollog = $this->OnlineLog->find('first',
							array(
								'conditions' => array('id' => $this->Auth->user('online'))
							)
						);
						$logtimediff =
							strtotime($now->format('Y-m-d H:i:s'))
							- strtotime($ollog['OnlineLog']['intime']);
						if ($logtimediff < $this->__timeout) $gonnalog = false;
					}
					if ($gonnalog) {
						$ollog = array('OnlineLog' => array());
						$ollog['OnlineLog'] += array('accountid' => $this->Auth->user('id'));
						$ollog['OnlineLog'] += array('intime' => $now->format('Y-m-d H:i:s'));
						$ollog['OnlineLog'] += array('inip' => __getclientip());
						$ollog['OnlineLog'] += array(
							'outtime' => date(
								'Y-m-d H:i:s',
								strtotime(
									"+" . $this->__timeout . " second",
									strtotime($now->format('Y-m-d H:i:s'))
								)
							)
						);
						$this->OnlineLog->id = null;
						$this->OnlineLog->save($ollog);
						$this->TransAccount->id = $this->Auth->user('id');
						$this->TransAccount->saveField('online', $this->OnlineLog->id);
						$this->TransAccount->saveField('lastlogintime', $now->format('Y-m-d H:i:s'));
					}
					$this->redirect($this->Auth->redirect());
				} else {// means login failed
					if (!empty($userinfo)) {
						if ($userinfo['TransAccount']['status'] == 0) {
							$this->Session->setFlash(
								'(Your account for this site is temporarily suspended for fraud review.)',
								'default',
								array('class' => 'suspended-warning')
							);
						} else {
							/*try to find the new username by searching the mappings table*/
							if ($userinfo['TransAccount']['username'] != $this->data['TransAccount']['username']) {
								$this->Session->setFlash(
									sprintf('Your username has been changed from "%s" to "%s", please use the new one to login.',
										$this->data['TransAccount']['username'],
										$userinfo['TransAccount']['username']
									)
								);
								$this->data['TransAccount']['username'] = $userinfo['TransAccount']['username'];
							} else {
								$this->Session->setFlash('(incorrect password)');
							}
						}
					} else {
						if ($this->data['TransAccount']['username'])
							$this->Session->setFlash('(username: "' . $this->data['TransAccount']['username'] . '" doesn\'t exist.)');
					}
				}
			} else {
				$this->data['TransAccount']['password'] = '';
				$this->Session->setFlash('Your validation codes are incorrect, please try again.');
			}
		}
	}
	
	function logout() {
		if ($this->Auth->user()) {
			$this->TransAccount->id = $this->Auth->user('id');
			$userinfo = $this->TransAccount->read();
			if ($userinfo['TransAccount']['online'] != -1) {
				$this->OnlineLog->id = $userinfo['TransAccount']['online'];
				$ollog = $this->OnlineLog->read();
				if ($ollog['OnlineLog']['accountid'] == $this->Auth->user('id')) {
					$now = new DateTime("now", new DateTimeZone($this->__svrtz));
					$this->OnlineLog->id = $userinfo['TransAccount']['online'];
					$this->OnlineLog->saveField('outtime', $now->format('Y-m-d H:i:s'));
					$this->TransAccount->id = $this->Auth->user('id');
					$this->TransAccount->saveField('online', -1);
				}
			}
		}
		
		/*logout part*/
		$this->Session->destroy();
		$this->Auth->logout();
		$this->redirect($this->Auth->redirect());
		
	}
	
	function forgotpwd() {
		$this->layout = 'loginlayout';
		
		if ($this->Auth->user()) {
			$this->redirect(array('controller' => 'trans', 'action' => 'index'));
		}
		
		if (!empty($this->data)) {
			$this->data['Forgot']['username'] = trim($this->data['Forgot']['username']);
			$this->data['Forgot']['email'] = trim($this->data['Forgot']['email']);
			$r = $this->TransAccount->find('first',
				array(
					'conditions' => array(
						'lower(username)' => strtolower($this->data['Forgot']['username'])
					)
				)
			);
			if (empty($r)) {
				$this->Session->setFlash('Sorry, username ' . $this->data['Forgot']['username'] . ' doesn\'t exist.');
				$this->redirect(array('controller' => 'trans', 'action' => 'forgotpwd'));
			} else {
				if ($r['TransAccount']['role'] == 0) {//means an administrator
					$this->Session->setFlash('Sorry, we are unable to retrieve your password.');
					$this->redirect(array('controller' => 'trans', 'action' => 'forgotpwd'));
				} else if ($r['TransAccount']['role'] == 1) {//means an office
					$_r = $this->TransCompany->find('first',
						array(
							'conditions' => array(
								'id' => $r['TransAccount']['id']
							)
						)
					);
					if (empty($_r)) {
						$this->Session->setFlash('Sorry, username(c) ' . $this->data['Forgot']['username'] . ' doesn\'t exist.');
						$this->redirect(array('controller' => 'trans', 'action' => 'forgotpwd'));
					}
					if (strtolower($_r['TransCompany']['manemail']) != strtolower($this->data['Forgot']['email'])) {
						$this->Session->setFlash('Sorry, the email address is incorrect, please try again.');
						$this->redirect(array('controller' => 'trans', 'action' => 'forgotpwd'));
					}
					/*
					 * then we can use the email logic send the password with $_r['TransCompany']['manemail']
					 */
					$issent = $this->__sendemail(
						'Your Americanweblink Password',
						"Hi,\nYour Americanweblink password is:" . $r['TransAccount']['originalpwd'] . "\n"
						. "\nThanks,\nAmericanweblink webmaster.",//must use " instead of ' at this $content parameter
						'support@americanweblink.com',
						$_r['TransCompany']['manemail']
					);
					if ($issent) {
						$this->Session->setFlash('Password sent, please check it out.');
						$this->redirect(array('controller' => 'trans', 'action' => 'login'));
					} else {
						//$this->Session->setFlash($issent);//redim this line to debug
						$this->Session->setFlash('Failed to send password, please contact your administrator.');
					}
				} else if ($r['TransAccount']['role'] == 2) {//means an agent
					$_r = $this->TransAgent->find('first',
						array(
							'conditions' => array(
								'id' => $r['TransAccount']['id']
							)
						)
					);
					if (empty($_r)) {
						$this->Session->setFlash('Sorry, username(a) ' . $this->data['Forgot']['username'] . ' doesn\'t exist.');
						$this->redirect(array('controller' => 'trans', 'action' => 'forgotpwd'));
					}
					if (strtolower($_r['TransAgent']['email']) != strtolower($this->data['Forgot']['email'])) {
						$this->Session->setFlash('Sorry, the email address is incorrect, please try again.');
						$this->redirect(array('controller' => 'trans', 'action' => 'forgotpwd'));
					}
					/*
					 * then we can use the email logic send the password with $_r['TransAgent']['email']
					 */
					$issent = $this->__sendemail(
						'Your Americanweblink Password',
						"Hi,\nYour Americanweblink password is:" . $r['TransAccount']['originalpwd'] . "\n"
						. "\nThanks,\nAmericanweblink webmaster.",//must use " instead of ' at this $content parameter
						'support@americanweblink.com',
						$_r['TransAgent']['email']
					);
					if ($issent) {
						$this->Session->setFlash('YOUR PASSWORD WAS SENT, PLEASE CONTACT YOUR BRANCH MANAGER.');
						$this->redirect(array('controller' => 'trans', 'action' => 'login'));
					} else {
						//$this->Session->setFlash($issent);
						$this->Session->setFlash('Failed to send password, please contact your administrator.');
					}
				}
			}
		}
	}
	
	function contactus() {
		if ($this->Auth->user()) {
			$this->layout = 'defaultlayout';
		} else {
			$this->layout = 'loginlayout';
		}
		
		if (!empty($this->data)) {
			/*validate the posted fields*/
			$this->FakeContactUs->set($this->data);
			if (!$this->FakeContactUs->validates()) {
				$this->Session->setFlash('Please notice the tips below.');
				return;
			}
			/*send the message*/
			$this->data['FakeContactUs']['email'] = trim($this->data['FakeContactUs']['email']);
			$issent = $this->__sendemail(
				$this->data['FakeContactUs']['subject'],
				"From:" . $this->data['FakeContactUs']['email'] . "\n\n" . $this->data['FakeContactUs']['message'],
				"support@americanweblink.com",
				"help@americanweblink.com",
				$this->data['FakeContactUs']['email']
			);
			$redirecturl = '';
			if ($this->Auth->user()) {
				$redirecturl = array('controller' => 'trans', 'action' => 'index');
			} else {
				$redirecturl = array('controller' => 'trans', 'action' => 'login');
			}
			if ($issent) {
				$this->Session->setFlash('Message sent, please wait for reply.');
				$this->redirect($redirecturl);
			} else {
				$this->Session->setFlash('Failed to send message, please contact your administrator.');
				$this->redirect($redirecturl);
			}
		}
	}
	
	function addnews() {
		$this->layout = 'defaultlayout';
		
		if (empty($this->data)) {
			/*prepare the notes for the current logged in user*/
			$info = $this->Bulletin->find('first',
				array(
					'fields' => array('id', 'info'),
					'conditions' => array('archdate' => null)
				)
			);
			$this->data = $info;
		} else {
			$this->Bulletin->id = $this->data['Bulletin']['id'];
			if ($this->Bulletin->saveField('info', $this->data['Bulletin']['info'])) {
				//$this->Session->setFlash('ALERTS updated.');
				$this->redirect(array('controller' => 'trans', 'action' => 'index'));
			} else {
				$this->Session->setFlash("Something wrong, please contact your administrator.");
			}
		}
	}
	
	function updpopupmsg() {
		$this->layout = 'defaultlayout';
		
		if (empty($this->data)) {
			/*prepare the notes for the current logged in user*/
			$this->TransAdmin->id = 1;//HARD CODE: we put the popup message into here
			$this->data = $this->TransAdmin->read();
			if (empty($this->data)) {
				$this->Session->setFlash('Please create your first admin account, so we could continue the popup message setup.');
				$this->redirect(array('controller' => 'trans', 'action' => 'index'));
			}
		} else {
			$this->TransAdmin->id = $this->data['TransAdmin']['id'];
			if ($this->TransAdmin->saveField('notes', $this->data['TransAdmin']['notes'])) {
				$this->Session->setFlash('Popup message updated.');
				$this->redirect(array('controller' => 'trans', 'action' => 'index'));
			} else {
				$this->Session->setFlash("Something wrong, please contact your administrator.");
			}
		}
	}
	
	function updadmin() {
		$this->layout = 'defaultlayout';
		
		if (empty($this->data)) {
			$this->TransAccount->id = $this->Auth->user('id');
			$account = $this->TransAccount->read();
			$account['TransAccount']['password'] = $account['TransAccount']['originalpwd'];
			$this->data['TransAccount'] = $account['TransAccount'];
			$this->TransAdmin->id = $this->Auth->user('id');
			$admin = $this->TransAdmin->read();
			$this->data['TransAdmin'] = $admin['TransAdmin'];
			$this->set('rs', $this->data);
		} else {
			/*validate the posted fields*/
			$this->TransAccount->set($this->data);
			$this->TransAdmin->set($this->data);
			if (!$this->TransAccount->validates() || !$this->TransAdmin->validates()) {
				//$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please notice the tips below the fields.');
				return;
			}
			
			/*check if the passwords match or empty or untrimed*/
			$originalpwd = $this->data['TransAccount']['originalpwd'];
			if (strlen(trim($originalpwd)) != strlen($originalpwd)) {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please remove any blank in front of or at the end of your password and try again.');
				return;
			}
			//if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != $this->Auth->password($this->data['TransAccount']['originalpwd'])) {
			if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != md5($this->data['TransAccount']['originalpwd'])) {
				$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['originalpwd'] = '';
				$this->Session->setFlash('The passwords don\'t match to each other, please try again(and do not left it blank).');
				return;
			}
			
			/*actually save the data*/
			if ($this->TransAccount->save($this->data)) {
				$this->Session->setFlash('Account changed.');
				if ($this->TransAdmin->save($this->data)) {
					$this->Session->setFlash('Profile changed. Please remember your new password if changed.');
					$this->redirect(array('controller' => 'trans', 'action' => 'index'));
				}
			}
			$this->Session->setFlash('Something wrong here, please contact your administrator.');
		}
	}
	
	function regcompany($id = null) {
		$this->layout = 'defaultlayout';
		
		/*prepare the countries for this view*/
		$cts = $this->TransCountry->find('list', array('fields' => array('TransCountry.abbr', 'TransCountry.fullname')));
		$this->set('cts', $cts);
		
		/*prepare associated sites data*/
		$exsites = $this->SiteExcluding->find('list',
			array(
				'fields' => array('id', 'siteid'),
				'conditions' => array('companyid' => $id)
			)
		);
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1)
			)
		);
		$exsites = array_unique($exsites);
		$exsites = array_flip($exsites);
		foreach ($exsites as $k => $v) {
			if (in_array($k, array_keys($sites))) {
				$exsites[$k] = $sites[$k];
			}
		}
		$this->set(compact('exsites'));
		$this->set(compact('sites'));
		
		$this->set('payouttype', $this->TransCompany->payouttype);
		if (!empty($this->data)) {
			/*check if the passwords match or empty or untrimed*/
			$originalpwd = $this->data['TransAccount']['originalpwd'];
			if (strlen(trim($originalpwd)) != strlen($originalpwd)) {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please remove any blank in front of or at the end of your password and try again.');
				return;
			}
			//if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != $this->Auth->password($this->data['TransAccount']['originalpwd'])) {
			if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != md5($this->data['TransAccount']['originalpwd'])) {
				$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['originalpwd'] = '';
				$this->Session->setFlash('The passwords don\'t match to each other, please try again(and do not left it blank).');
				return;
			}
			
			/*validate the posted fields*/
			$this->TransAccount->set($this->data);
			$this->TransCompany->set($this->data);
			if (!$this->TransAccount->validates() || !$this->TransCompany->validates()) {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please notice the tips below the fields.');
				return;
			}
			
			/*make the value of field "regtime" to the current time*/
			$this->data['TransAccount']['regtime'] = date('Y-m-d H:i:s');
			
			/*actually save the posted data*/
			$this->TransAccount->create();
			$this->data['TransAccount']['username4m'] = __fillzero4m($this->data['TransAccount']['username']);
			if ($this->TransAccount->save($this->data)) {//1stly, save the data into 'trans_accounts'
				$this->Session->setFlash('Only account added.Please contact your administrator immediately.');
				
				$this->data['TransCompany']['id'] = $this->TransAccount->id;
				$this->TransCompany->create();
				if ($this->TransCompany->save($this->data)) {//2ndly, save the data into 'trans_companies'
					/*after an office added, update the site_excluding data, then*/
					$__sites = $this->data['SiteExcluding']['siteid'];
					if (is_array($__sites)) {
						$__sites = array_diff(array_keys($sites), $__sites);
					} else {
						$__sites = array_keys($sites);
					}
					$exdata = array();
					foreach ($__sites as $__site) {
						array_push(
							$exdata, 
							array(
								'companyid' => $this->data['TransCompany']['id'],
								'siteid' => $__site
							)
						);
					}
					$this->SiteExcluding->deleteAll(//since if no recs to del, it seems also return false, so we ignore it here
						array('companyid' => $this->data['TransCompany']['id'])
					);
					$exdone = false;
					if (!empty($exdata)) {
						$exdone = ($this->SiteExcluding->saveAll($exdata) ? true : false);
					} else {
						$exdone = true;
					}
					
					/*redirect to some page*/
					$this->Session->setFlash(
						'Office "'
						. $this->data['TransAccount']['username']
						. '" added.'
						. ($exdone ? '' : '<br><i>(Site associating failed.)</i>')
					);
					if ($id != -1) {
						$this->redirect(array('controller' => 'trans', 'action' => 'lstcompanies'));
					} else {
						$this->redirect(array('controller' => 'trans', 'action' => 'regcompany'));
					}
				} else {
					$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
					//should add some codes here to delete the record that saved in 'trans_accounts' table before if failed
				}
			} else {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
			}
		}
	}

	function regagent($id = null) {
		$this->layout = 'defaultlayout';
		
		/*prepare the companies for this view*/
		$cps = $this->TransViewCompany->find('list',
			array(
				'fields' => array('companyid', 'officename'),
				'order' => 'username4m'
			)
		);
		$this->set('cps', $cps);
		/*prepare the email of the current office*/
		if ($this->Auth->user('role') == 1) {
			$this->TransCompany->id = $this->Auth->user('id');
			$curcom = $this->TransCompany->read();
			$this->set(compact('curcom'));
		}
		/*prepare the countries for this view*/
		$cts = $this->TransCountry->find('list', array('fields' => array('TransCountry.abbr', 'TransCountry.fullname')));
		$this->set('cts', $cts);
		
		/*prepare associated sites data*/
		$exsites = $this->SiteExcluding->find('list',
			array(
				'fields' => array('id', 'siteid'),
				'conditions' => array('agentid' => $id)
			)
		);
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1)
			)
		);
		$exsites = array_unique($exsites);
		$exsites = array_flip($exsites);
		foreach ($exsites as $k => $v) {
			if (in_array($k, array_keys($sites))) {
				$exsites[$k] = $sites[$k];
			}
		}
		$this->set(compact('exsites'));
		$this->set(compact('sites'));
		
		if (!empty($this->data)) {
			/*check if the passwords match or empty or untrimed*/
			$originalpwd = $this->data['TransAccount']['originalpwd'];
			if (strlen(trim($originalpwd)) != strlen($originalpwd)) {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please remove any blank in front of or at the end of your password and try again.');
				return;
			}
			//if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != $this->Auth->password($this->data['TransAccount']['originalpwd'])) {
			if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != md5($this->data['TransAccount']['originalpwd'])) {
				$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['originalpwd'] = '';
				$this->Session->setFlash('The passwords don\'t match to each other, please try again(and do not left it blank).');
				return;
			}
			
			/*validate the posted fields*/
			$this->TransAccount->set($this->data);
			$this->TransAgent->set($this->data);
			if (!$this->TransAccount->validates() || !$this->TransAgent->validates()) {
				//$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please notice the tips below the fields.');
				return;
			}

			/*make the value of field "regtime" to the current time*/
			$this->data['TransAccount']['regtime'] = date('Y-m-d H:i:s');
			
			/*actually save the posted data*/
			$this->TransAccount->create();
			$this->data['TransAccount']['username4m'] = __fillzero4m($this->data['TransAccount']['username']);
			if ($this->TransAccount->save($this->data)) {//1stly, save the data into 'trans_accounts'
				$this->Session->setFlash('Only account added.');
				
				$this->data['TransAgent']['id'] = $this->TransAccount->id;
				//$this->data['TransAgent']['companyid'] = $this->data['TransCompany']['id'] == null ? 0 : $this->data['TransCompany']['id'];
				$this->TransAgent->create();
				if ($this->TransAgent->save($this->data)) {//2ndly, save the data into 'trans_agents'
					/*after agent saved, update the site_excluding data, then*/ 
			        $__sites = $this->data['SiteExcluding']['siteid']; 
					if (is_array($__sites)) { 
					  $__sites = array_diff(array_keys($sites), $__sites); 
					} else { 
					  $__sites = array_keys($sites); 
					} 
					$exdata = array(); 
					foreach ($__sites as $__site) { 
					  array_push( 
					    $exdata,  
					    array( 
					      'agentid' => $this->data['TransAgent']['id'], 
					      'siteid' => $__site 
					    ) 
					  ); 
					} 
					$this->SiteExcluding->deleteAll(//since if no recs to del, it seems also return false, so we ignore it here 
					  array('agentid' => $this->data['TransAgent']['id']) 
					); 
					$exdone = false; 
					if (!empty($exdata)) { 
					  $exdone = ($this->SiteExcluding->saveAll($exdata) ? true : false); 
					} else { 
					  $exdone = true; 
					} 
					 
					/*redirect to some page*/ 
					$this->Session->setFlash('Agent "' 
					  . $this->data['TransAccount']['username'] . '" added.' 
					  . ($exdone ? '' : '<br><i>(Site associating failed.)</i>') 
					);
					if ($id != -1) {
						$this->redirect(array('controller' => 'trans', 'action' => 'lstagents'));
					} else {
						$this->redirect(array('controller' => 'trans', 'action' => 'regagent'));
					}
				} else {
					$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
					//should add some codes here to delete the record that saved in 'trans_accounts' table before if failed
				}
			} else {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
			}
		}
	}
	
	function updcompany($id = null) {
		$this->layout = 'defaultlayout';
		
		/*prepare the countries for this view*/
		$cts = $this->TransCountry->find('list', array('fields' => array('TransCountry.abbr', 'TransCountry.fullname')));
		$this->set('cts', $cts);
				
		/*prepare associated sites data*/
		$exsites = $this->SiteExcluding->find('list',
			array(
				'fields' => array('id', 'siteid'),
				'conditions' => array('companyid' => $id)
			)
		);
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1)
			)
		);
		$exsites = array_unique($exsites);
		$exsites = array_flip($exsites);
		foreach ($exsites as $k => $v) {
			if (in_array($k, array_keys($sites))) {
				$exsites[$k] = $sites[$k];
			}
		}
		$this->set(compact('exsites'));
		$this->set(compact('sites'));
		
		$this->set('payouttype', $this->TransCompany->payouttype);
		$this->TransAccount->id = $id;
		$this->TransCompany->id = $id;
		if (empty($this->data)) {
			/*read the office into the update page*/
			$account = $this->TransAccount->read();
			//$account['TransAccount']['password'] = '';
			//$account['TransAccount']['originalpwd'] = '';
			$account['TransAccount']['password'] = $account['TransAccount']['originalpwd'];
			$company = $this->TransCompany->read();
			$this->data['TransAccount'] = $account['TransAccount'];
			$this->data['TransCompany'] = $company['TransCompany'];
		} else {
			/*check if the passwords match or empty or untrimed*/
			$originalpwd = $this->data['TransAccount']['originalpwd'];
			if (strlen(trim($originalpwd)) != strlen($originalpwd)) {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please remove any blank in front of or at the end of your password and try again.');
				return;
			}
			//if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != $this->Auth->password($this->data['TransAccount']['originalpwd'])) {
			if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != md5($this->data['TransAccount']['originalpwd'])) {
				$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['originalpwd'] = '';
				$this->Session->setFlash('The passwords don\'t match to each other, please try again(and do not left it blank).');
				return;
			}
			
			/*validate the posted fields*/
			$this->TransAccount->set($this->data);
			$this->TransCompany->set($this->data);
			if (!$this->TransAccount->validates() || !$this->TransCompany->validates()) {
				//$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please notice the tips below the fields.');
				return;
			}
			
			/*actually save the posted data*/
			$this->TransAccount->create();
			$this->data['TransAccount']['username4m'] = __fillzero4m($this->data['TransAccount']['username']);
			if ($this->TransAccount->save($this->data)) {//1stly, save the data into 'trans_accounts'
				$this->Session->setFlash('Only account updated.');
				
				$this->data['TransCompany']['id'] = $this->TransAccount->id;
				$this->TransCompany->create();
				if ($this->TransCompany->save($this->data)) {//2ndly, save the data into 'trans_companies'
					/*after the office saved, update the site_excluding data, then*/
					$exdone = true;
					if ($this->Auth->user('role') == 0) {//only when it's an admin
						$__sites = $this->data['SiteExcluding']['siteid'];
						if (is_array($__sites)) {
							$__sites = array_diff(array_keys($sites), $__sites);
						} else {
							$__sites = array_keys($sites);
						}
						$exdata = array();
						foreach ($__sites as $__site) {
							array_push(
								$exdata, 
								array(
									'companyid' => $this->data['TransCompany']['id'],
									'siteid' => $__site
								)
							);
						}
						$this->SiteExcluding->deleteAll(//since if no recs to del, it seems also return false, so we ignore it here
							array('companyid' => $this->data['TransCompany']['id'])
						);
						if (!empty($exdata)) {
							$exdone = ($this->SiteExcluding->saveAll($exdata) ? true : false);
						} else {
							$exdone = true;
						}
					}
					
					/*redirect to some page*/
					$this->Session->setFlash('Office "'
						. $this->data['TransAccount']['username'] . '" updated.'
						. ($exdone ? '' : '<br><i>(Site associating failed.)</i>')
					);
					if ($this->Auth->user('role') == 0) {// means an administrator
						$this->redirect(array('controller' => 'trans', 'action' => 'lstcompanies'));
					} else if ($this->Auth->user('role') == 1) {// means an office
						$this->redirect(array('controller' => 'trans', 'action' => 'index'));
					}
					$this->redirect(array('controller' => 'trans', 'action' => 'lstcompanies'));
				} else {
					$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
					//should add some codes here to delete the record that saved in 'trans_accounts' table before if failed
				}
			} else {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
			}			
		}
	}
	
	function updagent($id = null) {
		$this->layout = 'defaultlayout';
		
		/*prepare the companies for this view*/
		$cps = $this->TransViewCompany->find('list',
			array(
				'fields' => array('companyid', 'officename'),
				'order' => 'username4m'
			)
		);
		$this->set('cps', $cps);
		/*prepare the email of the current office*/
		if ($this->Auth->user('role') == 1) {
			$this->TransCompany->id = $this->Auth->user('id');
			$curcom = $this->TransCompany->read();
			$this->set(compact('curcom'));
		}
		/*prepare the countries for this view*/
		$cts = $this->TransCountry->find('list', array('fields' => array('TransCountry.abbr', 'TransCountry.fullname')));
		$this->set('cts', $cts);
		
		/*prepare associated sites data*/
		$exsites = $this->SiteExcluding->find('list',
			array(
				'fields' => array('id', 'siteid'),
				'conditions' => array('agentid' => $id)
			)
		);
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1)
			)
		);
		$exsites = array_unique($exsites);
		$exsites = array_flip($exsites);
		foreach ($exsites as $k => $v) {
			if (in_array($k, array_keys($sites))) {
				$exsites[$k] = $sites[$k];
			}
		}
		$this->set(compact('exsites'));
		$this->set(compact('sites'));
		
		$this->TransAccount->id = $id;
		$this->TransAgent->id = $id;
		if (empty($this->data)) {
			/*read the agent into the update page*/
			$account = $this->TransAccount->read();
			//$account['TransAccount']['password'] = '';
			//$account['TransAccount']['originalpwd'] = '';
			$account['TransAccount']['password'] = $account['TransAccount']['originalpwd'];
			$agent = $this->TransAgent->read();
			$this->data['TransAccount'] = $account['TransAccount'];
			$this->data['TransAgent'] = $agent['TransAgent'];
			$this->set('results', $this->data);
		} else {
			$agent = $this->TransAgent->read();
			$this->set('results', $this->data);
			/*check if the passwords match or empty or untrimed*/
			$originalpwd = $this->data['TransAccount']['originalpwd'];
			if (strlen(trim($originalpwd)) != strlen($originalpwd)) {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please remove any blank in front of or at the end of your password and try again.');
				return;
			}
			//if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != $this->Auth->password($this->data['TransAccount']['originalpwd'])) {
			if (empty($this->data['TransAccount']['originalpwd']) || $this->data['TransAccount']['password'] != md5($this->data['TransAccount']['originalpwd'])) {
				$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['originalpwd'] = '';
				$this->Session->setFlash('The passwords don\'t match to each other, please try again(and do not left it blank).');
				return;
			}
			
			/*validate the posted fields*/
			$this->TransAccount->set($this->data);
			$this->TransAgent->set($this->data);
			if (!$this->TransAccount->validates() || !$this->TransAgent->validates()) {
				//$this->data['TransAccount']['password'] = '';
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
				$this->Session->setFlash('Please notice the tips below the fields.');
				return;
			}
						
			/*actually save the posted data*/
			$this->TransAccount->create();
			$this->data['TransAccount']['username4m'] = __fillzero4m($this->data['TransAccount']['username']);
			if ($this->TransAccount->save($this->data)) {//1stly, save the data into 'trans_accounts'
				$this->Session->setFlash('Only account updated.');
				
				$this->TransAgent->create();
				if ($this->TransAgent->save($this->data)) {//2ndly, save the data into 'trans_agents'
					/*after agent saved, update the site_excluding data, then*/ 
					$exdone = true;
					if (in_array($this->Auth->user('role'), array(0, 1))) {//if it's an admin or an office
						$__sites = $this->data['SiteExcluding']['siteid']; 
						if (is_array($__sites)) { 
						  $__sites = array_diff(array_keys($sites), $__sites); 
						} else { 
						  $__sites = array_keys($sites); 
						} 
						$exdata = array(); 
						foreach ($__sites as $__site) { 
						  array_push( 
						    $exdata,  
						    array( 
						      'agentid' => $this->data['TransAgent']['id'], 
						      'siteid' => $__site 
						    ) 
						  ); 
						} 
						$this->SiteExcluding->deleteAll(//since if no recs to del, it seems also return false, so we ignore it here 
						  array('agentid' => $this->data['TransAgent']['id']) 
						); 
						if (!empty($exdata)) { 
						  $exdone = ($this->SiteExcluding->saveAll($exdata) ? true : false); 
						} else { 
						  $exdone = true; 
						} 
					}
					/*
					 * If the agent username is changed, then we should change the campaignid
					 * in agent_site_mappings with sites whoes campaign id rule is "__SAME__".
					 * 0.judge if the username is in agent_site_mappings.
					 * 1.set all the flags of old campaign ids to 0s.
					 * 2.insert the new one.
					 */
					//step 0
					$mpchgdone = true;
					$rs = $this->ViewMapping->find('first',
						array(
							'conditions' => array(
								'LOWER(campaignid)' => strtolower($this->data['TransAccount']['username'])
							)
						)
					);
					$__SAME__sites = $this->TransSite->find('list',
						array(
							'fields' => array('id', 'id'),
							'conditions' => array('id not' => array(1, 2, 5))//which means not CAM4,2HM or ADMINTEST
						)
					);
					if (empty($rs)) {
						//step 1
						$mpchgdone = $mpchgdone && $this->AgentSiteMapping->updateAll(
							array('flag' => 0),
							array(
								'agentid' => $this->data['TransAgent']['id'],
								'siteid' => $__SAME__sites
							)
						);
						//step 2
						foreach ($__SAME__sites as $site) {
							$data = array(
								'AgentSiteMapping' => array(
									'id' => null,
									'agentid' => $this->data['TransAgent']['id'],
									'siteid' => $site,
									'campaignid' => $this->data['TransAccount']['username']
								)
							);
							$this->AgentSiteMapping->create();
							if ($this->AgentSiteMapping->save($data)) {
								$mpchgdone = $mpchgdone && true;
							} else {
								$mpchgdone = $mpchgdone && false;
							}
						}
					}
					
					
					 
					/*redirect to some page*/ 
					$this->Session->setFlash('Agent "' 
					  . $this->data['TransAccount']['username'] . '" updated.' 
					  . ($exdone ? '' : '<br/><i>(Site associating failed.)</i>')
					  . ($mpchgdone ? '' : '<br/><i>(Mappings changing failed.)</i>')
					);
					if ($this->Auth->user('role') == 0) {// means an administrator
						$this->redirect(array('controller' => 'trans', 'action' => 'lstagents'));
					} else if ($this->Auth->user('role') == 1) {// means an office
						$this->redirect(
							array('controller' => 'trans', 'action' => 'lstagents',
								'id' => $this->Auth->user('id')
							)
						);
					} else if ($this->Auth->user('role') == 2) {// means an agent
						$this->redirect(array('controller' => 'trans', 'action' => 'index'));
					}
				} else {
					$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
					//should add some codes here to delete the record that saved in 'trans_accounts' table before if failed
				}
			} else {
				$this->data['TransAccount']['password'] = $this->data['TransAccount']['originalpwd'];
			}
		}
	}
		
	function lstcompanies($id = null) {
		$this->layout = 'defaultlayout';
		
		/*prepare for the searching part*/
		if (!empty($this->data)) {
			$searchfields = $this->data['TransViewCompany'];
			if (strlen($searchfields['username']) == 0 && empty($searchfields['username'])) {
				$conditions = array('1' => '1');
			} else {
				$conditions = array(
					'username like' => ('%' . $searchfields['username'] . '%')
				);
			}
		} else {
			if ($id == null || !is_numeric($id)) {
				if ($this->Session->check('conditions_com')) {
					$conditions = $this->Session->read('conditions_com');
				} else {
					$conditions = array('1' => '1');
				}
			} else {
				if ($id != -1) {
					$conditions = array('companyid' => $id);
				} else {//"-1" is specially for the administrator
					$conditions = array('1' => '1');
				}
			}
		}
		
		$this->Session->write('conditions_com', $conditions);
		
		$this->paginate = array(
			'TransViewCompany' => array(
				'limit' => $this->__limit,
				'conditions' => $conditions,
				'order' => 'regtime desc'
			)
		);
		
		$this->set('status', $this->TransAccount->status);
		$this->set('online', $this->TransAccount->online);
		$this->set('rs',
			$this->paginate('TransViewCompany')
		);
	}
	
	function lstagents($id = null) {
		$this->layout = 'defaultlayout';
		
		/*prepare for the searching part*/
		if (!empty($this->data)) {// if there are any POST data
			$conditions = array(
				'username like' => ('%' . trim($this->data['TransViewAgent']['username']) . '%'),
				'lower(ag1stname) like' => ('%' . strtolower(trim($this->data['TransViewAgent']['ag1stname'])) . '%'),
				'lower(aglastname) like' => ('%' . strtolower(trim($this->data['TransViewAgent']['aglastname'])) . '%'),
				'lower(email) like' => ('%' . strtolower(trim($this->data['TransViewAgent']['email'])) . '%')
			);
			if ($this->Auth->user('role') == 0) {
				$companyid = $this->data['TransCompany']['id'];
				if ($companyid != 0) {
					$conditions['companyid'] = array(-1, $companyid);
				}
			} else if ($this->Auth->user('role') == 1){
				$companyid = $this->data['TransViewAgent']['companyid'];
				$conditions['companyid'] = array(-2, $companyid);
			}
			$status = $this->data['TransViewAgent']['status'];
			if ($status != -1) {
				$conditions['status'] = $status;
			}
			$campaignid = trim($this->data['AgentSiteMapping']['campaignid']);
			if (!empty($campaignid)) {
				$ags = $this->AgentSiteMapping->find('list',
					array(
						'fields' => array('id', 'agentid'),
						'conditions' => array(
							'campaignid like' => ('%' . $campaignid . '%')
						)
					)
				);
				$ags = array_unique($ags);
				$conditions['id'] = $ags;
			}
			$exsite = $this->data['SiteExcluding']['siteid'];
			if ($exsite != -1) {
				$exags = $this->SiteExcluding->find('list',
					array(
						'fields' => array('id', 'agentid'),
						'conditions' => array('siteid' => $exsite)
					)
				);
				$exags = array_unique($exags);
				if (array_key_exists('id', $conditions)) {
					$conditions['id'] = array_intersect($conditions['id'], $exags);
				} else {
					$conditions['id'] = $exags;
				}
			}
		} else {
			if ($id == null || !is_numeric($id)) {
				if ($this->Session->check('conditions_ag')) {
					$conditions = $this->Session->read('conditions_ag');
				} else {
					$conditions = array('1' => '1');
				}
			} else {
				if ($id != -1) {
					$arr = array(-3, $id);//!!!important!!!we must do this to ensure that the "order by" in MYSQL could work normally but not being misunderstanding 
					$conditions = array('companyid' => $arr);
				} else {//"-1" is specially for the administrator
					$conditions = array('1' => '1');
				}
			}
			
		}

		$this->Session->write('conditions_ag', $conditions);
		
		$this->paginate = array(
			'TransViewAgent' => array(
				'conditions' => $conditions,
				'limit' => $this->__limit,
				'order' => 'username4m'
			)
		);
		
		$coms = array();
		if ($this->Auth->user('role') == 0) {
			$coms = $this->TransCompany->find('list',
				array(
					'fields' => array('id', 'officename'),
					'order' => 'officename'
				)
			);
		}
		$coms = array('0' => 'All') + $coms;
		$this->set(compact('coms'));

		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1)
			)
		);
		$sites = array('-1' => '-----------------------') + $sites;
		$this->set(compact('sites'));
		
		$this->set('status', $this->TransAccount->status);
		$this->set('online', $this->TransAccount->online);
		$this->set('limit', $this->__limit);
		$this->set('rs',
			$this->paginate('TransViewAgent')
		);
	}
	
	function lstlogins($id = -1) {
		$this->layout = 'defaultlayout';
		
		$selcom = $selagent = 0;
		$startdate = date('Y-m-d', mktime (0,0,0,date("m"), date("d") - 6 ,date("Y")));
		$enddate = date('Y-m-d');
		
		if (!empty($this->data)) {
			$startdate = $this->data['ViewOnlineLog']['startdate'];
			$enddate = $this->data['ViewOnlineLog']['enddate'];
			$selcom = $this->data['Stats']['companyid'];
			$selagent = $this->data['ViewOnlineLog']['agentid'];
		} else {
			if ($id != -1) {
				$startdate = '0000-00-00';
				$enddate = date('Y-m-d', mktime (0,0,0,date("m"), date("d") ,date("Y") + 1));
				$selagent = $id;
			} else {
				if (array_key_exists('page', $this->passedArgs)) {
					if ($this->Session->check('conditions_loginlogs')) {
						$conds = $this->Session->read('conditions_loginlogs');
						$startdate = $conds['startdate'];
						$enddate = $conds['enddate'];
						$selcom = $conds['selcom'];
						$selagent = $conds['selagent'];
					}
				}
			}
		}
		
		if ($this->Auth->user('role') == 1) {
			$selcom = $this->Auth->user('id');
		} else if ($this->Auth->user('role') == 2) {
			$selagent = $this->Auth->user('id');
			$rs = $this->TransAgent->find('first',
				array(
					'fields' => array('companyid'),
					'conditions' => array('id' => $selagent)
				)
			);
			$selcom = $rs['TransAgent']['companyid'];
		}
		
		$conditions = array('1' => '1');
		if ($this->Auth->user('role') == 1) {
			$conditions = array('id' => $this->Auth->user("id"));
		}
		$coms = $this->TransCompany->find('list',
			array(
				'fields' => array('id', 'officename'),
				'order' => 'officename',
				'conditions' => $conditions 
			)
		);
		if (count($coms) > 1) $coms = array('0' => 'All') + $coms;
		$ags = $this->TransViewAgent->find('list',
			array(
				'fields' => array('id', 'username'),
				'conditions' => array('companyid' => ($selcom == 0 ? array_keys($coms) : $selcom)),
				'order' => 'username4m'
			)
		);
		if (count($ags) > 1) $ags = array('0' => 'All') + $ags;
		$this->set(compact('coms'));
		$this->set(compact('ags'));
		
		$conds = array(
			'startdate' => $startdate, 'enddate' => $enddate,
			'selcom' => $selcom, 'selagent' => $selagent
		);
		$this->Session->write('conditions_loginlogs', $conds);
		
		$conditions = array(
			'accountid !=' => '2',//HARD CODE:NOT TO SHOW THE adminuser's logs
			'convert(intime, date) >=' => $startdate,
			'convert(outtime, date) <=' => $enddate
		);
		$comcond = $agentcond = array('1' => '1');
		if ($selcom != 0) {
			$comcond = array('accountid' => array(-1, $selcom));
			if ($selagent != 0) {
				$agentcond = array('accountid' => array(-1, $selagent));
			} else {
				$agentcond = array(
					'accountid' => $this->TransAgent->find('list',
						array(
							'fields' => array('id', 'id'),
							'conditions' => array('companyid' => $selcom)
						)
					)
				);
			}
			$conditions['OR'] = array($comcond, $agentcond);
		} else {
			if ($selagent != 0) {
				$agentcond = array('accountid' => array(-1, $selagent));
			}
			$conditions['AND'] = array($comcond, $agentcond);
		}
		
		if ($selcom != 0) $conditions['accountid'] = array(-1, $selcom);
		if ($selagent != 0) {
			if (array_key_exists("accountid", $conditions))
				array_push($conditions['accountid'], $selagent);
			else $conditions['accountid'] = array(-1, $selagent);
		} else {
			if (array_key_exists("accountid", $conditions)) {
				$conditions['accountid'] += array_keys($ags);
			}
		}
		
		$this->set(compact('startdate'));
		$this->set(compact('enddate'));
		$this->set(compact('selcom'));
		$this->set(compact('selagent'));
		
		$this->paginate = array(
			'ViewOnlineLog' => array(
				'conditions' => $conditions,
				'order' => 'intime desc',
				'limit' => $this->__limit
			)
		);
		$this->set('rs',
			$this->paginate('ViewOnlineLog')
		);
	}
	
	function activatem() {
		/*prepare the parameters*/
		$ids = null;
		if (array_key_exists('ids', $this->passedArgs)) {
			$ids = explode(',', $this->passedArgs['ids']);
		}
		$status = -1;
		if (array_key_exists('status', $this->passedArgs)) {
			$status = intval($this->passedArgs['status']);
		}
		$from = -1;
		if (array_key_exists('from', $this->passedArgs)) {
			$from = intval($this->passedArgs['from']);
		}
		if ($ids == null || $status == -1 || $from == -1) {
			$this->redirect(array('controller' => 'trans', 'action' => 'index'));
		}
		if ($status > 1 || $status < 0) {
			$this->redirect(array('controller' => 'trans', 'action' => 'index'));
		}
		$action = 'lstcompanies';
		if ($from == 1) $action = 'lstagents';
		
		/*update the field "status" of table trans_accounts*/
		if ($this->TransAccount->updateAll(array('status' => $status), array('id' => $ids))) {
			$this->Session->setFlash('The selected all have been ' . $this->TransAccount->status[$status] . '.');
		};
		
		$this->redirect(array('controller' => 'trans', 'action' => $action));
	}
	
	function requestchg() {
		$this->layout = 'defaultlayout';
		
		$data = $this->data;
		$content = "";
		if (!empty($data)) {
			if (array_key_exists('Requestchg', $data)) {
				/*try to send the request*/
				if ($data['Requestchg']['role'] == 2) {//means a request for changing an agent
					$sites = $this->TransSite->find('list',
						array(
							'fields' => array('id', 'abbr'),
							'conditions' => array('id' => $data['SiteExcluding']['siteid'])
						)
					);
					
					$content = "Request for:\n\n" 
						. "Office(*):" . $data['TransAgent']['companyshadow'] . "\n"
						. "First Name(*):" . $data['TransAgent']['ag1stname'] . "\n"
						. "Last Name(*):" . $data['TransAgent']['aglastname'] . "\n"
						. "Email(*):" . $data['TransAgent']['email'] . "\n"
						. "Username(*):" . $data['TransAccount']['username'] . "\n"
						. "Password(*):" . $data['TransAccount']['originalpwd'] . "\n"
						. "Street Name & Number:" . $data['TransAgent']['street'] . "\n"
						. "City:" . $data['TransAgent']['city'] . "\n"
						. "State & Zip:" . $data['TransAgent']['state'] . "\n"
						. "Country(*):" . $data['TransAgent']['country'] . "\n"
						. "Instant Messenger Contact(*):" . $data['TransAgent']['im'] . "\n"
						. "Cell NO.(*):" . $data['TransAgent']['cellphone'] . "\n"
						. "Associated Sites:" . implode(",", $sites) . "\n";
						
					/*send the message*/
					$issent = false;
					if ($data['Requestchg']['type'] == 'reg') {//means an adding request
						$subject = "Request For New Agent";
						$content .= "\n\n(Request from office manager \"" . $data['Requestchg']['offiname']
							. "\", with email address \"" . $data['Requestchg']['from'] . "\").";
						$issent = $this->__sendemail($subject, $content);
					} else if ($data['Requestchg']['type'] == 'upd') {//means an updating request
						$subject = "Request For Updating Agent";
						$content .= "\n\n(Request from office manager \"" . $data['Requestchg']['offiname']
							. "\", with email address \"" . $data['Requestchg']['from'] . "\").";
						$issent = $this->__sendemail($subject, $content);
					}
					
					if ($issent) {
						$this->Session->setFlash('Request sent, please wait for reply.');
					} else {
						//$this->Session->setFlash($issent);
						$this->Session->setFlash('Failed to send request, please contact your administrator.');
					}
				}
			}
		}
		$this->set('data', $this->data);
		$this->set(compact("content"));
	}
	
	function addchatlogs() {
		$this->layout = 'defaultlayout';
		
		if ($this->Auth->user('role') != 2) {// if not an agent
			$this->Session->setFlash('Only agent could submit chat logs.');
			$this->redirect(array('controller' => 'trans', 'action' => 'index'));	
		}
		if (!empty($this->data)) {
			$this->ChatLog->create();
			$submittime = new DateTime("now", new DateTimeZone($this->__svrtz));
			$this->data['ChatLog'] = array_merge(
				$this->data['ChatLog'],
				array('submittime' => $submittime->format("Y-m-d H:i:s"))
			);
			if ($this->ChatLog->save($this->data)) {
				$r = $this->TransViewAgent->find('first',
					array(
						'conditions' => array('id' => $this->data['ChatLog']['agentid'])
					)
				);
				$r0 = $this->TransSite->find('first',
					array(
						'conditions' => array('id' => $this->data['ChatLog']['siteid'])
					)
				);
				$subject = 'Office:' . $r['TransViewAgent']['officename']
					. ' Agent:' . $r['TransViewAgent']['username']
					. ' -- Chat Log';
				$content = "Client:" . $this->data['ChatLog']['clientusername'] . "\n"
					. "Conversation:(" . $r0['TransSite']['sitename'] . ")\n"
					. $this->data['ChatLog']['conversation'] . "\n"
					. "-" . $submittime->format("Y-m-d H:i:s") . " " . $this->__svrtz;
				$mailto = ''
					//. strtolower($r['TransViewAgent']['officename']) . '_qa@cleanchattersinc.com';
					. 'support@americanweblink.com';
				$fmsg = '';
				if ($this->__sendemail(
						$subject, $content,
						'support@americanweblink.com',
						$mailto
					) != true) {
					$fmsg = '(Failed to email out.<0>)';
				};
				if ($this->__sendemail(
						$subject, $content,
						'support@americanweblink.com',
						//'qa@cleanchattersinc.com'
						'support@americanweblink.com'
					) != true) {
					$fmsg = '(Failed to email out.<1>)';
				};
				$this->Session->setFlash('Chat log submitted.' . $fmsg);
				$this->redirect(array('controller' => 'trans', 'action' => 'lstchatlogs'));
			}
		}
		
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename'),
				'conditions' => array('status' => 1)
			)
		);
		$this->set(compact("sites"));
	}
	
	function lstchatlogs($id = -1) {
		$this->layout = 'defaultlayout';
		
		$startdate = date('Y-m-d', mktime (0,0,0,date("m"), date("d") - 6 ,date("Y")));
		$enddate = date('Y-m-d');
		$selcom = $selagent = $selsite = 0;
		if ($this->Auth->user('role') == 1) {// means an office
			$selcom = $this->Auth->user('id');
			if ($id != -1) {
				$selagent = $id;
			}
		} else if ($this->Auth->user('role') == 2) {// means an agent
			$selagent = $this->Auth->user('id');
			$rs = $this->TransAgent->find('first',
				array(
					'fields' => array('companyid'),
					'conditions' => array('id' => $selagent)
				)
			);
			if (empty($rs)) $selcom = 0;
			else $selcom = $rs['TransAgent']['companyid'];
		} else if ($this->Auth->user('role') == 0) {// means an admin
			if ($id != -1) {
				$selagent = $id;
			}
		}
		
		if (!empty($this->data)) {
			$startdate = $this->data['ViewChatLog']['startdate'];
			$enddate = $this->data['ViewChatLog']['enddate'];
			$selcom = $this->data['Stats']['companyid'];
			$selagent = $this->data['ViewChatLog']['agentid'];
			$selsite = $this->data['ViewChatLog']['siteid'];
		} else {
			if ($id != -1) {
				if ($this->Session->check('conditions_chatlogs')) {
					$conds = $this->Session->read('conditions_chatlogs');
					$startdate = $conds['startdate'];
					$enddate = $conds['enddate'];
					$selcom = $conds['selcom'];
					$selagent = $conds['selagent'];
					$selsite = $conds['selsite'];
				} else {
					$conditions = '0 = 1';
				}
			} else {
				if (array_key_exists('page', $this->passedArgs)) {
					if ($this->Session->check('conditions_chatlogs')) {
						$conds = $this->Session->read('conditions_chatlogs');
						$startdate = $conds['startdate'];
						$enddate = $conds['enddate'];
						$selcom = $conds['selcom'];
						$selagent = $conds['selagent'];
						$selsite = $conds['selsite'];
					}
				}
			}
		}
		
		$conditions = array('1' => '1');
		if ($this->Auth->user('role') == 1) {
			$conditions = array('id' => $this->Auth->user('id'));
		}
		$coms = $this->TransCompany->find('list',
			array(
				'fields' => array('id', 'officename'),
				'order' => 'officename',
				'conditions' => $conditions 
			)
		);
		if (count($coms) > 1) $coms = array('0' => 'All') + $coms;
		$ags = $this->TransViewAgent->find('list',
			array(
				'fields' => array('id', 'username'),
				'conditions' => array('companyid' => ($selcom == 0 ? array_keys($coms) : $selcom)),
				'order' => 'username4m'
			)
		);
		if (count($ags) > 1) $ags = array('0' => 'All') + $ags;
		$sites = $this->TransSite->find('list',
			array(
				'fields' => array('id', 'sitename')
			)
		);
		$sites = array('0' => 'All') + $sites;
		$this->set(compact('coms'));
		$this->set(compact('ags'));
		$this->set(compact('sites'));
		
		$conds = array(
			'startdate' => $startdate, 'enddate' => $enddate,
			'selcom' => $selcom, 'selagent' => $selagent, 'selsite' => $selsite
		);
		$this->Session->write('conditions_chatlogs', $conds);
		
		$conditions = array(
			'convert(submittime, date) >=' => $startdate,
			'convert(submittime, date) <=' => $enddate
		);
		if ($selcom != 0) $conditions['companyid'] = array(-1, $selcom);
		if ($this->Auth->user('role') == 1) {
			$conditions['companyid'] = array(-1, $this->Auth->user('id'));
		}
		if ($selagent != 0) $conditions['agentid'] = array(-1, $selagent);
		if ($this->Auth->user('role') == 2) {
			$conditions['agentid'] = array(-1, $this->Auth->user('id'));
		}
		if ($selsite != 0) $conditions['siteid'] = array(-1, $selsite);
		
		$this->set(compact('startdate'));
		$this->set(compact('enddate'));
		$this->set(compact('selcom'));
		$this->set(compact('selagent'));
		$this->set(compact('selsite'));
		
		$this->paginate = array(
			'ViewChatLog' => array(
				'conditions' => $conditions,
				'order' => 'username4m',
				'limit' => $this->__limit
			)
		);
		$this->set('rs', $this->paginate('ViewChatLog'));
	}
		
	function __go($siteid, $typeid, $url, $referer, $agentid, $clicktime, $linkid = null) {
		//if (__isblocked(__getclientip())) {
		if (false) {
			$this->Session->setFlash('Sorry, you\'re not allowed to check the link.');
			$this->render('/trans/go');
			return;
		} else {
			/*log this click*/
			$this->data['TransClickout']['linkid'] = $linkid;
			$this->data['TransClickout']['agentid'] = $agentid;
			$this->data['TransClickout']['clicktime'] = $clicktime;
			$this->data['TransClickout']['fromip'] = __getclientip();
			$this->data['TransClickout']['siteid'] = $siteid;
			$this->data['TransClickout']['typeid'] = $typeid;
			$this->data['TransClickout']['url'] = $url;
			$this->data['TransClickout']['referer'] = $referer;
			$this->TransClickout->save($this->data);
			/*and redirect to the real url*/
			$this->redirect($url);
		}
	}
	
	function golink() {
		$to = '';
		if (array_key_exists('to', $this->passedArgs)) {
			$to = $this->passedArgs['to'];
		}
		
		$ids = explode(',', __codec($to, 'D'));//$ids[0] will be the link id, and $ids[1] should be the agent id
		$linkid = $ids[0];
		$agentid = $ids[1];
		$this->TransLink->id = $linkid;
		$this->data = $this->TransLink->read();
		$siteid = $this->data['TransLink']['siteid'];
		
		$r = $this->TransSite->find('first',
			array(
				'conditions' => array(
					'id' => $siteid
				)
			)
		);
		if (empty($r)) {
			$this->Session->setFlash("No such site!");
			$this->render('/trans/go', 'errorlayout');
			return;
		} else {
			/*
			if ($r['TransSite']['status'] == 0) {
				$this->Session->setFlash("The site has been suspended for now, contact you aministrator for further informations, please.");
				$this->render('/trans/go', 'errorlayout');
				return;
			}
			*/
		}
		$r = $this->TransAgent->find('first',
			array(
				'conditions' => array('id' => $agentid)
			)
		);
		if (empty($r)) {
			$this->Session->setFlash("No such agent!");
			$this->render('/trans/go', 'errorlayout');
			return;
		}
		/*
		$companyid = $r['TransAgent']['companyid'];
		$r = $this->SiteExcluding->find('first',
			array(
				'conditions' => array(
					'OR' => array(
						array('siteid' => $siteid, 'agentid' => $agentid),
						array('siteid' => $siteid, 'companyid' => $companyid)
					)
				)
			)
		);
		if (!empty($r)) {
			$this->Session->setFlash("Sorry, you are not allowed to the link for the moment.");
			$this->render('/trans/go', 'errorlayout');
			return;
		}
		*/
		
		$this->__go($siteid, -1, '', $this->data['TransLink']['url'], '', $agentid, date('Y-m-d H:i:s'), $linkid);
	}
	
	function go() {
		$this->layout = 'errorlayout';
		
		/*
		 * get referer URL and parse it
		 */
		$referer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
		$purl = parse_url($referer);
		$phost = '';
		if ($purl !== false && array_key_exists("host", $purl)) {
			$phost = $purl['host'];
		}
		
		if (count($this->passedArgs) != 3) {//if there are illegal args passed
			$this->Session->setFlash("Undefined link, please try another one.");
			return;
		}
		$siteid = $this->passedArgs[0];
		$typeid = $this->passedArgs[1];
		$agentusername = trim($this->passedArgs[2]);
		
		$r = $this->TransSite->find('first',
			array(
				'conditions' => array(
					'id' => $siteid
				)
			)
		);
		if (empty($r)) {
			$this->Session->setFlash("No such site!");
			return;
		} else {
			/*
			if ($r['TransSite']['status'] == 0) {
				$this->Session->setFlash("The site has been suspended for now, contact you aministrator for further informations, please.");
				return;
			}
			*/
		}
		
		$r = $this->TransViewAgent->find('first',
			array(
				'conditions' => array(
					'lower(username)' => strtolower($agentusername)
				)
			)
		);
		if (empty($r)) {
			$this->Session->setFlash("Agent does not exist, please try again.");
			return;
		}
		$agentid = $r['TransViewAgent']['id'];
		/*
		$companyid = $r['TransViewAgent']['companyid'];		
		$r = $this->SiteExcluding->find('first',
			array(
				'conditions' => array(
					'OR' => array(
						array('siteid' => $siteid, 'agentid' => $agentid),
						array('siteid' => $siteid, 'companyid' => $companyid)
					)
				)
			)
		);
		if (!empty($r)) {
			$this->Session->setFlash("Sorry, you are not allowed to the link for the moment.");
			return;
		}
		*/
		
		$r = $this->TransType->find('first',
			array(
				'conditions' => array(
					'siteid' => $siteid,
					'id' => $typeid
				)
			)
		);
		if (empty($r)) {
			$this->Session->setFlash("Undefined type, please try another one.");
			return;
		}
		$url = $r['TransType']['url'];
		$searchstr = '__CAM__';
		if (strpos($url, $searchstr) === false) {
			$this->Session->setFlash("Undefined replace string, please try another one.");
			return;
		}
		$r = $this->AgentSiteMapping->find('first',
			array(
				'conditions' => array(
					'siteid' => $siteid,
					'agentid' => $agentid
				)
			)
		);
		if (empty($r)) {//no campaign id found
			$this->Session->setFlash("Undefined campaign, please try another one.");
			return;
		}
		$campaignid = $r['AgentSiteMapping']['campaignid'];
		$url = str_replace($searchstr, $campaignid, $url);
		
		//$this->Session->setFlash($url);//for debug
		$this->__go($siteid, $typeid, $url, $phost, $agentid, date('Y-m-d H:i:s'));
	}
	
	function upload() {
		Configure::write('debug', '0');
		$this->layout = 'errorlayout';
		
		if (!array_key_exists('CKEditorFuncNum', $_GET)) {
			$this->set('script', __makeuploadhtml(1, '', 'Illegal request!'));
			exit();
		}
		$fn = $_GET['CKEditorFuncNum'];
		/*
		 * see if there is any file uploads
		*/
		if (!isset($HTTP_POST_FILES) && !isset($_FILES)) {
			$this->set('script', __makeuploadhtml(1, '', "No file uploads."));
			exit();
		}
		
		if(!isset($_FILES) && isset($HTTP_POST_FILES)) {
			$_FILES = $HTTP_POST_FILES;
		}
		
		$files = array_values($_FILES);
		$_file = $files[0];
		
		$filename = "/var/www/awl/uploads/images/" . $_file['name'];
		if (!copy($_file['tmp_name'], $filename)) {
			$this->set('script', __mkuploadhtml($fn, '', 'Failed to upload.'));
		} else {
			$this->set('script', __mkuploadhtml($fn, '/../awl/uploads/images/' . $_file['name'], 'Image uploaded.'));
		}
	}
}
