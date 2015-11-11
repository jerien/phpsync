<?php
$agent_host = $_GET["agent_host"];
$dir = "";

function remoteData($agent_host,$dir){
  if($_SERVER["SERVER_NAME"] == $agent_host || $agent_host == "" || $_GET["data"]){ return ""; }
  $ch = curl_init(); 
  curl_setopt($ch, CURLOPT_URL, $agent_host.$_SERVER["SCRIPT_NAME"]."?data=1"); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $remoteData = curl_exec($ch); 
  curl_close($ch);
  return $remoteData;
}

function localData($dir){
  global $agent_host;
  $remoteData = remoteData($agent_host,$dir);
  $missingLocal = $remoteData;

  foreach (scanFiles($_SERVER['DOCUMENT_ROOT'].$dir) as $value){
    $path = "[".str_replace($_SERVER['DOCUMENT_ROOT'].$dir,"",$value)."]";
    $missingLocal = str_replace($path."\n","",$missingLocal);
    if(strpos($remoteData,$path)===false || $remoteData == ""){
      $localData .= $path."\n";
    }
  }
  return $localData.'</pre><pre style="color:red;">'.$missingLocal;
}

function scanFiles($rootDir, $allData=array()){
  $invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd", "/cache/", "/tmp/", "index.html");
  //$visibleFileNames = array("media");
  
  $dirContent = scandir($rootDir);
  foreach($dirContent as $key => $content){
    $path = $rootDir.'/'.$content;
    if(!in_array($content, $invisibleFileNames) && !in_array("/".$content."/", $invisibleFileNames)){
      if(is_file($path) && is_readable($path)){
        $allData[] = $path;
      } elseif(is_dir($path) && is_readable($path)){
        $allData = scanFiles($path, $allData);
      }
    }
  }
  return $allData;
}

function GetFileCount($dir){ 
  $files = array(); 
  $directory = opendir($dir); 
  while($item = readdir($directory)){ 
   if(($item != ".") && ($item != "..") && ($item != ".svn") ){ 
      $files[] = $item;
   } 
  } 
  $numFiles = count($files); 
  return $numFiles; 
}

function GetDirectorySize($path){
  $bytestotal = 0;
  $path = realpath($path);
  if($path!==false){
      foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
          $bytestotal += $object->getSize();
      }
  }
  return format_size($bytestotal);
}

function format_size($size){
  $units = explode(' ', 'B KB MB GB TB PB');
  $mod = 1024;
  for ($i = 0; $size > $mod; $i++) { $size /= $mod; }
  $endIndex = strpos($size, ".")+3;
  return substr( $size, 0, $endIndex).' '.$units[$i];
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<body>
<pre>
<?php echo localData($dir); ?>
</pre>
</body>
</html>
