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
 var maxW = $(document).width();
 var maxH = $(document).height();
 var ratio = currH / currW;
 if (ratio > 1){
  if (currH > maxH) {
   currH = maxH;
   currW = currH / ratio;
  } else if (currW > maxW) {
   currW = maxW;
   currH = currW * ratio;
  }
 } else {
  if (currW > maxW) {
   currW = maxW;
   currH = currW * ratio;
  } else if (currH > maxH) {
   currH = maxH;
   currW = currH / ratio;
  }
 }
 return [currW, currH];
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

echo "<img src=\"loading.gif\" id=image>";
#echo "<div id=imagediv></div>";
echo "</body></html>";
