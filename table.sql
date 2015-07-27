/* SYNTAX: mysql -useandb -pPASSWORD < FILE */
USE seandb;

/*\ 
|*| NOTE: None of these operations require any user input, beyond clicking
|*| the external map buttons; everything else is automatic.
|*|
|*| Operations:
|*|     Upload photo: 
|*|         Minimum required values:
|*|             user_id (generated from cookie)
|*|             x (generated from device)
|*|             y (generated from device)
|*|             checkmark (generated from device)
|*|             file_hash (generated from uploaded file)
|*|             file_extension (generated from uploaded file)
|*|     Click neighborhood (view photo):
|*|         Required values:
|*|             user_id (generated from cookie)
|*|             gnis
|*|             checkmark
|*|     Query for all photos:
|*|         Required values:
|*|             user_id
\*/

/* * * * * * * * * * * * * * QUERIES * * * * * * * * * * * * * * * * *
*   add a picture
        insert into media (user_id, x, y, checkmark, file_hash, file_extension) values ();
*   view a picture
        select * from media where user_id= and checkmark= and list=
*   view all picture
        select * from media where user_id=
*   delete a picture
        update media set status=0 where user_id= file_hash=
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

CREATE TABLE IF NOT EXISTS `geo_media` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL, /* cookie ID -> users table -> id */
    `list` int(11) DEFAULT 0, /* Users can have multiple maps, each with their own distinct photo sets */
    `x` DECIMAL(10, 7) NOT NULL,
    `y` DECIMAL(10, 7) NOT NULL,
    `scale` INT DEFAULT 1,
    `status` INT DEFAULT 1, /* 1 = visible to user, 0 is soft-deleted. */
    `gnis` INT DEFAULT 2411786, /* 2411786 = San Francisco! http://geonames.usgs.gov/apex/f?p=gnispq:3:::NO::P3_FID:2411786 */
    `checkmark` INT(11) NOT NULL, /* Neighborhood #, at least for now */
    `file_hash` VARCHAR(40) NOT NULL, /* MD5 is fine */
    `file_extension` VARCHAR(8) NOT NULL, /* no dot! */
    PRIMARY KEY `id` (`id`),
    KEY `user_id` (`user_id`),
    KEY `gnis` (`gnis`),
    KEY `checkmark` (`checkmark`),
    KEY `x` (`x`),
    KEY `y` (`y`)
) ENGINE=InnoDB;

/*\
|*| Operations:
|*|     Upload photo:
|*|         Minimum required values:
|*|             hash (generated from device)
|*|     Retrieve photo:
|*|         Minimum required values:
|*|             hash (generated from device)
\*/

/* * * * * * * * * * * * * * QUERIES * * * * * * * * * * * * * * * * *
*   Anything that requires a user_id
        select id where hash=
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

CREATE TABLE IF NOT EXISTS `geo_users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `reg_sec` INT(11) NOT NULL,
    `hash` VARCHAR(40) NOT NULL, /* Not md5; custom hashy thing, for now at least */
    `perms` INT DEFAULT 1, /* 1 = Can see their own media, but nobody else's. 0 = account disabled/deleted on their own. -1 = account deleted by admins. */
    `default_gnis` INT DEFAULT 2411786, /*2411786 = San Francisco! http://geonames.usgs.gov/apex/f?p=gnispq:3:::NO::P3_FID:2411786 */
    `contact_type` INT DEFAULT 1, /* 1 = guest */
    `contact_value` VARCHAR(256) DEFAULT NULL,
    PRIMARY KEY `cookie` (`reg_sec`, `hash`),
    KEY `id` (`id`)
) ENGINE=InnoDB;

/* TODO: Create `lists` table

/*\
|*|
\* /

/* * * * * * * * * * * * * * QUERIES * * * * * * * * * * * * * * * * *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /

CREATE TABLE IF NOT EXISTS `geo_lists` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL, /* cookie ID -> users table -> id * /
    `internal_id` int(11) DEFAULT 0, /* Contextual within the user's set of lists. Every user has a list of internal_id 0. * /
    `status` INT DEFAULT 1, /* 1 = visible to user, 0 is soft-deleted. * /
    PRIMARY KEY `id` (`id`),
    KEY `user_id` (`user_id`),
    KEY `internal_id` (`internal_id`)
) ENGINE=InnoDB;