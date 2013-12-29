<?php

require_once("lib/NexusGallery.php");

$ng = new NexusGallery();
$url = $ng->getConfig('gallery_url');
$image = $ng->getImageToSort();

?>

<html><head><title>Nexus Sort</title>
<style>
body,html {
 margin: 0;
 padding: 0;
 height: 100%;
 width: 100%;
 background-color: black;
 color: white;
}
#progressBar {
 position: relative;
 display: inline-block;
 width: 400px;
 height: 26px;
 border: 1px solid #000;
 background-color: #666;
}

#progressFill {
 position: relative;
 display: inline-block;
 left: 1px;
 width: 0px;
 height: 26px;
 overflow: hidden;
 background-color: #0a0;
 font-family: Arial, Helvetica, sans-serif;
 font-size: 24px;
 float: left;
}

#imagePersistenceInput {
 background-color: #888;
 color: white;
 border: 1px solid #bbb;
 width: 40px;
}

.controls {
 color: #444;
 text-decoration: none;
}

a.controls {
 color: #888;
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
  var newSize = scaleSize($('#image').width(), $('#image').height());
  $('#image').width(newSize[0]);
  $('#image').height(newSize[1]);
});

var ajaxInAction = false;
function moveImage(file, dest) {
  if (ajaxInAction) return;
  //alert("ajax.php?mode=moveImage&file="+file+"&dest="+dest);
  ajaxInAction = true;
  $.ajax({
    url: "ajax.php?mode=moveImage&file="+file+"&dest="+dest, success: function() {
      location.reload();
  }});
}
  
function doDisplay(str,delay) {
 if (!delay) delay = 5;
 $('#data').append('<div id="display' + displayId + '">' + str + '</div>');
 setTimeout("removeDiv('display" + displayId + "');", 1000*delay);
 displayId = displayId + 1;
}
function removeDiv(id) {
 $('#' + id).remove();
}
</script>
</head><body>
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
  <tr height=10% valign=top>
    <td width=100% align=center>
      <?php
  $str = "";
  foreach ($ng->listAllGalleries() as $k => $v) {
    $str .= "[ <a href=# onclick=\"moveImage('" . $image . "', '" . $ng->getConfig('gallery_base') . "/" . $k . "');\">" . $k . "</a> ] ";  
  }
  echo $str;
       ?>
    </td>
  </tr><tr height=80% valign=center>
    <td width=100% align=center>
      <?php
  echo "<img id=image src=\"" . $image . "\">";
      ?>
    </td>
  </tr><tr height=20% valign=bottom>
    <td width=100% align=center> 
      <?php
  $str = "";
  foreach ($ng->listAllGalleries() as $k => $v) {
    $str .= "[ $k ] ";  
  }
  echo $str;
       ?>
    </td>
  <tr>
</table>
</body></html>















