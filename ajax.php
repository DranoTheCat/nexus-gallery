<?
require_once("lib/NexusGallery.php");
$ng = new NexusGallery();

$url = $ng->getConfig('gallery_url');

$next = $ng->nextImage();

$imgurl = $url . '/' . $img;
#echo json_encode($next);

?>
