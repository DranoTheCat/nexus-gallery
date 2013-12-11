nexus-gallery
=============

A better picture frame, primarily for tablets

## About ##
Nexus Gallery is a gallery system for passive devices acting as picture frames.  As such, the slideshow display is separate from the control interface.  This allows you to control the gallery playback from a device separate of the picture frame; for example, controll a gallery running on a Nexus 7 tablet via your computer's web browser.

## Features ##
  * Consistent image selection across all devices, based on time windows
  * Galleries
    + Galleries are fully dynamic, based on the folders in the gallery\_base setting
    + Subgalleries of unlimited nesting depth are supported
    + Gallery configuration is fully discovered from the filesystem

## Road Map ##
  * **Milestone 0: [Complete!]**
    + nexus\_cli.php -- Command line testing tool
  * **Milestone 1: [In Progress]**
    + slideshow.php -- Display the image slideshow, very minimal UI
    + control.php   -- Control the image slideshow
      - Thumbs up / Thumbs Down
      - Control which galleries are included/excluded
      - Delete Image
      - Move image back into incoming to be resorted
      - Add / Remove tags to images
  * **Milestone 2:**
    + sort.php      -- Sort new images into galleries
  * **Milestone 3:**
    + rpg.php       -- RPG elements
    + war.php       -- Image War!

## TODO ##
  * Implement control.php
    + Controls only; no image display
    + Thumbs up / Thumbs Down image
    + Add / remove image tags
    + Add / Remove galleries to include 
    + Add / Remove galleries to exclude
    + Delete current image
    + Unsort -- move image back into queue to be sorted again
    + Change image display time
