<?php
class uploadDatabase
{
 private function LockFile($file, $timeout = 10, $exitOnTimeout = false, $tryToForceLock = true)
 {
  $timeoutTime = time()+$timeout;
  $locking = true;
  #$nloops = 0;
  while($locking)
  {
   #$nloops=$nloops+1;
   $handle = @fopen("$file".".lock", 'x');
   if($handle != false){$locking = false;fclose($handle);}
   if(time() >= $timeoutTime){if($exitOnTimeout){exit();}elseif($tryToForceLock){unlink("$file".".lock");$timeoutTime = time()+$timeout;}}
   #if($nloops >= $giveUpAfter){if($exitOnError){exit();}else{unlink("$file".".lock");$nloops=0}}
  }
 }
 private function UnlockFile($file){
  unlink("$file".".lock");
 }
 public function CleanOldUploads()
 {
  $this->LockFile('./uploads/uploadDatabase.xml');
  
  $dom = new DOMDocument('1.0', 'UTF-8');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  if(file_exists("./uploads/uploadDatabase.xml"))
  {
   $dom->load('./uploads/uploadDatabase.xml');
   $root = $dom->getElementsByTagName("root")->item(0);
   $keyCleanTimeNode = $root->getElementsByTagName("keyCleanTime")->item(0);
   if($keyCleanTimeNode->nodeValue <= time())
   {
    $keyCleanTimeNode->nodeValue = "".time()+60;
    $keyExpireNodes = $root->getElementsByTagName("GetKeyExpireDate");
    for($i = $keyExpireNodes->length; --$i >= 0;)
    {
     $keyExpireNode = $keyExpireNodes->item($i);
     if($keyExpireNode->nodeValue <= time())
     {
      $keyExpireNodeParent=$keyExpireNode->parentNode;
      unlink("./uploads/data/".substr($keyExpireNodeParent->childNodes->item(0)->tagName, 1));
      $keyExpireNodeParent->parentNode->removeChild($keyExpireNodeParent);
     }
    }
    $dom->save('./uploads/uploadDatabase.xml');
   }
  }
  $this->UnlockFile('./uploads/uploadDatabase.xml');
 }
 
 public function GenUploadKey($file)
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
  $this->LockFile('./uploads/uploadDatabase.xml');
  if(!file_exists('./uploads/uploadDatabase.xml'))
  {
   $dom = new DOMDocument('1.0', 'UTF-8');
   $dom->preserveWhiteSpace = false;
   $dom->formatOutput = true;
   $root = $dom->createElement('root');
   $root = $dom->appendChild($root);
   $keyCleanTimeNode = $dom->createElement('keyCleanTime', "".time()+60);
   $keyCleanTimeNode = $root->appendChild($keyCleanTimeNode);
   $dom->save('./uploads/uploadDatabase.xml');
  }
  $RandomString = generateRandomString();
  
  $dom = new DOMDocument;
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->load('./uploads/uploadDatabase.xml');
  $root = $dom->documentElement;
  $keyroot = $dom->createElement("key");
  $keyroot = $root->appendChild($keyroot);
  $key = $dom->createElement("_$RandomString", $file);
  $key = $keyroot->appendChild($key);
  $attr=$dom->createAttribute('id'); $attr->value = 'UploadID'; $key->appendChild($attr); $key->setIdAttribute('id', true);
  $keyexpire = $dom->createElement("GetKeyExpireDate", "".time()+60);
  $keyexpire = $keyroot->appendChild($keyexpire);
  $dom->save('./uploads/uploadDatabase.xml');
  $this->UnlockFile('./uploads/uploadDatabase.xml');
  
  return $RandomString;
 }
 public function ValidateUpload($UploadKey, $NewDirectory = "./uploads/validated/")
 {
  $this->LockFile('./uploads/uploadDatabase.xml');
  
  $dom = new DOMDocument('1.0', 'UTF-8');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  if(file_exists("./uploads/uploadDatabase.xml"))
  {
   $dom->load('./uploads/uploadDatabase.xml');
   $root = $dom->getElementsByTagName("root")->item(0);
   $keyNode = $root->getElementsByTagName("_$UploadKey")->item(0);
   if($keyNode != null)
   {
    $keyNodeParent=$keyNode->parentNode;
    $keyNodeParent->parentNode->removeChild($keyNodeParent);
    $dom->save('./uploads/uploadDatabase.xml');

    rename("./uploads/data/".$UploadKey, "$NewDirectory".$keyNode->nodeValue);
   }
  }
  $this->UnlockFile('./uploads/uploadDatabase.xml');
 }
}
if(isset($_FILES['file'])){
 $uploadDatabase=new uploadDatabase();
 $uploadDatabase->CleanOldUploads();
 $UploadKey=$uploadDatabase->GenUploadKey(str_replace('/', '', $_FILES['file']['name']));
 $file_tmp_name =$_FILES['file']['tmp_name'];
 move_uploaded_file($file_tmp_name,"./uploads/data/".$UploadKey);
 echo $UploadKey;
}
/*
$uploadDatabase=new uploadDatabase();
$uploadDatabase->CleanOldUploads();
$uploadDatabase->ValidateUpload($UploadKey, "./data/".$_POST['username'].'/');
*/
?>
