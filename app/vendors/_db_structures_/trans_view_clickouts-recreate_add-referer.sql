DROP VIEW `trans_view_clickouts`;
CREATE VIEW `trans_view_clickouts` 
AS select 
    `c`.`id` AS `companyid`,`c`.`officename` AS `officename`,
    `a`.`agentid` AS `agentid`,`d`.`username` AS `username`,
    `a`.`clicktime` AS `clicktime`,`a`.`fromip` AS `fromip`,
    `a`.`referer`, `a`.`siteid` AS `siteid`,
    (select `trans_sites`.`sitename` AS `sitename` 
	    from `trans_sites` 
	    where (`trans_sites`.`id` = `a`.`siteid`)) AS `sitename`,
    `a`.`typeid` AS `typeid`,
    (select `trans_types`.`typename` AS `typename` 
        from `trans_types` 
        where (`trans_types`.`id` = `a`.`typeid`)) AS `typename`,
    `a`.`url` AS `url` from 
(((`trans_clickouts` `a` join `trans_agents` `b`) join `trans_companies` `c`) join `trans_accounts` `d`) where ((`a`.`agentid` = `b`.`id`) and (`b`.`id` = `d`.`id`) and (`b`.`companyid` = `c`.`id`));