<?php defined('SYSPATH') or die('No direct script access.'); 
/**
 * Controller for displaying barcode images
 * 
 * @version 01 - Victor Koech
 *
 * PHP version 5
 */	  
class Controller_Barcode extends Controller
{
	public function action_index() {
		$dt = ob_get_clean();
		var_dump($dt);
		echo 1;exit;
		$fontSize = 10; // GD1 in px ; GD2 in point
		$marge = 10; // between barcode and hri in pixel
		$x = 121;  // barcode center
		$y = 25;  // barcode center
		$height = 50;  // barcode height in 1D ; module size in 2D
		$width = 2;  // barcode height in 1D ; not use in 2D
		$angle = 0; // rotation in degrees 
		$code = '123456'; // barcode, of course ;)
		$type = 'code39';

		// ————————————————– //
		// ALLOCATE GD RESSOURCE
		// ————————————————– //
		$im = imagecreatetruecolor(245, 55);
		$black = ImageColorAllocate($im,0x00,0x00,0x00);
		$white = ImageColorAllocate($im,0xff,0xff,0xff);
		imagefilledrectangle($im, 0, 0, 300, 300, $white);

		// ————————————————– //
		// GENERATE
		// ————————————————– //
		$data = Barcode::gd($im, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);

		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
		exit;
	}
}
