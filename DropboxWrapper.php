<?
require_once "dropbox-sdk/Dropbox/autoload.php";
use \Dropbox as dbx;

class DropboxWrapper {
	private static $_appKey = 'gz5bqcta8m96cka';
	private static $_appSecret = 'gz5bqcta8m96cka';
	private static $_webAuth = NULL;

	public static $_OAUTH_TOKEN_STRING = 'droppie_oauth_token';

	public static function getAuthorizeUrl() {
		try {
			$webAuth = DropboxWrapper::getWebAuth();
			$authorizeUrl = $webAuth->start();
		} catch (Exception $e) {
		    	echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
//		return $authorizeUrl;
		
		// Workaround since the above crap is not working
		return 'https://www.dropbox.com/1/oauth2/authorize?client_id=' . DropboxWrapper::$_appKey . '&response_type=code';
	}

	public static function getAuthToken() {
		return variable_get(DropboxWrapper::$_OAUTH_TOKEN_STRING, '');
	}

	private static function getWebAuth() {
		if( $_webAuth == NULL) {
			$module_path = drupal_get_path('module', 'droppie');
			$jsonPath = getcwd() . '/' . $module_path . '/' . 'app_token.json';
	
			$appInfo = dbx\AppInfo::loadFromJsonFile($jsonPath);
			$_webAuth = new dbx\WebAuthNoRedirect($appInfo, "Droppie-Oauth-token");
		}
		return $_webAuth;
	}
	
	public static function setAccessToken($oauthToken) {
		$webAuth = DropboxWrapper::getWebAuth();	
		$webAuth->finish($oauthToken);
	}
}
?>
