#!/usr/bin/php

<?php
  include_once("lib/NexusGallery.php");

  # Main
  $ng = new NexusGallery(true); # True so we get debug

  $ng->debugQueue();
  list($image, $time_till_next) = $ng->getImage();

?>
