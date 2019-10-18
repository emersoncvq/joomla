<?php
/**
* @file
* @brief    sigplus Image Gallery Plus image generation
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2017 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'filesystem.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'librarian.php';

/**
* Rotates an image in-place.
* @param $image The image resource to rotate.
* @param $angle The angle in degrees to rotate.
* @return True on success, false on failure.
*/
function imagerotateinplace(&$image, $angle) {
	if (($rotated = imagerotate($image, $angle, 0)) !== false) {
		// rotation successful, we no longer need the original image
		imagedestroy($image);
		$image = $rotated;
		return true;
	} else {
		// rotation fails
		return false;
	}
}

/**
* Computes the scaled target image size when an input image does not match desired dimensions.
*/
function imagescaledimensions($input_width, $input_height, $desired_width, $desired_height) {
	$scale_factor = 1;
	if ($input_width > 0 && $input_height > 0 && $desired_width > 0 && $desired_height > 0) {
		$scale_factor = min((float)$desired_width / (float)$input_width, (float)$desired_height / (float)$input_height);
	} else if ($input_width > 0 && $desired_width > 0) {
		$scale_factor = (float)$desired_width / (float)$input_width;
	} else if ($input_height > 0 && $desired_height > 0) {
		$scale_factor = (float)$desired_height / (float)$input_height;
	}
	$computed_width = (int)round($scale_factor * $input_width);
	$computed_height = (int)round($scale_factor * $input_height);
	return array($computed_width, $computed_height);
}

function imagefitdimensions($input_width, $input_height, $desired_width, $desired_height, $crop = false) {
	if ($crop || $desired_width > 0 && $desired_height > 0) {
		return array($desired_width, $desired_height);
	} else {
		return imagescaledimensions($input_width, $input_height, $desired_width, $desired_height);
	}
}

/**
* Exposes an iterator interface to produce several thumbnail images from a single source.
*/
class SigPlusNovoThumbnailIterator implements Iterator {
	private $image;
	private $thumbnail;
	private $params;
	private $position = 0;

	public function __construct($image, array $params) {
		$this->image = $image;
		$this->params = $params;
	}

	public function rewind() {
		// included for completeness but this iterator does not support rewinding
		$this->position = 0;
	}

	public function current() {
		if ($this->position < count($this->params) - 1) {
			// make a copy because resampling in libraries ImageMagick and GraphicsMagick destroys the original image
			$this->thumbnail = clone $this->image;
		} else {
			// no need to make a copy because there will be no more operations
			$this->thumbnail = $this->image;
		}

		$item = clone $this->params[$this->position];
		$item->image = $this->thumbnail;
		return $item;
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;

		// depending on iteration position, destroys the original or a copy
		$this->thumbnail->destroy();
	}

	public function valid() {
		return $this->position < count($this->params);
	}
}

class SigPlusNovoImageLibrary {
	/**
	* Available memory computed from total memory and memory usage.
	*/
	protected static function memory_get_available() {
		static $limit = null;  // value of php.ini configuration directive memory_limit in bytes
		if (!isset($limit)) {
			$inilimit = trim(ini_get('memory_limit'));
			if (empty($inilimit)) {  // no limit set
				$limit = false;
			} elseif (is_numeric($inilimit)) {
				$limit = (int) $inilimit;
			} else {
				$limit = (int) substr($inilimit, 0, -1);
				switch (strtolower(substr($inilimit, -1))) {
					case 'g':
						$limit *= 1024;
					case 'm':
						$limit *= 1024;
					case 'k':
						$limit *= 1024;
				}
			}
		}

		if ($limit !== false) {
			if ($limit < 0) {
				return false;  // no memory upper limit set in php.ini
			} else {
				return $limit - memory_get_usage(true);
			}
		} else {
			return false;
		}
	}

	/**
	* Generates a thumbnail image for an original image.
	* @param $output_params An array of objects with keys `path`, `width`, `height`, `crop` and `quality`.
	*/
	public function createRealThumbnail($image_path, array $output_params) {
		throw new SigPlusNovoImageLibraryUnavailableException();
	}

	/**
	* Generates a watermarked image for an original image.
	* @param string $image_path The full path to the image to place a watermark into.
	* @param string $watermark_path The full path to the image to use as a watermark.
	* @param string $watermarked_image_path The full path where the watermarked image should be written.
	* @param $params An object with keys `position`, `x`, `y` and optionally `quality`.
	*/
	public function createRealWatermarked($image_path, $watermark_path, $watermarked_image_path, $params) {
		throw new SigPlusNovoImageLibraryUnavailableException();
	}

	/**
	* Generates a thumbnail image for an original image.
	* @param $output_params An array of objects with keys `path`, `width`, `height`, `crop` and `quality`.
	*/
	public function createThumbnail($image_path, array $output_params) {
		if ('svg' != strtolower(pathinfo($image_path, PATHINFO_EXTENSION))) {
			return $this->createRealThumbnail($image_path, $output_params);
		} else {
			foreach ($output_params as $item) {
				copy($image_path, $item->path);
			}
			return true;
		}
	}

	/**
	* Generates a watermarked image for an original image.
	* @param string $image_path The full path to the image to place a watermark into.
	* @param string $watermark_path The full path to the image to use as a watermark.
	* @param string $watermarked_image_path The full path where the watermarked image should be written.
	* @param $params An object with keys `position`, `x`, `y` and optionally `quality`.
	*/
	public function createWatermarked($image_path, $watermark_path, $watermarked_image_path, $params) {
		if ('svg' != strtolower(pathinfo($image_path, PATHINFO_EXTENSION))) {
			return $this->createRealWatermarked($image_path, $watermark_path, $watermarked_image_path, $params);
		} else {
			copy($image_path, $watermarked_image_path);
			return true;
		}
	}

	public static function instantiate($library) {
		if ($library == 'default') {
			if (is_imagick_supported()) {
				$library = 'imagick';
			} elseif (is_gmagick_supported()) {
				$library = 'gmagick';
			} elseif (is_gd_supported()) {
				$library = 'gd';
			} else {
				$library = 'none';
			}
		}
		switch ($library) {
			case 'gd':
				if (is_gd_supported()) {
					return new SigPlusNovoImageLibraryGD();
				}
				break;
			case 'gmagick':
				if (is_gmagick_supported()) {
					return new SigPlusNovoImageLibraryGmagick();
				}
				break;
			case 'imagick':
				if (is_imagick_supported()) {
					return new SigPlusNovoImageLibraryImagick();
				}
				break;
		}
		return new SigPlusNovoImageLibrary();  // all operations will throw an image library unavailable exception
	}

	/**
	* Checks whether sufficient memory is available to load and process an image.
	*/
	protected function checkMemory($imagepath) {
		$memory_available = self::memory_get_available();
		if ($memory_available !== false) {
			$imagedata = fsx::getimagesize($imagepath);
			if ($imagedata === false) {
				return;
			}
			if (!isset($imagedata['channels'])) {  // assume RGB (i.e. 3 channels)
				$imagedata['channels'] = 3;
			}
			if (!isset($imagedata['bits'])) {  // assume 8 bits per channel
				$imagedata['bits'] = 8;
			}

			$memory_required = (int)ceil($imagedata[0] * $imagedata[1] * $imagedata['channels'] * $imagedata['bits'] / 8);

			$safety_factor = 1.8;  // not all available memory can be consumed in order to ensure safe operations, safety factor is an empirical value
			if ($safety_factor * $memory_required >= $memory_available) {
				throw new SigPlusNovoOutOfMemoryException($memory_required, $memory_available, $imagepath);
			}
		}
	}

	/**
	* Computes the coordinates where a watermark image is to be placed within a target image.
	*/
	protected function computeCoordinates($params, $width, $height, $w, $h) {
		$position = isset($params->position) ? $params->position : false;
		$x = isset($params->x) ? $params->x : 0;
		$y = isset($params->y) ? $params->y : 0;
		$centerx = floor(($width - $w) / 2);
		$centery = floor(($height - $h) / 2);
		switch ($position) {
			case 'nw': break;
			case 'n':  $x = $centerx; break;
			case 'ne': $x = $width - $w - $x; break;
			case 'w':  $y = $centery; break;
			case 'c':  $x = $centerx; $y = $centery; break;
			case 'e':  $y = $centery; $x = $width - $w - $x; break;
			case 'sw': $y = $height - $h - $y; break;
			case 's':  $x = $centerx; $y = $height - $h - $y; break;
			case 'se': $x = $width - $w - $x; $y = $height - $h - $y; break;
			default:   $y = $height - $h - $y; break;
		}
		return array($x, $y);
	}
}

class SigPlusNovoImageLibraryGD extends SigPlusNovoImageLibrary {
	/**
	* Creates an in-memory image from a local or remote image.
	* @param string $imagepath The absolute path to a local image or the URL to a remote image.
	*/
	private static function imageFromFile($imagepath, &$orientation = null) {
		$ext = strtolower(pathinfo($imagepath, PATHINFO_EXTENSION));
		switch ($ext) {
			case 'jpg': case 'jpeg':
				$image = fsx::imagecreatefromjpeg($imagepath);
				break;
			case 'gif':
				$image = fsx::imagecreatefromgif($imagepath);
				break;
			case 'png':
				$image = fsx::imagecreatefrompng($imagepath);
				break;
			default:
				$image = false;  // missing or unrecognized extension
				break;
		}

		if ($image !== false && function_exists('exif_read_data') && func_num_args() > 1) {
			$exif = @exif_read_data($imagepath, 'IFD0');
			if (!empty($exif['Orientation'])) {
				$orientation = $exif['Orientation'];
			}
		}

		return $image;
	}

	/**
	* Exports an in-memory image to a local image file.
	* @param string $imagepath The absolute path to a local image.
	* @param image $image In-memory image to export.
	* @param int $quality Quality measure between 0 and 100 for JPEG compression.
	*/
	private static function imageToFile($imagepath, $image, $quality) {
		$ext = strtolower(pathinfo($imagepath, PATHINFO_EXTENSION));
		switch ($ext) {
			case 'jpg': case 'jpeg':
				return fsx::imagejpeg($image, $imagepath, $quality);
			case 'gif':
				return fsx::imagegif($image, $imagepath);
			case 'png':
				return fsx::imagepng($image, $imagepath, 9);
			default:
				return false;  // missing or unrecognized extension
		}
	}

	/**
	* Gets orientation-aware image dimensions.
	* @param $orientation Orientation of the image resource with values 1 to 8 corresponding to the EXIF "Orientation tag"; 0 denotes unknown.
	* @param $image_w Image width, as per pixel data.
	* @param $image_h Image height, as per pixel data.
	* @return A two-element array of orientation-aware width and height.
	*/
	private static function getOrientationDimensions($orientation, $image_w, $image_h) {
		$orientation_w = $image_w;
		$orientation_h = $image_h;
		switch ($orientation) {
			case 5: case 6: case 7: case 8:  // image is transposed
				$orientation_w = $image_h;
				$orientation_h = $image_w;
				break;
			default:  // image is not transposed
		}
		return array($orientation_w, $orientation_h);
	}

	/**
	* Flips and/or rotates an image to match the original camera orientation.
	* @param $image A GD image resource.
	* @param $orientation Orientation of the image resource with values 1 to 8 corresponding to the EXIF "Orientation tag"; 0 denotes unknown.
	* @return True on success.
	*/
	private static function normalizeOrientation(&$image, $orientation) {
		$result = true;
		switch ($orientation) {
			case 1:  // do nothing
				break;
			case 2:  // flip horizontally
				$result = $result && imageflip($image, IMG_FLIP_HORIZONTAL);
				break;
			case 3:  // rotate by 180 degrees
				$result = $result && imageflip($image, IMG_FLIP_BOTH);
				break;
			case 4:  // flip vertically
				$result = $result && imageflip($image, IMG_FLIP_VERTICAL);
				break;
			case 5:  // transpose (flip vertically and rotate clockwise by 90 degrees)
				$result = $result && imageflip($image, IMG_FLIP_VERTICAL);
				$result = $result && imagerotateinplace($image, 270);
				break;
			case 6:  // rotate clockwise by 90 degrees
				$result = $result && imagerotateinplace($image, 270);
				break;
			case 7:  // transverse (flip vertically and rotate counter-clockwise by 90 degrees)
				$result = $result && imageflip($image, IMG_FLIP_VERTICAL);
				$result = $result && imagerotateinplace($image, 90);
				break;
			case 8:  // rotate counter-clockwise by 90 degrees
				$result = $result && imagerotateinplace($image, 90);
				break;
		}
		return $result;
	}

	/**
	* Determines whether an image is an animated GIF image.
	*/
	private static function isAnimated($imagepath) {
		if ('gif' != strtolower(pathinfo($imagepath, PATHINFO_EXTENSION))) {
			return false;  // only GIF format supports animation
		} else {
			return (bool)preg_match('/\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)/s', file_get_contents($imagepath));
		}
	}

	public function createRealThumbnail($image_path, array $output_params) {
		// check memory requirement for operation
		$this->checkMemory($image_path);

		if (self::isAnimated($image_path)) {
			// get GIF animation sequence
			$gifDecoder = new SigPlusNovoGifDecoder(fsx::file_get_contents($image_path));
			$delays_between_frames = $gifDecoder->GIFGetDelays();
			$loop_count = $gifDecoder->GIFGetLoop();
			$disposal = 0;  // $gifDecoder->GIFGetDisposal()[0]
			$transparent_red = $gifDecoder->GIFGetTransparentR();
			$transparent_green = $gifDecoder->GIFGetTransparentG();
			$transparent_blue = $gifDecoder->GIFGetTransparentB();

			foreach ($output_params as $item) {
				// re-scale each frame of the animated image
				$target_frames = array();
				foreach ($gifDecoder->GIFGetFrames() as $source_frame) {
					// convert string data into an image resource
					$source_image = imagecreatefromstring($source_frame);

					// re-scale a single frame
					$target_image = $this->getThumbnailFromResource($source_image, null, $item->width, $item->height, $item->crop, $item->quality);

					if ($target_image) {
						// convert image resource into a string
						ob_start();
						imagegif($target_image);
						$target_frames[] = ob_get_clean();

						// release image resource
						imagedestroy($target_image);
					}

					// release image resource
					imagedestroy($source_image);
				}

				// build an animated frames array from separate frames
				$gifEncoder = new SigPlusNovoGifEncoder(
					$target_frames,
					$delays_between_frames, $loop_count, $disposal,
					$transparent_red, $transparent_green, $transparent_blue
				);

				// save the animation in a single file
				fsx::file_put_contents($item->path, $gifEncoder->GetAnimation());
			}

			unset($gifDecoder);
			return true;
		} else {
			// load image from file
			$source_img = self::imageFromFile($image_path, $orientation);
			if (!$source_img) {
				return false;  // could not create image from file
			}

			$result = true;
			foreach ($output_params as $item) {
				$result = $result && $this->createThumbnailFromResource($source_img, $orientation, $item->path, $item->width, $item->height, $item->crop, $item->quality);
			}

			imagedestroy($source_img);
			return $result;
		}
	}

	public function createThumbnailFromResource($source_img, $orientation, $thumbpath, $thumb_w, $thumb_h, $crop = true, $quality = 85) {
		// process image
		$thumb_img = $this->getThumbnailFromResource($source_img, $orientation, $thumb_w, $thumb_h, $crop, $quality);
		if (!$thumb_img) {
			return false;
		}

		// save image to file
		$result = self::imageToFile($thumbpath, $thumb_img, $quality);
		imagedestroy($thumb_img);
		return $result;
	}

	/**
	* Creates a thumbnail image from a source image.
	*
	* @param $source_img The image resource to serve as source.
	* @param $orientation Orientation of the image resource with values 1 to 8 corresponding to the EXIF "Orientation tag"; 0 denotes unknown.
	* @param $expected_w The desired thumbnail width.
	* @param $expected_h The desired thumbnail height.
	* @param $crop Whether to crop images or re-scale them.
	*/
	private function getThumbnailFromResource($source_img, $orientation, $expected_w, $expected_h, $crop = true, $quality = 85) {
		// set intermediate dimensions for target image, taking into account original image orientation
		list($thumb_w, $thumb_h) = self::getOrientationDimensions($orientation, $expected_w, $expected_h);

		// get dimensions for cropping and resizing
		$orig_w = imagesx($source_img);
		$orig_h = imagesy($source_img);
		if (false && $thumb_w >= $orig_w && $thumb_h >= $orig_h) {  // nothing to do
			$thumb_img = $source_img;
		} else {
			$ratio_orig = $orig_w/$orig_h;  // width-to-height ratio of original image
			if ($crop) {  // resize with automatic centering, crop image if necessary
				if ($thumb_w == 0 || $thumb_h == 0) {
					throw new SigPlusNovoImageProcessingException('Both width and height must be specified when images are rescaled with cropping enabled.');
				}

				$ratio_thumb = $thumb_w/$thumb_h;  // width-to-height ratio of thumbnail image
				if ($ratio_thumb > $ratio_orig) {  // crop top and bottom
					$zoom = $orig_w / $thumb_w;  // zoom factor of original image w.r.t. thumbnail
					$crop_h = floor($zoom * $thumb_h);
					$crop_w = $orig_w;
					$crop_x = 0;
					$crop_y = floor(0.5 * ($orig_h - $crop_h));
				} else {  // crop left and right
					$zoom = $orig_h / $thumb_h;  // zoom factor of original image w.r.t. thumbnail
					$crop_h = $orig_h;
					$crop_w = floor($zoom * $thumb_w);
					$crop_x = floor(0.5 * ($orig_w - $crop_w));
					$crop_y = 0;
				}
			} else {  // resize with fitting larger dimension, do not crop image
				$crop_w = $orig_w;
				$crop_h = $orig_h;
				$crop_x = 0;
				$crop_y = 0;
				if ($thumb_w == 0) {  // width unspecified
					$zoom = $orig_h / $thumb_h;
					$thumb_w = floor($orig_w / $zoom);
				} elseif ($thumb_h == 0) {  // height unspecified
					$zoom = $orig_w / $thumb_w;
					$thumb_h = floor($orig_h / $zoom);
				} elseif ($thumb_w/$thumb_h > $ratio_orig) {  // fit height
					$zoom = $orig_h / $thumb_h;
					$thumb_w = floor($orig_w / $zoom);
				} else {  // fit width
					$zoom = $orig_w / $thumb_w;
					$thumb_h = floor($orig_h / $zoom);
				}

				// formula above may produce zero width or height for extremely narrow and extremely elongated images
				if ($thumb_w < 1) {
					$thumb_w = 1;  // any image must be at least 1px wide
				}
				if ($thumb_h < 1) {
					$thumb_h = 1;  // any image must be at least 1px tall
				}
			}

			// create resource for thumbnail image
			$thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);

			// set transparency for a palette image
			if (!imageistruecolor($source_img) && ($transparentindex = imagecolortransparent($source_img)) >= 0) {
				// convert index transparency to color (alpha channel) transparency
				if (imagecolorstotal($source_img) > $transparentindex) {  // transparent color is in palette
					$transparentrgba = imagecolorsforindex($source_img, $transparentindex);
				} else {  // use white as transparent background color
					$transparentrgba = array('red' => 255, 'green' => 255, 'blue' => 255);
				}

				// fill image with transparent color
				$transparentcolor = imagecolorallocate($thumb_img, $transparentrgba['red'], $transparentrgba['green'], $transparentrgba['blue']);
				imagecolortransparent($thumb_img, $transparentcolor);
				imagefilledrectangle($thumb_img, 0, 0, $thumb_w, $thumb_h, $transparentcolor);
				imagecolordeallocate($thumb_img, $transparentcolor);
			}

			// set alpha blending mode
			$result = true;
			if (imageistruecolor($source_img)) {
				$result = $result && imagealphablending($thumb_img, false);
				$result = $result && imagesavealpha($thumb_img, true);
			}

			// re-sample image into thumbnail size
			if (imageistruecolor($source_img)) {
				// use re-sample for true color images (e.g. PNG, JPEG)
				$result = $result && imagecopyresampled($thumb_img, $source_img, 0, 0, $crop_x, $crop_y, $thumb_w, $thumb_h, $crop_w, $crop_h);
			} else {
				// use re-size for palette images (e.g. GIF)
				$result = $result && imagecopyresized($thumb_img, $source_img, 0, 0, $crop_x, $crop_y, $thumb_w, $thumb_h, $crop_w, $crop_h);

				// convert true color thumbnail image to match palette source image
				$result = $result && imagetruecolortopalette($thumb_img, false, imagecolorstotal($source_img));
			}

			// flip and/or rotate target image to match original image orientation
			$result = $result && self::normalizeOrientation($thumb_img, $orientation);

			if ($result === false) {
				imagedestroy($thumb_img);
				return false;
			}
		}

		return $thumb_img;
	}

	public function createRealWatermarked($image_path, $watermark_path, $watermarked_image_path, $params) {
		if (self::isAnimated($image_path)) {
			throw new SigPlusNovoImageProcessingException("A watermark cannot be applied to animated images.");
		}

		if (!isset($params->quality)) {
			$params->quality = 85;
		}

		// check memory requirement for operation
		$this->checkMemory($image_path);

		// load watermark image
		$watermark_img = self::imageFromFile($watermark_path);
		if (!$watermark_img) {
			return false;  // could not create image from file
		}

		// load image
		$source_img = self::imageFromFile($image_path, $orientation);
		if (!$source_img) {
			return false;  // could not create image from file
		}

		// get image dimensions, taking into account camera orientation
		list($width, $height) = self::getOrientationDimensions($orientation, imagesx($source_img), imagesy($source_img));

		// flip and/or rotate target image to match original image orientation
		$result = self::normalizeOrientation($source_img, $orientation);

		// get target location for watermark image
		$w = imagesx($watermark_img);
		$h = imagesy($watermark_img);
		list($x, $y) = $this->computeCoordinates($params, $width, $height, $w, $h);

		// super-impose watermark
		$result = $result && imagecopy($source_img, $watermark_img, $x, $y, 0, 0, $w, $h);
		imagedestroy($watermark_img);

		$result = $result && self::imageToFile($watermarked_image_path, $source_img, $params->quality);
		imagedestroy($source_img);
		return $result;
	}
}

class SigPlusNovoImageLibraryImagick extends SigPlusNovoImageLibrary {
	private static function isAnimated($image) {
		$frames = 0;
		foreach ($image as $i) {
			$frames++;
			if ($frames > 1) {
				return true;
			}
		}
		return false;
	}

	private static function getImageOrientation($image) {
		if (method_exists($image, 'getImageOrientation')) {
			return $image->getImageOrientation();
		} elseif (function_exists('exif_read_data')) {
			$filepath = $image->getImageFilename();
			if (empty($filepath)) {
				$filepath = 'data:image/jpeg;base64,' . base64_encode($image->getImageBlob());
			}

			$exif = @exif_read_data($filepath);
			return isset($exif['Orientation']) ? $exif['Orientation'] : null;
		} else {
			return 0;
		}
	}

	/**
	* Flips and/or rotates an image to match the original camera orientation.
	* @param $image An ImageMagick image resource.
	* @param $orientation Orientation of the image resource with values 1 to 8 corresponding to the EXIF "Orientation tag"; 0 denotes unknown.
	* @return True on success.
	*/
	private static function normalizeOrientation($image, $orientation) {
		$result = true;
		if (!empty($orientation)) {
			switch ($orientation) {
				case 1:  // do nothing
					break;
				case 2:  // flip horizontally
					$result = $result && $image->flopImage();
					break;
				case 3:  // rotate by 180 degrees
					$result = $result && $image->flipImage();
					$result = $result && $image->flopImage();
					break;
				case 4:  // flip vertically
					$result = $result && $image->flipImage();
					break;
				case 5:  // transpose (flip vertically and rotate clockwise by 90 degrees)
					$result = $result && $image->transposeImage();
					break;
				case 6:  // rotate clockwise by 90 degrees
					$result = $result && $image->rotateImage(new ImagickPixel(), 90);
					break;
				case 7:  // transverse (flip vertically and rotate counter-clockwise by 90 degrees)
					$result = $result && $image->transverseImage();
					break;
				case 8:  // rotate counter-clockwise by 90 degrees
					$result = $result && $image->rotateImage(new ImagickPixel(), -90);
					break;
			}

			if (method_exists($image, 'getImageOrientation') && method_exists($image, 'setImageOrientation')) {
				if ($image->getImageOrientation()) {
					$result = $result && $image->setImageOrientation(1);
				}
			}
		}
		return $result;
	}

	public function createRealThumbnail($image_path, array $output_params) {
		$result = true;
		try {
			$original_image = new Imagick($image_path);

			// remove all EXIF data but keep ICC profile (which causes richer colors)
			$profiles = $original_image->getImageProfiles('icc', true);
			$original_image->stripImage();
			if (!empty($profiles)) {
				$original_image->profileImage('icc', $profiles['icc']);
			}

			// get iterator over output parameters that clones source image on demand
			$iterator = new SigPlusNovoThumbnailIterator($original_image, $output_params);

			if (self::isAnimated($original_image)) {
				foreach ($iterator as $item) {
					// loop through the frames
					foreach ($item->image as $frame) {
						if ($crop) {  // resize with automatic centering, crop frame if necessary
							$frame->cropThumbnailImage($item->width, $item->height);
						} else {  // resize with fitting larger dimension, do not crop frame
							$frame->thumbnailImage($item->width, $item->height, true);
						}
						$frame->setImagePage($item->width, $item->height, 0, 0);
					}

					// write animated image to disk
					$result = $result && $item->image->writeImages($item->path, true);
				}
			} else {
				// get image orientation
				$orientation = self::getImageOrientation($original_image);

				// set compression target
				foreach ($iterator as $item) {
					$item->image->setImageCompressionQuality($item->quality);

					// resize standard (non-animated) image
					if ($item->crop) {  // resize with automatic centering, crop image if necessary
						$result = $result && $item->image->cropThumbnailImage($item->width, $item->height);
					} else {  // resize with fitting larger dimension, do not crop image
						$result = $result && $item->image->thumbnailImage($item->width, $item->height, true);
					}

					// flip and/or rotate target image to match original image orientation
					$result = $result && self::normalizeOrientation($item->image, $orientation);

					// write standard image to disk
					$result = $result && $item->image->writeImage($item->path);
				}
			}
		} catch (ImagickException $exception) {
			throw new SigPlusNovoImageProcessingException($exception->getMessage());
		}
		return $result;
	}

	public function createRealWatermarked($image_path, $watermark_path, $watermarked_image_path, $params) {
		$result = true;
		try {
			// load target image
			$image = new Imagick($image_path);

			// flip and/or rotate target image to match original image orientation
			$orientation = self::getImageOrientation($image);
			$result = self::normalizeOrientation($image, $orientation);

			// load watermark image
			$watermark = new Imagick($watermark_path);
			$geometry = $watermark->getImageGeometry();
			$w = $geometry['width'];
			$h = $geometry['height'];

			// calculate coordinates of watermark within target image bounds
			$geometry = $image->getImageGeometry();
			$width = $geometry['width'];
			$height = $geometry['height'];
			list($x, $y) = $this->computeCoordinates($params, $width, $height, $w, $h);

			// super-impose watermark on target image
			$result = $result && $image->compositeImage($watermark, Imagick::COMPOSITE_DEFAULT, $x, $y);
			$watermark->destroy();

			// write target image to disk
			$result = $result && $image->writeImage($watermarked_image_path);
			$image->destroy();
		} catch (ImagickException $exception) {
			throw new SigPlusNovoImageProcessingException($exception->getMessage());
		}
		return $result;
	}
}

class SigPlusNovoImageLibraryGmagick extends SigPlusNovoImageLibrary {
	/**
	* Returns image EXIF orientation.
	* @param $image A Gmagick image resource.
	* @return Orientation of the image resource with values 1 to 8 corresponding to the EXIF "Orientation tag"; 0 denotes unknown.
	*/
	private static function getImageOrientation($image) {
		if (function_exists('exif_read_data')) {
			$filepath = $image->getFilename();
			if (empty($filepath)) {
				$filepath = 'data:image/jpeg;base64,' . base64_encode($image->getImageBlob());
			}

			$exif = @exif_read_data($filepath);
			return isset($exif['Orientation']) ? $exif['Orientation'] : null;
		} else {
			return 0;
		}
	}

	/**
	* Flips and/or rotates an image to match the original camera orientation.
	* @param $image A Gmagick image resource.
	* @param $orientation Orientation of the image resource with values 1 to 8 corresponding to the EXIF "Orientation tag"; 0 denotes unknown.
	*/
	private static function normalizeOrientation($image, $orientation) {
		if (!empty($orientation)) {
			switch ($orientation) {
				case 1:  // do nothing
					break;
				case 2:  // flip horizontally
					$image->flopImage();
					break;
				case 3:  // rotate by 180 degrees
					$image->flipImage();
					$image->flopImage();
					break;
				case 4:  // flip vertically
					$image->flipImage();
					break;
				case 5:  // transpose (flip vertically and rotate clockwise by 90 degrees)
					$image->flipImage();
					$image->rotateImage(new GmagickPixel(), -90);
					break;
				case 6:  // rotate clockwise by 90 degrees
					$image->rotateImage(new GmagickPixel(), 90);
					break;
				case 7:  // transverse (flip vertically and rotate counter-clockwise by 90 degrees)
					$image->flipImage();
					$image->rotateImage(new GmagickPixel(), 90);
					break;
				case 8:  // rotate counter-clockwise by 90 degrees
					$image->rotateImage(new GmagickPixel(), -90);
					break;
			}

			// reset orientation to default
			$image->stripImage();
		}
	}

	public function createRealThumbnail($image_path, array $output_params) {
		try {
			// read source image from disk
			$original_image = new Gmagick();
			$original_image->readImage($image_path);

			// get image orientation
			$orientation = self::getImageOrientation($original_image);

			// get iterator over output parameters that clones source image on demand
			$iterator = new SigPlusNovoThumbnailIterator($original_image, $output_params);

			foreach ($iterator as $item) {
				$item->image->setCompressionQuality($item->quality);

				if ($item->crop) {  // resize with automatic centering, crop image if necessary
					$item->image->cropThumbnailImage($item->width, $item->height);
				} else {  // resize with fitting larger dimension, do not crop image
					$item->image->thumbnailImage($item->width, $item->height, true);
				}

				// flip and/or rotate target image to match original image orientation
				self::normalizeOrientation($item->image, $orientation);

				// write target image to disk
				$item->image->writeImage($item->path);
			}
		} catch (GmagickException $exception) {
			throw new SigPlusNovoImageProcessingException($exception->getMessage());
		}

		return true;
	}

	public function createRealWatermarked($image_path, $watermark_path, $watermarked_image_path, $params) {
		try {
			// load target image
			$image = new Gmagick();
			$image->readImage($image_path);

			// flip and/or rotate target image to match original image orientation
			$orientation = self::getImageOrientation($image);
			self::normalizeOrientation($image, $orientation);

			// load watermark image
			$watermark = new Gmagick();
			$watermark->readImage($watermark_path);
			$width = $watermark->getImageWidth();
			$height = $watermark->getImageHeight();

			// calculate coordinates of watermark within target image bounds
			list($x, $y) = $this->computeCoordinates($params, $image->getImageWidth(), $image->getImageHeight(), $width, $height);

			// super-impose watermark on target image
			$image->compositeImage($watermark, Gmagick::COMPOSITE_DEFAULT, $x, $y);
			$watermark->destroy();

			// write target image to disk
			$image->writeImage($watermarked_image_path);
			$image->destroy();
		} catch (GmagickException $exception) {
			throw new SigPlusNovoImageProcessingException($exception->getMessage());
		}

		return true;
	}
}

class SigPlusNovoGifDecoder {
	private $GIF_TransparentR = -1;
	private $GIF_TransparentG = -1;
	private $GIF_TransparentB = -1;
	private $GIF_TransparentI = 0;
	private $GIF_buffer = array();
	private $GIF_arrays = array();
	private $GIF_delays = array();
	private $GIF_dispos = array();
	private $GIF_stream = "";
	private $GIF_string = "";
	private $GIF_bfseek = 0;
	private $GIF_anloop = 0;
	private $GIF_screen = array();
	private $GIF_global = array();
	private $GIF_sorted;
	private $GIF_colorS;
	private $GIF_colorC;
	private $GIF_colorF;

	/**
	* Decodes an animated GIF image into a sequence of frames.
	*
	* @param $GIF_pointer Binary data of an animated GIF image.
	*/
	public function __construct($GIF_pointer) {
		$this->GIF_stream = $GIF_pointer;
		self::GIFGetByte(6);
		self::GIFGetByte(7);
		$this->GIF_screen = $this->GIF_buffer;
		$this->GIF_colorF = ($this->GIF_buffer[4] & 0x80) ? 1 : 0;
		$this->GIF_sorted = ($this->GIF_buffer[4] & 0x08) ? 1 : 0;
		$this->GIF_colorC = $this->GIF_buffer[4] & 0x07;
		$this->GIF_colorS = 2 << $this->GIF_colorC;
		if ($this->GIF_colorF == 1) {
			self::GIFGetByte(3 * $this->GIF_colorS);
			$this->GIF_global = $this->GIF_buffer;
		}
		for ($cycle = 1; $cycle; ) {
			if (self::GIFGetByte(1)) {
				switch ($this->GIF_buffer[0]) {
				case 0x21:  // character "!" indicates an extension block
					self::GIFReadExtensions();
					break;
				case 0x2C:  // character "," indicates an image
					self::GIFReadDescriptor();
					break;
				case 0x3B:  // character ";" should be the last byte of file
					$cycle = 0;
					break;
				}
			} else {
				$cycle = 0;
			}
		}
	}

	private function GIFReadExtensions() {
		self::GIFGetByte(1);  // Graphic Control Label
		if ($this->GIF_buffer[0] == 0xff) {
			for (;;) {
				self::GIFGetByte(1);
				if (($u = $this->GIF_buffer[0]) == 0x00) {
					break;
				}
				self::GIFGetByte($u);
				if ($u == 0x03) {
					$this->GIF_anloop = ($this->GIF_buffer[1] | $this->GIF_buffer[2] << 8);
				}
			}
		} else {
			for (;;) {
				self::GIFGetByte(1);  // Block Size
				if (($u = $this->GIF_buffer[0]) == 0x00) {  // block size of zero marks the end of Graphic Control Extension
					break;
				}
				self::GIFGetByte($u);  // read as many bytes as size of block
				if ($u == 0x04) {
					//     +---------------+
					//  0  |               |       Block Size                    Byte
					//     +---------------+
					//  1  |     |     | | |       <Packed Fields>               See below
					//     +---------------+
					//  2  |               |       Delay Time                    Unsigned
					//     +-             -+
					//  3  |               |
					//     +---------------+
					//  4  |               |       Transparent Color Index       Byte
					//     +---------------+
					//
					//      <Packed Fields>  =     Reserved                      3 Bits
					//                             Disposal Method               3 Bits
					//                             User Input Flag               1 Bit
					//                             Transparent Color Flag        1 Bit
					$this->GIF_dispos[] = ($this->GIF_buffer[0] >> 2) & 0x07;
					$this->GIF_delays[] = ($this->GIF_buffer[1] | $this->GIF_buffer[2] << 8);
					if ($this->GIF_buffer[0] & 0x01) {
						$this->GIF_TransparentI = $this->GIF_buffer[3];
					}
				}
			}
		}
	}

	private function GIFReadDescriptor() {
		$GIF_screen = array();
		self::GIFGetByte(9);
		$GIF_screen = $this->GIF_buffer;
		$GIF_colorF = ($this->GIF_buffer[8] & 0x80) ? 1 : 0;
		if ($GIF_colorF) {
			$GIF_code = $this->GIF_buffer[8] & 0x07;
			$GIF_sort = ($this->GIF_buffer[8] & 0x20) ? 1 : 0;
		} else {
			$GIF_code = $this->GIF_colorC;
			$GIF_sort = $this->GIF_sorted;
		}
		$GIF_size = 2 << $GIF_code;
		$this->GIF_screen[4] &= 0x70;
		$this->GIF_screen[4] |= 0x80;
		$this->GIF_screen[4] |= $GIF_code;
		if ($GIF_sort) {
			$this->GIF_screen[4] |= 0x08;
		}
		if ($this->GIF_TransparentI) {
			$this->GIF_string = 'GIF89a';
		} else {
			$this->GIF_string = 'GIF87a';
		}
		self::GIFPutByte($this->GIF_screen);
		if ($GIF_colorF == 1) {
			self::GIFGetByte(3 * $GIF_size);
			if($this->GIF_TransparentI) {
				$this->GIF_TransparentR = $this->GIF_buffer[3 * $this->GIF_TransparentI + 0];
				$this->GIF_TransparentG = $this->GIF_buffer[3 * $this->GIF_TransparentI + 1];
				$this->GIF_TransparentB = $this->GIF_buffer[3 * $this->GIF_TransparentI + 2];
			}
			self::GIFPutByte($this->GIF_buffer);
		} else {
			if ($this->GIF_TransparentI) {
				$this->GIF_TransparentR = $this->GIF_global[3 * $this->GIF_TransparentI + 0];
				$this->GIF_TransparentG = $this->GIF_global[3 * $this->GIF_TransparentI + 1];
				$this->GIF_TransparentB = $this->GIF_global[3 * $this->GIF_TransparentI + 2];
			}
			self::GIFPutByte($this->GIF_global);
		}
		if ($this->GIF_TransparentI) {
			$this->GIF_string .= "!\xF9\x04\x1\x0\x0".chr($this->GIF_TransparentI)."\x0";
		}
		$this->GIF_string .= chr(0x2C);
		$GIF_screen[8] &= 0x40;
		self::GIFPutByte($GIF_screen);
		self::GIFGetByte(1);
		self::GIFPutByte($this->GIF_buffer);
		for (;;) {
			self::GIFGetByte(1);
			self::GIFPutByte($this->GIF_buffer);
			if (($u = $this->GIF_buffer[0]) == 0x00) {
				break;
			}
			self::GIFGetByte($u);
			self::GIFPutByte($this->GIF_buffer);
		}
		$this->GIF_string .= chr(0x3B);
		$this->GIF_arrays[] = $this->GIF_string;
	}

	private function GIFGetByte($len) {
		$this->GIF_buffer = array();
		for ($i = 0; $i < $len; $i++) {
			if ($this->GIF_bfseek > strlen($this->GIF_stream)) {
				return 0;
			}
			$this->GIF_buffer[] = ord($this->GIF_stream{$this->GIF_bfseek++});  // { and } stand for string indexing
		}
		return 1;
	}

	private function GIFPutByte($bytes) {
		foreach ($bytes as $byte) {
			$this->GIF_string .= chr($byte);
		}
	}

	public function GIFGetFrames() {
		return $this->GIF_arrays;
	}

	public function GIFGetDelays() {
		return $this->GIF_delays;
	}

	public function GIFGetLoop() {
		return $this->GIF_anloop;
	}

	public function GIFGetDisposal() {
		return $this->GIF_dispos;
	}

	public function GIFGetTransparentR() {
		return $this->GIF_TransparentR;
	}

	public function GIFGetTransparentG() {
		return $this->GIF_TransparentG;
	}

	public function GIFGetTransparentB() {
		return $this->GIF_TransparentB;
	}
}

class SigPlusNovoGifEncoder {
	private $GIF = 'GIF89a';
	private $BUF = array();
	/** The number of times the animation is to be repeated, or 0 to repeat indefinitely. */
	private $LOP = 0;
	/** Disposal. */
	private $DIS = 2;
	/** Transparent color, or -1 for no transparent color. */
	private $COL = -1;
	private $IMG = -1;

	/**
	* Encodes a sequence of frames into an animated GIF image.
	*
	* @param $GIF_src Binary data of image frames, each array element corresponding to a frame.
	* @param $GIF_dly Delay time.
	* @param $GIF_lop The number of times the animation is to be repeated, or 0 to repeat indefinitely.
	* @param $GIF_dis Disposal.
	* @param $GIF_red Red component of transparent color, or -1 for no transparent color.
	* @param $GIF_grn Green component of transparent color, or -1 for no transparent color.
	* @param $GIF_blu Blue component of transparent color, or -1 for no transparent color.
	*/
	public function __construct(array $GIF_src, array $GIF_dly, $GIF_lop, $GIF_dis, $GIF_red, $GIF_grn, $GIF_blu) {
			$this->LOP = ($GIF_lop > -1) ? $GIF_lop : 0;
			$this->DIS = ($GIF_dis > -1) ? ($GIF_dis < 3 ? $GIF_dis : 3) : 2;
			$this->COL = ($GIF_red > -1 && $GIF_grn > -1 && $GIF_blu > -1) ? ($GIF_red | ($GIF_grn << 8) | ($GIF_blu << 16)) : -1;
			for ($i = 0; $i < count($GIF_src); $i++) {
				$this->BUF[] = $GIF_src[$i];
				if (substr($this->BUF[$i], 0, 6) != 'GIF87a' && substr($this->BUF[$i], 0, 6) != 'GIF89a') {
					// invalid image format (not a GIF image)
					throw new SigPlusNovoImageFormatException();
				}
				for ($j = (13 + 3 * (2 << (ord($this->BUF[$i]{10}) & 0x07))), $k = true; $k; $j++) {
					switch ($this->BUF[$i]{$j}) {  // { and } stand for string indexing
					case '!':
						if ((substr($this->BUF[$i], ($j + 3), 8)) == 'NETSCAPE') {
							// already an animated image
							throw new SigPlusNovoImageFormatException();
						}
						break;
					case ';':
						$k = false;
						break;
					}
				}
			}
			self::GIFAddHeader();
			for ($i = 0; $i < count($this->BUF); $i++) {
				self::GIFAddFrames($i, $GIF_dly[$i]);
			}
			self::GIFAddFooter();
	}

	private function GIFAddHeader() {
		$cmap = 0;
		if (ord($this->BUF[0]{10}) & 0x80) {
			$cmap = 3 * (2 << (ord($this->BUF[0]{10}) & 0x07));  // { and } stand for string indexing
			$this->GIF .= substr($this->BUF[0], 6, 7);
			$this->GIF .= substr($this->BUF[0], 13, $cmap);
			$this->GIF .= "!\377\13NETSCAPE2.0\3\1" . self::GIFWord($this->LOP) . "\0";
		}
	}

	private function GIFAddFrames($i, $d) {
		$Locals_str = 13 + 3 * (2 << (ord($this->BUF[$i]{10}) & 0x07));
		$Locals_end = strlen($this->BUF[$i]) - $Locals_str - 1;
		$Locals_tmp = substr($this->BUF[$i], $Locals_str, $Locals_end);
		$Global_len = 2 << (ord($this->BUF[0]{10}) & 0x07);
		$Locals_len = 2 << (ord($this->BUF[$i]{10}) & 0x07);
		$Global_rgb = substr($this->BUF[0], 13, 3 * (2 << (ord($this->BUF[0]{10}) & 0x07)));
		$Locals_rgb = substr($this->BUF[$i], 13, 3 * (2 << (ord($this->BUF[$i]{10}) & 0x07)));
		$Locals_ext = "!\xF9\x04".chr(($this->DIS << 2) + 0).chr(($d >> 0) & 0xFF).chr(($d >> 8) & 0xFF)."\x0\x0";
		if ($this->COL > -1 && (ord($this->BUF[$i]{10}) & 0x80)) {
			for ($j = 0; $j < (2 << (ord($this->BUF[$i]{10}) & 0x07)); $j++) {
				if (ord($Locals_rgb{3 * $j + 0}) == (($this->COL >> 16) & 0xFF) && ord($Locals_rgb{3 * $j + 1}) == (($this->COL >> 8) & 0xFF) && ord($Locals_rgb{3 * $j + 2}) == (($this->COL >> 0) & 0xFF)) {
					$Locals_ext = "!\xF9\x04".chr(($this->DIS << 2) + 1).chr(($d >> 0) & 0xFF).chr(($d >> 8) & 0xFF).chr($j)."\x0";
					break;
				}
			}
		}
		switch($Locals_tmp{0}) {
		case '!' :
			$Locals_img = substr($Locals_tmp, 8, 10);
			$Locals_tmp = substr($Locals_tmp, 18, strlen($Locals_tmp) - 18);
			break;
		case ',' :
			$Locals_img = substr($Locals_tmp, 0, 10);
			$Locals_tmp = substr($Locals_tmp, 10, strlen($Locals_tmp) - 10);
			break;
		}
		if ((ord($this->BUF[$i]{10}) & 0x80) && $this->IMG > -1) {
			if ($Global_len == $Locals_len) {
				if (self::GIFBlockCompare($Global_rgb, $Locals_rgb, $Global_len)) {
						$this->GIF .= $Locals_ext.$Locals_img.$Locals_tmp;
				} else {
					$byte = ord($Locals_img{9});
					$byte |= 0x80;
					$byte &= 0xF8;
					$byte |= (ord($this->BUF[0]{10}) & 0x07);
					$Locals_img{9} = chr($byte);
					$this->GIF .= $Locals_ext.$Locals_img.$Locals_rgb.$Locals_tmp;
				}
			} else {
				$byte = ord($Locals_img{9});
				$byte |= 0x80;
				$byte &= 0xF8;
				$byte |= (ord($this->BUF[$i]{10}) & 0x07);
				$Locals_img{9} = chr($byte);
				$this->GIF .= $Locals_ext.$Locals_img.$Locals_rgb.$Locals_tmp;
			}
		} else {
			$this->GIF .= $Locals_ext.$Locals_img.$Locals_tmp;
		}
		$this->IMG = 1;
	}

	private function GIFAddFooter() {
		$this->GIF .= ';';
	}

	private function GIFBlockCompare($GlobalBlock, $LocalBlock, $Len) {
		for ($i = 0; $i < $Len; $i++) {
			if($GlobalBlock{3 * $i + 0} != $LocalBlock{3 * $i + 0} || $GlobalBlock{3 * $i + 1} != $LocalBlock{3 * $i + 1} || $GlobalBlock{3 * $i + 2} != $LocalBlock{3 * $i + 2}) {
				return 0;
			}
		}
		return 1;
	}

	private function GIFWord($int) {
		return chr($int & 0xFF).chr(($int >> 8) & 0xFF);
	}

	public function GetAnimation() {
		return $this->GIF;
	}
}

/**
* Extracts a frame from an MPEG movie into an image file.
*/
class SigPlusNovoMPEGPosterExtractor {
	/** The GD image processing library wrapper. */
	private $imagelibrary;

	public static function instantiate() {
		if (is_gd_supported() && extension_loaded('ffmpeg')) {
			return new SigPlusNovoMPEGPosterExtractor(new SigPlusNovoImageLibraryGD());
		} else {
			return false;
		}
	}

	private function __construct(SigPlusNovoImageLibraryGD $imagelibrary) {
		$this->imagelibrary = $imagelibrary;
	}

	public function createPosterImage($moviepath, $thumbpath, $thumb_w, $thumb_h, $crop = true, $quality = 85) {
		$movie = new ffmpeg_movie($moviepath);

		// extract frame from the video
		$thumbindex = (int) round($movie->getFrameCount() / 2.5);
		$frame = $movie->getFrame($thumbindex);
		$poster_img = $frame->toGDImage();

		// process and save image
		return $this->imagelibrary->createThumbnailFromResource($poster_img, null, $thumbpath, $thumb_w, $thumb_h, $crop, $quality);
	}
}
