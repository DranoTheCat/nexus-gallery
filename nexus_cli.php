#!/usr/bin/php

<?php
  include_once("lib/NexusGallery.php");

  # Main
  $ng = new NexusGallery(1); # True so we get debug

#  $ng->debugQueue();
#  list($image, $time_till_next) = $ng->getImage();

  $galleries = $ng->listAllGalleries(); 
  output($galleries);
  $ng->setAllowedGalleries(Array('Cats', 'DeviantArt', 'Grow'));
  $galleries = $ng->listAllGalleries(); 
  output($galleries);

  $ng->setImagePersistence(100);


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
