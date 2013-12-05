<?

/*************
 * Slideshow app
 *************/

include_once("lib/NexusGallery.php");

$ng = new NexusGallery();
$url = $ng->getConfig('gallery_url');

$img = $ng->nextImage();
$imgname = basename($img);
$imgurl = $url . '/' . $img;

echo "<html><head><title>Nexus Gallery</title>";
echo "<style>
html,body {
 margin: 0;
 padding: 0;
 background: black;
 text-color: white;
}
</style>";
echo "</head><body>";
echo "<img src=\"" . $imgurl . "\">";
echo "</body></html>";
