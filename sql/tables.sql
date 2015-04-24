CREATE TABLE `user_settings` (
  `user` varchar(100) NOT NULL,
  `bot_type` varchar(100) NOT NULL,
  `setting` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`user`,`bot_type`,`setting`),
  KEY `user_bot` (`user`,`bot_type`)
);

CREATE TABLE `storage` (
  `responder` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`responder`,`key`)
);