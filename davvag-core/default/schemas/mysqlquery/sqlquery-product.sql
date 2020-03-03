ALTER TABLE `s_localhost`.`products` 
ADD FULLTEXT INDEX `index2` (`name` ASC, `caption` ASC, `keywords` ASC);
