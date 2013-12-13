<?php

// TODO: method for get number of views

class NexusGallery {

  protected $mysqli;
  protected $config;
  protected $override;
  protected $included_galleries;
  protected $excluded_galleries;

  public function __construct($debug = 0) {
    $this->loadConfig();
    $this->mysqli = new mysqli($this->config['mysql_host'], $this->config['mysql_user'], $this->config['mysql_pass'], $this->config['mysql_db']) or die($this->mysqli->error);
    $this->debug = $debug;
    date_default_timezone_set('America/Los_Angeles');
  }

  ### Public Methods

  public function deleteImage() {
    list($image, $time_till_next) = $this->getImage();
    if ($this->debug) echo "[ Deleting " . $image . " to " . $this->config['trash_directory'] . " ]\n";
    system("mv \"$image\" \"" . $this->config['trash_directory'] . "\"") or die("Couldn't move file");
    return true;
  }

  public function addTag($tag) {
    list($image, $time_till_next) = $this->getImage();
    if ($this->debug) echo "[ Adding '$tag' tag for " . $image . " ]\n";
    $result = $this->mysqli->query("SELECT tags FROM imageCounters WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die ($this->mysqli->error);
    if (mysqli_num_rows($result) > 0) {
      $data = mysqli_fetch_assoc($result); 
      $tags = explode(';', $data['tags']);
      array_push($tags, $tag);
      $tags = array_unique($tags);
      $tag_string = join(';', $tags);
      $tag_string = preg_replace("/^;/", "", $tag_string);
      if ($this->debug) echo "    * Current tags: $tag_string\n";
      $this->mysqli->query("UPDATE imageCounters SET tags='" . addslashes($tag_string) . "' WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die ($this->mysqli->error);
    } else {
      if ($this->debug) echo "    * No current tags set; doing init.\n";
      $this->mysqli->query("UPDATE imageCounters SET tags='" . addslashes($tag_string) . "' WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die ($this->mysqli->error);
    }
    return true;
  }

  public function removeTag($tag) {
    list($image, $time_till_next) = $this->getImage();
    if ($this->debug) echo "[ Removing '$tag' tag for " . $image . " ]\n";
    $result = $this->mysqli->query("SELECT tags FROM imageCounters WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die ($this->mysqli->error);
    if (mysqli_num_rows($result) > 0) {
      $data = mysqli_fetch_assoc($result); 
      if ($data['tags']) {
        $tags = explode(';', $data['tags']);
        $tags = array_diff($tags, array($tag));
        $tag_string = join(';', $tags);
        $tag_string = preg_replace("/^;/", "", $tag_string);
        if ($this->debug) echo "    * Current tags: $tag_string\n";
        $this->mysqli->query("UPDATE imageCounters SET tags='" . addslashes($tag_string) . "' WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die ($this->mysqli->error);
      } else {
        if ($this->Debug) echo "  * Image has no current tags.\n";
      }
    }
    return true;
  }

  public function listAllTags() {
    if ($this->debug) echo "[ Retrieving ALL image tags ]\n";
    $tags = Array();
    $result = $this->mysqli->query("SELECT tags FROM imageCounters") or die ($this->mysqli->error);
    while ($row = mysqli_fetch_assoc($result)) {
      $tags = array_merge($tags, explode(';', $row['tags']));
    }
    sort($tags);
    return array_filter(array_unique($tags));
  }

  public function listImageTags() {
    list($image, $time_till_next) = $this->getImage();
    if ($this->debug) echo "[ Retrieving tags for " . $image . " ]\n";
    $result = $this->mysqli->query("SELECT tags FROM imageCounters WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die ($this->mysqli->error);
    if (mysqli_num_rows($result) > 0) {
      $data = mysqli_fetch_assoc($result); 
      $tags = Array();
      if ($data['tags'])
        $tags = explode(';', $data['tags']);
      return $tags; 
    }
  }

  public function truncateQueue() {
    if ($this->debug) echo "[ Truncating nextImageCache table ]\n";
    $this->mysqli->query("TRUNCATE TABLE nextImageCache") or die ($this->mysqli->error);
    return true;
  }

  public function resetOverrides() {
    if ($this->debug) echo "[ Resetting to base config ]\n";
    unlink($this->config['working_directory'] . '/running-config.yaml');
    unset($this->config);
    unset($this->override);
    $this->loadConfig();
    return true;
  }

  public function setIncludedGalleries($new_galleries) {
    if ($this->debug) echo "[ Setting Included Galleries ]\n";
    $this->included_galleries = $new_galleries;
    $this->saveOverrides();
    $this->truncateQueue();
    $this->generateNextImageCache();
    return true;
  }

  public function setExcludedGalleries($new_galleries) {
    if ($this->debug) echo "[ Setting Excluded Galleries ]\n";
    $this->excluded_galleries = $new_galleries;
    $this->saveOverrides();
    $this->truncateQueue();
    $this->generateNextImageCache();
    return true;
  }

  public function thumbsUp() {
    if ($this->debug) echo " [ Current Image Thumbs Up ++ ]\n";
    list($image, $time_till_next) = $this->getImage();
    $this->mysqli->query("UPDATE imageCounters SET thumbs_up = thumbs_up + 1 WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die($this->mysqli->error);
    return true;
  }

  public function thumbsDown() {
    if ($this->debug) echo " [ Current Image Thumbs Down ++ ]\n";
    list($image, $time_till_next) = $this->getImage();
    $this->mysqli->query("UPDATE imageCounters SET thumbs_down = thumbs_down + 1 WHERE filepath='" . addslashes($image) . "' LIMIT 1") or die($this->mysqli->error);
    return true;
  }

  public function listGalleries($path) {
    if ($this->debug >= 2) echo "    + $path\n";

    $galleries = Array();
    if ($handle = opendir($path) or die()) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry == '.' || $entry == '..') continue;
        if (is_dir($path . "/" . $entry)) {
          $lpath = preg_replace(':' . $this->config['gallery_base'] . ':', '', $path);
          $lpath = preg_replace(':^/:', '', $lpath);
          if ($lpath)
            array_push($galleries, $lpath . '/' . $entry);
          else
            array_push($galleries, $entry);
          $galleries = array_merge($galleries, $this->listGalleries($path . "/" . $entry));
        }
      }
    }
    return $galleries;
  }

  public function listAllGalleries() {
    if ($this->debug) echo "[ Listing Galleries ]\n";

    $galleries = $this->listGalleries($this->config['gallery_base']);
    asort($galleries);
    $fgalleries = Array();
    foreach ($galleries as $g)
      $fgalleries[$g] = ($this->isGalleryEnabled($g)) ? 1 : 0;
    return $fgalleries;
  }

  public function isGalleryEnabled($gallery) {
    return (in_array($gallery, $this->included_galleries)) ? true : false;
  }
  
  public function setImagePersistence($time) {
    $this->config['image_persistence'] = $time;
    $this->saveOverrides();
    return true;
  }

  public function unsortImage() {
    list($image, $time_till_next) = $this->getImage();
    if ($this->debug) echo "* Moving current image " . $image  . " back into " . $this->config['incoming_base'] . "\n";
    system("mv \"$image\" \"" . $this->config['incoming_base'] . "\"") or die("Couldn't move file");
    return true;
  }

  public function debugQueue() {
    if ($this->debug) echo "* debugQueue - We have " . $this->nextImageCacheCount() . " images left in the queue.\n";

    if ($this->nextImageCacheCount() < 1)
      $this->generateNextImageCache();

    $queue_results = $this->mysqli->query("SELECT filepath,displaytime FROM nextImageCache WHERE displaytime >= (UNIX_TIMESTAMP() - " . $this->config['image_persistence'] . ") ORDER BY displaytime ASC");
    while ($row = mysqli_fetch_assoc($queue_results)) {
      echo ($row['displaytime'] + $this->config['image_persistence'] - time()) . " seconds from now: " . strftime("%Y-%m-%d %H:%M:%S", $row['displaytime']) . " - " . $row['filepath'] . "\n";
    }
  }

  public function getImage() {
    if ($this->debug) echo "* nextImage - We have " . $this->nextImageCacheCount() . " images left in the queue.\n";

    $now = time();
   
    if ($this->nextImageCacheCount() < 1)
      $this->generateNextImageCache();

    $next_image_result = $this->mysqli->query("SELECT filepath,displaytime FROM nextImageCache WHERE displaytime >= (UNIX_TIMESTAMP() - " . $this->config['image_persistence'] . ") ORDER BY displaytime ASC LIMIT 1");
    $data = mysqli_fetch_assoc($next_image_result);

    # Update counters
    $last_viewed_result = $this->mysqli->query("SELECT last_view,num_views FROM imageCounters WHERE filepath='" . addslashes($data['filepath']) . "' LIMIT 1");
    if ($last_viewed_result->num_rows > 0) {
      $lvdata = mysqli_fetch_assoc($last_viewed_result);
      if ($this->debug) echo "* " . $data['filepath'] . " has been viewed " . $lvdata['num_views'] . " times before.\n";
      if ($now - $lvdata['last_view'] > $this->config['image_persistence']) { # Last view was fairly long ago
        $this->mysqli->query("UPDATE imageCounters SET num_views='" . ($lvdata['num_views'] + 1) . "', last_view=UNIX_TIMESTAMP() WHERE filepath='" . addslashes($data['filepath']) . "'") or die($this->mysqli->error);;
      } else {
        if ($this->debug) echo "* " . $data['filepath'] . " has had counters updated within the past " . $this->config['image_persistence'] . " seconds, and so will not be updated again this cycle.\n";
      }
    } else { # Image hasn't been seen before
      if ($this->debug) echo "* " . $data['filepath'] . " has never been viewed before.\n";
      $this->mysqli->query("INSERT INTO imageCounters (id, filepath, thumbs_up, thumbs_down, num_views, last_view, tags) VALUES ('', '" . addslashes($data['filepath']) . "', 0, 0, 1, UNIX_TIMESTAMP(), '')") or die($this->mysqli->error);;
    } 

    $time_left = $data['displaytime'] + $this->config['image_persistence'] - $now;

    if ($this->debug) echo "* Next Image: " . $data['filepath'] . "\n";
    if ($this->debug) echo "* Current Image Time Remaining: " . $time_left . "\n";

    $this->current_image = $data['filepath'];
    return Array($data['filepath'], $time_left);
  }

  public function getConfig($v) {
    return $this->config[$v];
  }




  ### Protected Methods ###

  protected function loadConfig() {
    # YAML loading and fixup
    $this->config = yaml_parse_file("conf/config.yaml");
    if (file_exists($this->config['working_directory'] . '/running-config.yaml')) {
      $this->override = yaml_parse_file($this->config['working_directory'] . '/running-config.yaml');
      foreach ($this->override as $k => $v)
        $this->config[$k] = $v;
    }
    $this->configChomp('included_galleries');
    $this->configChomp('excluded_galleries');
    $this->included_galleries = preg_split('/,/', $this->config['included_galleries']);
    $this->excluded_galleries = preg_split('/,/', $this->config['excluded_galleries']);
    $this->saveOverrides();
  }

  protected function saveOverrides() {
    if ($this->debug) echo "[ Writing the working running-config.yaml to " . $this->config['working_directory'] . " ]\n";
    $this->config['included_galleries'] = join(', ', $this->included_galleries);
    $this->config['excluded_galleries'] = join(', ', $this->excluded_galleries);
    yaml_emit_file($this->config['working_directory'] . '/running-config.yaml', $this->config);
    chmod($this->config['working_directory'] . '/running-config.yaml', 0600);
  }

  protected function configChomp($v) { # Strips spaces so split will work correctly
    $this->config[$v] = preg_replace("/\s+/", '', $this->config[$v]);
  }

  protected function nextImageCacheCount() {
    $result = $this->mysqli->query("SELECT COUNT(id) as total FROM nextImageCache WHERE displaytime >= (UNIX_TIMESTAMP() - " . $this->config['image_persistence'] . ")");
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
  }

  protected function loadImages($path) {
    $local_images = Array();
    if ($handle = opendir($path) or die()) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry == '.' || $entry == '..') continue;
        if (is_dir($path . "/" . $entry)) {
          $local_images = array_merge($local_images, $this->loadImages($path . "/" . $entry));
        } else {
          $lpath = preg_replace(':' . $this->config['gallery_base'] . '/:', '', $path); # /: to get rid of trailing slash
          if (in_array($lpath, $this->included_galleries)) {
            array_push($local_images, $lpath . "/" . $entry);
          } else {
            foreach ($this->included_galleries as $k => $v) {
              if (preg_match(":^$v/\w+:", $lpath) && !in_array($lpath, $this->excluded_galleries)) {
                array_push($local_images, $lpath . "/" . $entry);
              } 
            }
          }
        }
      }
    }
    closedir($handle);
    return $local_images;
  }

  protected function shuffle_assoc(&$array) {
    $keys = array_keys($array);
    $new = Array();
    shuffle($keys);
    foreach ($keys as $k)
      $new[$k] = $array[$k];
    $array = $new;
    return true; 
  }

  protected function generateNextImageCache() {
    if ($this->debug) echo "[ Generating Next Image Cache ... ]\n"; 

    $this->mysqli->query("LOCK TABLES nextImageCache WRITE,imageCounters READ");

    $now = time();

    # Find all current filesystem images according to filters
    $local_images = $this->loadImages($this->config['gallery_base']);

    # First, grab the imageCounters for known files
    $seen_files = Array();
    $temp_files = Array();
    $result = $this->mysqli->query("SELECT * FROM imageCounters WHERE thumbs_up >= thumbs_down") or die($this->mysqli->error);
    while ($data = mysqli_fetch_assoc($result)) {
      $seen_files[$data['filepath']] = $data['num_views'];
    }

    # Second, build the temp_files array with filepaths and page views
    foreach ($local_images as $v) {
      if (!isset($seen_files[$v]))
        $temp_files[$v] = 0; 
      else
        $temp_files[$v] = $seen_files[$v];
    }

    # Third, sort temp_files so the least viewed are at the front
    asort($temp_files);

    # Fourth, truncate to only the first cache_chunk_size elements
    $final_files = array_slice($temp_files, 0, $this->config['cache_chunk_size']);

    # Fifth, let's randomize this array
    $this->shuffle_assoc($final_files);

    $i = 0;
    foreach ($final_files as $f => $v) {
      $time_window = $now + ($i * $this->config['image_persistence']);
      $this->mysqli->query("INSERT INTO nextImageCache (id, displaytime, filepath) VALUES ('', '" . $time_window . "', '" . addslashes($f) . "')") or die($this->mysqli->error);
      $i++;
    }

    # Unlock  tables
    $this->mysqli->query("UNLOCK TABLES");
  }
}

?>
