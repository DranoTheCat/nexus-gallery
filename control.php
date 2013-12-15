<?php

require_once("lib/NexusGallery.php");

$ng = new NexusGallery();
$url = $ng->getConfig('gallery_url');

?>

<html><head><title>Nexus Control</title>
<style>
body,html {
 margin: 0;
 padding: 0;
 height: 100%;
 width: 100%;
 background-color: black;
 color: white;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
<script type="text/javascript">
$(function() {
 timerAjax();
});
$.ajaxSetup({ cache: false });
var ajaxInAction = false;
function timerAjax() {
 if (ajaxInAction) { return; }
 ajaxInAction = true;
 $.ajax({
  url: "ajax.php",
  success: function(result){
   var jsonData = eval('(' + result + ')');
   var timeLeft = jsonData['timeLeft'] * 1000;

   document.getElementById('image').innerHTML = jsonData['imageName'];
   document.getElementById('taglist').innerHTML = jsonData['tags'];

   setTimeout('timerAjax();', timeLeft);
   ajaxInAction = false;
  }
 });
}

var displayId = 1;
function control(command,param) {
 $.ajax({
  url: "ajax.php?command=" + command,
  success: function(result){
   var jsonData = eval('(' + result + ')');
   if (jsonData['mode'] == 'alert') {
    alert(jsonData['string']);
   } else if (jsonData['mode'] == 'display') {
    $('#data').append('<div id="display' + displayId + '">' + jsonData['string'] + '</div>');
    setTimeout("removeDiv('display" + displayId + "');", 1000*jsonData['delay']);
   } else if (jsonData['mode'] == 'populateGalleries') {
    var mystr = '';
    $.each(jsonData['galleries'], function(k, v) {
     if (v) {
      mystr = mystr + '<a href="#" onclick="removeGallery(\'removeGallery\',\'' + k + '\'); return false;" style="color: purple;">' + k + '</a><br>';
     } else {
      mystr = mystr + '<a href="#" onclick="control(\'addGallery\',\'' + k + '\'); return false;" style="color: green;">' + k + '</a><br>';
     }
    });
    $('#gallerylist').html(mystr);
   }
  }
 });
}

function removeDiv(id) {
 $('#' + id).remove();
}

</script>
</head><body>
<center><h1><div id=image></div></h1></center>
<center><h3><div id=taglist></div></h3></center>
<center><h3><div id=data></div></h3></center>
<center>[ <a href="#" onclick="control('test'); return false;">Test</a> | <a href="#" onclick="control('populateGalleries'); return false;">Galleries</a> ]</center>
<center><div id=gallerylist></div></center>
</body></html>
