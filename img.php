<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$file='oldboxes.png';


$test='string';
//$test='save';
//$test='encode';
//$test='invalid_file';
//$test='encode_html';
//$test='invalid_img';


require_once 'protectimg.class.php';

switch  ($test) {
  case 'string':
   $img=new protect_img($file);
   $img->add_watermark="Name Here";
   $img->add_watermark="<name@email.com>";
   $img->add_watermark="http://www.website.com";
   // add watermark text first, width guessing might not work out as you want, 
   // just play around to get the proper width to meet your needs
   $img->watermark_width=300;
   $img->watermark_font=8;   
   /**
$img->watermark_alpha=46;
$img->watermark_font=5;
$img->watermark_start_x=20;
$img->watermark_start_y=180;
$img->watermark_border=8;
$img->watermark_height=70;
$img->watermark_colors=array('0xABCDEF', '0x012345');
 */
   $img->water_mark();
   $img->send_img();
  break;
  case 'invalid_file':
   $img=new protect_img('$invalid');
  break;
  case 'invalid_img':
   $invalid='/var/log/apache2/error.log';      
   $img=new protect_img($invalid);
  break;
  case 'encode': // return encoded string, for storing in a db, after water_mark
   header("Content-type: text/plain");
   $img=new protect_img($file);
   //$img->add_watermark=$mark;
   $img->add_watermark="Name Here";
   $img->add_watermark="<name@email.com>";
   $img->add_watermark="http://www.website.com";
   // add watermark text first, width guessing might not work out as you want, 
   // just play around to get the proper width to meet your needs
   $img->watermark_width=300;
   $img->watermark_font=8; 
   $img->encode_img();
   echo $img->encoded;
   exit();
  break;
  case 'encode_html': // encode image, after water_mark
   header("Content-type: text/html");
   $img=new protect_img($file);
   //$img->add_watermark=$mark;
   $img->add_watermark="Name Here";
   $img->add_watermark="<name@email.com>";
   $img->add_watermark="http://www.website.com";
   // add watermark text first, width guessing might not work out as you want, 
   // just play around to get the proper width to meet your needs
   $img->watermark_width=300;
   $img->watermark_font=8;   
   $img->water_mark();
   $img->encode_img();
   echo $img->encode_img_html();
  break;
  case 'save':
   $img=new protect_img($file);
   //$img->add_watermark=$mark;
   $img->add_watermark="Name Here";
   $img->add_watermark="<name@email.com>";
   $img->add_watermark="http://www.website.com";
   // add watermark text first, width guessing might not work out as you want, 
   // just play around to get the proper width to meet your needs
   $img->watermark_width=300;
   $img->watermark_font=8; 
   $img->water_mark();
   $img->save_img();

   $file=  str_replace(basename($_SERVER['SCRIPT_NAME']), $img->save ,$_SERVER['SCRIPT_NAME']);
   header("Content-type: text/html");
   echo '<a href="http://'.$_SERVER['HTTP_HOST'].$file.'" >'.$file.'</a> saved';
  break;
}  
?>

