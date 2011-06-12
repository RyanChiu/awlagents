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
if (empty($siteid)) {
	exit(sprintf("The site with abbreviation \"%s\" does not exist.\n", $abbr));
}
if (count($typeids) != 1) {
	exit(sprintf("The site with abbreviation \"%s\" should have 1 type at least.\n", $abbr));
}
/*get all the campaign mappings of the site*/
$sql = sprintf("select * from view_mappings where siteid = %d", $siteid);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$agents = array();
while ($row = mysql_fetch_assoc($rs)) {
	$agents += array($row['campaignid'] => $row['agentid']);
}
if (empty($agents)) {
	exit(sprintf("The site with abbreviation \"%s\" does not have any campaign ids asigned for agents.\n", $abbr));
}
/*try to read stats data*/
//to get link, we used to use 34431 for campaign_id, and to get stats data, we used 34430, but now we just only use 37692 for both
$srclink = 'https://www.pimpmansion.com/user/keywords.php?xml=1'
	. '&key=2641a71dbd48a7dcd9634ddd13bbb2e1'
	. '&campaign_id=37692&form1_submit4=Show&form1_select1=custom_interval'
	. '&form1_select8=%s&form1_select9=%s&form1_select10=%s'
	. '&form1_select11=%s&form1_select12=%s&form1_select13=%s';
$url = sprintf($srclink, $ymd[2], $ymd[1], $ymd[0], $ymd[2], $ymd[1], $ymd[0]);
//echo "\n$url\n";//for debug
$retimes = 0;
$response = file_get_contents($url);
//var_dump($response);//for debug
//exit("\nend dumping xml streams.\n");//for debug
while ($response === false) {
	$retimes++;
	sleep(35);
	$response = file_get_contents($url);
	if ($retimes == 1) break;
}
if ($response === false) {
	$mailinfo = 
		__phpmail("maintainer.cci@gmail.com",
			"HMS STATS GETTING ERROR, REPORT WITH DATE: " . date('Y-m-d H:i:s') . "(retried " . $retimes . " times)",
			"<b>FROM WEB02</b><br><b>--ERROR REPORT</b><br>"
		);
	exit(sprintf("Failed to read stats data.(%s)(%d times)\n", $mailinfo, $retimes));
}
$xml = simplexml_load_string($response);
if ($xml === false) {
	exit(sprintf("\nFailed to parse stats data.\n"));
}
/*//for debug
foreach ($xml->children() as $item) {
	$attr = $item->attributes();
	echo $attr['value'] . " =>"
		. "\n" . $item->visits 
		. "\n" . $item->signups 
		. "\n" . $item->sales
		. "\n" . $item->recurring
		. "\n" . $item->refunds
		. "\n" . $item->commission
		. "\n";
}
exit("\nend printing xml.\n");
*/
$i = $j = $m = 0;
foreach ($xml->children() as $item) {
	$attr = $item->attributes();
	if (in_array($attr['value'], array_keys($agents))) {
		//echo $attr['value'], ', ' . $agents['' . $attr['value']] . "\n"; continue; //for debug
		/*
		 * try to put stats data into db
		 * 0.see if there is any frauds data except 0 or null, if there is, remember it and save it back in step 2
		 * 1.delete the data already exist
		 * 2.insert the new data
		 */
		$frauds = 0;
		$conditions = sprintf('convert(trxtime, date) = "%s" and siteid = %d'
			. ' and typeid = %d and agentid = %d and campaignid = "%s"',
			$date, $siteid, $typeids[0], $agents['' . $attr['value']], '' . $attr['value']);
		$sql = 'select * from trans_stats where ' . $conditions;
		$result = mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error() . "->1\n");
		if (mysql_num_rows($result) != 0) {
			if (mysql_num_rows($result) != 1) {
				exit("It should be only 1 row data by day.\n");
			}
			$row = mysql_fetch_assoc($result);
			$frauds = empty($row['frauds']) ? 0 : $row['frauds'];
		}
		
		$sql = 'delete from trans_stats where ' . $conditions;
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$m += mysql_affected_rows();
		
		$sql = sprintf(
			'insert into trans_stats'
			. ' (agentid, campaignid, siteid, typeid, raws, uniques, frauds, chargebacks, signups, sales_number, trxtime)'
			. ' values (%d, "%s", %d, %d, 0, %d, %d, %d, %d, %d, "%s")',
			$agents['' . $attr['value']], '' . $attr['value'], $siteid, $typeids[0],
			$item->visits, $frauds, $item->refunds, $item->signups, $item->sales,
			$date
		);
		//echo $sql . "\n"; continue;//for debug
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error() . "->2\n");
		$j += mysql_affected_rows();
		$i++;
	}
}
if ($i == 0) {
	echo "No stats data exist by now.\n";
}
echo $m . " row(s) deleted.\n";
echo $j . "(/" . $i . ") row(s) inserted.\n";
echo "retried " . $retimes . " times.\n";
echo "Processing " . $date . " OK\n";
?>
