<?
require_once("lib/NexusGallery.php");
global $ng;
$ng = new NexusGallery();


$command = $_GET['command'];

if ($command) {
 if (function_exists('ajax_'.$command)) call_user_func('ajax_'.$command);
 else return ajax_error("No such function: " . $command);
} else
 echo json();

function ajax_populateGalleries() {
 global $ng;
 $galleries = $ng->listAllGalleries();
 echo json(array('mode'=>'populateGalleries','galleries'=>$galleries));
}

function ajax_test() {
 echo json(array('mode'=>'display', 'string'=>"This is a test", 'delay'=>10));
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
