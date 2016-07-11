<?php
class download
{
 function test1234()
 {
  echo 'hi';
 }
 
 function GenGetKey($file)
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
   $dom->save('./data/getkeys.xml');
  }
  $RandomString = generateRandomString();

  $locking = true;
  $nloops = 0;
  while($locking)
  {
   $nloops=$nloops+1;
   $handle = @fopen('./data/getkeys.xml.lock', 'x');
   if($handle != false){$locking = false;fclose($handle);}
   if($nloops >= 1000){exit();}
  }
  
  $dom = new DOMDocument;
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->load('./data/getkeys.xml');
  $root = $dom->documentElement;
  $key = $dom->createElement("_$RandomString", $file);
  $key = $root->appendChild($key);
  $dom->save('./data/getkeys.xml');
  unlink('./data/getkeys.xml.lock');
  
  return $RandomString;
 } 
}
?>
