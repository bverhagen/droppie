<?
abstract class CloudService
{
    const GoogleDrive = 'Google Drive';
}

abstract class DroppieDefines {
	const DROPPIE_ROOT_DIR = 'droppie_root_dir';
	const DROPPIE_CLOUD_SERVICE = 'droppie_cloud_service';
	const DROPPIE_SHOW_ICON = 'droppie_show_icon';
	const DROPPIE_SHOW_FILE_SIZE = 'droppie_show_file_size';
	const DROPPIE_FORMAT_KEY = 'droppie_format';
	public static $cloudServiceWrapper = NULL;
}

abstract class CloudServiceItem {
	public $_name = '';
	public $_icon = '';
	public $_size = 0;
	
	abstract public function isDirectory();
	abstract public function isFile();

	public function getSize() {
		return $this->_size;
	}

	public function getIconUrl() {
		return $this->_icon;
	}
}

class CloudServiceDirectory extends CloudServiceItem {
	public $_children = array();
		
	public function isDirectory() {
		return true;
	}

	public function isFile() {
		return false;
	}
}

class CloudServiceFile extends CloudServiceItem {
	public function isDirectory() {
		return false;
	}

	public function isFile() {
		return true;
	}
}

abstract class CloudServiceWrapper {
	abstract protected function retrieveAllFiles();
	abstract protected function buildHierarchicalTree($allFiles);

	public static function &get(&$form, $key) {
		switch($key) {
			case DroppieDefines::DROPPIE_ROOT_DIR:
			case DroppieDefines::DROPPIE_CLOUD_SERVICE:
			case DroppieDefines::DROPPIE_FORMAT_KEY:
			default:
				return $form[$key];
			case DroppieDefines::DROPPIE_SHOW_ICON:
			case DroppieDefines::DROPPIE_SHOW_FILE_SIZE:
				return $form[DroppieDefines::DROPPIE_FORMAT_KEY][$key];
		}
	}

	public static function getValue($key, $default) {
		switch($key) {
			case DroppieDefines::DROPPIE_ROOT_DIR:
			case DroppieDefines::DROPPIE_CLOUD_SERVICE:
			case DroppieDefines::DROPPIE_FORMAT_KEY:
			default:
				return variable_get($key, $default);
			case DroppieDefines::DROPPIE_SHOW_ICON:
			case DroppieDefines::DROPPIE_SHOW_FILE_SIZE:
				$currentValue = variable_get(DroppieDefines::DROPPIE_FORMAT_KEY, NULL);
				if($currentValue) {
					return $currentValue[$key];
				} else {
					return $default;
				}
		}
	}

	protected static function selectRoot($idTree, $rootDir) {
		if($rootDir === '/' || empty($rootDir) || $rootDir === '.') {
			return $idTree;
		}

		// Explode rootDir
		$dirDelimiter = '/';
		$rootDir = ltrim($rootDir, $dirDelimiter);
		$explodedRootDir = explode($dirDelimiter, $rootDir);

		$tree = $idTree;
		foreach($explodedRootDir as $descentDir) {
			$tree = $tree->_children[$descentDir];
		}
		return $tree;
	}

	protected function buildInMemoryCopy() {
		$allFiles = $this->retrieveAllFiles();
		return $this->buildHierarchicalTree($allFiles);
	}
}
?>
