nexus-gallery
=============

A better picture frame, primarily for tablets

## Features ##
  * Consistent image selection across all devices, based on time windows
  * Galleries
    + Galleries are fully dynamic, based on the folders in the gallery\_base setting
    + Subgalleries of unlimited nesting depth are supported
    + Gallery configuration is fully discovered from the filesystem

## Road Map ##
  * **Milestone 0:**
    + nexus\_cli.php -- Command line testing tool
  * **Milestone 1:**
    + slideshow.php -- Display the image slideshow, very minimal UI
    + control.php   -- Control the image slideshow
      - Thumbs up / Thumbs Down
      - Control which galleries are allowed/disallowed
      - Delete Image
      - Move image back into incoming to be resorted
  * **Milestone 2:**
    + sort.php      -- Sort new images into galleries
  * **Milestone 3:**
    + war.php       -- Image War!

## TODO ##
  * Change slideshow to not poll every 2 seconds, but instead grab current image, next image, how many seconds until the time should change
  * Real-time decision of what subgalleries are allowable, instead of hard-coded in config
