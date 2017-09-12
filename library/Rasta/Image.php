<?php
// v.0.2.1
/*
CHANGELOG

0.2.1		-Added output() screen, in addition to save/write
			-Added support for PNG transparency
			-Made contenttype the PNG by default

*/
class Rasta_Image
{
	var $res 			= NULL;
	var $source 		= NULL;
	var $contenttype 	= IMAGETYPE_PNG;

	/***
	 * Constructor
	 * $src_or_resource: src is the path to an image.  If it exists, the image will be automatically opened
	 *		can also be an already created image resource
	 */
	function Rasta_Image ($src_or_resource=NULL)
	{
		if( ! is_null($src_or_resource) )
			if ( is_resource($src_or_resource) )
				$this->resource($src_or_resource);
			else
				$this->open($src_or_resource);
	}

	/***
	 * Rotate the active image
	 * $degrees: number of degrees to spin the image, if this is something like
	 *		45, it will leave gaps in the frame which will be filled in by $bkg
	 * $bkg: If image is rotated in a way that does not fill the frame, this is the color that will be used
	 */
	function rotate ($degrees, $bkg='0')
	{
		$im = imagerotate( $this->res, $degrees, $bkg );
		imagedestroy($this->res);
		$this->res = $im;
	}

	/***
	 * Rotate image right
	 * Shortcut function that will rotate the image to the right
	 */
	function rotate_right() { $this->rotate(270); }

	/***
	 * Rotate image 180 degrees
	 * Shortcut function that will rotate the image 180 degrees
	 */
	function rotate_180() { $this->rotate(180); }

	/***
	 * Rotate image left
	 * Shortcut function that will rotate the image to the left
	 */
	function rotate_left() { $this->rotate(90); }

	/***
	 * Attempt to automatically rotate the image based on exif data
	 * 	a lot of digital cameras store orientation data of the camera
	 *		this will use that to automatically fix an images orientation
	 *
	 *		Note: This will only work if you have the function exif_read_data
	 *			which is part of PHPs exif data extension
	 */
	function auto_rotate() {
		if( ! function_exists('exif_read_data') ) return false;
		$exif = exif_read_data($this->source);

		$ort = $exif['IFD0']['Orientation'];
		 switch($ort) // http://www.impulseadventure.com/photo/exif-orientation.html
		 {
			case 1: // regular, do nothing
				break;
			case 2:
				return $this->flip_h();
			case 3:
				return $this->rotate_180();
			case 4:
				return $this->flip_v();
			case 5:
				return ($this->flip_h() && $this->rotate_right());
			case 6:
				return $this->rotate_right();
			case 7:
				return ($this->flip_h() && $this->rotate_left());
			case 8:
				return $this->rotate_left();
		 }
	}

	/***
	 * Will flip an image (mirror it)
	 *
	 * $bFlipH: flip horizontal  (true/false)
	 * $bFlipV: flip vertical (true/false)
	 *
	 */
	function flip($bFlipH, $bFlipV=false)
	{
		$imgsrc = $this->res;
		$width = imagesx($imgsrc);
		$height = imagesy($imgsrc);
		$imgdest = imagecreatetruecolor($width, $height);
		$this->prepare($imgdest);

		for ($x=0 ; $x<$width ; $x++)
		{
			for ($y=0 ; $y<$height ; $y++)
			{
				if ($bFlipH && $bFlipV) imagecopy($imgdest, $imgsrc, $width-$x-1, $height-$y-1, $x, $y, 1, 1);
				else if ($bFlipH) imagecopy($imgdest, $imgsrc, $width-$x-1, $y, $x, $y, 1, 1);
				else if ($bFlipV) imagecopy($imgdest, $imgsrc, $x, $height-$y-1, $x, $y, 1, 1);
			}
		}

		$this->res = $imgdest;
		imagedestroy($imgsrc);
	}

	/***
	 * Shortcut functions to flip
	 */
	function flip_h() { $this->flip(true, false); }
	function flip_v() { $this->flip(false, true); }
	function flip_both() { $this->flip(true, true); }

	/***
	 * Resizes an image to fit in a certain size
	 *
	 * $newdim:  This is the largest dimension the image can contain in
	 *		pixels.  If either of the measurements are larger than this size,
	 *		the image will be scaled
	 *
	 * $square: (true/false) make the new image square instead of keeping the dimensions
	 *
	 * $resample: high quality resize?  it is good to turn this off if you are doing
	 *		lots of conversions and quality isn't a huge issue
	 *		resample on/off is a noticeable difference in time
	 */
	function resize($newdim, $square=false, $bHeight=false, $resample=true) {
		$src_width 	= imagesx( $this->res );
		$src_height = imagesy( $this->res );
		$src_w 		= $src_width;
		$src_h 		= $src_height;
		$src_x 		= 0;
		$src_y 		= 0;

		$percent = false;
		if ( $newdim < 1 )
			$percent = $newdim;
		elseif ( substr($newdim, -1) == '%' )
			$percent = substr($newdim, 0, -1)/100;

		if ( false !== $percent )
			$newdim = round( ($bHeight ? ($src_height*$percent) : ($src_width*$percent) ) );

		if ($square)
		{
			$dst_w = $newdim;
			$dst_h = $newdim;
			if ( ! $bHeight )
			{
				$src_x = ceil( ( $src_width - $src_height ) / 2 );
				$src_w = $src_height;
				$src_h = $src_height;
			}
			else
			{
				$src_y = ceil( ( $src_height - $src_width ) / 2 );
				$src_w = $src_width;
				$src_h = $src_width;
			}
		}
		else
		{
			if ( ! $bHeight )
			{
				$dst_w = $newdim;
				$dst_h = floor( $src_height * ($dst_w / $src_width) );
			}
			else
			{
				$dst_h = $newdim;
				$dst_w = floor( $src_width * ($dst_h / $src_height) );
			}
		}
		$dst_im = imagecreatetruecolor($dst_w,$dst_h);
		$this->prepare($dst_im);
		if ($resample)
			imagecopyresampled($dst_im, $this->res, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		else
			imagecopyresized($dst_im, $this->res, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

		imagedestroy($this->res);
		$this->res = $dst_im;
	}

	/***
	 * Shortcut functions to resize an image to certain, standard sizes
	 */
	function width($newdim, $square=false, $resample=true) { $this->resize($newdim, $square, false, $resample); }
	function height($newdim, $square=false, $resample=true) { $this->resize($newdim, $square, true, $resample); }
	function resize_1600($square=false) { $this->resize(1600, $square); }
	function resize_1200($square=false) { $this->resize(1200, $square); }
	function resize_1024($square=false) { $this->resize(1024, $square); }
	function resize_800($square=false) { $this->resize(800, $square); }
	function resize_640($square=false) { $this->resize(640, $square); }

	/***
	 * Generate a thumbnail from the loaded image
	 *
	 * $dest:  The destination file on disk to save new thumbnail
	 *
	 * $out: The type of image to create (uses PHPs standard image constants for PNG, JPG, GIF)
	 *
	 * $newdim:  This is the largest dimension the image can contain in
	 *		pixels.  If either of the measurements are larger than this size,
	 *		the image will be scaled
	 *
	 * $square: create a square thumbnail
	 *
	 * $resample: high quality resize?  it is good to turn this off if you are doing
	 *		lots of conversions and quality isn't a huge issue
	 */
	function thumbnail($dest, $newdim, $square=false, $bHeight=false, $out=NULL, $resample=true) {
		$src_width 	= imagesx($this->res);
		$src_height = imagesy($this->res);
		$src_w 		= $src_width;
		$src_h 		= $src_height;
		$src_x 		= 0;
		$src_y 		= 0;

		$percent = false;
		if ( $newdim < 1 )
			$percent = $newdim;
		elseif ( substr($newdim, -1) == '%' )
			$percent = substr($newdim, 0, -1)/100;

		if ( false !== $percent )
			$newdim = round( ($bHeight ? ($src_height*$percent) : ($src_width*$percent) ) );

		if ($square)
		{
			$dst_w = $largest_dim;
			$dst_h = $largest_dim;
			if ( ! $bHeight )
			{
				$src_x = ceil( ($src_width - $src_height) / 2 );
				$src_w = $src_height;
				$src_h = $src_height;
			}
			else
			{
				$src_y = ceil( ($src_height - $src_width) / 2 );
				$src_w = $src_width;
				$src_h = $src_width;
			}
		}
		else
		{
			if ( ! $bHeight )
			{
				$dst_w = $largest_dim;
				$dst_h = floor( $src_height * ($dst_w / $src_width) );
			}
			else
			{
				$dst_h = $largest_dim;
				$dst_w = floor( $src_width * ($dst_h / $src_height) );
			}
		}
		$dst_im = imagecreatetruecolor($dst_w,$dst_h);
		$this->prepare($dst_im);
		if ($resample)
			imagecopyresampled($dst_im, $this->res, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		else
			imagecopyresized($dst_im, $this->res, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

		if ( is_null($out) )
			$out = $this->contenttype;
		switch($out)
		{
			case IMAGETYPE_PNG:
			  imagepng($dst_im, $dest);
			break;
			case IMAGETYPE_GIF:
			  imagegif($dst_im, $dest);
			break;
			case IMAGETYPE_JPEG:

			  imagejpeg($dst_im, $dest);
			break;
		}
		imagedestroy($dst_im);
	}

	/***
	 * Shortcut functions to some standard thumbnail sizes
	 */
	function thumbnail_xsmall($dest, $out, $square=false) { $this->thumbnail($dest, $out, 60, $square); }
	function thumbnail_small($dest, $out, $square=false) { $this->thumbnail($dest, $out, 80, $square); }
	function thumbnail_medium($dest, $out, $square=false) { $this->thumbnail($dest, $out, 160, $square); }
	function thumbnail_large($dest, $out, $square=false) { $this->thumbnail($dest, $out, 300, $square); }
	function thumbnail_xlarge($dest, $out, $square=false) { $this->thumbnail($dest, $out, 512, $square); }

	/***
	 * Crop an image by N pixels
	 *
	 * Same order as CSS property
	 *
	 * This will crop an image by the number of pixels you specify.
	 *		For example, to crop 10 pixels off the bottom and left sides
	 *		you would do crop(10, 0, 10)
	 */
	function crop ($top, $right=0, $bottom=0, $left=0)
	{
		$w  = imagesx($this->res);
		$h  = imagesy($this->res);
		$nw = $w - ($left+$right);
		$nh = $h - ($top+$bottom);
		$im = imagecreatetruecolor( $nw, $nh );
		$this->prepare($im);

		imagecopy($im, $this->res, 0, 0, $left, $top, $nw, $nh );

		imagedestroy($this->res);
		$this->res = $im;
	}

	/***
	 * Shortcut functions to crop
	 */
	function crop_top($px) { $this->crop($px); }
	function crop_right($px) { $this->crop(0, $px, 0, 0); }
	function crop_bottom($px) { $this->crop(0, 0, $px, 0); }
	function crop_left($px) { $this->crop(0, 0, 0, $px); }
	function crop_h($px) { $this->crop($px, 0, $px, 0); }
	function crop_v($px) { $this->crop(0, $px, 0, $px); }
	function crop_all($px) { $this->crop($px, $px, $px, $px); }

	/***
	 * Open an image from a file on disk
	 */
	function open($src)
	{
		$this->source = $src;
		switch( ( $this->contenttype = exif_imagetype($src) ) )
		{
			case IMAGETYPE_PNG:
			  $this->res = imagecreatefrompng($src);
			break;
			case IMAGETYPE_GIF:
			  $this->res = imagecreatefromgif($src);
			break;
			case IMAGETYPE_JPEG:
			  $this->res = imagecreatefromjpeg($src);
			break;
		}

		$this->prepare($this->res);
	}

	// takes an image resource and a content type and prepares the resource
	function prepare($res, $contenttype=NULL)
	{
		if ( is_null($contenttype) ) $contenttype = $this->contenttype;

		if ( $contenttype == IMAGETYPE_PNG )
		{
			imagesavealpha($res, true);
			imagealphablending($res, false);
		}
	}

	/***
	 * Get/set image resource
	 */
	function resource ($res=NULL)
	{
		if ( is_null($res) )
			return $this->res;
		else
			$this->res = $res;
	}

	/***
	 * Save the image back to the original file
	 */
	function save($out=NULL)
	{
		if ( ! is_null($this->source) )
			$this->write($this->source, $out);
	}

	/***
	 * Save the image to a different location
	 */
	function write($dest, $out=NULL)
	{
		if(is_null($out))
			$out = $this->contenttype;

		switch($out)
		{
			case IMAGETYPE_PNG:
			  imagepng($this->res, $dest);
			break;
			case IMAGETYPE_GIF:
			  imagegif($this->res, $dest);
			break;
			case IMAGETYPE_JPEG:
			  imagejpeg($this->res, $dest);
			break;
		}
	}

	/***
	 * Output the image to the stream (browser)
	 */
	function output($out=NULL)
	{
		switch($out)
		{
			default:
			case IMAGETYPE_PNG:
			  $contenttype = 'png';
			break;
			case IMAGETYPE_GIF:
			  $contenttype = 'gif';
			break;
			case IMAGETYPE_JPEG:
			  $contenttype = 'jpeg';
			break;
		}

		header('Content-type: image/'.$contenttype);
		$this->write(NULL, $out);
	}

	/***
	 * Kill self
	 */
	function destroy()
	{
		imagedestroy($this->res);
		$this->source=NULL;
		$this->contenttype = NULL;
	}
}

if( ! function_exists('exif_imagetype') )
{
	function exif_imagetype ( $f )
	{
		if ( false !== ( list(,,$type,) = getimagesize( $f ) ) )
			return $type;
		return IMAGETYPE_PNG; // meh
	}
}