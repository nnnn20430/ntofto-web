<?php
function sizeFilter( $bytes, $decimals = 2 )
{
	$label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $label[$factor];
}

$dir = $_SERVER['PATH_ROOT'].'/'.$_SERVER['PATH_INFO'].'/';
$files = array_diff(scandir($dir), array());
$list = "<html>\n<body>\n<table class='table1' width='100%'>\n";
$list .= '<tr><th valign="top"></th><th width="100%"><a href="">Name</a></th><th style="padding-left: 100px; white-space: nowrap;"><a href="">Last modified</a></th><th><a href="">Size</a></th></tr>'."\n";
$list .= '<tr><th colspan="6"><hr class="hr123"></th></tr>'."\n";

foreach ($files as $k => $v){
	$name = $v;
	//if (strlen($name) > 30)
	// $name = substr($name, 0, 27) . '...';
	$path = realpath($_SERVER['PATH_ROOT'].'/'.$_SERVER['PATH_INFO'].'/'.$v);
	$datemodified = date('Y\-m\-d G:i', filemtime($path));
	$size = sizeFilter(filesize($path));
	$type = '  ';
	$hrefSuffix = '';
	if(filetype($path) == 'dir') {$type = 'DIR'; $hrefSuffix = '/';}
	$list .= '<tr><td valign="top"></td>' . '<td><a href=' . $name . $hrefSuffix . '>' . $name . $hrefSuffix . '</a></td>' . '<td align="right">' . $datemodified . '</td>' . '<td align="right">' . $size . '</td></tr>' . "\n";
}
$list .= '<tr><th colspan="6"><hr class="hr123"></th></tr>'."\n";
$list .= "</table>\n</body>\n</html>";
$list .= "<style>table.table1{border-spacing: 10px 0px;border-collapse:separate !important;position:relative;top:30px;}</style>";
$list .= "<style>.hr123{border-top: 1px solid #000000 !important;}</style>";
$list .= "<style>.unselectable {-moz-user-select: none;-webkit-user-select: none;-ms-user-select: none;user-select: none;-webkit-user-drag: none;user-drag: none;cursor: pointer;}</style>";
echo $list;
?>
