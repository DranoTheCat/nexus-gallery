<?

/*************
 * Slideshow app
 *************/


require_once("lib/NexusGallery.php");
$ng = new NexusGallery();
$url = $ng->getConfig('gallery_url');

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
var url = '<?echo $url;?>';
function slideshowAjax() {
 if (ajaxInAction) { return; }
 ajaxInAction = true;
 $.ajax({
  url: "ajax.php",
  success: function(result){
   var jsonData = eval('(' + result + ')');
   var curImg = url + '/' + jsonData[0];
   var timeLeft = jsonData[1] * 1000;
   document.getElementById('image').src = curImg;
   setTimeout('slideshowAjax();', timeLeft);
   ajaxInAction = false;
  }
 });
}
</script>
</head><body>
<?

echo "<img src=\"loading.gif\" id=image>";
echo "</body></html>";
