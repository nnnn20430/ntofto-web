<?php

$indexStyle = <<<EOF
body {
	background-color: #121212;
	color: #BBBBBB;
	font-family: "Monospace", Monospace;
	margin-top: 30px;
}
a {
	color: #FFFFFF;
	text-decoration: none;
	font-weight: bold;
	outline:0;
}
a:hover {
	color: #00BFFF;
	text-decoration: none;
}
a:active {
	color: #FF3300;
	text-decoration: none;
	outline:0;
}
.errorno {
	color: #ff3300;
	font-size: 500%;
	margin: 0px;
}
th {
	font-size: 120%;
	border-bottom-width: 3px;
	border-bottom-style: solid;
	border-bottom-color: #33FF00;
}
td {
	padding-right: 30px;
}
EOF;

function sizeFilter($bytes, $decimals = 2) {
	$label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
	$factor = floor((strlen($bytes) - 1) / 3);
	$filteredSize = $bytes/pow(1024, $factor);
	$filteredSize = sprintf("%.{$decimals}f", $filteredSize);
	$sizeString = $filteredSize . $label[$factor];
	return $sizeString;
}

function filesGroupDir($dir, $array) {
	$dirArr = [];
	$fileArr = [];
	foreach ($array as $k => $v) {
		$path = getRealPath($dir.$v);
		if(filetype($path) == 'dir') {
			$dirArr = array_merge($dirArr, [$v]);
		} else {
			$fileArr = array_merge($fileArr, [$v]);
		}
	}
	return array_merge($dirArr, $fileArr);
}

function getRealPath($path) {
	$rpath = realpath($path);
	if ($rpath === false) {
		$escapedPath = escapeshellarg($path);
		$execOutput = [];
		$execReturn = 1;
		@exec("realpath $escapedPath", $execOutput, $execReturn);
		if ($execReturn === 0) {
			$rpath = $execOutput[0];
		}
	}
	return $rpath;
}

function getFileSize($path) {
	$size = filesize($path);
	if ($size === false) {
		$escapedPath = escapeshellarg($path);
		$execOutput = [];
		$execReturn = 1;
		@exec("stat -Lc%s $escapedPath", $execOutput, $execReturn);
		if ($execReturn === 0) {
			$size = $execOutput[0];
		}
	}
	return $size;
}

function getFileMTime($path) {
	$mtime = filemtime($path);
	if ($mtime === false) {
		$escapedPath = escapeshellarg($path);
		$execOutput = [];
		$execReturn = 1;
		@exec("stat -Lc%Y $escapedPath", $execOutput, $execReturn);
		if ($execReturn === 0) {
			$mtime = $execOutput[0];
		}
	}
	return $mtime;
}

function main() {
	global $indexStyle;

	$dir = $_SERVER['PATH_ROOT'] . $_SERVER['PATH_INFO'];
	$files = array_diff(scandir($dir), array('.', '..'));
	$files = filesGroupDir($dir, $files);

	$html = "";

	$html .= "<!DOCTYPE html>\n";
	$html .= "<html>\n";
	$html .= "<head>\n";
	$html .= "<meta charset=\"UTF-8\">\n";
	$html .= "<style>" . $indexStyle . "</style>\n";
	$html .= "</head>\n";
	$html .= "<body>\n";
	$html .= "<h2>Index of " . $_SERVER['PATH_INFO'] . "</h2>\n";
	$html .= "<table>\n";

	$html .= "<tr>\n";
	$html .= "<th>Name</th>\n";
	$html .= "<th>Size</th>\n";
	$html .= "<th>Time</th>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= '<td><a href="..">..</a></td>'."\n";
	$html .= "<td></td>\n";
	$html .= "<td></td>\n";
	$html .= "</tr>\n";

	foreach ($files as $k => $v) {
		$name = $v;
		if (strlen($name) > 30)
			$name = substr($name, 0, 27) . '...';
		$path = getRealPath($dir.$v);
		$datemodified = date('Y\-m\-d H:i', getFileMTime($path));
		$size = sizeFilter(getFileSize($path));
		$hrefSuffix = '';
		if(filetype($path) == 'dir') {
			$hrefSuffix = '/';
			$size = "-";
		}
		$html .= "<tr>\n";
		$html .= '<td><a href="' . $v.$hrefSuffix . '">' . $name.$hrefSuffix . '</a></td>'."\n";
		$html .= "<td>" . $size . "</td>\n";
		$html .= "<td>" . $datemodified . "</td>\n";
		$html .= "</tr>\n";
	}

	$html .= "</table>\n";
	$html .= "</body>\n";
	$html .= "</html>\n";

	echo $html;
}

main();

?>
