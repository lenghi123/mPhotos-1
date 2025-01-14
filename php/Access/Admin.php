<?php

namespace PhotosManager\Access;

use PhotosManager\Modules\Album;
use PhotosManager\Modules\Albums;
use PhotosManager\Modules\Import;
use PhotosManager\Modules\Photo;
use PhotosManager\Modules\Response;
use PhotosManager\Modules\Session;
use PhotosManager\Modules\Settings;
use PhotosManager\Modules\Slide;
use PhotosManager\Modules\Validator;

final class Admin extends Access
{

    public static function init($fn)
    {

        switch ($fn) {

            // Slide functions
            case 'Slide::getSlides':self::getSlidesAction();
                break;
            case 'Slide::setPosition':self::setPositionSlideshowAction();
                break;
            case 'Slide::getSlide':self::getSlideAction();
                break;
            case 'Slide::add':self::addToSlideshowAction();
                break;
            case 'Slide::delete':self::deleteSlideAction();
                break;
            case 'Slide::setNote':self::setSlideNoteAction();
                break;

            // Albums functions
            case 'Albums::get':self::getAlbumsAction();
                break;

            // Album functions
            case 'Album::get':self::getAlbumAction();
                break;
            case 'Album::getPublic':self::checkAlbumAccessAction();
                break;
            case 'Album::add':self::addAlbumAction();
                break;
            case 'Album::setTitle':self::setAlbumTitleAction();
                break;
            case 'Album::setDescription':self::setAlbumDescriptionAction();
                break;
            case 'Album::setPublic':self::setAlbumPublicAction();
                break;
            case 'Album::setPosition':self::setPositionAction();
                break;
            case 'Album::delete':self::deleteAlbumAction();
                break;
            case 'Album::merge':self::mergeAlbumsAction();
                break;
            case 'Album::addFolder':self::addFolderAction();
                break;
            case 'Album::setFolderTitle':self::setFolderTitleAction();
                break;
            case 'Album::deleteFolder':self::deleteFolderAction();
                break;
            case 'Album::getFolders':self::getFoldersAction();
                break;
            case 'Album::import':self::importAlbumAction();
                break;

            // Photo functions
            case 'Photo::get':self::getPhotoAction();
                break;
            case 'Photo::setTitle':self::setPhotoTitleAction();
                break;
            case 'Photo::setDescription':self::setPhotoDescriptionAction();
                break;
            case 'Photo::setStar':self::setPhotoStarAction();
                break;
            case 'Photo::setPublic':self::setPhotoPublicAction();
                break;
            case 'Photo::setAlbum':self::setPhotoAlbumAction();
                break;
            case 'Photo::setTags':self::setPhotoTagsAction();
                break;
            case 'Photo::duplicate':self::duplicatePhotoAction();
                break;
            case 'Photo::delete':self::deletePhotoAction();
                break;

            // Add functions
            case 'Photo::add':self::uploadAction();
                break;
            case 'Import::url':self::importUrlAction();
                break;
            case 'Import::server':self::importServerAction();
                break;

            // Search functions
            case 'search':self::searchAction();
                break;

            // Session functions
            case 'Session::init':self::initAction();
                break;
            case 'Session::login':self::loginAction();
                break;
            case 'Session::logout':self::logoutAction();
                break;

            // Settings functions
            case 'Settings::setLogin':self::setLoginAction();
                break;
            case 'Settings::setSorting':self::setSortingAction();
                break;
            case 'Settings::setDropboxKey':self::setDropboxKeyAction();
                break;

            // $_GET functions
            case 'Album::getArchive':self::getAlbumArchiveAction();
                break;
            case 'Photo::getArchive':self::getPhotoArchiveAction();
                break;

        }

        self::fnNotFound();

    }

    // Slides functions
    private static function getSlidesAction()
    {

        Validator::required(isset($_POST['albumID']), __METHOD__);

        $slide = new Slide();
        Response::json($slide->getSlides($_POST['albumID']));

    }

    private static function setPositionSlideshowAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['slideOrder'], $_POST['folders']), __METHOD__);

        $slide = new Slide();
        Response::json($slide->setPosition());

    }

    private static function getSlideAction()
    {

        Validator::required(isset($_POST['slideID']), isset($_POST['albumID']), __METHOD__);

        $slide = new Slide();
        Response::json($slide->getSlide($_POST['slideID'], $_POST['albumID']));

    }

    private static function addToSlideshowAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['photoID']), __METHOD__);
        if (!isset($_POST['folderID'])) {
            $_POST['folderID'] = null;
        }

        $slide = new Slide();
        Response::json($slide->add($_POST['albumID'], $_POST['photoID'], $_POST['folderID']));

    }

    private static function deleteSlideAction()
    {

        Validator::required(isset($_POST['slideIDs']), __METHOD__);

        $slide = new Slide();
        Response::json($slide->delete($_POST['slideIDs']));

    }

    private static function setSlideNoteAction()
    {

        Validator::required(isset($_POST['slideID']), isset($_POST['note']), __METHOD__);

        $slide = new Slide();
        Response::json($slide->setNote($_POST['slideID'], $_POST['note']));

    }

    // Albums functions

    private static function getAlbumsAction()
    {

        $albums = new Albums();
        Response::json($albums->get(false));

    }

    // Album functions

    private static function getAlbumAction()
    {
        Validator::required(isset($_POST['albumID']), __METHOD__);

        $album = new Album($_POST['albumID']);

        if ($_POST['albumID'] === 's' || $_SESSION['identifier'] === $album->getIdentifier()) {

            Response::json($album->get());

        } else if ($album->getPublic() === true) {

            // Album public
            if ($album->checkPassword($_POST['password']) === true) {
                Response::json($album->get());
            } else {
                Response::warning('Input password!');
            }

        } else {

            // Album private
            Response::warning('Album private!');

        }
    }

    private static function checkAlbumAccessAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['password']), __METHOD__);

        $album = new Album($_POST['albumID']);

        if ($album->getPublic() === true) {

            // Album public
            if ($album->checkPassword($_POST['password']) === true) {
                Response::json(true);
            } else {
                Response::json(false);
            }

        } else {

            // Album private
            Response::json(false);

        }

    }

    private static function addAlbumAction()
    {

        Validator::required(isset($_POST['title']), __METHOD__);

        $album = new Album(null);
        Response::json($album->add($_POST['title']), JSON_NUMERIC_CHECK);

    }

    private static function setAlbumTitleAction()
    {

        Validator::required(isset($_POST['albumIDs'], $_POST['title']), __METHOD__);

        $album = new Album($_POST['albumIDs']);
        Response::json($album->setTitle($_POST['title']));

    }

    private static function setAlbumDescriptionAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['description']), __METHOD__);

        $album = new Album($_POST['albumID']);
        Response::json($album->setDescription($_POST['description']));

    }

    private static function setPositionAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['photoOrder']), __METHOD__);

        $album = new Album($_POST['albumID']);
        Response::json($album->setPosition());

    }

    private static function setAlbumPublicAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['password'], $_POST['visible'], $_POST['downloadable']), __METHOD__);

        $album = new Album($_POST['albumID']);
        Response::json($album->setPublic($_POST['public'], $_POST['password'], $_POST['visible'], $_POST['downloadable']));

    }

    private static function deleteAlbumAction()
    {

        Validator::required(isset($_POST['albumIDs']), __METHOD__);

        $album = new Album($_POST['albumIDs']);
        Response::json($album->delete());

    }

    private static function mergeAlbumsAction()
    {

        Validator::required(isset($_POST['albumIDs']), __METHOD__);
        $album = new Album($_POST['albumIDs']);
        Response::json($album->merge());

    }

    private static function addFolderAction()
    {

        Validator::required(isset($_POST['title'], $_POST['albumID']), __METHOD__);
        // Validator::required(isset($_POST['parent_folder']), __METHOD__);
        if (!isset($_POST['parent_folder'])) {
            $_POST['parent_folder'] = null;
        }

        $album = new Album($_POST['albumID']);
        Response::json($album->addFolder($_POST['title'], $_POST['parent_folder']), JSON_NUMERIC_CHECK);

    }

    private static function setFolderTitleAction()
    {

        Validator::required(isset($_POST['title'], $_POST['albumID'], $_POST['folderID']), __METHOD__);
        $album = new Album($_POST['albumID']);
        Response::json($album->setFolderTitle($_POST['folderID'], $_POST['title']), JSON_NUMERIC_CHECK);

    }

    private static function deleteFolderAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['folderIDs']), __METHOD__);
        $album = new Album($_POST['albumID']);
        Response::json($album->deleteFolder($_POST['folderIDs']), JSON_NUMERIC_CHECK);

    }

    private static function getFoldersAction()
    {

        Validator::required(isset($_POST['albumID']), __METHOD__);

        $album = new Album($_POST['albumID']);
        Response::json($album->getFolders());

    }

    private static function importAlbumAction() {

        Validator::required(isset($_FILES), __METHOD__);

        $album = new Album(null);
        Response::json($album->import($_FILES), JSON_NUMERIC_CHECK);

    }



    // Photo functions

    private static function getPhotoAction()
    {

        Validator::required(isset($_POST['photoID'], $_POST['albumID']), __METHOD__);

        $photo = new Photo($_POST['photoID']);
        Response::json($photo->get($_POST['albumID']));

    }

    private static function setPhotoTitleAction()
    {

        Validator::required(isset($_POST['photoIDs'], $_POST['title']), __METHOD__);

        $photo = new Photo($_POST['photoIDs']);
        Response::json($photo->setTitle($_POST['title']));

    }

    private static function setPhotoDescriptionAction()
    {

        Validator::required(isset($_POST['photoID'], $_POST['description']), __METHOD__);

        $photo = new Photo($_POST['photoID']);
        Response::json($photo->setDescription($_POST['description']));

    }

    private static function setPhotoStarAction()
    {

        Validator::required(isset($_POST['photoIDs']), __METHOD__);

        $photo = new Photo($_POST['photoIDs']);
        Response::json($photo->setStar());

    }

    private static function setPhotoPublicAction()
    {

        Validator::required(isset($_POST['photoID']), __METHOD__);

        $photo = new Photo($_POST['photoID']);
        Response::json($photo->setPublic());

    }

    private static function setPhotoAlbumAction()
    {

        Validator::required(isset($_POST['photoIDs'], $_POST['albumID']), __METHOD__);

        $photo = new Photo($_POST['photoIDs']);
        Response::json($photo->setAlbum($_POST['albumID']));

    }

    private static function setPhotoTagsAction()
    {

        Validator::required(isset($_POST['photoIDs'], $_POST['tags']), __METHOD__);

        $photo = new Photo($_POST['photoIDs']);
        Response::json($photo->setTags($_POST['tags']));

    }

    private static function duplicatePhotoAction()
    {

        Validator::required(isset($_POST['photoIDs']), __METHOD__);

        $photo = new Photo($_POST['photoIDs']);
        Response::json($photo->duplicate());

    }

    private static function deletePhotoAction()
    {

        Validator::required(isset($_POST['photoIDs']), __METHOD__);

        $photo = new Photo($_POST['photoIDs']);
        Response::json($photo->delete());

    }

    // Add functions

    private static function uploadAction()
    {

        Validator::required(isset($_FILES, $_POST['albumID']), __METHOD__);

        $photo = new Photo(null);
        Response::json($photo->add($_FILES, $_POST['albumID']), JSON_NUMERIC_CHECK);

    }

    private static function importUrlAction()
    {

        Validator::required(isset($_POST['url'], $_POST['albumID']), __METHOD__);

        $import = new Import();
        Response::json($import->url($_POST['url'], $_POST['albumID']));

    }

    private static function importServerAction()
    {

        Validator::required(isset($_POST['albumID'], $_POST['path']), __METHOD__);

        $import = new Import();
        Response::json($import->server($_POST['path'], $_POST['albumID']));

    }

    // Search functions

    private static function searchAction()
    {

        Validator::required(isset($_POST['term']), __METHOD__);

        Response::json(search($_POST['term']));

    }

    // Session functions

    private static function initAction()
    {

        $session = new Session();
        Response::json($session->init(false));

    }

    private static function loginAction()
    {

        Validator::required(isset($_POST['user'], $_POST['password']), __METHOD__);

        $session = new Session();
        Response::json($session->login($_POST['user'], $_POST['password']));

    }

    private static function logoutAction()
    {

        $session = new Session();
        Response::json($session->logout());

    }

    // Settings functions

    private static function setLoginAction()
    {

        Validator::required(isset($_POST['username'], $_POST['password']), __METHOD__);

        if (isset($_POST['oldPassword']) === false) {
            $_POST['oldPassword'] = '';
        }

        Response::json(Settings::setLogin($_POST['oldPassword'], $_POST['username'], $_POST['password']));

    }

    private static function setSortingAction()
    {

        Validator::required(isset($_POST['typeAlbums'], $_POST['orderAlbums'], $_POST['typePhotos'], $_POST['orderPhotos']), __METHOD__);

        $sA = Settings::setSortingAlbums($_POST['typeAlbums'], $_POST['orderAlbums']);
        $sP = Settings::setSortingPhotos($_POST['typePhotos'], $_POST['orderPhotos']);

        if ($sA === true && $sP === true) {
            Response::json(true);
        } else {
            Response::json(false);
        }

    }

    private static function setDropboxKeyAction()
    {

        Validator::required(isset($_POST['key']), __METHOD__);

        Response::json(Settings::setDropboxKey($_POST['key']));

    }

    // Get functions

    private static function getAlbumArchiveAction()
    {

        Validator::required(isset($_GET['albumID']), __METHOD__);

        $album = new Album($_GET['albumID']);
        $album->getArchive();

    }

    private static function getPhotoArchiveAction()
    {

        Validator::required(isset($_GET['photoID']), __METHOD__);

        $photo = new Photo($_GET['photoID']);
        $photo->getArchive();

    }

}
