<?php
/**
* 图片大小的格式化,
* @param string $srcImage 原图片的路径信息
* @param int $width 指定图片格式化后的宽度
* @param int $height 指定图片格式化后的高度
* @param string $fileDir 缩放后的文件存放路径
* @param string $type 图片格式化类型,默认为 jpg 格式
* @param string $bestFit 是否强制调整大小以获取最佳效果
* @param string $refresh 是否删除缓存。refresh或空
* @return bool
*/
function imageSizeFormateImagick($srcImage,$width,$height,$fileDir,$type='jpg',$bestFit=true,$refresh)
{   //200,370
    // 获得原图片尺寸
    $srcSize = getimagesize($srcImage);//500,300
    $fix_width = 0;
    $fix_height = 0;
    //根据原图长宽尺寸判断缩放以哪个为准
    if ($srcSize[0] > $srcSize[1]) {
        $fix_width = $width;
        $fix_height = ($width/$srcSize[0])*$srcSize[1];
    } else {
        $fix_width = ($height/$srcSize[1])*$srcSize[0];
        $fix_height = $height;
    }
    if ($fix_width > $width) {
        //$width = $fix_width;
        $height = $fix_height/$fix_width*$width;
    } else if ($fix_height > $height) {
        //$height = $fix_height;
        $width = $fix_width/$fix_height*$height;
    } else {
        $width = $fix_width;
        $height = $fix_height;
    }
    if ($srcSize[0] < $width) {
        $width = $srcSize[0];
    }
    if ($srcSize[1] < $height) {
        $height = $srcSize[1];
    }
    //整型化分辨率
    $width = floor($width);
    $height = floor($height);
    // 以md5(完整路径+分辨率)作为文件名
    $fileName = $srcImage."x".$width."_".$height.".".$type;
    $dstPath = $fileDir.md5($fileName);
    //不重复生成
    if ($refresh=="" && file_exists($dstPath)) {
        return $dstPath;
    } else if ($refresh == "1") {
		unlink($dstPath);
		//die($dstPath);
	}
		
    $imagick = new Imagick ($srcImage);
    if ($type != "gif") {        
        /* 缩略图大小设置 */
        $imagick->thumbnailImage($width,$height,$bestFit);
        /* 缩略图的格式化类型 */
        $imagick->setImageFormat ($type);
        //生成图片
        if( $imagick->writeImage($dstPath) )
        {
            $imagick->destroy();
            return $dstPath;
        }
    } else {
        $dest = new Imagick(); 
        $color_transparent = new ImagickPixel("transparent"); //透明色 
        foreach($imagick as $img){ 
            $page = $img->getImagePage(); 
            $tmp = new Imagick(); 
            $tmp->newImage($page['width'], $page['height'], $color_transparent, 'gif'); 
            $tmp->compositeImage($img, Imagick::COMPOSITE_OVER, $page['x'], $page['y']);             
            $tmp->thumbnailImage($width, $height, true);
            $tmp->setImageDispose($img->getImageDispose());
            //保存第一帧作为缩略图
            $tmp->writeImage($dstPath);
            $tmp->clear();
            return $dstPath;
            /*$dest->addImage($tmp); 
            $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
            $dest->setImageDelay($img->getImageDelay()); 
            $dest->setImageDispose($img->getImageDispose());          */
        } 
        /*$dest->coalesceImages(); 
        $dest->writeImages($dstPath, true); 
          
        $dest->clear(); 
        return $dstPath;*/
    }
    
    return false;
    
}

$filepath = $_GET['filepath'];
$extname = substr(basename($filepath),strrpos(basename($filepath),".")+1);
$resizeW = abs($_GET['size_w']);
$resizeH = abs($_GET['size_h']);
$refresh = isset($_GET['refresh'])?$_GET['refresh']:"";

if(0 == ($resizeW * $resizeH)){
    header('HTTP/1.1 400 Bad Request');
    exit();
}

$filepath = trim($filepath);
$filepath = '/opt/storage_client/'.$filepath;

//本地是修改时间
$isFile = is_file($filepath);
if(!$isFile){
    header('HTTP/1.1 404 Not Found');
    echo $filepath."Not Found";
    exit();
}

$fileDir = "/opt/cache/";
//$newimg = imageSizeFormateGD($filepath,$resizeW,$resizeH,$fileDir."/thumb/");
$newimg = imageSizeFormateImagick($filepath,$resizeW,$resizeH,$fileDir,$extname,true,$refresh);
header('Content-Type: image/jpeg');
header("Last-Modified: " . gmdate ("r", $lastModifyTime));
$fp = fopen($newimg, "r");
echo fread($fp, filesize($newimg));
fclose($fp);
?>
