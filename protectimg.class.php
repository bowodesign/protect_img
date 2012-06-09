<?php
/**
 *  @package protect_img
 *  @author  Karl Holz 
 *  @version 0.1
 * 
 * 
 * 
 * Copyright (c) 2012 Karl Holz, www.salamcast.com
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation the rights to use, 
 * copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or 
 * substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS 
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.   
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, 
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * This Class will search your HTML/xHTML document for image files and embed them into 
 * the document Please note this will make your html file a lot bigger, so use with care. 
 */

/**
 * Secure Image, text watermark your image and clean out it's metadata like GPS, cammera info, etc
 * 
 */
class protect_img {
    /**
     * Image File variables
     * @var string $file //img file
     * @var string $save // saved water marked file
     * @var string $im   //img resource
     * @var string $tmp  //temp img for encoding
     */
    private $file;
    private $save;
    private $im;   
    private $tmp;  
    /**
     * secure_img, bootstrap the class with img file
     * @param string $file 
     */
    function __construct($file) {
     if (is_file($file)) {
      $this->file=$file;
      $this->save='wm'.basename($this->file).'.jpg'; // new file to save watermarked image as
      $this->im=$this->open_img($this->file);        // open image
      $this->tmp=__DIR__.'/'.__CLASS__.'.jpg';       // tmp img file
     } else { 
         $this->error_img("Only works with a file"); 
     }
    }
    /**
     * clean up tmp file 
     */
    function __destruct() {
     if (is_file($this->tmp)) { unlink($this->tmp); }
    }
    /**
     * private variables for watermark string
     * 
     * @param array $watermark     // file or string
     * @param int $mark_alpha      // watermark transpanency
     * @param int $mark_font       // font type
     * @param int $mark_start_x    // left and right position
     * @param int $mark_start_y    // up and down position
     * @param int $mark_border     // watermark boarder
     * @param int $mark_height     // watermark height
     * @param int $mark_width      // watermark width
     * @param string $color1       // boarder and font color
     * @param string $color2       // background color
     * @param string $encoded      // encoded image string 
     */
    private $watermark=array(); //file or string
    private $mark_alpha=50; 
    private $mark_font=4;
    private $mark_start_x=20;
    private $mark_start_y=20;
    private $mark_border=9;
    private $mark_height=36;
    private $mark_width;
    private $color1='0x0000FF';
    private $color2='0xFFFFFF';
    private $encoded;
    /**
     * __get() select private values magicly
     * @param string $name
     * @return string, on error display image with error 
     */
    function __get($name) {
        switch ($name) {
            case 'encoded': return $this->encoded; break;
            case 'save':    return $this->save;    break;
            default:        $this->error_img('You called an unknown variable');
        };
    }
    /**
     * __set() for setting private watermark properties
     * @param type $name
     * @param type $value 
     * @return bool, return error image
     */
    function __set($name, $value) {
     switch($name) {
      case 'add_watermark':
       $this->watermark[]=$value;
       $new=round(((strlen($value))*(((strlen($value)/2)+10))/2));
       if ($new > $this->mark_width) {
        $this->mark_width=$new;
       }
       $this->mark_height=$this->mark_height+20;
      break;
      case 'watermark_alpha':
       if (is_numeric($value) && (0 <= $value || $value <= 100)) { 
           $this->mark_alpha=$value; 
       } else {  
           $this->error_img($value." is not a number or between 0 and 100, plese fix this ");
       }
      break;
      case 'watermark_font':
       if (is_numeric($value)) { 
           $this->mark_font=$value; 
       } else {
           $this->error_img($value." is not a number, plese fix this ");
       }
      break;
      case 'watermark_start_x':
       if (is_numeric($value)) { 
           $this->mark_start_x=$value; 
       } else {
           $this->error_img($value." is not a number, plese fix this");
       }
      break;
      case 'watermark_start_y':
       if (is_numeric($value)) {  
        $this->mark_start_y=$value;
       } else {  
        $this->error_img($value." is not a number, plese fix this ");
       }
      break;
      case 'watermark_border':
       if (is_numeric($value)) { 
           $this->mark_border=$value; 
       } else {  
           $this->error_img($value." is not a number, plese fix this ");
       }
      break;
      case 'watermark_height':
       if (is_numeric($value)) { 
           $this->mark_height=$value; 
       } else {  
           $this->error_img($value." is not a number, plese fix this ");
       }
      break;
      case 'watermark_width':
       if (is_numeric($value)) { 
           $this->mark_width=$value; 
       } else {  
           $this->error_img($value." is not a number, plese fix this");
       }
      break;
      case 'watermark_colors':
       if(is_array($value) && count($value) == 2) {
        $this->color1=array_shift($value);
        $this->color2=array_shift($value);
       } else {  
           $this->error_img("only works with an array with two values!"); 
       }
      break;
      default : $this->error_img("Can't set the following value ".$name); break;
     }
     return TRUE;
    }

  /**
   * apply water mark to image
   * @param type $x pass any to unset right alignment
   * @param type $y pass any to unset bottom alignment
   * @return resource returns an imagecopymerge resource
   */
  function water_mark($x='right', $y='bottom') {
   if (is_array($this->watermark) && count($this->watermark) > 0) {
    $stamp=  imagecreatetruecolor($this->mark_width, $this->mark_height);
    imagefilledrectangle($stamp, 0, 0, $this->mark_width-5, $this->mark_height-5, $this->color1);
    imagefilledrectangle($stamp, $this->mark_border, $this->mark_border, $this->mark_width-($this->mark_border+5), $this->mark_height-($this->mark_border+5), $this->color2);
    $top=$this->mark_border+11;
    foreach ($this->watermark as $w) {
     imagestring($stamp, $this->mark_font, $this->mark_border+11, $top, $w, $this->color1);
     $top=$top+18;
    }
   } else {
       $this->error_img('Watermark is not set');
   }
   // if set to right, then subtract watermark width and start width from image width 
   if ($x == 'right') { 
       $start_x=imagesx($this->im)-imagesx($stamp)-$this->mark_start_x; 
   } else { 
       $start_x=$this->mark_start_x; 
   }
   // if set to bottom, then subtract watermark height and start height from image height
   if ($y == 'bottom') { 
       $start_y=imagesy($this->im)-imagesy($stamp)-$this->mark_start_y; 
   } else {
       $start_y=$this->mark_start_y; 
   }
   return imagecopymerge($this->im, $stamp, $start_x, $start_y, 0, 0, imagesx($stamp), imagesy($stamp), $this->mark_alpha);
  }
  /**
   * Send image to the browser as jpeg 
   */
  function send_img(){
   header("Content-type: image/jpeg");
   imagejpeg($this->im);
   imagedestroy($this->im);
   exit();
  }
  /**
   * save the water marked image to a new file in the current path
   */
  function save_img() {
   return $this->write_img($this->save);
  }
  
  /**
   * write new image to a file
   * @param type $file
   * @return boolean 
   */
  function write_img($file) {
   imagejpeg($this->im, $file);
   imagedestroy($this->im);
   return TRUE;
  }
  /**
   * encode jpeg image as base64 with data: prefix
   * @return boolean 
   */
  function encode_img() {
   imagejpeg($this->im, $this->tmp);
   imagedestroy($this->im);
   $t=file_get_contents($this->tmp);
   $this->encoded='data:image/jpeg;base64,'.base64_encode($t);
   return TRUE;
  }
  /**
   * encode img with <img /> html tag
   * @return string 
   */
  function encode_img_html($alt='watermark tester') {
   if (isset($this->encoded)) {
    return '<img src="'.$this->encoded.'" alt="'.$alt.'" />';
   } else { 
       $this->error_img("You must encode this image first "); 
   }
  }
  /**
   * open_img
   * @param type $filename
   * @return boolean 
   */
  function open_img($file) {
   $fn=explode('.', $file);
   $type=array_pop($fn);
   switch (strtolower($type)) { // add more mime types
    case "jpg": case "jpeg": case "jpe":  return imagecreatefromjpeg($file);
    break; case "gif":                    return imagecreatefromgif($file);
    break; case "png":                    return imagecreatefrompng($file);
    break; case "bmp": case "wbmp":       return imagecreatefromwbmp($file);
    break; default:  $this->error_img("$type is invalid, use an image file [ bmp, jpg, gif or png ]");
   }
   return TRUE;
  }
  
  /**
   * prints error in an image instead of text
   * @param string $txt 
   */
  private function error_img($txt) {
    $w=600;
    $h=60;
    $f=12;
    $stamp=  imagecreatetruecolor($w, $h);
    imagefilledrectangle($stamp, 0, 0, $w-5, $h-5, '0xCD0000');
    imagefilledrectangle($stamp, $this->mark_border, $this->mark_border, $w-($this->mark_border+5), $h-($this->mark_border+5), '0xFFFAFA');
    imagestring($stamp, $f, $this->mark_border+11, $this->mark_border+11, $txt, '0xCC1100');
    header("HTTP/1.0 500 Internal Server Error");
    header("Content-type: image/jpeg");
    imagejpeg($stamp);
    imagedestroy($stamp);
    exit();
  }
}
?>
