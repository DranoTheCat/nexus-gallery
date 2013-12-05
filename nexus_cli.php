#!/usr/bin/php

<?php
  include_once("lib/NexusGallery.php");

  # Main
  $ng = new NexusGallery();
  print $ng->nextImage();

?>
