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

function ajax_emptyQueue() {
 global $ng;
 $ng->truncateQueue();
 echo json(array('mode'=>'display', 'string'=>"Emptied the queue", 'delay'=>3));
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
 $ng->resetImageCounters();
 echo json(array('mode'=>'display', 'string'=>"Reset all image counters", 'delay'=>3));
}

function ajax_imagePersistence() {
 global $ng;
 $ng->setImagePersistence($_GET['param']);
 $ng->truncateQueue();
 echo json(array('image_persistence'=>$_GET['param']));
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
 echo json(array('mode'=>'populateGalleries','galleries'=>$galleries));
}

function ajax_error($string) {
 echo json(array('mode'=>'alert', 'string'=>'Error: ' . $string));
}

function json($data = array()) {
 global $ng;
 $next = $ng->getImage();
 return
  json_encode(
   array_merge(
    $next,
    $data,
    array(
     'image'=>$next[0],
     'timeLeft'=>$next[1],
     'imageName'=>basename($next[0]),
     'tags'=>implode(', ', $ng->listImageTags())
    )
   )
  );
}

?>
