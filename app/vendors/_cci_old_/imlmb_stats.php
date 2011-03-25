<?php
/*
 * Only take one parameter which is like '2010-05-01,12:34:56'.
 * It will checkout all agents in agent_site_mappings table and
 * get the stats data from the link with "username", and put the
 * data into trans_stats.
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

if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01'
	exit("Only 1 parameter needed like '2010-05-01'.\n");
}

$date = __get_remote_date($argv[1], "Asia/Manila", -14);
if ($date === false) {
	exit("Illegal parameter, it should be like '2010-05-01,12:34:56'.\n");
}

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);

$zconn = new zmysqlConn;
/*find out the typeids and siteid from db by "iml" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
if (count($typeids) != 1) {
	exit(sprintf("The site with abbreviation \"%s\" should have 1 type at least.\n", $abbr));
}
if (empty($siteid)) {
	exit(sprintf("The site with abbreviation \"%s\" does not exist.\n", $abbr));
}
/*get all the agent usernames with the site in mappings*/
$sql = sprintf("select * from view_mappings where siteid = %d", $siteid);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$agents = array();
while ($row = mysql_fetch_assoc($rs)) {
	$agents += array($row['campaignid'] => $row['agentid']);
}
if (empty($agents)) {
	//#debug echo sprintf("The site with abbreviation \"%s\" does not have any campaign ids asigned for agents.\n", $abbr);
	exit(sprintf("The site with abbreviation \"%s\" does not have any campaign ids asigned for agents.\n", $abbr));
}
//#debug exit(print_r($agents, true) . print_r($typeids, true) . "\n" . $siteid . "\n");

/*try to read the stats from remote server*/
$url = sprintf(
	'http://affserver.globalmailer.com/125117797155/promostats167.asp'
	. '?RepPass=98dfsanl239sdfidsfkljsda'
	. '&FromDate=%s&ToDate=%s',
	$date, $date
);
//#debug echo $url . "\n";
$retimes = 0;
$xml = simplexml_load_file($url);
while ($xml === false) {
	$retimes++;
	sleep(30);
	$xml = simplexml_load_file($url);
	if ($retimes == 3) break;
}
	
if ($xml === false) {
	$mailinfo = 
	__phpmail("maintainer.cci@gmail.com",
		"XIER STATS GETTING ERROR, REPORT WITH DATE: " . date('Y-m-d H:i:s') . "(retried " . $retimes . " times)",
		"<b>FROM WEB02</b><br><b>--ERROR REPORT</b><br>"
	);
	exit(sprintf("Failed to read stats data.(%s)(%d times)\n", $mailinfo, $retimes));
}
$i = $j = $m = 0;
foreach ($xml as $key => $value) {
	/*# debug
	echo "promocode: " . $value->promocode . "\n";
	echo "clicks: " . $value->clicks . "\n";
	echo "free: " . $value->free . "\n";
	echo "signups: " . $value->signups . "\n\n";
	continue;
	*/
	if (in_array($value->promocode, array_keys($agents))) {//compare promocode as campaignid but not agent username
		//#debug echo $value->promocode . "," . $agents['' . $value->promocode] . ";\n"; continue;
		
		/*
		 * try to put stats data into db
		 * 0.see if there is any frauds data except 0 or null, if there is, remember it and save it back in step 2
		 * 1.delete the data already exist
		 * 2.insert the new data
		 */
		$frauds = 0;
		$conditions = sprintf('convert(trxtime, date) = "%s" and siteid = %d'
			. ' and typeid = %d and agentid = %d and campaignid = "%s"',
			$date, $siteid, $typeids[0], $agents['' . $value->promocode], '' . $value->promocode);
		$sql = 'select * from trans_stats where ' . $conditions;
		$result = mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
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
			. ' (agentid, campaignid, siteid, typeid, raws, uniques, chargebacks, signups, frauds, sales_number, trxtime)'
			. ' values (%d, "%s", %d, %d, %d, 0, 0, %d, %d, %d, "%s")',
			$agents['' . $value->promocode], '' . $value->promocode, $siteid, $typeids[0],
			$value->clicks, $value->free, $frauds, $value->signups,
			$date
		);
		//#debug echo $value->promocode . ", "; continue;
		//#debug echo $sql . "\n";
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
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
