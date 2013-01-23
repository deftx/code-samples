<?php
/**
 * Session handler and Cache setup (SAMPLE)
 * 
 * @author Rob Vella 
 * @copyright  Copyright (c) 2012 Rob Vella
 */
class Session extends Base
{
	/**
	  * Expire time for session if no activity
	  * 
	  */
	const expiresIn = 1440;

	/**
	  * Class constructor
	  *
	  * @return void
	  */
	public function __construct()
	{
		require_once APP_VENDORS . "Zend/Cache.php";
		require_once APP_VENDORS . "Zend/Session.php";
		require_once APP_VENDORS . "Zend/Session/SaveHandler/DbTable.php";
		require_once APP_VENDORS . "Zend/Registry.php";

		register_shutdown_function('session_write_close');

		$DbAdapter = DbAdapter::getInstance();

		// We are using session save handler to store session data in the DB
		$db_session_config = array(
			'name'=>'sessionNew',
			'primary'=>'id',
			'modifiedColumn'=>'modified',
			'createdColumn'=>'created',
			'lifetimeColumn'=>'lifetime',
			'dataColumn'=>'data',
			'lifetime'  =>'15552000', // 180 days for DB entry
			'db' => $DbAdapter->get("SHARED"),
		);

		// Writes Session to DB
		Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($db_session_config));
		Zend_Session::start(
			array(
				'cookie_lifetime' => 31536000, // 365 days for PHPSESSID
			)
		);
		
		// Cookie handler
		Loader::includeClass("framework","Cookie");
		Cookie::init($_COOKIE);

		// Auth setup
		$authNamespace = self::getAuth();
		// Cookie is unset when browser is closed
		$authNamespace->auth_bc = isset($_COOKIE['auth_bc']);
		// Unsets after 24 mins of being logged in. If user waits too long, they can no longer transact without re-authorizing
		$authNamespace->canTransact = ($authNamespace->auth_bc && $authNamespace->canTransact ? true : false);

		$expiresIn = self::expiresIn;

		if (isset($authNamespace->storage->oAuthInfo)) {
			$expiresIn = ($authNamespace->storage->oAuthInfo['expires'] - time());
			
			if ($expiresIn > self::expiresIn || $expiresIn <= 0) {
				$expiresIn = self::expiresIn;
			}
		}

		// Refresh canTransact timer every page load for 24 minutes
		$authNamespace->setExpirationSeconds($expiresIn,'canTransact');
	}

	/**
	 * Returns Zend_Auth's namespace
	 * 
	 * @return array
	 */
	static public function getAuth()
	{
		$namespace = Zend_Auth::getInstance()->getStorage()->getNamespace();
		
		if (!Zend_Registry::isRegistered('authNamespace')) {
			$authNamespace = new Zend_Session_Namespace("Zend_Auth");
			Zend_Registry::set("authNamespace",$authNamespace);
		} else {
			$authNamespace = Zend_Registry::get("authNamespace");
		}
		
		return $authNamespace; 
	}
	
	/**
	 * Returns user portion of Zend_Auth's storage
	 * 
	 * @return stdClass
	 */
	static public function getAuthUser()
	{
		return Zend_Auth::getInstance()->getStorage()->read();
	}
}