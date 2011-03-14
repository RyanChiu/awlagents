<?php
/*
 * only works on "program=5 or = 7" conditions
 */

include 'zmysqlConn.class.php';
include 'magpierss/rss_fetch.inc';
include 'extrakits.inc.php';

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);
//echo $abbr . "\n";

/*dealing with rss data*/
$rss = array();
if (($argc - 1) == 1) {//if there is 1 parameter and it must mean a date like '2010-04-01'
	$rsssrclink = 'http://webmasters.cams4pleasure.com/custom/xmlstatus.php'
		. '?username=bvlgari2010&password=dreaming'
		. '&start=' . $argv[1]
		. '&end=' . $argv[1];
	$link = $rsssrclink . '&program=5';
	$rss_5 = fetch_rss($link);
	if ($rss_5 === false) {
		exit(sprintf("Failed to read stats data from %s.\n", $link));
	}
	$link = $rsssrclink . '&program=7';
	$rss_7 = fetch_rss($link);
	if ($rss_7 === false) {
		exit(sprintf("Failed to read stats data from %s.\n", $link));
	}
	array_push($rss, $rss_5);
	array_push($rss, $rss_7);
	//echo count($rss_5->items) . ',' . count($rss_7->items) . ',' . count($items);
} else {
	echo "Only 1 parameter allowed, like '2010-03-01'.\n";
	exit();
}

$zconn = new zmysqlConn;
/*find out the typeids and siteid from db by "cam4" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
//echo print_r($typeids, true) . $siteid . "\n";
if (count($typeids) != 2) {
	exit(sprintf("The site with abbreviation \"%s\" should have 2 types.\n", $abbr));
}

/*dealing with db data*/
/*1,find out if there is any data in trans_stats when trxtime equals to argv[1], if there are, then remove them.*/
$sql = sprintf('delete from trans_stats where convert(trxtime, date) = "%s" and siteid = %d',
	$argv[1], $siteid
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
echo mysql_affected_rows() . " row(s) deleted.\n";
/*2,insert the data*/
if (!empty($rss)) {
	$j = 0;
	for ($i = 0; $i < count($rss); $i++) {
		foreach ($rss[$i]->items as $item) {
			/*
			 * see if the campaign name (actually the agent name here) exists in our db.
			 * if it does, then insert the stats
			 * */
			$sql = sprintf('select * from trans_view_agents where lower(username) = "%s"', strtolower($item['campaign']));
			$rs = mysql_query($sql, $zconn->dblink)
				or die ("Something wrong with: " . mysql_error());
			if (mysql_num_rows($rs) > 0) {
				$row = mysql_fetch_assoc($rs);
				$sql = sprintf('insert into trans_stats (agentid, raws, uniques, chargebacks, sales_number, typeid, siteid, trxtime) values (%d, %d, %d, %d, %d, %d, %d, "%s")',
					//$row['id'], $item['raw'], $item['uniques'], $item['chargebacks'], $item['sales_number'], $i == 0 ? 1 : 2, 1, $argv[1]);
					$row['id'], $item['raw'], $item['uniques'], $item['chargebacks'], $item['sales_number'], $typeids[$i], $siteid, $argv[1]);
				//echo $sql . "\n";
				$rs = mysql_query($sql, $zconn->dblink)
					or die ("Something wrong with: " . mysql_error());
				$j++;
			}
		}
	}
	echo $j . " row(s) inserted.\n";
	echo "Processing " . $argv[1] . " OK\n";
}
?>
