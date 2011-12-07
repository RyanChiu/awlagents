drop view trans_view_sites;
create view `trans_view_sites` as
select `s`.`id` AS `id`,`s`.`hostname` AS `hostname`,`s`.`sitename` AS `sitename`,
concat(`s`.`sitename`, ' (', `s`.`type`, ')') as `sitenametype`,
`s`.`abbr` AS `abbr`,`s`.`srcdriver` AS `srcdriver`,`s`.`url` AS `url`,
`s`.`contactname` AS `contactname`,`s`.`email` AS `email`,`s`.`phonenums` AS `phonenums`,`s`.`type` AS `type`,`s`.`payouts` AS `payouts`,`s`.`notes` AS `notes`,`s`.`status` AS `status`,(select count(`trans_links`.`id`) AS `count(id)` 
from `trans_links` 
where (`trans_links`.`siteid` = `s`.`id`)) AS `linkstotal`,(select count(`trans_types`.`id`) AS `count(id)` from `trans_types` where (`trans_types`.`siteid` = `s`.`id`)) AS `typestotal` from `trans_sites` `s`;
