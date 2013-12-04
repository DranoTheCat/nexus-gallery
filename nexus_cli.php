#!/usr/bin/php

<?php
  include_once("lib/NexusGallery.php");

  # YAML loading and fixup
  $config = yaml_parse_file("conf/config.yaml");
  $config['allowed_galleries'] = preg_replace("/\s+/", '', $config['allowed_galleries']);
  $config['excluded_galleries'] = preg_replace("/\s+/", '', $config['excluded_galleries']);

  # Main
  $ng = new NexusGallery($config);
  print $ng->nextImage();

?>
