<?php

namespace PhotosManager\Access;

use PhotosManager\Modules\Slide;
use PhotosManager\Modules\Album;
use PhotosManager\Modules\Albums;
use PhotosManager\Modules\Photo;
use PhotosManager\Modules\Response;
use PhotosManager\Modules\Session;
use PhotosManager\Modules\Validator;

final class Guest extends Access {

	public static function init($fn) {

		switch ($fn) {

            // Slide functions
            case 'Slide::getSlides':                self::getSlidesAction(); break;
            case 'Slide::getSlide':                 self::getSlideAction(); break;

            case 'Slide::setPosition':              self::setPositionSlideshowAction(); break;


			// Albums functions
			case 'Albums::get':       self::getAlbumsAction(); break;

			// Album functions
			case 'Album::get':        self::getAlbumAction(); break;
			case 'Album::getPublic':  self::checkAlbumAccessAction(); break;

            case 'Album::setPosition':self::setPositionAction(); break;

			// Photo functions
			case 'Photo::get':        self::getPhotoAction(); break;

			// Session functions
			case 'Session::init':     self::initAction(); break;
			case 'Session::login':    self::loginAction(); break;
			case 'Session::logout':   self::logoutAction(); break;

			// $_GET functions
			case 'Album::getArchive': self::getAlbumArchiveAction(); break;
			case 'Photo::getArchive': self::getPhotoArchiveAction(); break;

		}

		self::fnNotFound();

	}


    // Slides functions 
    private static function getSlidesAction() {

        Validator::required(isset($_POST['albumID']), __METHOD__);

		$slide = new Slide();
		Response::json($slide->getSlides($_POST['albumID']));

    }

    private static function getSlideAction() {

        Validator::required(isset($_POST['slideID']), isset($_POST['albumID']), __METHOD__);

		$slide = new Slide();
		Response::json($slide->getSlide($_POST['slideID'], $_POST['albumID']));

    }


    // Albums functions

	private static function getAlbumsAction() {

		$albums = new Albums();
		Response::json($albums->get(true));

	}

	// Album functions

	private static function getAlbumAction() {

		Validator::required(isset($_POST['albumID'], $_POST['password']), __METHOD__);

		$album = new Album($_POST['albumID']);

		if ($_POST['albumID'] === 's') {
            Response::json($album->get());
        } else if ($album->getPublic()===true) {

			// Album public
			if ($album->checkPassword($_POST['password'])===true) Response::json($album->get());
			else                                                  Response::warning('Wrong password!');

		} else {

			// Album private
			Response::warning('Album private!');

		}

	}

	private static function checkAlbumAccessAction() {

		Validator::required(isset($_POST['albumID'], $_POST['password']), __METHOD__);

		$album = new Album($_POST['albumID']);

		if ($album->getPublic()===true) {

			// Album public
			if ($album->checkPassword($_POST['password'])===true) Response::json(true);
			else                                                  Response::json(false);

		} else {

			// Album private
			Response::json(false);

		}

	}

	// Photo functions

	private static function getPhotoAction() {

		Validator::required(isset($_POST['photoID'], $_POST['albumID'], $_POST['password']), __METHOD__);

		$photo = new Photo($_POST['photoID']);

		$pgP = $photo->getPublic($_POST['password']);

		if ($pgP===2)      Response::json($photo->get($_POST['albumID']));
		else if ($pgP===1) Response::warning('Wrong password!');
		else if ($pgP===0) Response::warning('Photo private!');

	}

	// Session functions

	private static function initAction() {

		$session = new Session();
		Response::json($session->init(true));

	}

	private static function loginAction() {

		Validator::required(isset($_POST['user'], $_POST['password']), __METHOD__);

		$session = new Session();
		Response::json($session->login($_POST['user'], $_POST['password']));

	}

	private static function logoutAction() {

		$session = new Session();
		Response::json($session->logout());

	}

	// $_GET functions

	private static function getAlbumArchiveAction() {

		Validator::required(isset($_GET['albumID'], $_GET['password']), __METHOD__);

		$album = new Album($_GET['albumID']);

		if ($album->getPublic()&&$album->getDownloadable()) {

			// Album Public
			if ($album->checkPassword($_GET['password'])) $album->getArchive();
			else                                          Response::warning('Wrong password!');

		} else {

			// Album Private
			Response::warning('Album private or not downloadable!');

		}

	}

	private static function getPhotoArchiveAction() {

		Validator::required(isset($_GET['photoID'], $_GET['password']), __METHOD__);

		$photo = new Photo($_GET['photoID']);

		$pgP = $photo->getPublic($_GET['password']);

		// Photo Download
		if ($pgP===2) {

			// Photo Public
			$photo->getArchive();

		} else {

			// Photo Private
			Response::warning('Photo private or password incorrect!');

		}

	}

}

?>