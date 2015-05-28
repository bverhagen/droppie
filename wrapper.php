<?
require_once realpath(dirname(__FILE__) . "/cloudService.php");
#require_once realpath(dirname(__FILE__) . "/DropboxWrapper.php");
#use \DropboxWrapper as dbw;
require_once realpath(dirname(__FILE__) . "/GoogleDriveWrapper.php");
use \GoogleDriveWrapper as gdw;

function getWrapperInterface() {
	static $_wrapperInterface;
	if (!isset($_wrapperInterface)) {
		$_wrapperInterface = new WrapperInterface();
	}
	return $_wrapperInterface;
}

class WrapperInterface {
	private $_cloudService = NULL;
	
	private function createCloudService($service) {
		switch($service) {
			default:
				drupal_set_message(t('Error: unkown service.'), 'error');
				$cloudService = NULL;
				break;
			case CloudService::GoogleDrive:
				$cloudService = new GoogleDriveWrapper();
				break;
		}
		return $cloudService;
	}

	public function getCloudService() {
		if($this->_cloudService == NULL) {
			$service = DroppieDefines::getValue(DroppieDefines::DROPPIE_CLOUD_SERVICE, -1);
			$this->_cloudService = $this->createCloudService($service);	
		}
		return $this->_cloudService;
	}

	/* TODO: this function may not work */
	private	function dropbox_form(&$form) {
		$cloudService = new DropboxWrapper();

		$oauthToken = dbw::getAuthToken();

		$form[dbw::$_OAUTH_TOKEN_STRING] = array(
			'#type' => 'textfield',
			'#maxlength' => 255,
			'#title' => t('Oauth token'),
			'#default_value' => $oauthToken
		);

		return $cloudService;
	}

	private function googledrive_form(&$form) {
		$cloudService = new GoogleDriveWrapper();
		$form['droppie_googledrive_reauth'] = array(
			'#markup' => l(t('Reauthenticate'),$cloudService->getAuthorizeUrl()),	
		);
		return $cloudService;
	}
}
?>
