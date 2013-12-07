<?

/*************
 * Slideshow app
 *************/


require_once("lib/NexusGallery.php");
$ng = new NexusGallery();

?>
<html><head><title>Nexus Gallery</title>
<style>
html,body {
 margin: 0;
 padding: 0;
 background: black;
 text-color: white;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
<script type="text/javascript">
$(function() {
 slideshowAjax();
});
$.ajaxSetup({ cache: false });
var ajaxInAction = false;
var curImg = '';
function slideshowAjax() {
 if (ajaxInAction) { return; }
 ajaxInAction = true;
 $.ajax({
  url: "ajax.php",
  success: function(result){
   if (result != curImg) {
    document.getElementById('image').src = result;
    curImg = result;
   }
//   setTimeout('slideshowAjax();', <? echo round($ng->getConfig('gallery_refresh_delay') * 1000); ?>);
   ajaxInAction = false;
  }
 });
}
</script>
</head><body>
<?

echo "<img src=\"loading.gif\" id=image>";
echo "</body></html>";
