<?

abstract class Format {
	public static function formatDirs($entries, $dir) {
		$formattedDirs = '<table>';
		$formattedDirs .= '<thead>';
		$formattedDirs .= '<tr>';
		if(DroppieDefines::getValue(DroppieDefines::DROPPIE_SHOW_ICON, false)) {
			$formattedDirs .= '<th class="droppie_icon"></th>';
		}
		$formattedDirs .= '<th class="droppie_name">' . t('Name') . '</th>';
		if(DroppieDefines::getValue(DroppieDefines::DROPPIE_SHOW_FILE_SIZE, false)) {
			$formattedDirs .= '<th class="droppie_size">' . t('Size') . '</th>';
		}
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
			$format .= self::getRootHtml($dir);
		}

		$odd = false;
		foreach($entries->_children as $entry) {
			$newEntry = '<tr class="';
			if($odd) {
				$odd = false;
				$newEntry .= "odd";
			} else {
				$odd = true;
				$newEntry .= "even";
			}
			$newEntry .= '">';
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

	private static function getRootHtml($dir) {
		$rootHtml = '<tr class="odd">';
		if(DroppieDefines::getValue(DroppieDefines::DROPPIE_SHOW_ICON, false)) {
			$rootHtml .= '<td class="droppie_icon droppie_icon_parent">&ltri;</td>';
		}
		$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$rootHtml .= '<td class="droppie_name"><a href="' . $currentUrl . '?&dir=' . self::getParentDir($dir) . '"> ..</a></td>';
		if(DroppieDefines::getValue(DroppieDefines::DROPPIE_SHOW_FILE_SIZE, false)) {
			$rootHtml .= '<td class="droppie_size"></td>';
		}
		$rootHtml .= '</tr>'; 
		return $rootHtml;
	}

	private static function getIconHtml($entry) {
		$iconHtml = '';
		if(DroppieDefines::getValue(DroppieDefines::DROPPIE_SHOW_ICON, false)) {
			$iconHtml .= '<td class="droppie_icon"><img src="' . $entry->getIconUrl() . '"/></td>';
		}
		return $iconHtml;
	}

	private static function getNameHtml($entry, $dir) {
		$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$name = '<td class="droppie_name"><a href="' . $currentUrl;
		if($entry->isDirectory()) {
			$name .= '?&dir=';
		} else {
			$name .= '?&file=';
		}
		$name .= $dir . '/' . $entry->_name . '">' . $entry->_name . '</a></td>';
		return $name;
	}

	private static function getSizeHtml($entry) {
		$sizeHtml = '';
		if(DroppieDefines::getValue(DroppieDefines::DROPPIE_SHOW_FILE_SIZE, false)) {
			$sizeHtml .= '<td class="droppie_size">' . self::convertSize($entry->getSize()) . '</td>';
		}
		return $sizeHtml;
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
