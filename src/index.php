<?php
// format: /cover/[ISBN].jpg
$args = explode('/', $_SERVER['REQUEST_URI']);
if($args[1] === 'index.php') {
  array_splice($args, 1, 1);
}

preg_match("/^([0-9-]*)?\.jpg\??.*?$/i", $args[2], $matches);

// format
if ($args[1] !== 'cover' || !$matches) {
  http_response_code(404);
  die;
}

$isbn = $matches[1];
$path = "./cover/$isbn.jpg";

// output exist img
if (file_exists($path)) {
  $imgData = file_get_contents($path);
  header("Content-Type: image/jpeg");
  echo $imgData;
}

//
// fetch remote
$url = "http://book.douban.com/isbn/$isbn/";
$data = file_get_contents($url);
$regex = '/<a class="nbg"\s*?href="(.+?)"[\s\S]*?<img src="(.*?)"/i';

preg_match($regex, $data, $matches);
$imgLink = $matches[1];
if (strpos($imgLink, "update_image") > -1) {
  header("Location: /assets/book-default.jpg");
} else {
  // use small img
  $imgLink = str_replace('/l/', '/s/', $imgLink);
  $imgData = file_get_contents($imgLink);
  file_put_contents($path, $imgData);
  header("Content-Type: image/jpeg");
  header("Cache-Control: 2592000");
  echo $imgData;
}
