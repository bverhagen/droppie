<?
require_once realpath(dirname(__FILE__) . "/cloudService.php");
require_once realpath(dirname(__FILE__) . '/google-api-php-client/src/Google/autoload.php');

class GoogleDriveDirectory extends CloudServiceDirectory  {
	public $_id = -1;

	public function __construct($id) {
		$this->_id = $id;
	}
}

class GoogleDriveFile extends CloudServiceFile {
	public $_id = -1;

	public function __construct($id) {
		$this->_id = $id;
	}
}

class GoogleDriveWrapper extends CloudServiceWrapper {
	private $_client = NULL;
	private $_service = NULL;
	private $_isAuthenticated = false;
	private $_memoryCopy = NULL;
	const _OAUTH_TOKEN = 'droppie_gdw_oauth_token';
	const _OAUTH_ACCESS_TOKEN = 'droppie_gdw_access_token';
	const _OAUTH_REFRESH_TOKEN = 'droppie_gdw_refresh_token';

	public function __construct() {
		$this->_client = new Google_Client();
		// Get your credentials from the console
		$this->_client->setClientId('675897377805-ja4bnm8j0bgbi402b0ora649uvp3jug9.apps.googleusercontent.com');
		$this->_client->setClientSecret('#########################');
		$this->_client->setRedirectUri($GLOBALS['base_url'] . '/admin/config/content/droppie/googledrive');
		$this->_client->setScopes(array('https://www.googleapis.com/auth/drive'));
		$this->_client->setAccessType('offline');
		$this->_client->setApprovalPrompt('force');
		$this->_service = new Google_Service_Drive($this->_client);
	}
 	
	public static function getCloudServiceType() {
		return CloudService::GoogleDrive;
	}

	public function getAuthorizeUrl() {
		return $this->_client->createAuthUrl();
	}

	public function setAccessToken($oauthToken) {
		$this->_client->authenticate($oauthToken);
		$this->saveAuthenticationDetails();	
		$this->_isAuthenticated = true;
	}

	private function saveAuthenticationDetails() {
		$accessToken = $this->_client->getAccessToken();
		variable_set(self::_OAUTH_ACCESS_TOKEN, $accessToken);
		$refreshToken = $this->_client->getRefreshToken();
		if($refreshToken != NULL) {
			variable_set(self::_OAUTH_REFRESH_TOKEN, $refreshToken);
		} else {
			if(variable_get(self::_OAUTH_REFRESH_TOKEN, NULL) == NULL) {
				drupal_set_message(t('Error: no refresh token received. <br>'), 'error');
			}
		}
	}

	private function refreshAuthentication() {
		$refreshToken = variable_get(self::_OAUTH_REFRESH_TOKEN, NULL);
		if($refreshToken != NULL) {
			$this->_client->refreshToken($refreshToken);
		} else {
			drupal_set_message(t('Refresh token was empty, may not be able to authenticate next time.'), 'warning'); 
		}
		$this->saveAuthenticationDetails();	
	}

	public function canBeAuthenticated() {
		return variable_get(self::_OAUTH_REFRESH_TOKEN, NULL) != NULL;
	}

	public function authenticate() {
		if($this->canBeAuthenticated()) {
			if($this->_client->isAccessTokenExpired()) {
				$this->refreshAuthentication();
			}
			if(!$this->isAuthenticated()) {
				$accessToken = variable_get(self::_OAUTH_ACCESS_TOKEN, NULL);
				if($accessToken == NULL) {
					drupal_set_message(t('Invalid access token. Please reauthenticate.'), 'error');
					return;
				}
				$this->_client->setAccessToken($accessToken);
				$this->_isAuthenticated = true;
			}
		} else {
			drupal_set_message(t('You were not authenticated<br>'), 'error');
		}
	}
	
	public function isAuthenticated() {
		return $this->_isAuthenticated;
	}

	private static function getIdMapping($files) {
		$idMapping = array();
		foreach($files as $item) {
			$childId['id'] = $item['id'];
			$childId['isDirectory'] = ($item->mimeType == 'application/vnd.google-apps.folder');
			foreach($item['modelData']['parents'] as $parent) {
				$parentId = $parent['id'];
				$idMapping[$parentId][] = $childId; 
			}		
		}	
		return $idMapping;
	}

	private static function findRootId($idMapping) {
		// Root id is not found as a child of any other id
		foreach($idMapping as $id => $Map) {
			$found = true;
			foreach($idMapping as $idMap) {
				foreach($idMap as $item) {
					if($item['id'] == $id) {
						// This is not the rootId
						$found = false;
						break;
					}
				}	
			}
			if($found) {
				return $id;
			}
		}
	}

	private static function getIdTree($idMapping) {
		$rootId = self::findRootId($idMapping);
		
		// Build the tree
		$tree[$rootId] = new GoogleDriveDirectory($rootId);
		$tree[$rootId]->_children = $idMapping[$rootId];
		self::buildTree($tree[$rootId], $idMapping);
		return $tree;
	}

	private static function buildTree(&$tree, $idMapping) {
		// TODO this is going to blow up our stack someday
		$tmpTree = $tree;
		foreach($tmpTree->_children as $index => $leaf) {
			$id = $leaf['id'];
			if(array_key_exists($id, $idMapping)) {
				$tree->_children[$id] = new GoogleDriveDirectory($id);
				$tree->_children[$id]->_children = $idMapping[$id];
				self::buildTree($tree->_children[$id], $idMapping);
			} else {
				if($leaf['isDirectory']) {
					$tree->_children[$id] = new GoogleDriveDirectory($id);
				} else {
					$tree->_children[$id] = new GoogleDriveFile($id);
				}
			}
			unset($tree->_children[$index]);
		}
	}

	private static function convertToDrive($key, $driveFile, $fileMapping) {
		if(array_key_exists($key, $fileMapping)) {
			$fileMap = $fileMapping[$key];
			$driveFile->_name = $fileMap->getTitle();
			$driveFile->_icon = $fileMap->getIconLink();
			$driveFile->_size = $fileMap->getFileSize();
		} else {
			// means this should be the root dir
			$driveFile->_name = '/';
		}
	}

	private static function convertToDriveFiles(&$idTree, $fileMapping) {
		$idTreeTmp = $idTree;
		foreach($idTreeTmp as $key => $value) {
			self::convertToDrive($key, $value, $fileMapping);
			$idTree[$value->_name] = $value;
			if($value->isDirectory()) {
				self::convertToDriveFiles($idTree[$value->_name]->_children, $fileMapping);
			}
			unset($idTree[$key]);
		}
	}

	private static function getFileMapping($files) {
		$fileMap = array();
		foreach($files as $file) {
			$fileMap[$file['id']] = $file;
		}
		return $fileMap;
	}
	
	protected function buildHierarchicalTree($files) {
		$fileMapping = self::getFileMapping($files);
		$idMapping = self::getIdMapping($files);
		$idTree = self::getIdTree($idMapping);
		self::convertToDriveFiles($idTree, $fileMapping);
		$memoryTree = self::selectRoot($idTree['/'], variable_get(DroppieDefines::DROPPIE_ROOT_DIR, NULL));
		return $memoryTree;
	}

	public function listFiles($dir) {
		$this->authenticate();
		if($this->_memoryCopy == NULL) {
			$this->_memoryCopy = $this->buildInMemoryCopy();
		}
		return self::selectRoot($this->_memoryCopy, $dir);	
	}

	public function getFile($file) {
		$this->authenticate();
		if($this->_memoryCopy == NULL) {
			$this->_memoryCopy = $this->buildInMemoryCopy();
		}
		return $this->getFileHelper($this->_memoryCopy, $file);
	}

	private function getFileHelper($idTree, $file){
		$fileDir = dirname($file);
		$fileName = basename($file);
		$subTree = self::selectRoot($this->_memoryCopy, $fileDir);
		$fileObject = $subTree->_children[$fileName];
		if($fileObject == NULL || ! $fileObject->isFile()) {
			drupal_set_message(t('Error: target is not a file'), 'error');
			return $_SERVER['HTTP_HOST'] . '/' . request_uri(); 
		}
		$link = $this->getGoogleDriveFile($fileObject->_id);
		return strtok($link, '?');
	}
	
	private function getGoogleDriveFile($fileId) {
		$file = $this->_service->files->get($fileId);
		print_r2($file);
		return $file->getDownloadUrl();
	}

	/**
	 * Retrieve a list of File resources.
	 *
	 * @return Array List of Google_Service_Drive_DriveFile resources.
	 */
	protected function retrieveAllFiles($trashed = false) {
		$result = array();
		$pageToken = NULL;
		$converted_trashed = ($trashed) ? 'true' : 'false';

		do {
			try {
				$parameters = array('q' => 'trashed=' . $converted_trashed);
					if ($pageToken) {
						$parameters['pageToken'] = $pageToken;
					}
					$files = $this->_service->files->listFiles($parameters);

					$result = array_merge($result, $files->getItems());
					$pageToken = $files->getNextPageToken();
			} catch (Exception $e) {
				print "An error occurred: " . $e->getMessage();
				$pageToken = NULL;
			}
		} while ($pageToken);
		return $result;
	}
}

?>
