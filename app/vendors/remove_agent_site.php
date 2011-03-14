<?php
/*
 * The script takes 2 parameters:
 * the 1st one is "agent username" (case insensitive),
 * and the 2nd one is "site id".
 * The command line is like this: php remove_agent_site.php dd02 2.
 * If an agent has no data or zero totals in trans_stats at some site,
 * we should take the campaign asigned for him/her back (remove it from 
 * agent_site_mappings).
 */
include 'zmysqlConn.class.php';

//exit:if parameters are wrong
if (($argc - 1) != 2) {
	exit("It must take 2 parameters (one for \"agent username\", one for \"site id\"), like \n\""
		. basename($argv[0])
		. " agent0 2\".\nplease try again.\n");
}
$agusername = $argv[1];
$siteid = $argv[2];
$zconn = new zmysqlConn();
$sql = sprintf(
	"select id from trans_accounts"
	. " where LOWER(username) = '%s'",
	strtolower($agusername)
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
//exit:if no such agent exist
if (mysql_num_rows($rs) <= 0) {
	exit("No such agent whoes username is " . $agusername . ".\n");
}
$row = mysql_fetch_assoc($rs);
$agentid = $row['id'];
$sql = sprintf(	"select * from agent_site_mappings where agentid = %d and siteid = %d",
	$agentid, $siteid
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
//exit:if no campaign asigned to the agent
if (mysql_num_rows($rs) <= 0) {
	exit("No campaign(s) asigned for the agent at all.\n");
}
$sql = sprintf(	"select distinct agentid from trans_stats where agentid = %d and siteid = %d",
	$agentid, $siteid
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
//exit:if there are no useful data for the agent
if (mysql_num_rows($rs) > 0) {
	$sql = sprintf(
		"select sum("
		. "	case when isnull(raws) then 0 else raws end + "
		. "	case when isnull(uniques) then 0 else uniques end + "
		. "	case when isnull(chargebacks) then 0 else chargebacks end + " 
		. "	case when isnull(signups) then 0 else signups end + "
		. "	case when isnull(frauds) then 0 else frauds end + "
		. "	case when isnull(sales_number) then 0 else sales_number end"
		. " ) as totals"
		. " from trans_stats"
		. " where agentid = %d and siteid = %d"
		. " group by agentid, siteid",
		$agentid, $siteid
	);
	$rs = mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	$row = mysql_fetch_assoc($rs);
	if ($row['totals'] > 0) {
		exit("The agent has some stats data at this site, no removes.\n");
	}
}

$sql = sprintf(
	"delete from agent_site_mappings"
	. " where agentid = %d and siteid = %d",
	$agentid, $siteid
);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
echo "Campaign removed(" . mysql_affected_rows() . ").\n";
?>