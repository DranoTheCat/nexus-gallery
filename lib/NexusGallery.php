<?php

class NexusGallery {

  protected $mysqli;
  protected $config;
  protected $allowed_galleries;
  protected $excluded_galleries;

  public function __construct() {
    $this->loadConfig();
    $this->mysqli = new mysqli($this->config['mysql_host'], $this->config['mysql_user'], $this->config['mysql_pass'], $this->config['mysql_db']) or die($this->mysqli->error);
    $this->allowed_galleries = preg_split('/,/', $this->config['allowed_galleries']);
    $this->excluded_galleries = preg_split('/,/', $this->config['excluded_galleries']);
  }

  public function nextImage() {
    if ($this->nextImageCacheCount() < $this->config['min_cache_count'])
      $this->generateNextImageCache();

    $next_image_result = $this->mysqli->query("SELECT filepath FROM nextImageCache WHERE displaytime >= (UNIX_TIMESTAMP() - " . $this->config['image_persistence'] . ") ORDER BY displaytime ASC LIMIT 1");
    $data = mysqli_fetch_assoc($next_image_result);
    print $data['filepath'] . "\n"; #TODO - fix print
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
    print "DEBUG: " . $data['total'] . "\n";
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

  protected function generateNextImageCache() {
    print "[ Generating Next Image Cache ... ]\n";

    $now = time();
#    $this->mysqli->query("TRUNCATE TABLE nextImageCache");

    $local_images = $this->loadImages($this->config['gallery_base']);

    $used_images = Array(); 
    for ($i = 0; $i < $this->config['cache_chunk_size']; $i++) {
      $r = rand(0, sizeof($local_images) - 1);
      while (isset($used_images[$r])) { # This is fucking stupid.  Randomize the array instead then step through it.
        if (sizeof($used_images) == sizeof($local_images))
          $used_images = Array(); # Reset, need to have duplicates
        $r = rand(0, sizeof($local_images) - 1);
      } 
      $time_window = $now + ($i * $this->config['image_persistence']);
      $this->mysqli->query("INSERT INTO nextImageCache (id, displaytime, filepath) VALUES ('', '" . $time_window . "', '" . $local_images[$r] . "')") or die($this->mysqli->error);
      $used_images[$r] = 1;
    }
  }
}

?>
