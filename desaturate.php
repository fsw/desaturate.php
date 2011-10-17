<?php
/**
  
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -f 
RewriteRule ^(.*)\.(jpeg|jpg|gif|bmp|png|css)$ desaturate.php?type=$2&file=$1.$2 [L]

 **/

define('DESATURATE',1);
//define('DESATURATE',0);

define('FILES_ROOT','');

//define('METHOD',1);
//define('METHOD',2);
define('METHOD',3);

//set to null for no caching
//define('CACHE_DIR','cache');
define('CACHE_DIR',NULL);


$file_path = realpath(FILES_ROOT.$_GET['file']);
$file_type = strtolower($_GET['type']);

if(!file_exists($file_path)){
  die('file not found');
}

if(!in_array($file_type,array('jpeg','jpg','gif','bmp','png','css'))){
  die('type error');
}

/* calculates gray value for given grb color */
function getValue($r,$g,$b){
 
	switch( METHOD ){
		case 1:
			$gray = round( .299*$r + .587*$g + .114*$b );
			break;
		case 2:
			$gray = floor((max($r,max($g,$b)) + min($r,min($g,$b))) / 2);
			break;
		case 3:
 			$gray = round( .333*$r + .333*$g + .334*$b );
			break;
	}
	return $gray;
  
}

function parseHexColor($matches){
  $v = getValue( hexdec($matches[1]), hexdec($matches[2]), hexdec($matches[3]) );
  return sprintf("#%02x%02x%02x", $v, $v, $v);
}

function parseRgbColor($matches){
  $v = getValue( $matches[1], $matches[2], $matches[3] );
  return sprintf("#%02x%02x%02x", $v, $v, $v);
}

function parsePercentColor($matches){
  $v = getValue( round((int)$matches[1]*2.55), round((int)$matches[2]*2.55), round((int)$matches[3]*2.55) );
  return sprintf("#%02x%02x%02x", $v, $v, $v);
}


//helper for array sorting
function keycmp($a, $b){
    if (strlen($a) == strlen($b))
        return 0;
    if (strlen($a) > strlen($b))
        return -1;
   	return 1;
}

/* converts CSS to grayscale */
function parseCSS( $css ){
  
  $css_color_names = array(
  "AliceBlue"=>"F0F8FF","AntiqueWhite"=>"FAEBD7","Aqua"=>"00FFFF","Aquamarine"=>"7FFFD4","Azure"=>"F0FFFF","Beige"=>"F5F5DC","Bisque"=>"FFE4C4","Black"=>"000000","BlanchedAlmond"=>"FFEBCD","Blue"=>"0000FF","BlueViolet"=>"8A2BE2","Brown"=>"A52A2A","BurlyWood"=>"DEB887","CadetBlue"=>"5F9EA0","Chartreuse"=>"7FFF00","Chocolate"=>"D2691E","Coral"=>"FF7F50","CornflowerBlue"=>"6495ED","Cornsilk"=>"FFF8DC","Crimson"=>"DC143C","Cyan"=>"00FFFF","DarkBlue"=>"00008B","DarkCyan"=>"008B8B","DarkGoldenRod"=>"B8860B","DarkGray"=>"A9A9A9","DarkGrey"=>"A9A9A9","DarkGreen"=>"006400","DarkKhaki"=>"BDB76B","DarkMagenta"=>"8B008B","DarkOliveGreen"=>"556B2F","Darkorange"=>"FF8C00","DarkOrchid"=>"9932CC","DarkRed"=>"8B0000","DarkSalmon"=>"E9967A","DarkSeaGreen"=>"8FBC8F","DarkSlateBlue"=>"483D8B","DarkSlateGray"=>"2F4F4F","DarkSlateGrey"=>"2F4F4F","DarkTurquoise"=>"00CED1","DarkViolet"=>"9400D3","DeepPink"=>"FF1493","DeepSkyBlue"=>"00BFFF","DimGray"=>"696969","DimGrey"=>"696969","DodgerBlue"=>"1E90FF","FireBrick"=>"B22222","FloralWhite"=>"FFFAF0","ForestGreen"=>"228B22","Fuchsia"=>"FF00FF","Gainsboro"=>"DCDCDC","GhostWhite"=>"F8F8FF","Gold"=>"FFD700","GoldenRod"=>"DAA520","Gray"=>"808080","Grey"=>"808080","Green"=>"008000","GreenYellow"=>"ADFF2F","HoneyDew"=>"F0FFF0","HotPink"=>"FF69B4","IndianRed"=>"CD5C5C","Indigo"=>"4B0082","Ivory"=>"FFFFF0",
  "Khaki"=>"F0E68C","Lavender"=>"E6E6FA","LavenderBlush"=>"FFF0F5","LawnGreen"=>"7CFC00","LemonChiffon"=>"FFFACD","LightBlue"=>"ADD8E6","LightCoral"=>"F08080","LightCyan"=>"E0FFFF","LightGoldenRodYellow"=>"FAFAD2","LightGray"=>"D3D3D3","LightGrey"=>"D3D3D3","LightGreen"=>"90EE90","LightPink"=>"FFB6C1","LightSalmon"=>"FFA07A","LightSeaGreen"=>"20B2AA","LightSkyBlue"=>"87CEFA","LightSlateGray"=>"778899","LightSlateGrey"=>"778899","LightSteelBlue"=>"B0C4DE","LightYellow"=>"FFFFE0","Lime"=>"00FF00","LimeGreen"=>"32CD32","Linen"=>"FAF0E6","Magenta"=>"FF00FF","Maroon"=>"800000","MediumAquaMarine"=>"66CDAA","MediumBlue"=>"0000CD","MediumOrchid"=>"BA55D3","MediumPurple"=>"9370D8","MediumSeaGreen"=>"3CB371","MediumSlateBlue"=>"7B68EE","MediumSpringGreen"=>"00FA9A","MediumTurquoise"=>"48D1CC","MediumVioletRed"=>"C71585","MidnightBlue"=>"191970","MintCream"=>"F5FFFA","MistyRose"=>"FFE4E1","Moccasin"=>"FFE4B5","NavajoWhite"=>"FFDEAD","Navy"=>"000080","OldLace"=>"FDF5E6","Olive"=>"808000","OliveDrab"=>"6B8E23","Orange"=>"FFA500","OrangeRed"=>"FF4500","Orchid"=>"DA70D6","PaleGoldenRod"=>"EEE8AA","PaleGreen"=>"98FB98","PaleTurquoise"=>"AFEEEE","PaleVioletRed"=>"D87093","PapayaWhip"=>"FFEFD5","PeachPuff"=>"FFDAB9","Peru"=>"CD853F","Pink"=>"FFC0CB","Plum"=>"DDA0DD","PowderBlue"=>"B0E0E6","Purple"=>"800080","Red"=>"FF0000","RosyBrown"=>"BC8F8F","RoyalBlue"=>"4169E1","SaddleBrown"=>"8B4513","Salmon"=>"FA8072","SandyBrown"=>"F4A460","SeaGreen"=>"2E8B57","SeaShell"=>"FFF5EE","Sienna"=>"A0522D","Silver"=>"C0C0C0","SkyBlue"=>"87CEEB","SlateBlue"=>"6A5ACD","SlateGray"=>"708090","SlateGrey"=>"708090","Snow"=>"FFFAFA","SpringGreen"=>"00FF7F","SteelBlue"=>"4682B4","Tan"=>"D2B48C","Teal"=>"008080","Thistle"=>"D8BFD8","Tomato"=>"FF6347","Turquoise"=>"40E0D0","Violet"=>"EE82EE","Wheat"=>"F5DEB3","White"=>"FFFFFF","WhiteSmoke"=>"F5F5F5","Yellow"=>"FFFF00","YellowGreen"=>"9ACD32");
  
  foreach($css_color_names as &$c)
	$c = "#".$c;

  //sort array by key lenght so we first search for LightGreen and then for Green
  uksort($css_color_names, "keycmp");
  //removing "LightGrey" etc
  $css = str_ireplace( array_keys($css_color_names), array_values($css_color_names), $css );
  //desaturating #f1A etc
  $css = preg_replace_callback("/#([0-9A-Fa-f])([0-9A-Fa-f])([0-9A-Fa-f])\b/","parseHexColor",$css);
  //desaturating #f562A3 etc
  $css = preg_replace_callback("/#([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})\b/","parseHexColor",$css);
  //desaturating rgb(1,23,120) etc
  $css = preg_replace_callback("/rgb\(\s*\b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\b\s*,\s*\b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\b\s*,\s*\b([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\b\s*\)/i","parseRgbColor",$css);
  //desaturating rgb(10%,5%,100%) etc
  $css = preg_replace_callback("/rgb\(\s*(\d?\d%|100%)+\s*,\s*(\d?\d%|100%)+\s*,\s*(\d?\d%|100%)+\s*\)/i","parsePercentColor",$css);
  
  return $css;
}

/* converts GD image to grayscale */
function convertImage( $im ){
	
	$imgw = imagesx($im);
	$imgh = imagesy($im);

	$im2 = imagecreatetruecolor($imgw,$imgh);
	imagealphablending($im2, false); // setting alpha blending on
	imagesavealpha($im2, true);

	for ($j=0; $j<$imgh; $j++){

		set_time_limit( 10 );

		for ($i=0; $i<$imgw; $i++){

			// get the rgb value for current pixel
			$rgb = ImageColorAt($im, $i, $j);

			$c = imagecolorsforindex($im, $rgb);
			// get the Value from the RGB value
			//$g = floor(($c['red'] + $c['green'] + $c['blue']) / 3);
			$g = getValue($c['red'],$c['green'],$c['blue']);
			round( .299*$c['red'] + .587*$c['green'] + .114*$c['blue'] );
			// grayscale values have r=g=b=g	
			$val = imagecolorallocatealpha($im, $g, $g, $g, $c['alpha']);
			// set the gray value
			imagesetpixel ($im2, $i, $j, $val);
		}
	}

	return $im2;
}

$cache_name = NULL;

if( CACHE_DIR )
	$cache_name = CACHE_DIR."/".md5($file_path); 

if($file_type == "css"){

	header("Content-type: text/css");
	$css = file_get_contents($file_path);
	if( ! DESATURATE ) {
		echo $css;
		die();
	}
	if($cache_name && file_exists($cache_name)){
		readfile($cache_name);
	} else {
		$css = parseCSS( $css );
		if($cache_name)
			file_put_contents( $cache_name, $css );
		echo $css;
	}

} else {

	if(!$details = getimagesize($file_path))
		die('image error'); 

	if(! DESATURATE ){
		header('Content-Type: ' . $details['mime']);
		readfile( $file_path );
		die();
	}
	
	if($cache_name && file_exists($cache_name)){
		header('Content-type: image/png');
		readfile($cache_name);
		die();
	}

	switch($file_type){

		case 'jpg':
		case 'jpeg':
			$im = ImageCreateFromJpeg($file_path);
			break;

		case 'gif':
			//TODO
			//header ('Content-Type: ' . $details['mime']);
			//readfile( $file_path );
			//die();
			$im = imagecreatefromgif( $file_path );
			//imagealphablending($im, true); // setting alpha blending on
			imagesavealpha( $im, true ); // save alphablending setting (important)
			break;
		case 'bmp':
			//TODO
			header ('Content-Type: ' . $details['mime']);
			readfile( $file_path );
			die();
			break;
		case 'png':
			$im = imagecreatefrompng( $file_path );
			imagealphablending( $im, true ); // setting alpha blending on
			imagesavealpha( $im, true ); // save alphablending setting (important)
			break;

	}
	$im2 = convertImage($im);
	imagedestroy($im);
	header('Content-type: image/png');
	if($cache_name)
		imagepng($im2,$cache_name);
	
	imagepng($im2);
	imagedestroy($im2);

}


