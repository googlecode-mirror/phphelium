CREATE TABLE IF NOT EXISTS `pages` (
    `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(100) DEFAULT NULL,
    `keywords` varchar(75) DEFAULT NULL,
    `description` varchar(150) DEFAULT NULL,
    `summary` varchar(150) DEFAULT NULL,
    `base` varchar(50) DEFAULT NULL,
    `uri` text NOT NULL,
    `controller` varchar(30) NOT NULL,
    `directive` varchar(30) DEFAULT NULL,
    `secure` tinyint(1) NOT NULL DEFAULT '0',
    `params` text,
    `bypass` tinyint(1) NOT NULL DEFAULT '0',
    `matching` tinyint(1) NOT NULL DEFAULT '0',
    `cache` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `email_queue` (
    `email_queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `recipient` varchar(100) NOT NULL,
    `sender` varchar(100) NOT NULL,
    `subject` varchar(250) NOT NULL,
    `content` text NOT NULL,
    `sent_date` datetime DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`email_queue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;