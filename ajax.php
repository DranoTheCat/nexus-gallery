<?php
require_once("lib/NexusGallery.php");
global $ng;
$ng = new NexusGallery();

$command = false;
if (isset($_GET['command'])) $command = $_GET['command'];

if ($command) {
 if (function_exists('ajax_'.$command)) call_user_func('ajax_'.$command);
 else return ajax_error("No such function: " . $command);
} else
 echo json();

function ajax_moveImage() {
 global $ng;
 $ng->moveImage($_GET['file'], $_GET['dest']);
}

function ajax_emptyQueue() {
 global $ng;
 $ng->truncateQueue();
 echo json(array('mode'=>'display', 'string'=>"Emptied the queue", 'delay'=>3));
} 

function ajax_unsortImage() {
 global $ng;
 $ng->unsortImage();
 echo json(array('mode'=>'display', 'string'=>"Unsorted Image", 'delay'=>3));
} 

function ajax_thumbsUp() {
 global $ng;
 $ng->thumbsUp();
 echo json(array('mode'=>'display', 'string'=>"Added a point! :)", 'delay'=>3));
}

function ajax_thumbsDown() {
 global $ng;
 $ng->thumbsDown();
 echo json(array('mode'=>'display', 'string'=>"Added a thumbs down :(", 'delay'=>3));
}

function ajax_resetCounters() {
 global $ng;
 if ($_GET['param']) {
  $ng->resetImageCounters($_GET['param']);
  echo json(array('mode'=>'display', 'string'=>"Reset image counters for " . $_GET['param'], 'delay'=>3));
 } else {
  $ng->resetImageCounters();
  echo json(array('mode'=>'display', 'string'=>"Reset all image counters", 'delay'=>3));
 }
}

function ajax_imagePersistence() {
 global $ng;
 $ng->setImagePersistence($_GET['param']);
 $ng->truncateQueue();
 echo json(array('image_persistence'=>$_GET['param']));
}

function ajax_addTag() {
 global $ng;
 $ng->addTag($_GET['param']);
 $tags = $ng->listAllTags();
 echo json(array('mode'=>'display', 'string'=>"Added Tag " . $_GET['param'], 'delay'=>3, 'tags'=>$tags, 'js'=>'$(\'#newtag\').val(\'\');'));
}

function ajax_remTag() {
 global $ng;
 $ng->removeTag($_GET['param']);
 $tags = $ng->listAllTags();
 echo json(array('mode'=>'display', 'string'=>"Removed Tag " . $_GET['param'], 'delay'=>3, 'tags'=>$tags, 'js'=>'$(\'#newtag\').val(\'\');'));
}

function ajax_exclude() {
 global $ng;
 $newExclude = $_GET['param'];
 $excludes = $ng->listExcludedGalleries();
 if (in_array($newExclude, $excludes)) { ajax_error("Gallery already excluded."); return; }
 $excludes[] = $newExclude;
 $ng->setExcludedGalleries($excludes);
 ajax_populateGalleries();
}

function ajax_unExclude() {
 global $ng;
 $exclude = $_GET['param'];
 $excludes = $ng->listExcludedGalleries();
 if (!in_array($exclude, $excludes)) { ajax_error("Gallery is not excluded."); return; }
 $newExcludes = array();
 foreach ($excludes as $k => $v) {
  if ($v == $exclude) continue;
  $newExcludes[] = $v;
 }
 $ng->setExcludedGalleries($newExcludes);
 ajax_populateGalleries();
}

function ajax_addGallery() {
 global $ng,$_GET;
 $galleries = $ng->listAllGalleries();
 $galleries_enabled = array();
 foreach ($galleries as $gallery => $enabled) {
  if ($enabled) $galleries_enabled[] = $gallery;
 }
 $new = $_GET['param'];
 if (!$new) { ajax_error("Invalid gallery passed"); return; }
 if (in_array($new, $galleries_enabled)) { ajax_error("Gallery already enabled."); return; }
 if (!isset($galleries[$new])) { ajax_error("Unknown gallery passed: " . $new); return; }
 $galleries_enabled[] = $new;
 $ng->setIncludedGalleries($galleries_enabled);
 ajax_populateGalleries();
}

function ajax_removeGallery() {
 global $ng,$_GET;
 $galleries = $ng->listAllGalleries();
 $galleries_enabled = array();
 $toRemove = $_GET['param'];
 if (!isset($galleries[$toRemove])) { ajax_error("Unknown gallery passed: " . $toRemove); return; }
 foreach ($galleries as $gallery => $enabled) {
  if ($enabled && ($gallery != $toRemove)) $galleries_enabled[] = $gallery;
 }
 $ng->setIncludedGalleries($galleries_enabled);
 ajax_populateGalleries();
}

function ajax_populateGalleries() {
 global $ng;
 $galleries = $ng->listAllGalleries();
 $excludes = $ng->listExcludedGalleries();
 $tags = $ng->listAllTags();
 echo json(array('mode'=>'populateGalleries','galleries'=>$galleries,'excludes'=>$excludes,'tags'=>$tags));
}

function ajax_error($string) {
 echo json(array('mode'=>'alert', 'string'=>'Error: ' . $string));
}

function json($data = array()) {
 global $ng;
 $next = $ng->getImage();
 $itags = $ng->listImageTags();
 return
  json_encode(
   array_merge(
    $next,
    $data,
    array(
     'image'=>$next[0],
     'timeLeft'=>$next[1],
     'imageName'=>basename($next[0]),
     'enabledTags'=>$itags
    )
   )
  );
}

?>
