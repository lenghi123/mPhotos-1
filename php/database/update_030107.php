<?php

/**
 * Update to version 3.1.7
 */

use PhotosManager\Modules\Database;
use PhotosManager\Modules\Response;

// Add position to photos
$query  = Database::prepare($connection, "SELECT `position` FROM `?` LIMIT 1", array(PHOTOS_MANAGER_TABLE_PHOTOS));
$result = Database::execute($connection, $query, 'update_030107', __LINE__);

if ($result===false) {

	$query  = Database::prepare($connection, "ALTER TABLE `?` ADD `position` TINYINT(1) NOT NULL", array(PHOTOS_MANAGER_TABLE_PHOTOS));
	$result = Database::execute($connection, $query, 'update_030107', __LINE__);

	if ($result===false) Response::error('Could not add position to database!');

}
// Set version
if (Database::setVersion($connection, '030107')===false) Response::error('Could not update version of database!');

?>