<?php
$filepath = trim($_GET["filepath"]);
$name = isset($_GET["name"])?$_GET["name"]:"";
$ext = trim($_GET["ext"]);

$filepath = '/opt/storage_client/'.$filepath.$ext;
if (file_exists($filepath) == false){
    header('HTTP/1.1 404 Not Found');
    echo "file not found";
    exit();
}
$fileHandler = fopen($filepath, 'r');
if(false === $fileHandler){
    header('HTTP/1.1 404 Not Found');
    echo "file access error";
    exit();
}
$filename = "";
if (strlen($name) > 0) {
    $filename = $name.".".$ext;
} else {
    $filename = basename($filepath);
}
//echo "<pre>";var_dump($_SERVER["HTTP_USER_AGENT"]);echo "</pre>";exit;
//Mozilla/5.0 (X11; Linux i686; rv:19.0) Gecko/20100101 Firefox/19.0
//Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.81 Safari/537.1
if (strpos($_SERVER["HTTP_USER_AGENT"], "Firefox")=== false) {
    $filename = str_replace('+', '%20', urlencode($filename));
}

Header("Content-type: application/octet-stream");
Header("Accept-Ranges: bytes");
Header("Accept-Length: ".filesize($filepath));
Header("Content-Length: ".filesize($filepath));
Header('Content-Disposition: attachment; filename="'.$filename.'"');
$limit = 1024*1024;
$dlslice = ceil(filesize($filepath)/$limit);
for($i=0;$i<$dlslice;$i++){
    echo fread ($fileHandler, $limit);
}
fclose($fileHandler);
exit();  
