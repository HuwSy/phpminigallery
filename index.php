<?php
  /**
   * The PHP Mini Gallery V1.4
   * (C) 2008 Richard "Shred" Koerber -- all rights reserved
   * http://www.shredzone.net/go/minigallery
   * extended by http://github.com/huwsy
   *
   * Requirements: PHP 4.1 or higher, GD (GD2 recommended) or ImageMagick
   *
   * This software is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
   *
   * This program is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   *
   * You should have received a copy of the GNU General Public License
   * along with this program; if not, write to the Free Software
   * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
   */

  /*=== Allow shell running to build thumbs in advanced ===*/
  if($argc>1) parse_str(implode('&',array_slice($argv, 1)), $_GET);
  $view = 1;
  if (isset($_GET['build'])) $view = 0;

  /*=== CONFIGURATION ===*/
  $CONFIG = array();
  $CONFIG['thumb.width']    = 240;      // Thumbnail min width (pixels)
  $CONFIG['thumb.height']   = 240;      // Thumbnail min height (pixels)
  $CONFIG['thumb.scale']    = 'gd2';    // Set to 'gd2', 'im' or 'gd'
  $CONFIG['thumb.prefix']   = '';       // Prefix for thumb file names, defaults to '.' if thumbs same path as images
  $CONFIG['thumb.suffix']   = '_' . $CONFIG['thumb.width'] . ".jpg";       // Suffix for thumb
  $CONFIG['video.suffix']   = '_' . $CONFIG['thumb.width'] . ".gif";       // Suffix for thumb
  $CONFIG['tool.imagick']   = '/usr/bin/convert';   // Path to convert
  $CONFIG['tool.ffmpeg']    = '/usr/bin/ffmpeg';    // Path to convert videos
  $CONFIG['template']       = 'template.php';       // Template file
  $CONFIG['web']            = '/var/www/gallery';       // Web root path, doesnt have to be where php is ran
  $CONFIG['images']         = '/var/www/gallery/images';    // Start path of images
  $CONFIG['thumbs']         = '/var/www/gallery/thumbs';    // Start path of thumbnails
  $CONFIG['folder']         = 'folder.png';         // Folder icon
  $CONFIG['files.images']   = 'jpe?g|png|gif';      // Allowed images, non jpg|png|gif must be supported by imagemagick and ideally browser
  $CONFIG['files.videos']   = 'mov|mp4|avi|mpe?g|mkv';  // Allowed videos, must be supported by ffmpeg and ideally browser

  if ($CONFIG['images'] == $CONFIG['thumbs'] && $CONFIG['thumb.prefix'] == '') {
    $CONFIG['thumb.prefix'] = '.';
  }
  $CONFIG['thumb.ratio'] = $CONFIG['thumb.height']/$CONFIG['thumb.width'];

  /*=== ALLOW SUBFOLDERS ===*/
  $path = "/";
  if(isset($_GET['path'])) {
    $path = trim($_GET['path']);

    //--- Protect against hacker attacks ---
    if(preg_match('#\.\.#', $path)) die("Illegal characters in path!");
  }

  /*=== Additional functions ===*/
  function calc_gps($val) {
    if (isset($val)) {
      $exp = explode('/', $val);
      return $exp[0] / $exp[1];
    }
  }

  function create_gif($CONFIG, $file, $thfile) {
    $scale = $CONFIG['thumb.width'].':-1';
    $video = shell_exec(sprintf(
      '%s -i "%s" -vstats 2>&1',
      $CONFIG['tool.ffmpeg'],
      $file
    ));
    $regex = "/Video: ([^\r\n]*), ([^,]*), ([0-9]{1,4})x([0-9]{1,4})/";
    if (preg_match($regex, $video, $regs)) {
      if (($regs [4] ? $regs [4] : null)>($regs [3] ? $regs [3] : null)) {
        $scale = '-1:'.$CONFIG['thumb.height'];
      }
    }
    exec(sprintf(
      '%s -ss 0 -t 3 -i "%s" -vf "fps=10,scale=%s:flags=lanczos,split[s0][s1];[s0]palettegen=max_colors=32[p];[s1][p]paletteuse=dither=bayer" -loop 0 "%s"',
      $CONFIG['tool.ffmpeg'],
      $file,
      $scale,
      $thfile
    ));
  }

  function create_thumb($CONFIG, $file, $thfile) {
    //--- Get information about the image ---
    $aySize = getimagesize($file);
    if(!isset($aySize)) die("Picture $file not recognized...");

    //--- Compute the thumbnail size, keep aspect ratio ---
    $srcWidth = $aySize[0];  $srcHeight = $aySize[1];
    if($srcWidth==0 || $srcHeight==0) {   // Avoid div by zero
      $thWidth  = 0;
      $thHeight = 0;
    }else if($srcWidth > $srcHeight) {    // Landscape
      $thWidth  = $CONFIG['thumb.height'] * $srcWidth / $srcHeight;
      $thHeight = $CONFIG['thumb.height'];
    }else {                               // Portrait
      $thWidth  = $CONFIG['thumb.width'];
      $thHeight = $CONFIG['thumb.width'] * $srcHeight / $srcWidth;
    }

    //--- Get scale mode ---
    $scmode = strtolower($CONFIG['thumb.scale']);

    //--- Create source image ---
    if($scmode!='im') {
      switch($aySize[2]) {
        case 1: $imgPic = imagecreatefromgif($file);  break;
        case 2: $imgPic = imagecreatefromjpeg($file); break;
        case 3: $imgPic = imagecreatefrompng($file);  break;
        default: $scmode = 'im'; 
      }
    }

    //--- Scale it ---
    switch($scmode) {
      case 'gd2':     // GD2
        $imgThumb = imagecreatetruecolor($thWidth, $thHeight);
        imagecopyresampled($imgThumb, $imgPic, 0,0, 0,0, $thWidth,$thHeight, $srcWidth,$srcHeight);
        break;
      case 'gd':      // GD
        $imgThumb = imagecreate($thWidth,$thHeight);
        imagecopyresized($imgThumb, $imgPic, 0,0, 0,0, $thWidth,$thHeight, $srcWidth,$srcHeight);
        break;
      case 'im':      // Image Magick
        exec(sprintf(
          '%s -geometry %dx%d -interlace plane %s jpeg:%s',
          $CONFIG['tool.imagick'],
          $CONFIG['thumb.width'],
          $CONFIG['thumb.height'],
          $file,
          $thfile
        ));
        break;
      default:
        die("Unknown scale mode ".$CONFIG['thumb.scale']);
    }

    //--- Rotate and save it ---
    if($scmode!='im') {
      switch(exif_read_data($file)['Orientation']) {
        case 5:
          imageflip($imgThumb, IMG_FLIP_HORIZONTAL);
        case 6:
          $imgThumb = imagerotate($imgThumb, 270, null);
          break;
        case 4:
          imageflip($imgThumb, IMG_FLIP_HORIZONTAL);
        case 3:
          $imgThumb = imagerotate($imgThumb, 180, null);
          break;
        case 7:
          imageflip($imgThumb, IMG_FLIP_HORIZONTAL);
        case 8:
          $imgThumb = imagerotate($imgThumb, 90, null);
          break;
      }

      imagejpeg($imgThumb, $thfile);
      imagedestroy($imgPic);
      imagedestroy($imgThumb);
    }
  }

  function display_thumb($thfile) {
    //--- Tell there is no image like that ---
    if ($thfile == "" || !is_file($thfile)) {
      header('HTTP/1.0 404 Not Found');
      print('Sorry, this picture was not found');
      exit();
    }

    //--- Check if there is an if-modified-since header ---
    $fileModified = date('D, d M Y H:i:s \G\M\T', filemtime($thfile));
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE']==$fileModified) {
      header('HTTP/1.0 304 Not Modified');
      exit();
    }

    //--- Send the thumbnail to the browser ---
    session_cache_limiter('');
    header('Content-Type: '.mime_content_type($thfile));
    header("Content-Length: ".filesize($thfile));
    header("Last-Modified: $fileModified");
    header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime("+90 days")) . ' GMT');
    readfile($thfile);
    exit();
  }

  function make_thumb($CONFIG, $path, $file) {
    if ($path != "" && $path != "/") {
      @mkdir($CONFIG['thumbs'] . $path, 0755, true);
    }

    if (preg_match('#\.(' . $CONFIG['files.videos'] . ')$#i', $file)) {
      $thfile = $CONFIG['thumbs'] . $path . "/" . $CONFIG['thumb.prefix'].$file.$CONFIG['video.suffix'];
    } else {
      $thfile = $CONFIG['thumbs'] . $path . "/" . $CONFIG['thumb.prefix'].$file.$CONFIG['thumb.suffix'];
    }
    $file = $CONFIG['images'] . $path . "/" . $file;

    //--- Get the thumbnail ---
    if(is_file($file) && is_readable($file)) {
      //--- Check if the thumbnail is missing or out of date ---
      if(!is_file($thfile) || filesize($thfile) < 8 || (filemtime($file)>filemtime($thfile))) {
        if (is_file($thfile)) {
          unlink($thfile);
        }
        if (preg_match('#\.(' . $CONFIG['files.videos'] . ')$#i', $file)) {
          create_gif($CONFIG, $file, $thfile);
        } elseif (preg_match('#\.(' . $CONFIG['files.images'] . ')$#i', $file)) {
          create_thumb($CONFIG, $file, $thfile);
        } else {
          return "";
        }
      }
      return $thfile;
    }
    return "";
  }

  /*=== SHOW A FULL FILE OR THUMBNAIL? ===*/
  if(isset($_GET['full'])) {
    $file = trim($_GET['full']);

    //--- Protect against hacker attacks ---
    if(preg_match('#\.\.|/#', $file)) die("Illegal characters in path!");

    $file = $CONFIG['images'] . $path . "/" . $file;

    display_thumb($file);
  }

  if(isset($_GET['thumb'])) {
    $file = trim($_GET['thumb']);

    //--- Protect against hacker attacks ---
    if(preg_match('#\.\.|/#', $file)) die("Illegal characters in path!");

    $thfile = make_thumb($CONFIG, $path, $file);
    display_thumb($thfile);
  }

  /*=== CREATE CONTEXT ===*/
  $CONTEXT = array();

  /*=== GET FILE AND DIR LISTING ===*/
  $ayFiles = [];
  $ayDirs = [];
  chdir($CONFIG['images'] . $path);
  array_multisort(array_map('filemtime', ($files = glob("*"))), SORT_DESC, $files);
  foreach($files as $filename) {
    if($filename[0]=='.') continue;                     // No dirs and temp files
    if(preg_match('#\.(' . $CONFIG['files.images'] . '|' . $CONFIG['files.videos'] . ')$#i', $filename)) {
      if(is_file($filename) && is_readable($filename)) {
        $ayFiles[] = $filename;
      }
    } elseif (is_dir($filename)) {
      $ayDirs[] = $filename;
    }
  }

  $CONTEXT['count'] = count($ayFiles) + count($ayDirs);
  $CONTEXT['files'] =& $ayFiles;

  /*=== SHOW A PICTURE? ===*/
  if(isset($_GET['pic'])) {
    $file = trim($_GET['pic']);

    //--- Protect against hacker attacks ---
    if(preg_match('#\.\.|/#', $file)) die("Illegal characters in path!");

    //--- Check existence ---
    if(!(is_file($CONFIG['images'] . $path . "/" . $file) && is_readable($CONFIG['images'] . $path . "/" . $file))) {
      header('HTTP/1.0 404 Not Found');
      print('Sorry, this picture was not found');
      exit();
    }

    $CONTEXT['page'] = 'picture';

    //--- Find our index ---
    $index = array_search($file, $ayFiles);
    if(!isset($index) || $index===false) die("Invalid picture $file");
    $CONTEXT['current'] = $index+1;

    //--- Get neighbour pictures ---
    $CONTEXT['first']   = $ayFiles[0];
    $CONTEXT['last']    = $ayFiles[count($ayFiles)-1];
    if($index>0)
      $CONTEXT['prev']  = $ayFiles[$index-1];
    if($index<count($ayFiles)-1)
      $CONTEXT['next']  = $ayFiles[$index+1];

    //--- Assemble the content ---
    $v = sprintf("index.php?path=%s&full=%s",
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($path))),
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($file)))
    );
    if (str_starts_with($CONFIG['images'],$CONFIG['web'])) {
      $v = substr(str_replace($CONFIG['web'],'',$CONFIG['images']),1).$path.'/'.$file;
    }
    if (preg_match('#\.(' . $CONFIG['files.videos'] . ')$#i', $file)) {
      $page = sprintf(
        '<video controls poster="index.php?path=%s&thumb=%s" class="picimg" alt="#%s %s - %s" border="0"><source src="%s" type="%s"></video>',
        str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($path))),
        str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($file))),
        htmlspecialchars($index+1),
        htmlspecialchars($file),
        htmlspecialchars(date ("d/m/Y H:i:s", filemtime($file))),
        htmlspecialchars($v),
        htmlspecialchars(mime_content_type($file) == "video/video/quicktime" ? "video/mp4" : mime_content_type($file))
      );
    } else {
      list($pWidth,$pHeight) = getimagesize($file);
      $page = sprintf(
        '<img class="picimg" src="%s" width="%s" height="%s" alt="#%s %s - %s" border="0" />',
        htmlspecialchars($v),
        htmlspecialchars($pWidth),
        htmlspecialchars($pHeight),
        htmlspecialchars($index+1),
        htmlspecialchars($file),
        htmlspecialchars(date ("d/m/Y H:i:s", filemtime($file)))
      );
    }
    $CONTEXT['pictag'] = $page;
    $exif = exif_read_data($file, 0, true);
    $output = '';
    if (isset($exif) && isset($exif["EXIF"])) {
      $output .= '<div class="exif">';
      $output .= sprintf("Device: %s %s<br>", $exif["IFD0"]["Make"], $exif["IFD0"]["Model"]);
      $output .= sprintf("Exposure: %ss (ISO%s)<br>", $exif["EXIF"]["ExposureTime"], $exif["EXIF"]["ISOSpeedRatings"]);
      if (isset($exif["GPS"])) {
        $output .= sprintf(
          "Latitude: %s %s° %s' %ss - <a href=https://www.openstreetmap.org/?mlat=%s%s&mlon=%s%s&zoom=15 target=_blank style=color:white> Map >></a><br>",
          $exif['GPS']['GPSLatitudeRef'] == "S" ? "-" : "",
          calc_gps($exif["GPS"]["GPSLatitude"][0]),
          calc_gps($exif["GPS"]["GPSLatitude"][1]),
          round(calc_gps($exif["GPS"]["GPSLatitude"][2])),
          $exif['GPS']['GPSLatitudeRef'] == "S" ? "-" : "",
          calc_gps($exif["GPS"]["GPSLatitude"][0]) + calc_gps($exif["GPS"]["GPSLatitude"][1])/60 + round(calc_gps($exif["GPS"]["GPSLatitude"][2]))/3600,
          $exif['GPS']['GPSLongitudeRef'] == "W" ? "-" : "",
          calc_gps($exif["GPS"]["GPSLongitude"][0]) + calc_gps($exif["GPS"]["GPSLongitude"][1])/60 + round(calc_gps($exif["GPS"]["GPSLongitude"][2]))/3600
        );
        $output .= sprintf(
          "Longitude: %s %s° %s' %ss<br>",
          $exif['GPS']['GPSLongitudeRef'] == "W" ? "-" : "",
          calc_gps($exif["GPS"]["GPSLongitude"][0]),
          calc_gps($exif["GPS"]["GPSLongitude"][1]),
          round(calc_gps($exif["GPS"]["GPSLongitude"][2]))
        );
        $output .= sprintf("Altitude: %sm<br>", round(calc_gps($exif["GPS"]["GPSAltitude"]),1));
        $output .= sprintf("Direction: %s°<br>", round(calc_gps($exif["GPS"]["GPSImgDirection"]),1));
      }
      $output .= "</div>";
    }
    if(is_file($file.'.txt') && is_readable($file.'.txt')) {
      $CONTEXT['caption'] = join('', file($file.'.txt')) . $output;
    } else {
      $CONTEXT['caption'] = $file . " - " . date("d/m/Y H:i:s", filemtime($file)) . $output;
    }
  }
  /*=== SHOW INDEX PRINT ===*/
  else{
    //--- Set context ---
    $CONTEXT['page']  = 'index';
    $CONTEXT['first'] = $ayFiles[0];
    $CONTEXT['last']  = $ayFiles[count($ayFiles)-1];
  }

  //--- Assemble the index table ---
  $page = '<div class="tabindex">'."\n";
  if ($path != "" && $path != "/") {
    $page .= sprintf(
      '<div class="tiles"><a href="index.php?path=%s" id="prev"><img class="thumbimg" loading="lazy" src="folder.png" alt="#Parent" border="0" /><div class="caption">[ Up ]</div></a></div>',
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars(substr($path,0,strripos($path, "/")))))
    );
  }
  foreach($ayDirs as $key=>$file) {
    $page .= sprintf(
      '<div class="tiles"><a href="index.php?path=%s%s%s"><img class="thumbimg" loading="lazy" src="folder.png" alt="#%s" border="0" /><div class="caption">%s</div></a></div>',
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($path))),
      ($path == "/" ? "" : "/"),
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($file))),
      htmlspecialchars($file),
      htmlspecialchars($file)
    );
  }
  foreach($ayFiles as $key=>$file) {
    if ($view == 0) $page .= "<!--" . make_thumb($CONFIG, $path, $file) . "-->";
    $page .= sprintf(
      '<div class="tiles"><a id="%s" href="index.php?path=%s&pic=%s"><img class="thumbimg" loading="lazy" src="index.php?path=%s&thumb=%s" alt="#%s %s - %s" border="0" /></a></div>',
      htmlspecialchars(preg_replace("/[^a-zA-Z0-9]/", "", $file)),
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($path))),
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($file))),
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($path))),
      str_replace('&','%26',str_replace('+','%2B',htmlspecialchars($file))),
      htmlspecialchars($key+1),
      htmlspecialchars($file),
      htmlspecialchars(date ("d/m/Y H:i:s", filemtime($file)))
    );
  }
  $page .= '</div>';

  //--- Set content ---
  $CONTEXT['indextag'] = $page;

  /*=== GET TEMPLATE CONTENT ===*/
  ob_start();
  require($CONFIG['template']);
  $template = ob_get_contents();
  ob_end_clean();

  $template = preg_replace('#<pmg:ratio/>#s', $CONFIG['thumb.ratio'], $template);

  /*=== REMOVE UNMATCHING SECTION ===*/
  if($CONTEXT['page']=='index') {
    $template = preg_replace('#<pmg:if\s+page="picture">.*?</pmg:if>#s', '', $template);
    $template = preg_replace('#<pmg:if\s+page="index">(.*?)</pmg:if>#s', '$1', $template);
  }else {
    $template = preg_replace('#<pmg:if\s+page="index">.*?</pmg:if>#s', '', $template);
    $template = preg_replace('#<pmg:if\s+page="picture">(.*?)</pmg:if>#s', '$1', $template);
  }

  /*=== REPLACE TEMPLATE TAGS ===*/
  //--- Always present neighbour links ---
  $aySearch  = array(
    '<pmg:first>', '</pmg:first>',
    '<pmg:last>', '</pmg:last>',
    '<pmg:root>', '</pmg:root>',
    '<pmg:toc>', '</pmg:toc>'
  );
  $ayReplace = array();
  if ($CONTEXT['first'] == "") {
    $ayReplace[] = '<span style="display:none">';
    $ayReplace[] = '</span>';
  } else {
    $ayReplace[] = sprintf('<a href="index.php?path=%s&pic=%s">', htmlspecialchars($path),htmlspecialchars($CONTEXT['first']));
    $ayReplace[] = '</a>';
  }
  $ayReplace[] = sprintf('<a href="index.php?path=%s&pic=%s">', htmlspecialchars($path),htmlspecialchars($CONTEXT['last']));
  $ayReplace[] = '</a>';
  if ($path == "" || $path == "/") {
    $ayReplace[] = '<span style="display:none">';
    $ayReplace[] = '</span>';
  } else {
    $ayReplace[] = sprintf('<a href="index.php?path=%s">', htmlspecialchars(substr($path,0,strripos($path, "/"))));
    $ayReplace[] = '</a>';
  }
  $ayReplace[] = sprintf('<a id="parent" href="index.php?path=%s#%s">', htmlspecialchars($path), preg_replace("/[^a-zA-Z0-9]/", "", $ayFiles[$CONTEXT['current'] - 1]));
  $ayReplace[] = '</a>';
  $template = str_replace($aySearch, $ayReplace, $template);

  //--- Link to previous picture ---
  if(isset($CONTEXT['prev'])) {
    $aySearch  = array('<pmg:prev>', '</pmg:prev>');
    $ayReplace = array(
      sprintf('<a id="prev" href="index.php?path=%s&pic=%s">', htmlspecialchars($path),htmlspecialchars($CONTEXT['prev'])),
      '</a>'
    );
    $template = str_replace($aySearch, $ayReplace, $template);
  }else {
    $template = preg_replace('#<pmg:prev>.*?</pmg:prev>#s', '', $template);
  }

  //--- Link to next picture ---
  if(isset($CONTEXT['next'])) {
    $aySearch  = array('<pmg:next>', '</pmg:next>');
    $ayReplace = array(
      sprintf('<a id="next" href="index.php?path=%s&pic=%s">', htmlspecialchars($path),htmlspecialchars($CONTEXT['next'])),
      '</a>'
    );
    $template = str_replace($aySearch, $ayReplace, $template);
  }else {
    $template = preg_replace('#<pmg:next>.*?</pmg:next>#s', '', $template);
  }

  //--- Image, Index Print, Caption ---
  $aySearch  = array('<pmg:image/>', '<pmg:index/>', '<pmg:caption/>', '<pmg:count/>', '<pmg:current/>', '<pmg:path/>');
  $ayReplace = array(
    (isset($CONTEXT['pictag'])   ? $CONTEXT['pictag']   : ''),
    (isset($CONTEXT['indextag']) ? $CONTEXT['indextag'] : ''),
    (isset($CONTEXT['caption'])  ? '<div class="caption">' . $CONTEXT['caption'] . '</div>' : ''),
    $CONTEXT['count'],
    (isset($CONTEXT['current'])  ? $CONTEXT['current']  : ''),
    $path == "" ? "" : $path
  );
  $template = str_replace($aySearch, $ayReplace, $template);

  /*=== PRINT TEMPLATE ===*/
  ob_start('ob_gzhandler');
  print($template);
  print("\n".'<!-- Created by PHP Mini Gallery, (C) Richard Shred Koerber, https://github.com/shred/phpminigallery and http://github.com/huwsy -->'."\n");
  exit();
?>
