<?php
/**
 * This driver read from a txt (such as hornm_campaigns.txt etc.) file which hold
 * all the campaign ids of one site and from another txt (such as hornm_campaigns
 * _exclude.txt etc.) file which hold excluded agent usernames (means no campaigns
 * will be asigned for them).
 * And then match the campaign ids into table agent_site_mappings with the active
 * agent who has no campaign ids at all(NO DELs).
 * And if txt file has its first line with "__SAME__", then we regard the agent
 * usernames in our table trans_view_agents are the campaign ids, and match them
 * into table agent_site_mappings. 
 * It takes 2 parameters, the 1st one is the file name hold those ids, and the
 * 2nd one is siteid.
 * 
 * 2010-05-23 17:11
 * altered with "don't delete anything in db but match and insert campaign id" stuff
 */
include 'zmysqlConn.class.php';

if (($argc - 1) == 2) {
	$path_parts = pathinfo($argv[0]);
	$fn = $path_parts["dirname"] . "/" . $argv[1];
	$siteid = $argv[2];
	$campaignids = array();//initialize the campaign ids from file
	if (file_exists($fn)) {
		$handle = fopen($fn, 'r');
		if ($handle) {
			/*read the campaign ids from the file into an array*/
			while (!feof($handle)) {
				$buf = fgets($handle);
				$buf = trim($buf);
				if (!empty($buf)) {
					array_push($campaignids, $buf);
				}
			}
		} else {
			exit(sprintf("Failed to open file \"%s\".\n", $fn));
		}
	} else {
		exit(sprintf("File \"%s\" does not exist.\n", $fn));
	}
	if (empty($campaignids)) {
		exit("No campaigns in file.\n");
	}
	$fn = $path_parts["dirname"] . "/" . basename($fn, ".txt") . "_exclude.txt";
	$exagents = array();
	if (file_exists($fn)) {
		$handle = fopen($fn, 'r');
		if ($handle) {
			/*read the excluded agents from the file into an array*/
			while (!feof($handle)) {
				$buf = fgets($handle);
				$buf = trim($buf);
				if (!empty($buf)) {
					array_push($exagents, $buf);
				}
			}
		} else {
			exit(sprintf("Failed to open file \"%s\".\n", $fn));
		}
	} else {
		echo sprintf("File \"%s\" does not exist.\n", $fn);
	}
	$zconn = new zmysqlConn();
	$buf = implode(',', $exagents);
	$buf = strtolower($buf);
	$exagents = explode(',', $buf);
	$gpexagents = array_chunk($exagents, 20);
	$exagents = array();
	foreach ($gpexagents as $gpexagent) {
		$sql = sprintf('select id from trans_accounts where LOWER(username) in ("'
			. implode('", "', $gpexagent)
			. '")'
		);
		$rs = mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		while ($row = mysql_fetch_assoc($rs)) {
			array_push($exagents, $row['id']);
		}
	}
	//exit(print_r($exagents, true));
	if ($campaignids[0] == "__SAME__") {
		/*insert agent username as campaign id into agent_site_mappings table.*/
		$sql = sprintf('insert into agent_site_mappings (siteid, agentid, campaignid)'
			. ' select %d as siteid, id as agentid, username as campaignid'
			. ' from trans_view_agents'
			. ' where id not in'
			/*
			 * replacing the following line with the next one in order to save the mess
			 * that the campaign id doesn't pair to agent username cause agent usernames
			 * have been changed.
			 */
			//. ' 	(select distinct agentid from agent_site_mappings where siteid = %d)',
			. ' 	(select distinct agentid from agent_site_mappings where siteid = %d and flag = 1)',
			$siteid, $siteid
		);
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		echo mysql_affected_rows() . " row(s) inserted.\n";
	} else {
		$sql = sprintf(
			'select agentid, campaignid from agent_site_mappings where siteid = %d'
			. ' union'
			. ' select id, "___" from trans_view_agents'
			. ' where id not in (select distinct agentid from agent_site_mappings where siteid = %d)'
			. ' 	and status = 1',
			$siteid, $siteid
		);//ATTENTION!!!!!! WE TAKE "___" INSTEAD OF "" IN CASE OF PREVENTING ARRAY_DIFF FUNCTION RETURN AN EMPTY ARRAY
		//echo $sql . "\n";
		$rs = mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$agent_campaignids = array();//initialize the agent & campaign ids from db
		while ($row = mysql_fetch_assoc($rs)) {
			/*read the campaign ids from the table into an array*/
			$agent_campaignids += array($row["agentid"] => $row["campaignid"]);
		}
		$leftids = array_diff($agent_campaignids, $campaignids);//find all campaign ids not in the file
		$rightids = array_diff($campaignids, $agent_campaignids);//find all campaign ids not in the table of db
		$rightids = array_values($rightids);
		
		/*asign the campaign id to the agent who does not have any campaign*/
		//echo "agent_campaignids:\n" . print_r($agent_campaignids, true); echo "\n\n";
		//echo "db not in file:\n" . print_r($leftids, true); echo "\n\n";
		//echo "file not in db:\n" . print_r($rightids, true); echo "\n\n";
		$i = 0;
		$sqls = array();
		foreach ($leftids as $k => $v) {
			if ($i >= count($rightids)) break;
			if (in_array($k, $exagents)) continue;//if the agent is excluded by txt file, then skip him/her
			if ($v == "___") {//if the value is empty, means it should be asigned a new campaign id (insert)
				$sql = sprintf('insert into agent_site_mappings (agentid, siteid, campaignid) values (%d, %d, "%s")',
					$k, $siteid, $rightids[$i]
				);
				array_push($sqls, $sql);
			}
			$i++;
		}
		$totalchgs = count($sqls);
		$realchgs = 0;
		//echo print_r($sqls, true) . "\n";
		$sqls = array_chunk($sqls, 1);
		for ($i = 0, $len = count($sqls); $i < $len; $i++) {
			$sql = implode(";\n", $sqls[$i]) . ";\n";
			mysql_query($sql, $zconn->dblink)
				or die ("Something wrong with: " . mysql_error());
			//echo $sql . "\n";
			$realchgs += mysql_affected_rows();
		}
		echo "" . $realchgs . "(/" . $totalchgs . ") row(s) affected.\n";
	}
} else {
	exit("It must take 2 parameters, like \"php commdrv_links.php xxx_campaigns.txt 2\".\nplease try again.\n");
}
?>
