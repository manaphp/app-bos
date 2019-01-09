/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`manabos` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `manabos`;

/*Table structure for table `bos_bucket` */

CREATE TABLE `bos_bucket` (
  `bucket_id` int(11) NOT NULL AUTO_INCREMENT,
  `bucket_name` varchar(64) CHARACTER SET ascii NOT NULL,
  `base_url` varchar(128) CHARACTER SET ascii NOT NULL,
  `access_key` char(32) CHARACTER SET ascii NOT NULL,
  `created_time` int(11) NOT NULL,
  PRIMARY KEY (`bucket_id`),
  UNIQUE KEY `bucket_name` (`bucket_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `bos_bucket` */

/*Table structure for table `bos_object` */

CREATE TABLE `bos_object` (
  `object_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(256) NOT NULL,
  `bucket_id` int(11) NOT NULL,
  `bucket_name` varchar(32) CHARACTER SET ascii NOT NULL,
  `original_name` varchar(128) NOT NULL,
  `mime_type` varchar(64) CHARACTER SET ascii NOT NULL,
  `extension` varchar(16) CHARACTER SET ascii NOT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `size` int(11) NOT NULL,
  `md5` char(32) CHARACTER SET ascii NOT NULL,
  `ip` char(15) CHARACTER SET ascii NOT NULL,
  `created_time` int(11) NOT NULL,
  PRIMARY KEY (`object_id`),
  KEY `bucket_name` (`bucket_name`),
  KEY `md5` (`md5`),
  KEY `extension` (`extension`),
  KEY `key` (`key`),
  KEY `bucket_id` (`bucket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `bos_object` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
