<?php
/*
in borwser do this in url
/test1234.php?file=compute.php
and you will get download for compute.php
*/
function readfile_chunked($filename,$offset=null,$retbytes=true) { 
   $chunksize = 1*(1024*1024); // how many bytes per chunk 
   $buffer = ''; 
   $cnt =0; 
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
   } 
   if ($offset != null){
    fseek($handle, $offset);
   }
   while (!feof($handle)) { 
       $buffer = fread($handle, $chunksize); 
       echo $buffer; 
       ob_flush(); 
       flush(); 
       if ($retbytes) { 
           $cnt += strlen($buffer); 
       } 
   } 
       $status = fclose($handle); 
   if ($retbytes && $status) { 
       return $cnt; // return num. bytes delivered like readfile() does. 
   } 
   return $status; 

}

function DownloadFile($file, $offset=null) { // $file = include path
        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            if ($offset != null){
             readfile_chunked($file, $offset);
            }else{
             readfile_chunked($file);
            }
            exit;
        }

    }

if(isset($_GET['file'])){
 $file = $_GET['file'];


 $filesize = filesize($file);

 $offset = 0;
 $length = $filesize;

 if ( isset($_SERVER['HTTP_RANGE']) ) {
 	// if the HTTP_RANGE header is set we're dealing with partial content
 
 	$partialContent = true;

	// find the requested range
	// this might be too simplistic, apparently the client can request
	// multiple ranges, which can become pretty complex, so ignore it for now
	preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);

	$offset = intval($matches[1]);
	$length = intval($matches[2]) - $offset;
 } else {
	$partialContent = false;
 }

 if ( $partialContent ) {
	// output the right headers for partial content

	header('HTTP/1.1 206 Partial Content');

	header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $filesize);
        DownloadFile($file, $offset);
 }

 DownloadFile($file);
}
?>
