<?php
/*
 * The left&right relation of following matchs is like:
 * left column is from feed xml file, right column is the fields name of table trans_stats. 
 * "Uniques" -> "uniques"
 * "Signups" -> "signups"
 * "Sales" -> "sales_number"
 * "Refunds" -> "chargebacks"
 * 
 * http://www.pimpmansion.com/user/main.php?xml=1&username=aquablue@cleanchattersinc.com&password=cxriscross611
 * http://www.pimpmansion.com/user/view_campaigns.php?xml=1&username=aquablue@cleanchattersinc.com&password=cxriscross611
 * http://www.pimpmansion.com/user/view_details.php?xml=1&username=aquablue@cleanchattersinc.com&password=cxriscross611&campaign_id=27478
 * 
 * No parameters for the driver
 */
include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

date_default_timezone_set("Europe/London");

function getValueByName($nodelist, $name) {
	for ($i = 0; $i < $nodelist->length; $i++) {
		if ($nodelist->item($i)->nodeName == $name) {
			return $nodelist->item($i)->nodeValue;
		}
	}
}

function getItemByType($nodelist, $type) {
	for ($i = 0; $i < $nodelist->length; $i++) {
		if ($nodelist->item($i)->getAttribute("type") == $type) {
			return $nodelist->item($i);
		}
	}
}

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);
//echo $abbr . "\n";

/*check out if the $type is avaliable*/
if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01'
	exit("Only 1 parameter needed like '2010-05-01'.\n");
}
$date = $argv[1];
$type = "";
if ($date == date("Y-m-d")) {
	$type = "today";
}
if ($date == date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')))) {
	$type = "yesterday";
}
if (empty($type)) {
	exit(sprintf("The date \"%s\" is neither today nor yesterday.\n", $date));
}

$zconn = new zmysqlConn;
/*find out the typeids and siteid from db by "hornm" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
//echo print_r($typeids, true) . $siteid . "\n";
if (count($typeids) != 1) {
	exit(sprintf("The site with abbreviation \"%s\" should have 1 type at least.\n", $abbr));
}

/*try to read stats data*/
$srclink = 'http://www.pimpmansion.com/user/view_campaigns.php?xml=1'
	. '&username=aquablue@cleanchattersinc.com&password=cxriscross611';
$doc = new DOMDocument();
if (!$doc->load($srclink)) {
	exit(sprintf("Failed to read stats data from %s.\n", $srclink));
}

/*
 * find out if there is any data in trans_stats when trxtime equals to $date,
 * if there are, then remove them.
 * */
$sql = sprintf('delete from trans_stats where convert(trxtime, date) = "%s" and siteid = %d',
	$date, $siteid
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
echo mysql_affected_rows() . " row(s) deleted.\n";

/*dealing with xml data and try to put values into table trans_stats*/
$campaigns = $doc->getElementsByTagName("campaign");
$i = $j = 0;
foreach ($campaigns as $campaign) {
	$campaignid = $campaign->getAttribute("id");
	/*see if agent exists whitch matches the campaign id*/
	$sql = sprintf(
		'select agentid from agent_site_mappings'
		. ' where campaignid = "%s"',
		$campaignid
	);
	$rs = mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	if (mysql_num_rows($rs) == 0) continue;
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		$agentid = $row['agentid'];
		
		$items = $campaign->getElementsByTagName("item");
		$item0 = getItemByType($items, "Uniques");
		$item1 = getItemByType($items, "Signups");
		$item2 = getItemByType($items, "Sales");
		$item3 = getItemByType($items, "Refunds");
		$sql = sprintf(
			'insert into trans_stats'
			. ' (agentid, siteid, typeid, raws, uniques, chargebacks, signups, sales_number, trxtime)'
			. ' values (%d, %d, %d, 0, %d, %d, %d, %d, "%s")',
			$agentid, $siteid, $typeids[0],
			getValueByName($item0->childNodes, $type),
			getValueByName($item3->childNodes, $type),
			getValueByName($item1->childNodes, $type),
			getValueByName($item2->childNodes, $type),
			$date
		);
		//echo $sql . "\n";
		$rs = mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$i++;
		$j += mysql_affected_rows();
		/*
		echo "Campaign Id " . $campaignid . ":"
			. $item0->getAttribute("type")
			. "(" . getValueByName($item0->childNodes, "today") . "," . getValueByName($item0->childNodes, "yesterday") . ")"
			. "/"
			. $item1->getAttribute("type")
			. "(" . getValueByName($item1->childNodes, "today") . "," . getValueByName($item1->childNodes, "yesterday") . ")"
			. "/"
			. $item2->getAttribute("type")
			. "(" . getValueByName($item2->childNodes, "today") . "," . getValueByName($item2->childNodes, "yesterday") . ")"
			. "/"
			. $item3->getAttribute("type")
			. "(" . getValueByName($item3->childNodes, "today") . "," . getValueByName($item3->childNodes, "yesterday") . ")"
			. "\n";
		*/
	} else {
		echo $sql . "\n";
		exit("One campaign should only matche one agent, something wrong here.\n");
	}
}

echo $j . "(/" . $i . ") row(s) inserted.\n";
echo "Processing " . $date . " OK\n";
//echo $srclink . "\n";
?>
