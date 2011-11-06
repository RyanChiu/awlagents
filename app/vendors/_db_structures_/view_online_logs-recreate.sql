DROP VIEW `view_online_logs`;
CREATE VIEW `view_online_logs` 
AS select 
    `a`.`id` AS `id`,`a`.`accountid` AS `accountid`,`b`.`username` AS `username`,
    `b`.`username4m` AS `username4m`,`a`.`intime` AS `intime`,`a`.`inip` as `inip`,
    `a`.`outtime` AS `outtime` 
from (`online_logs` `a` join `trans_accounts` `b`) 
where (`a`.`accountid` = `b`.`id`);