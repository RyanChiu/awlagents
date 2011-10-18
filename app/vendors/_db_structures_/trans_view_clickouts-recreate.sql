DROP VIEW `trans_view_clickouts` ;
CREATE VIEW `trans_view_clickouts` AS 
SELECT `c`.`id` AS `companyid` , `c`.`officename` AS `officename` , `a`.`agentid` AS `agentid` , 
`d`.`username` AS `username` , `a`.`clicktime` AS `clicktime` , `a`.`fromip` AS `fromip` , 
a.siteid, (select sitename from trans_sites where id = a.siteid) as sitename, 
a.typeid, (select typename from trans_types where id = a.typeid) as typename, 
a.url
FROM `trans_clickouts` `a` , `trans_agents` `b` , `trans_companies` `c` , `trans_accounts` `d` 
WHERE `a`.`agentid` = `b`.`id`
AND `b`.`id` = `d`.`id`
AND `b`.`companyid` = `c`.`id`