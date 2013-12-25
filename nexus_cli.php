#!/usr/bin/php

<?php
  include_once("lib/NexusGallery.php");

  # Main
  $ng = new NexusGallery(1); # True so we get debug

#  $ng->debugQueue();
  list($image, $time_till_next) = $ng->getImage();
  $ng->debugQueue();
  echo "Image: $image\n";

#  $galleries = $ng->listAllGalleries(); 
#  output($galleries);
#  $ng->setIncludedGalleries(Array('Cats', 'DeviantArt', 'Grow'));
#  $galleries = $ng->listAllGalleries(); 
#  output($galleries);

#  $ng->setImagePersistence(100);
#  echo "Quick sleep..\n";
#  sleep(2);

#  $ng->resetOverrides();
#  $galleries = $ng->listAllGalleries(); 
#  output($galleries);

  #$ng->deleteImage(); # Warning!  Not been tested yet -- recycle bin
/*

  print_r($ng->listImageTags());
  $ng->addTag('testing');
  $ng->addTag('testing2');
  print_r($ng->listImageTags());
  $ng->removeTag('testing2');
  print_r($ng->listImageTags());
  $ng->removeTag('testing3');
  print_r($ng->listImageTags());

  print_r($ng->listAllTags());
*/

  exit;

function output($galleries) {
  print "\nOutput Example:\n\n";
  foreach ($galleries as $k => $v) {
    if ($v)
      print "[X] $k\n";
    else
      print "[ ] $k\n";
  }
}
?>
