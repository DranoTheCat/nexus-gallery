<?php5

# MySQL Parameters
$conf['mysql_user'] =  'nexusgallery';
$conf['mysql_pass'] = 'nexusgallery';
$conf['mysql_db'] = 'nexusgallery';
$conf['mysql_host'] = 'localhost';

# Basic Configuration
$conf['gallery_base'] = '/path/to/gallery';
$conf['gallery_url'] = 'http://localhost/gallery';
$conf['gallery_refresh_delay'] = 2;
$conf['incoming_base'] = '/tmp';
$conf['image_persistence'] = 10; 				# How long to persist the image in seconds

# Advanced Configuration
$conf['cache_chunk_size'] = 60; 					# How many new images to load into the cache
$conf['working_directory'] = '/var/www/liquidthex/work/nexus-gallery/tmp';

?>
