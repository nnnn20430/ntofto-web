<?php
class download
{
 private function readfile_chunked($filename,$retbytes=true) { 
   $chunksize = 10*(1024*1024); // how many bytes per chunk 
   $buffer = ''; 
   $cnt =0; 
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
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
 private function DownloadFile($file){ // $file = include path
  if(file_exists($file)){
   set_time_limit(0);
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
   $this->readfile_chunked($file);
   exit;
  }
 }
 public function CleanOldKeys()
 {
  $Semaphore_key = ftok('./download.php', 'l');
  $Semaphore_id = sem_get($Semaphore_key);
  sem_acquire($Semaphore_id);
  
  $dom = new DOMDocument('1.0', 'UTF-8');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  if(file_exists("./data/getkeys.xml"))
  {
   $dom->load('./data/getkeys.xml');
   $keyCleanTimeNode = $dom->getElementsByTagName("keyCleanTime")->item(0);
   if($keyCleanTimeNode->nodeValue <= time())
   {
    $keyCleanTimeNode->nodeValue = "".time()+60;
    $keyExpireNodes = $dom->getElementsByTagName("GetKeyExpireDate");
    for($i = $keyExpireNodes->length; --$i >= 0;)
    {
     $keyExpireNode = $keyExpireNodes->item($i);
     if($keyExpireNode->nodeValue <= time())
     {
      $keyExpireNodeParent=$keyExpireNode->parentNode;
      $keyExpireNodeParent->parentNode->removeChild($keyExpireNodeParent);
     }
    }
    $dom->save('./data/getkeys.xml');
   }
  }
  sem_release($Semaphore_id);
 }
 
 public function GenGetKey($file)
 {
  $file = str_replace('/', '!SlashChar!', $file);
  function generateRandomString($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }
  if(!file_exists('./data/getkeys.xml'))
  {
   $dom = new DOMDocument('1.0', 'UTF-8');
   $dom->preserveWhiteSpace = false;
   $dom->formatOutput = true;
   $root = $dom->createElement('root');
   $root = $dom->appendChild($root);
   $keyCleanTimeNode = $dom->createElement('keyCleanTime', "".time()+60);
   $keyCleanTimeNode = $root->appendChild($keyCleanTimeNode);
   $dom->save('./data/getkeys.xml');
  }
  $RandomString = generateRandomString();
  $Semaphore_key = ftok('./download.php', 'l');
  $Semaphore_id = sem_get($Semaphore_key);
  sem_acquire($Semaphore_id);
  
  $dom = new DOMDocument;
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->load('./data/getkeys.xml');
  $root = $dom->documentElement;
  $keyroot = $dom->createElement("key");
  $keyroot = $root->appendChild($keyroot);
  $key = $dom->createElement("_$RandomString", $file);
  $key = $keyroot->appendChild($key);
  $keyexpire = $dom->createElement("GetKeyExpireDate", "".time()+60);
  $keyexpire = $keyroot->appendChild($keyexpire);
  $dom->save('./data/getkeys.xml');
  sem_release($Semaphore_id);
  
  return $RandomString;
 }
 public function Get($GetKey)
 {
  $Semaphore_key = ftok('./download.php', 'l');
  $Semaphore_id = sem_get($Semaphore_key);
  sem_acquire($Semaphore_id);
  
  $dom = new DOMDocument('1.0', 'UTF-8');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  if(file_exists("./data/getkeys.xml"))
  {
   $dom->load('./data/getkeys.xml');
   $keyNode = $dom->getElementsByTagName("_$GetKey")->item(0);
   if($keyNode != null)
   {
    $keyNodeParent=$keyNode->parentNode;
    $keyNodeParent->parentNode->removeChild($keyNodeParent);
    $dom->save('./data/getkeys.xml');
    sem_release($Semaphore_id);
    $this->DownloadFile(str_replace('!SlashChar!', '/', $keyNode->nodeValue));
   }
  }
 }
}
if(isset($_GET["get"]))
{
 $download=new download();
 $download->CleanOldKeys();
 $download->Get($_GET["get"]);
}
?>