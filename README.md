nexus-gallery
=============

A better picture frame, primarily for tablets

Features:
  * Consistent image selection across all devices, based on time windows
  * Galleries
    - Galleries are fully dynamic, based on the folders in the gallery\_base setting
    - Subgalleries of unlimited nesting depth are supported
    - Gallery configuration is fully discovered from the filesystem

Road Map:
  * slideshow.php -- Display the image slideshow, very minimal UI
  * sort.php      -- Sort new images into galleries

TODO:
  * Real-time decision of what subgalleries are allowable, instead of hard-coded in config
