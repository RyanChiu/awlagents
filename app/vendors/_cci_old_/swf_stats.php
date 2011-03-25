<?php
/*
 * Only take one parameter which is like '2010-05-01'.
 * It will checkout all agents in agent_site_mappings table and
 * get the stats data from the link with "username", and put the
 * data into trans_stats.
 */
include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	exit("Only 1 parameter needed like '2010-05-01,12:34:56'.\n");
}

$date = __get_remote_date($argv[1]);
if ($date === false) {
	exit("Illegal parameter, it should be like '2010-05-01,12:34:56'.\n");
}
$ymd = explode("-", $date);

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);

$zconn = new zmysqlConn;
/*find out the typeids and siteid from db by "swf" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
/*find all the agents in db and try to get the stats data one by one*/
$sql = sprintf('select * from view_mappings where siteid = %d', $siteid);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$j = $m = $n = 0;
while ($row = mysql_fetch_assoc($rs)) {
	/*Try to user curl to get the data*/
	$url = sprintf(
		'http://www.sexywivesfinder.com/stats/stats-export.php?aff=%s&d1=%d&m1=%d&y1=%d&d2=%d&m2=%d&y2=%d',
		$row['campaignid'],
		$ymd[2], $ymd[1], $ymd[0], $ymd[2], $ymd[1], $ymd[0]
	);
	$scrape_ch = curl_init();
	curl_setopt($scrape_ch, CURLOPT_URL, $url);
	curl_setopt($scrape_ch, CURLOPT_USERPWD, "cleanchatters:LKMSAL91");
	curl_setopt($scrape_ch, CURLOPT_RETURNTRANSFER, true); 
	$scrape = curl_exec($scrape_ch);
	curl_close($scrape_ch);
	//echo $row['username'] . "\n" . $scrape . "\n";
	$lines = explode("\n", $scrape);
	if (count($lines) < 2) {
		$errmsg = sprintf("It should have 2 lines at least for agent %s.\n", $row['username']);
		echo ($errmsg);
		$mailinfo = __phpmail("maintainer.cci@gmail.com",
			"SWF STATS GETTING ERROR, REPORT WITH DATE: " . date('Y-m-d H:i:s'),
			"<b>FROM WEB02</b><br><br>" .
			"vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv" .
			str_replace("\n", "<br/>", $scrape) .
			"<br/>" . $errmsg . "<br/>" .
			"^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^" .
			"<br><br/><b>--ERROR REPORT</b><br/>"
		);
		echo $mailinfo . "\n";
		continue;
	}
	$k = explode(",", $lines[0]);
	for ($i = 0; $i < count($k); $i++) $k[$i] = trim($k[$i]);
	$v = explode(",", $lines[1]);
	for ($i = 0; $i < count($v); $i++) $v[$i] = trim($v[$i]);
	$stats = array_combine($k, $v);
	//echo print_r($stats, true);
	/*
	 * Try to put data into db.
	 * If all the stats data are "0", then ignore them.
	 * */
	$sql = sprintf(
		'delete from trans_stats where convert(trxtime, date) = "%s" and siteid = %d'
		. ' and typeid = %d and agentid = %d and campaignid = "%s"',
		$date, $siteid, $typeids[0], $row['agentid'], $row['campaignid']
	);
	mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	$m += mysql_affected_rows();
	if (!array_key_exists('Click', $stats)) {
		echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
		echo $scrape . "\n";
		echo "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<\n";
		echo "No 'Click' data exist!\n";
		exit();
	}
	if ($stats['Click'] != 0
		|| $stats['Unique Click'] != 0
		|| $stats['Refund'] != 0
		|| $stats['Trial'] != 0
		|| $stats['Fraud'] != 0
		|| $stats['Bronze'] != 0
	) {		
		$sql = sprintf(
			'insert into trans_stats (agentid, campaignid, raws, uniques, chargebacks, signups, frauds, sales_number, typeid, siteid, trxtime)'
			. ' values (%d, "%s", %d, %d, %d, %d, %d, %d, %d, %d, "%s")',
			$row['agentid'],
			$row['campaignid'],
			$stats['Click'],
			$stats['Unique Click'],
			$stats['Refund'],
			$stats['Trial'],
			$stats['Fraud'],
			$stats['Bronze'],
			$typeids[0], $siteid, $date
		);
		//echo $sql . "\n";
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$j += mysql_affected_rows();
	} else {
		$n++;
	}
}
echo $m . " row(s) deleted. (" . $n . " row(s) ignored.)\n";
echo $j . "(/" . mysql_num_rows($rs) * count($typeids) . ") row(s) inserted.\n";
echo "Processing " . $date . " OK\n";
?>
