

CREATE USER 'fluid'@'localhost' IDENTIFIED BY 'fluidPa55';
GRANT USAGE ON *.* TO 'fluid'@'localhost' IDENTIFIED BY 'fluidPa55' 
WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;

CREATE DATABASE IF NOT EXISTS `fluid`;
GRANT ALL PRIVILEGES ON `fluid`.* TO 'fluid'@'localhost';

CREATE TABLE IF NOT EXISTS `tasks` (
`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Task ID',
  `name` varchar(255) NOT NULL COMMENT 'Task name',
  `description` text NOT NULL COMMENT 'Task description',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Enabled',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Task created at',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Task updated at'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Fluid Studios Task table';

ALTER TABLE `tasks` ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`);
ALTER TABLE `tasks` MODIFY `id` int(11) NOT NULL COMMENT 'Task ID';
ALTER TABLE `tasks` ADD UNIQUE(`name`);