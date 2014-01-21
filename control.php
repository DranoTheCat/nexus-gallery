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

#taglist {
 max-width: 300px;
 margin-left: 50px;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
<script type="text/javascript">
var image_persistence = <?php echo $ng->getConfig('image_persistence'); ?>;
var enabledTags = [ ];
var tags = [ ];
$(function() {
 timerAjax();
 control('populateGalleries');
 $('#imagePersistenceInput').val(image_persistence);
});
$.ajaxSetup({ cache: false });
var ajaxInAction = false;
var ajaxTimer = false;
function timerAjax() {
 if (ajaxInAction) { return; }
 clearTimeout(ajaxTimer);
 ajaxInAction = true;
 $.ajax({
  url: "ajax.php",
  success: function(result){
   var jsonData = eval('(' + result + ')');
   var timeLeft = jsonData['timeLeft'] * 1000;
   progressBar(jsonData['timeLeft']);

   document.getElementById('image').innerHTML = jsonData['imageName'];
   if (jsonData['enabledTags']) {
    enabledTags = jsonData['enabledTags'];
    populateTags();
   }

   ajaxTimer = setTimeout('timerAjax();', timeLeft+1000);
   ajaxInAction = false;
  }
 });
}

var excludes = false;
var displayId = 1;
function control(command,param) {
 $.ajax({
  url: "ajax.php?command=" + command + "&param=" + param,
  success: function(result){
   var jsonData = eval('(' + result + ')');
   if (jsonData['image_persistence']) {
    if (jsonData['image_persistence'] != image_persistence) doDisplay("Image Persistence Changed To: " + jsonData['image_persistence']);
    image_persistence = jsonData['image_persistence'];
    $('#imagePersistenceInput').val(image_persistence);
    timerAjax();
   }
   if (jsonData['tags']) {
    tags = jsonData['tags'];
   }
   if (jsonData['enabledTags']) {
    enabledTags = jsonData['enabledTags'];
    populateTags();
   }
   if (jsonData['excludes']) {
    excludes = jsonData['excludes'];
   }
   if (jsonData['js']) eval(jsonData['js']);
   if (jsonData['mode'] == 'alert') {
    alert(jsonData['string']);
   } else if (jsonData['mode'] == 'display') {
    doDisplay(jsonData['string'], jsonData['delay']);
   } else if (jsonData['mode'] == 'populateGalleries') {
    populateGalleries(jsonData['galleries']);
   }
  }
 });
}

function addNewTag() {
 control('addTag',$('#newtag').val());
}

function populateTags() {
 var mystr = '<table><tr><td>';
 var first = true;
 var pcmd = '';
 $.each(tags, function(k,v) {
  var sty = '';
  if ($.inArray(v, enabledTags) !== -1) { 
   sty = ' style="color: green;"';
   pcmd = 'remTag';
  } else {
   pcmd = 'addTag'; 
  }
  if (!first) mystr = mystr + ', ';
  first = false;
  mystr = mystr + '<a href="#" onclick="control(\'' + pcmd + '\',\''+v+'\');" title="' + v + '"' + sty + '>' + v + '</a>';
 });
 mystr = mystr + '</td></tr></table>';
 $('#taglist').html(mystr);
}

function populateGalleries(galleries) {
 var mystr = '<table><tr><td>';
 var mystr = '<table>';
 $.each(galleries, function(k, v) {
  if (v) {
   var funcName = 'removeGallery';
   var initColor = 'purple';
  } else {
   var funcName = 'addGallery';
   var initColor = 'green';
  }
  mystr = mystr + '<tr><td align=right>';
  mystr = mystr + '<span style="color: green;">' + k + '</span>';
  mystr = mystr + '</td><td>';
  mystr = mystr + '<span class=controls>';
  mystr = mystr + ' [<a href="#" onclick="control(\'resetCounters\', \'' + k + '\');" title="Reset Counters" class=controls>RC</a>]';
  mystr = mystr + ' [<a href="#" onclick="control(\'' + funcName + '\',\'' + k + '\'); return false;" style="color: ' + initColor + ';" class=controls>Inc</a>]';
  if ($.inArray(k, excludes) !== -1) {
   mystr = mystr + ' [<a href="#" onclick="control(\'unExclude\',\'' + k + '\'); return false;" style="color: purple;" class=controls>Exc</a>]';
  } else {
   mystr = mystr + ' [<a href="#" onclick="control(\'exclude\',\'' + k + '\'); return false;" style="color: green;" class=controls>Exc</a>]';
  }
  mystr = mystr + ' [<a href="#" onclick="control(\'moveImageHere\', \'' + k + '\');" title="Move Image Here" class=controls>MV</a>]';
  mystr = mystr + '</span>';
  mystr = mystr + '</td></tr>';
 });
 mystr = mystr + '</table>';
 mystr = mystr + '</td></tr></table>';
 $('#gallerylist').html(mystr);
 populateTags();
}

function doDisplay(str,delay) {
 if (!delay) delay = 5;
 $('#data').append('<div id="display' + displayId + '">' + str + '</div>');
 setTimeout("removeDiv('display" + displayId + "');", 1000*delay);
 displayId = displayId + 1;
}

var progressTimer = false;
function progressBar(remainingTime) {
 clearTimeout(progressTimer);
 var percent = (remainingTime / image_persistence) * 100;
 var barWidth = $('#progressBar').width()-2;
 var pixelsPerPercent = barWidth / 100;
 var newWidth = Math.floor(pixelsPerPercent * percent);
  
 $('#progressFill').animate({"width":newWidth+"px"},"slow");
 var remMinusOne = remainingTime - 1;
 progressTimer = setTimeout("progressBar("+remMinusOne+")", 1000);
}

function removeDiv(id) {
 $('#' + id).remove();
}

var ipTimer = false;
function changeImagePersist() {
 var newValue = $('#imagePersistenceInput').val();
 if (!newValue) return;
 if (!$.isNumeric(newValue)) return; 
 if (newValue <= 0) return;
 control('imagePersistence', newValue);
}

</script>
</head><body>
<center><h1><a href="#" onclick="control('thumbsDown');"><img src="images/thumbsDown.png" width=24 height=24></a>&nbsp;&nbsp;<span id=image></span>&nbsp;&nbsp;<a href="#" onclick="control('thumbsUp');"><img src="images/thumbsUp.png" width=24 height=24></a></h1></center>
<center><span id="progressBar"><span id="progressFill">&nbsp;</span></span></center>
<center>[ <a href="#" onclick="control('emptyQueue');">Empty Queue</a> | <a href="#" onclick="control('resetCounters');">Reset global counters</a> | <a href="#" onclick="control('unsortImage');">Unsort Image</a> | Time Per Image: <input type=text id="imagePersistenceInput" onchange="changeImagePersist();"> ]</center>
<center><h3><div id=data></div></h3></center>
<center><table border=0 cellpadding=0 cellspacing=0><tr><td>
<center><div id=gallerylist></div></center>
</td><td valign=top>
<center><input type=text id=newtag onchange="addNewTag();"></center>
<center><h3><div id=taglist></div></h3></center>
</td></tr></table></center>
</body></html>
