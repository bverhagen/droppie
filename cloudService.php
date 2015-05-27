<?
abstract class CloudService
{
    const GoogleDrive = 0;
}

abstract class DroppieDefines {
	const DROPPIE_ROOT_DIR = 'droppie_root_dir';
	const DROPPIE_CLOUD_SERVICE = 'droppie_cloud_service';
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
