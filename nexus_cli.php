#!/usr/bin/php

<?php
  include_once("lib/NexusGallery.php");

  # Main
  $ng = new NexusGallery(1); # True so we get debug

#  $ng->debugQueue();
#  list($image, $time_till_next) = $ng->getImage();

  $galleries = $ng->listAllGalleries(); 
  output($galleries);
  $ng->setIncludedGalleries(Array('Cats', 'DeviantArt', 'Grow'));
  $galleries = $ng->listAllGalleries(); 
  output($galleries);

  $ng->setImagePersistence(100);
  echo "Quick sleep..\n";
  sleep(2);

  $ng->resetOverrides();
  $galleries = $ng->listAllGalleries(); 
  output($galleries);

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
