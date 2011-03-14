<?php
include 'zmysqlConn.class.php';
include 'magpierss/rss_fetch.inc';
include 'extrakits.inc.php';

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);
//echo $abbr . "\n";

/*dealing with rss data*/
$rss = array();
if (($argc - 1) == 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	$date = __get_remote_date($argv[1]);
	if ($date === false) {
		exit("It only take one parameter, like '2009-09-01,12:34:56'.\n");
	}
	
	$rsssrclink = 'http://webmasters.cams4pleasure.com/custom/xmlstatus.php'
		. '?username=bvlgari2010&password=dreaming'
		. '&start=' . $date
		. '&end=' . $date;
	/*
	$link = $rsssrclink . '&program=5';
	$rss_5 = fetch_rss($link);
	if ($rss_5 === false) {
		exit(sprintf("Failed to read stats data from %s.\n", $link));
	}
	*/
	$rss_5 = array();
	/*
	$link = $rsssrclink . '&program=7';
	$rss_7 = fetch_rss($link);
	if ($rss_7 === false) {
		exit(sprintf("Failed to read stats data from %s.\n", $link));
	}
	*/
	$rss_7 = array();
	$link = $rsssrclink . '&program=8';
	$rss_8 = fetch_rss($link);
	if ($rss_8 === false) {
		$mailinfo = __phpmail("maintainer.cci@gmail.com",
			"CAM4 STATS GETTING ERROR (program=8), REPORT WITH DATE: " . date('Y-m-d H:i:s'),
			"<b>FROM WEB02</b><br><b>--ERROR REPORT</b><br>"
		);
		echo $mailinfo . "\n";
		exit(sprintf("Failed to read stats data from %s.\n", $link));
	}
	$link = $rsssrclink . '&program=10';
	$rss_10 = fetch_rss($link);
	if ($rss_10 === false) {
		$mailinfo = __phpmail("maintainer.cci@gmail.com",
			"CAM4 STATS GETTING ERROR (program=10), REPORT WITH DATE: " . date('Y-m-d H:i:s'),
			"<b>FROM WEB02</b><br><b>--ERROR REPORT</b><br>"
		);
		echo $mailinfo . "\n";
		exit(sprintf("Failed to read stats data from %s.\n", $link));
	}
	array_push($rss, $rss_5);
	array_push($rss, $rss_7);
	array_push($rss, $rss_8);
	array_push($rss, $rss_10);
} else {
	echo "Only 1 parameter allowed, like '2010-03-01,12:34:56'.\n";
	exit();
}

$zconn = new zmysqlConn;
/*find out the typeids and siteid from db by "cam4" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
//echo print_r($typeids, true) . $siteid . "\n";
if (count($typeids) != 4) {
	exit(sprintf("The site with abbreviation \"%s\" should have 4 types.\n", $abbr));
}

/*dealing with db data*/
/*1,find out if there is any data in trans_stats when trxtime equals to argv[1], if there are, then remove them.*/
$sql = sprintf('delete from trans_stats where convert(trxtime, date) = "%s" and siteid = %d and typeid in (%s)',
	$date, $siteid, implode(",", $typeids)
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
echo mysql_affected_rows() . " row(s) deleted.\n";
/*2,insert the data*/
if (!empty($rss)) {
	$j = 0;
	for ($i = 0; $i < count($rss); $i++) {
		if ($i < 2) continue;
		foreach ($rss[$i]->items as $item) {
			/*
			 * see if the campaign name (actually the agent name here) exists in our db.
			 * if it does, then insert the stats
			 * */
			$sql = sprintf(
				'select * from trans_view_agents where lower(username) = "%s"',
				strtolower($item['campaign'])
			);
			$rs = mysql_query($sql, $zconn->dblink)
				or die ("Something wrong with: " . mysql_error());
			if (mysql_num_rows($rs) > 0) {
				$row = mysql_fetch_assoc($rs);
				$sql = sprintf('insert into trans_stats'
					. ' (agentid, raws, uniques, chargebacks, sales_number, typeid, siteid, trxtime)'
					. ' values (%d, %d, %d, %d, %d, %d, %d, "%s")',
					//$row['id'], $item['raw'], $item['uniques'], $item['chargebacks'], $item['sales_number'], $i == 0 ? 1 : 2, 1, $date);
					$row['id'], $item['raw'], $item['uniques'], $item['chargebacks'],
					$item['sales_number'], $typeids[$i], $siteid, $date);
				//echo $sql . "\n";
				$rs = mysql_query($sql, $zconn->dblink)
					or die ("Something wrong with: " . mysql_error());
				$j++;
			}
		}
	}
	echo $j . " row(s) inserted.\n";
	echo "Processing " . $date . " OK\n";
}
?>
