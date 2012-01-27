ALTER TABLE `awlcake`.`trans_types` DROP INDEX `typename` ,
ADD UNIQUE `typename` ( `typename` , `siteid` );
