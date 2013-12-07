<?php

class NexusGallery {

  protected $mysqli;
  protected $config;
  protected $allowed_galleries;
  protected $excluded_galleries;

  public function __construct($debug = false) {
    $this->loadConfig();
    $this->mysqli = new mysqli($this->config['mysql_host'], $this->config['mysql_user'], $this->config['mysql_pass'], $this->config['mysql_db']) or die($this->mysqli->error);
    $this->allowed_galleries = preg_split('/,/', $this->config['allowed_galleries']);
    $this->excluded_galleries = preg_split('/,/', $this->config['excluded_galleries']);
    $this->debug = $debug;
    date_default_timezone_set('America/Los_Angeles');
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
      $this->mysqli->query("INSERT INTO imageCounters (id, filepath, thumbs_up, thumbs_down, num_views, last_view) VALUES ('', '" . addslashes($data['filepath']) . "', 0, 0, 1, UNIX_TIMESTAMP())") or die($this->mysqli->error);;
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


  protected function loadConfig() {
    # YAML loading and fixup
    $this->config = yaml_parse_file("conf/config.yaml");
    $this->configChomp('allowed_galleries');
    $this->configChomp('excluded_galleries');
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
          if (in_array($lpath, $this->allowed_galleries)) {
            array_push($local_images, $lpath . "/" . $entry);
          } else {
            foreach ($this->allowed_galleries as $k => $v) {
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

    $fp = fopen($this->config['flock_file'], "w+");
    if (flock($fp, LOCK_EX)) {
      if ($this->debug) echo "* Received exclusive lock.\n"; 
    } else {
      if ($this->debug) echo "* Could not receive an exclusive lock; Aborting.\n";
      return;
    }

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

    fclose($fp);
    unlink($fp);
  }
}

?>
