<?php
/*
 * The left&right relation of following matchs is like:
 * left column is from feed xml file, right column is the fields name of table trans_stats. 
 * "Uniques" -> "uniques"
 * "Signups" -> "signups"
 * "Sales" -> "sales_number"
 * "Refunds" -> "chargebacks"
 */
include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

function getValueByName($nodelist, $name) {
	for ($i = 0; $i < $nodelist->length; $i++) {
		if ($nodelist->item($i)->nodeName == $name) {
			return $nodelist->item($i)->nodeValue;
		}
	}
	if ($i == $nodelist->length) {
		exit("Warning!!! Source XML file wrong!!! No value name " . $name . " exists.\n");
	}
}

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);
//echo $abbr . "\n";

/*check out if the $date is in right format*/
if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	exit("Only 1 parameter needed like '2010-05-01,12:34:56'.\n");
}

/*
 * the following line will make the whole script exit if date string format is wrong
 */
$date = __get_remote_date($argv[1], "Europe/London", -1);

$ymd = explode("-", $date);

/*find out the typeids and siteid from db by "hornm" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
$zconn = new zmysqlConn;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
//echo print_r($typeids, true) . $siteid . "\n";
if (count($typeids) != 1) {
	exit(sprintf("The site with abbreviation \"%s\" should have 1 type at least.\n", $abbr));
}

/*try to read stats data*/
$srclink = 'https://www.pimpmansion.com/user/view_details.php?xml=1'
	. '&key=2641a71dbd48a7dcd9634ddd13bbb2e1'
	. '&campaign_id=%s&form1_submit1=Show&form1_select2=%s&form1_select3=%s&form1_select4=%s&';
$sql = sprintf('select * from view_mappings where siteid = %d' , $siteid);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$i = $j = $k = $m = 0;
while ($row = mysql_fetch_assoc($rs)) {
	if (empty($row['campaignid'])) {
		echo "Warning!!! Agent " . $row['username'] . " has no campaigns!!!\n";
		continue;
	}
	$url = sprintf($srclink, $row['campaignid'], $ymd[2], $ymd[1], $ymd[0]);
	$doc = new DOMDocument();
	if (!$doc->load($url)) {
		$mailinfo = __phpmail("maintainer.cci@gmail.com",
			"HORNM STATS GETTING ERROR, REPORT WITH DATE: " . date('Y-m-d H:i:s'),
			"<b>FROM WEB02</b><br/><b>--ERROR REPORT</b><br/>"
		);
		exit(sprintf("Failed to read stats data.(%s)\n", $mailinfo));
	}
	$items = $doc->getElementsByTagName("item");
	$doc = null;
	$uniques = 0;
	$chargebacks = 0;
	$signups = 0;
	$sales = 0;
	foreach ($items as $item) {
		$uniques += getValueByName($item->childNodes, "visits");
		$chargebacks += getValueByName($item->childNodes, "refunds");//actually not useful any more
		$signups += getValueByName($item->childNodes, "signups");
		$sales += getValueByName($item->childNodes, "sales");
	}
	//echo $url . "\n";//for debug
	//echo $row['agentid'] . "|" . $row['campaignid'] .  ":$uniques, $chargebacks, $signups, $sales.\n";//for debug
	//continue;//for debug
	/*
	 * find out if there is any data in trans_stats where trxtime equals to $date,
	 * if there are, remove them.
	 * If all the stats data are "0", then ignore them.
	 * */
	$conditions = sprintf(
		' where convert(trxtime, date) = "%s" and siteid = %d and agentid = %d and typeid = %d',
		$date, $siteid, $row['agentid'], $typeids[0]
	);
	$sql = 'select * from trans_stats' . $conditions;
	$results = mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	if (mysql_num_rows($results) > 1) {
		exit("It should not be more than 1 rows of stats data for an agent in a day.\n");
	}
	$frauds = 0;
	if (!empty($results)) {
		$_row = mysql_fetch_assoc($results);
		$frauds = $_row['frauds'];
	}
	$sql = 'delete from trans_stats' . $conditions;
	//echo $sql . "\n";
	//continue;
	mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	$k += mysql_affected_rows();
	if ($uniques != 0 || $chargebacks != 0 || $signups != 0 || $sales != 0) {
		/*
		 * insert uniques etc. into table trans_stats
		 */
		$sql = sprintf(
			'insert into trans_stats'
			. ' (agentid, siteid, typeid, raws, uniques, frauds, chargebacks, signups, sales_number, trxtime)'
			. ' values (%d, %d, %d, 0, %d, %d, %d, %d, %d, "%s")',
			$row['agentid'], $siteid, $typeids[0],
			$uniques, $frauds, $chargebacks, $signups, $sales,
			$date
		);
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$i++;
		$j += mysql_affected_rows();
	} else {
		$m++;
	}
}
echo $k . " row(s) deleted. (" . $m . " row(s) ignored.)\n";
echo $j . "(/" . $i . ") row(s) inserted.\n";
echo "Processing " . $date . " OK\n";
?>
