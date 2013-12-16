<?php

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
 color: white;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
<script type="text/javascript">
function scaleSize(currW, currH){
 var maxW = document.body.clientWidth - 10;
 var maxH = document.body.clientHeight - 10;
 var ratioW = maxW / currW; // Ratio needed to get width to 100%
 var ratioH = maxH / currH; // Ratio needed to get height to 100%
 if (ratioW > ratioH) {     // We should always use the lesser ratio
   return [currW*ratioH, currH*ratioH];
 } else {
   return [currW*ratioW, currH*ratioW];
 }
}
$(function() {
 slideshowAjax();
 //testLoop();
});
$.ajaxSetup({ cache: false });
var ajaxInAction = false;
var url = '<?php echo $url;?>';
var myImg = null;
function slideshowAjax() {
 if (ajaxInAction) { return; }
 ajaxInAction = true;
 $.ajax({
  url: "ajax.php",
  success: function(result){
   var jsonData = eval('(' + result + ')');
   var curImg = url + '/' + jsonData[0];
   var timeLeft = jsonData[1] * 1000;
   myImg = $('<img id="dynamic">');

//myImg.on('load', function(){
   myImg.load(function() {
    var newSize = scaleSize(this.width, this.height);

    $('#image').replaceWith(this);

    document.getElementById('image').width = newSize[0];
    document.getElementById('image').height = newSize[1];
   });

   myImg.attr('id', 'image');
   myImg.attr('src', curImg);

   setTimeout('slideshowAjax();', timeLeft);
   ajaxInAction = false;
  }
 });
}

function testLoop() {
   var newSize = scaleSize(document.getElementById('image').width, document.getElementById('image').height);
 document.getElementById('debugtext').innerHTML = 'W: ' + document.getElementById('image').width + ' ' + 'H: ' + document.getElementById('image').height + ' ' + 'NW: ' + newSize[0] + ' ' + 'NH: ' + newSize[1];
//   document.getElementById('image').width = newSize[0];
//   document.getElementById('image').height = newSize[1];
 setTimeout('testLoop()', 500);
}
</script>
</head><body>
<?php

echo "<table border=0 cellpadding=0 width=100% height=100%><tr height=100% valign=center><td width=100% align=center>";
echo "<img src=\"loading.gif\" id=image>";
echo "</td></tr></table>";
#echo "<div id=imagediv></div>";
echo "</body></html>";
