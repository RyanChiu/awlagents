<?php
class zmysqlConn
{
	var $dblink;
	
	function zmysqlConn(
		$username = "marijana",
		$password = "cc123qwe",
		$host = "localhost"
	)
	{
		$this->dblink = mysql_connect($host, $username, $password)
			or dir ("Something wrong with: " . mysql_error());
		mysql_select_db("zcleancake", $this->dblink)
			or die ("Something wrong with: " . mysql_error());
	}
}
?>
