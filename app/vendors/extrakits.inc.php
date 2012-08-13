<?php
	/*
	 * routines area
	 */
	date_default_timezone_set("Asia/Manila");

	/*
	 * functions area
	 */
	function __codec($string, $operation) {
		$codes = array(
			array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ',', ' '),
			array('6', '2', '0', 'a', 'c', '1', '3', '4', 'd', '5' ,'7', 'f')
		);
        if($operation=='D')
        {
        	$d = '';
        	for ($i = 0; $i < strlen($string); $i++) {
        		for ($j = 0; $j < count($codes[1]); $j++) {
        			if ($codes[1][$j] == $string[$i]) break;
        		}
        		if ($j == count($codes[1])) return 'err';
        		$d .= $codes[0][$j];
        	}
            return $d;
        }
        else
        {
        	$e = '';
        	for ($i = 0; $i < strlen($string); $i++) {
        		for ($j = 0; $j < count($codes[0]); $j++) {
        			if ($codes[0][$j] == $string[$i]) break;
        		}
        		if ($j == count($codes[0])) return 'err';
        		$e .= $codes[1][$j];
        	}
            return $e;
        }
    }
    
    function __getclientip() {
    	$onlineip = false;
		if(getenv('HTTP_CLIENT_IP')) { 
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR')) { 
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR')) { 
			$onlineip = getenv('REMOTE_ADDR');
		} else { 
			$onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
		}
		return $onlineip;
    }
    
   function __isblocked($ip, $fiplst = 'philippines') {
    	$handle = fopen(APP . 'vendors' . DS . $fiplst . '.txt', 'r');
    	while (!feof($handle)) {
    		$buf = fgets($handle);
    		$subnet = explode('/', $buf);
    		if (count($subnet) == 2) {
    			//echo '(ip:' . $subnet[0] . ', mask:' . long2ip(0xffffffff << 32 - $subnet[1]) . ')';
    			if (ip2long($ip) >> (32 - $subnet[1]) == ip2long($subnet[0]) >> (32 - $subnet[1])) return true;
    		}
    	}
    	fclose($handle);
    	return false;
    }
/*
function __isblocked($ip, $fiplst = 'philippines') {
        $url="http://208.76.89.61/isBlock.php?ip=$ip";
        $scrape_ch = curl_init();
        curl_setopt($scrape_ch, CURLOPT_URL, $url);
        curl_setopt($scrape_ch, CURLOPT_RETURNTRANSFER, true);
        
        $scrape = curl_exec( $scrape_ch );
        return "Y" == $scrape;
   }
*/
   
	function __fillzero4m($str, $forelen = 24, $afterlen = 24) {
		/*
		 * we get rid of characters followed "_" (and "_" itself)
		 * from $str here,in order to make the similar username
		 * be closely after sorting.
		 */
		$pos = strpos($str, "_");
		if ($pos !== false) {
			$str = substr($str, 0, $pos);
		}
		
		$str0 = $str;
		$str1 = "";
		for ($i = 0; $i < strlen($str); $i++) {
			if ($str{$i} >= '0' && $str{$i} <= '9') {
				$str0 = substr($str, 0, $i);
				$str1 = substr($str, $i, strlen($str) - $i);
				break;
			}
		}
		
		$str0 = $str0 . str_repeat("0", $forelen - strlen($str0));
		$str1 = str_repeat("0", $afterlen - strlen($str1)) . $str1;
		
		$str = substr($str0, 0, $forelen) . substr($str1, 0, $afterlen);
		
		return $str;
	}
	
	/*functions for stats drivers*/
	function __stats_get_abbr($argv0) {
		$path_parts = pathinfo($argv0);
		$basenames = explode("_", $path_parts['basename']);
		return $basenames[0];
	}
	
	function __stats_get_types_site(&$typeids, &$siteid, $abbr, $dblink) {
		/*find out the typeids and siteid from db by the abbreviation of the site*/
		$sql = sprintf(
			'select a.id as typeid, a.siteid from trans_types a, trans_sites b'
			. ' where a.siteid = b.id and b.abbr = "%s"'
			. ' order by a.id',
			$abbr
		);
		$rs = mysql_query($sql, $dblink)
			or die ("Something wrong with: " . mysql_error());
		$typeids = array();
		$siteid = null;
		while ($row = mysql_fetch_assoc($rs)) {
			array_push($typeids, $row['typeid']);
			$siteid = $row['siteid'];
		}
	}
	
	/*
	 * try to use phpmail class to send email
	 */
	function __phpmail($mailto = "maintainer.cci@gmail.com", $subject = "", $content = "") {
		require_once("Mail.php");
		$mailer = Mail::factory(
			"SMTP",
			array(
				'host' => "ssl://smtp.gmail.com",
				'port' => "465",
				'auth' => true,
				'username' => "agents.maintainer@gmail.com",
				'password' => "`1qaz2wsx"
			)
		);
		
		$a_headers['From'] = "agents.maintainer@gmail.com";
		$a_headers['To'] = $mailto;
		
		$a_headers['Subject'] = $subject;
		
		$res = $mailer->send($a_headers['To'], $a_headers, $content);
		if ($res) {
			$mailinfo = 'email sent.';
		} else {
			$mailinfo = $res->getMessage();
		}
		return $mailinfo;
	}
	
	/**
	 * get the local date of the stats servers
	 * parameters:
	 * origin_dt	the string present date, like 2010-05-01,12:34:56
	 * remote_tz	the time zone of the remote server, like "Europe/London"
	 * offset_h		the offset time in hours
	 * origin_tz	the time zone of the server which the origin_dt belongs to, like "America/New_York"
	 * islongf		if the return value should be as 2010-05-01 or 2010-05-01 12:00:01
	 */
	function __get_remote_date($origin_dt, $remote_tz = null, $offset_h = -1, $origin_tz = "America/New_York", $islongf = false) {
		$err = "Illegal parameter, it should be like '2010-05-01,12:34:56'.\n";
		if (strpos($origin_dt, ",") === false) {
			exit($err);
		}
		$datestr = trim(str_replace(",", " ", $origin_dt));
		if (strlen($datestr) != 19) {
			exit($err);
		}
		if (strtotime($datestr) == -1) {
			exit($err);
		}
		$arydt = explode(",", $origin_dt);
		$ymdhis = array();
		$ymdhis[0] = explode("-", $arydt[0]);
		if (count($ymdhis[0]) != 3) {
			exit($err);
		}
		$ymdhis[1] = explode(":", $arydt[1]);
		if (count($ymdhis[0]) != 3) {
			exit($err);
		}
		if ($remote_tz == null) {
			return $islongf ? $arydt[0] . " " . $arydt[1] : $arydt[0];
		}
		
		$_origin_dtz = new DateTimeZone($origin_tz);
		$_remote_dtz = new DateTimeZone($remote_tz);
		$_origin_dt = new DateTime("now", $_origin_dtz);
		$_remote_dt = new DateTime("now", $_remote_dtz);
		$offset = $_origin_dtz->getOffset($_origin_dt) - $_remote_dtz->getOffset($_remote_dt);
		$dt = date($islongf ? "Y-m-d H:i:s" : "Y-m-d",
			mktime(
				$ymdhis[1][0], $ymdhis[1][1], 
				$ymdhis[1][2] - $offset + ($offset_h * 3600), 
				$ymdhis[0][1], $ymdhis[0][2], $ymdhis[0][0])
		);
		return $dt;
	}
	
	/*
	 * for CKEditor, the file upload function module
	 */
	function __mkuploadhtml($fn,$fileurl,$message) 
	{ 
		$str = '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('
			. $fn
			. ', \''
			. $fileurl
			. '\', \''
			. $message
			. '\');</script>'; 
		return $str;
	}
	
	/*
	 * try to save a cookie forever
	 * when $cookievalue equals to null or is ignored, it'll try
	 * to reset the cookie named $cookiename for 1 year again if
	 * it exists and return the value of it, otherwise just will
	 * return null.
	 * when $cookievalue does not equal to null, it'll try to set
	 * the cookie named $cookiename the value of $cookievalue, and
	 * then return the value of $cookievalue, otherwise just will
	 * return null, too. 
	 */
	function __crucify_cookie($cookiename, $cookievalue = null) {
		if ($cookievalue == null) {
			if (isset($_COOKIE[$cookiename])) {
				setcookie(
					$cookiename,
					$_COOKIE[$cookiename], 
					time() + (60 * 60 * 24 * 365)// it seems that it could only be saved for 1 year
				);
				return $_COOKIE[$cookiename];
			}
			return null;
		} else {
			setcookie(
				$cookiename, 
				$cookievalue, 
				time() + (60 * 60 * 24 * 365)// it seems that it could only be saved for 1 year
			);
			if (isset($_COOKIE[$cookiename])) {
				return $_COOKIE[$cookiename];
			} else {
				return null;
			}
		}
	}
?>
