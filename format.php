<?

abstract class Format {
	public static function formatDirs($entries, $dir) {
		$formattedDirs = '<table>';
		$formattedDirs .= '<thead>';
		$formattedDirs .= '<tr><th></th><th>' . t('Name') . '</th><th>' . t('Size') . '</th>';
		$formattedDirs .= '</thead>';
		$formattedDirs .= '<tbody>';
		self::formatDirsHelper($formattedDirs, $entries, $dir);	
		$formattedDirs .= '</tbody>';
		$formattedDirs .= '</table>';
		return $formattedDirs;
	}

	private static function formatDirsHelper(&$format, $entries, $dir) {
		$addLater = '';
		$dir = trim($dir, '/');

		if(! self::isRoot($dir)) {
			$format .= '<tr><td>&ltri;</td><td><a href="' . $currentUrl . '?&dir=' . self::getParentDir($dir) . '"> ..</a></td><td></td></tr>'; 
		}

		foreach($entries->_children as $entry) {
			$newEntry = '<tr>';
			$newEntry .= self::getIconHtml($entry);
			$newEntry .= self::getNameHtml($entry, $dir);
			$newEntry .= self::getSizeHtml($entry, $dir);
			if($entry->isDirectory()) {
				$format .= $newEntry;
			} else {
				$addLater .= $newEntry;
			}
			$newEntry .= '</tr>';
		}
		$format .= $addLater;
	}

	private static function getIconHtml($entry) {
		return '<td><img src="' . $entry->getIconUrl() . '"/></td>';
	}

	private static function getNameHtml($entry, $dir) {
			$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$name = '<td><a href="' . $currentUrl;
			if($entry->isDirectory()) {
				$name .= '?&dir=';
			} else {
				$name .= '?&file=';
			}
			$name .= $dir . '/' . $entry->_name . '">' . $entry->_name . '</a></td>';
			return $name;
	}

	private static function getSizeHtml($entry) {
			return '<td>' . self::convertSize($entry->getSize()) . '</td>';
	}

	private static function isRoot($dir) {
		return ($dir === '' || $dir === '/');
	}

	private static function getParentDir($dir) {
		if(self::isRoot($dir)) {
			return $dir;
		}

		$dirSeparator = '/';
		$explodedDir = explode($dirSeparator, $dir);

		$rootDir = '';
		for($i = 0; $i < count($explodedDir) - 2; $i++) {
			$rootDir .= $explodedDir[$i] . $dirSeparator;	
		}
		$rootDir .= $explodedDir[count($explodedDir) - 2];
		return $rootDir;
	}
	private static function convertSize($size)
	{
		if($size == 0) {
			return '';
		}
	    	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	    	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	    	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
	}
}
?>
