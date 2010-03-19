<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                          *
*         Directory Browser and Image Viewer (short: "DBIV")               *
*                                                                          *
*  You can find more information on DBIV at http://www.schechtel.de/dbiv/  *
*  Version 1.0 -- Last change: 2002-10-27 -- Author: Roman Schechtel       *
*                                                                          *
*  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

define( 'DESC_FILE_EXT', '.desc.txt' );
define( 'HEADER', 'dbiv-header.txt' );
define( 'FOOTER', 'dbiv-footer.txt' );

$doNotList[] = '^\.';			// every file starting with a dot (.htaccess, .htpasswd)

$doNotList[] = '^index.php$'; 		// this file (as DBIV is shipped)
$doNotList[] = DESC_FILE_EXT . '$';	// files that contain explanations to image files.
$doNotList[] = '^dbiv-header.txt$';	// this file's content - if present - is displayed at the top of every page that DBIV generates.
$doNotList[] = '^dbiv-footer.txt$';	// this file's content - if present - is displayed at the bottom of every page that DBIV generates.

$displayAsImage[] = '.jpg$';
$displayAsImage[] = '.gif$';
$displayAsImage[] = '.png$';
$displayAsImage[] = '.bmp$';


define( 'COL_NUM', 3 );				// how many columns shall be used to print a file listing?
define( 'RELEASE_DATE', 	'2002-10-25' );	// release date of this script

define( 'LIST_DIRS', 		TRUE );		// shall directories be listed or not?
define( 'MAX_FILENAME_LEN',	25 );		// if a file name is longer than that it will be shortened and '...' will be appended to in the listing.

define( 'FILE_NUM_HTML',	'<td class="fileNum">%s</td>' );
define( 'FILE_LINK_HTML',	'<td><a href="%s" class="fileLink" title="%s">%s</a>' );
define( 'FILE_SIZE_HTML',	'<span class="fileSize">(%s KB)</span></td>' );  
define( 'COL_OPEN_HTML',	"<td class=\"colCell\">\n\n   <table>" );
define( 'COL_CLOSE_HTML',	"\n   </table>\n\n</td>\n" );
define( 'LINK_OPEN_HTML',	"\n   <tr>" );
define( 'LINK_CLOSE_HTML',	"</tr>" );

$isEmpty = true;


function cutFileName($fileName)
{
  if (strlen($fileName) > MAX_FILENAME_LEN) { 
    $fileName = substr($fileName, 0, MAX_FILENAME_LEN - 3); 
    $fileName .= '...';
  }
  return $fileName;
}



function fileCanBeListed($fileName)
{
  global $doNotList;
  foreach ($doNotList as $pattern) {
    if ( preg_match("/$pattern/i", $fileName) ) {
      return;
    }
  }
  return true; 
}



function fileIsImage($fileName)
{
  global $displayAsImage;
  if ( is_dir($fileName) ) return;
  foreach ($displayAsImage as $imgType) {
    if ( preg_match("/$imgType/i", $fileName) ) {
      return true;
    }
  }
}



function getFilesToList()
{
  global $dirImages;
  global $dirNonImages;
  global $directories;
  global $isEmpty;
  $handle = opendir ('.');
  while ( false !== ( $file = readdir($handle) ) ) {
    if ( !fileCanBeListed($file) ) continue;
    if ( fileIsImage($file) ) { $dirImages[] = $file; continue; }
    if ( is_dir($file) ) $directories[] = $file;
    else $dirNonImages[] = $file;
  }
  closedir($handle);
  if ( isset($dirImages) ) { sort($dirImages); $isEmpty = false; }
  if ( isset($dirNonImages) ) { sort($dirNonImages); $isEmpty = false; }
  if ( isset($directories) ) { sort($directories); $isEmpty = false; }
}



function printFileListing($files, $colNum, $urlStr = '%s')
{
  $i = 0;
  $rowPos = 1;
  $colIsOpened = false;
  $fileCount = count($files);
  $filesPerCol = ceil( $fileCount / $colNum );  
  if ( $fileCount <= $colNum ) { $filesPerCol = $fileCount; }

  echo "\n<table class=\"fileListingTable\">\n<tr>\n";
  while ( $i < $fileCount ) {
    $fileName = $files[$i];
    $fileNum = sprintf( '%0' . strlen( $fileCount ) . 'd' , $i+1); // so image number '1' will be printed as '002', if there are >= 100 files in the array;
    if ( strlen( $fileCount ) < 4 ) { $fileNum = sprintf( '%03d', $i + 1 ); } 
    $fileUrl = sprintf( $urlStr, rawurlencode( $fileName ) );
    if ( is_dir($fileName) ) $fileUrl .= '/';
    if ( !is_dir($fileName) ) $fileSize = sprintf( ceil( @filesize( $fileName ) / 1024 ) );
    // now let's print it.
    if ( !$colIsOpened ) { 
      echo COL_OPEN_HTML;
      $colIsOpened = true; 
    }
    if ( $rowPos > $filesPerCol ) { 
      echo COL_CLOSE_HTML;	// close the current column
      echo COL_OPEN_HTML;	// Open a new column
      $colIsOpened = true;
      $rowPos = 1;
    }    
    echo LINK_OPEN_HTML;
    printf( FILE_NUM_HTML, $fileNum );
    printf( FILE_LINK_HTML, $fileUrl, $fileName, cutFileName( $fileName ) );
    if ( isset($fileSize) ) printf( FILE_SIZE_HTML, $fileSize );
    echo LINK_CLOSE_HTML;
    $rowPos++;
    $i++;
  } // while 
  
  if ( $colIsOpened ) { echo COL_CLOSE_HTML; }
  echo "</tr>\n</table>\n\n";
}



function displayURL($fileName)
{
  return $_SERVER['PHP_SELF'] . '?img=' . rawurlencode($fileName);
}



function endOfScript($noFiles = false)
{
  if ($noFiles) echo "\n<p class=\"error\">There are no files in this directory.</p>\n";
  if ( file_exists(FOOTER) ) {
    echo "\n<div id='footer'>\n";
    readfile(FOOTER);
    echo "\n</div><!-- id='footer' -->";
  }
  echo "\n\n\n<div id='version'>\nDBIV &mdash; Directory Browser &amp; Image Viewer. Released ", RELEASE_DATE, "\n</div>\n";
  echo "\n\n</div><!-- id='inhalt' -->\n</body></html>";
  exit();
}

function getFileNum($fileName)
{
  global $dirImages;
  $i = 0;
  foreach($dirImages as $file) {
   if ( $fileName == $file ) return $i;
   $i++;
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
 <title>Browsing <?php echo $_SERVER['PHP_SELF']; ?></title>
 <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
 <style type="text/css" media="all">
  * { font-size: 10px; font-family: Verdana, Arial, Helvetica, sans-serif; }
  html, body { margin: 0; padding: 0; }
  #inhalt { padding: 1em; }
  h1 { background: black; color: white;  margin: -1em; margin-bottom: 1em; padding: 1em; border-bottom: 1px solid white; }
  #header { background: #afafaf; color: white; margin: -1em; padding: 1em; padding-bottom: 2em; border-top: 1px solid black; }
  #navLinks { margin: -1em; padding: 1em; border-bottom: 1px solid #484848; border-top: 1px solid black; color: silver; background: #eee; }
  #navLinks a:visited { color: #484848; }
  #navLinks a:hover { color: white; } 
  .error { color: red; font-wieght: bold; margin: 5em;}
  #image { margin-top: 4em; }
  table, td { margin: 0; padding: 0; border-collapse: collapse; }  
  .colCell td { padding: 0.2em; padding-left: 0; }   
  .colCell { vertical-align: top; }
  #fileName { margin-bottom: 0.2em; }
  #img { border: 1px solid black; }
  #imgDesc { width: 80ex; margin-top: 1em; }  
  .fileSize { margin-right: 2em; color: silver; }  
  .fileNum { font-weight: bold; }  
  .fileListingDiv { margin: 4em 0; }  
  a { text-decoration: none; padding: 0.2em; color: #484848; font-weight: bold; }  
  a:visited { color: #a2a2a2; }  
  a:hover { color: white; background: red; }  
  h2 { margin-bottom: 0.5em; border-bottom: 1px solid black; }   
  #version { margin: 3em -1em -1em -1em; padding: 1em; padding-left 0; background: #d2d2d2; color: white; }
  #version a { color: white };
  #version a:hover { background: #a2a2a2 ; } 
 </style>
</head><body>
<div id="inhalt">



<?php

getFilesToList();
echo '<h1>Browsing ', dirname($_SERVER['PHP_SELF']), "</h1>\n\n";

if ( file_exists(HEADER) ) {
  echo "<div id='header'>\n";
  readfile(HEADER);
  echo "\n</div><!-- id='header' -->\n\n";
}

if ( isset($_GET['img']) AND $_GET['img'] !== '' ) {
  $decReq = rawurldecode($_GET['img']); 
  if ( file_exists($decReq) AND fileCanBeListed($decReq) ) $imgToDisplay = $decReq;
  else echo "\n<p class=\"error\">The file you requested does not exist.</p>\n";
}

if ( $isEmpty ) endOfScript($isEmpty);

if ( isset($dirImages) ) {
 if ( !isset($imgToDisplay) ) $imgToDisplay = $dirImages[0]; 
 echo "\n\n<div id='navLinks'>";
 $fileNum = getFileNum($imgToDisplay);
 
 if ( isset($dirImages[$fileNum - 1]) ) echo '<a href="', displayURL($dirImages[$fileNum - 1]), '">Previous</a> | ';
 else echo 'Previous | ';
 
 if ( isset($dirImages[$fileNum + 1]) ) echo '<a href="', displayURL($dirImages[$fileNum + 1]), '">Next</a> |'; 
 else echo 'Next | ';
 
 echo "<a href='#listing'> Jump to file listing</a></div>\n\n";
 echo "<div id='image'><p id='fileName'>", sprintf( '%03d', $fileNum + 1 ), ': ', $imgToDisplay, "</p>\n";
 echo "<div><img id='img' src='", rawurlencode($imgToDisplay), "' alt=''></div>\n\n";
 if ( file_exists($imgToDisplay . DESC_FILE_EXT) ) {
   echo "<div id='imgDesc'>\n";
   readfile($imgToDisplay . DESC_FILE_EXT);
   echo "\n</div><!-- id='imgDesc' -->\n\n";
 }
 echo '<a name="listing"></a>';
 echo "</div><div id='imageListingDiv' class='fileListingDiv'>\n<h2>Image files:</h2>\n"; 
 printFileListing( $dirImages, COL_NUM, $_SERVER['PHP_SELF'] . '?img=%s' );
 echo '</div><!-- id="imageListingDiv" -->';
}

if ( isset($dirNonImages) ) {
  echo '<div id="nonImageListing" class="fileListingDiv"><h2>Non-image files:</h2>';
  printFileListing( $dirNonImages, COL_NUM );
  echo '</div><!-- id="nonImageListing" -->';
}

if ( isset($directories) ) {
  echo '<div id="directoryListing" class="fileListingDiv"><h2>Directories:</h2>';
  printFileListing( $directories, COL_NUM );
  echo '</div><!-- id="directoryListing" -->';
}

endOfScript(); 
?>