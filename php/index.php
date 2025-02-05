<?php

/**
 * @author    Tobias Reich
 * @copyright 2016 by Tobias Reich
 */

namespace PhotosManager;

use PhotosManager\Access\Admin;
use PhotosManager\Access\Guest;
use PhotosManager\Access\Installation;
use PhotosManager\Modules\Config;
use PhotosManager\Modules\Response;
use PhotosManager\Modules\Validator;

require __DIR__ . '/define.php';
require __DIR__ . '/autoload.php';

require __DIR__ . '/helpers/fastImageCopyResampled.php';
require __DIR__ . '/helpers/generateID.php';
require __DIR__ . '/helpers/getExtension.php';
require __DIR__ . '/helpers/getGPSCoordinate.php';
require __DIR__ . '/helpers/getGraphHeader.php';
require __DIR__ . '/helpers/getHashedString.php';
require __DIR__ . '/helpers/hasPermissions.php';
require __DIR__ . '/helpers/search.php';
require __DIR__ . '/helpers/filesUtils.php';

// Define the called function
if (isset($_POST['function'])) {
    $fn = $_POST['function'];
} else if (isset($_GET['function'])) {
    $fn = $_GET['function'];
} else {
    $fn = null;
}

// Check if a function has been specified
if (!empty($fn)) {

    // Start the session and set the default timezone
    session_start();
    date_default_timezone_set('UTC');

    // Validate parameters
    if (isset($_POST['albumIDs']) && Validator::isAlbumIDs($_POST['albumIDs']) === false) {
        Response::error('Wrong parameter type for albumIDs!');
    }

    if (isset($_POST['photoIDs']) && Validator::isPhotoIDs($_POST['photoIDs']) === false) {
        Response::error('Wrong parameter type for photoID!');
    }

    if (isset($_POST['albumID']) && Validator::isAlbumID($_POST['albumID']) == false) {
        Response::error('Wrong parameter type for albumID!');
    }

    if (isset($_POST['photoID']) && Validator::isPhotoID($_POST['photoID']) == false) {
        Response::error('Wrong parameter type for photoID!');
    }

    // Check if a configuration exists
    if (Config::exists() === false) {

        /**
         * Installation Access
         * Limited access to configure PhotosManager. Only available when the config.php file is missing.
         */

        Installation::init($fn);
        exit();

    }

    // Check if user is logged
    if ((isset($_SESSION['login']) && $_SESSION['login'] === true) &&
        (isset($_SESSION['identifier']))) {

        /**
         * Admin Access
         * Full access to PhotosManager. Only with correct password/session.
         */

        Admin::init($fn);
        exit();

    } else {

        /**
         * Guest Access
         * Access to view all public folders and photos in PhotosManager.
         */

        Guest::init($fn);
        exit();

    }

} else {

    Response::error('No API function specified!');

}
