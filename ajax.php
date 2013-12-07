<?
require_once("lib/NexusGallery.php");
$ng = new NexusGallery();

$next = $ng->getImage();

echo json_encode($next);

?>
