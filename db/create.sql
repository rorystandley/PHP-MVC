CREATE TABLE `items` (
  `id` int(11) NOT NULL auto_increment,
  `item_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
);
 
INSERT INTO `items` VALUES(1, 'Get Milk');
INSERT INTO `items` VALUES(2, 'Wash Car');