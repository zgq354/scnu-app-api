<?php
// format: /cover/[ISBN].jpg
$args = explode('/', $_SERVER['REQUEST_URI']);
if($args[1] === 'index.php') {
  array_splice($args, 1, 1);
}

preg_match("/^([0-9-]*)?\.jpg\??.*?$/i", $args[2], $matches);

// format
if ($args[1] !== 'cover' || !$matches) {
  header("Location: /assets/book-default.jpg");
  exit;
}

$isbn = $matches[1];
$path = "./cover/$isbn.jpg";

// output exist img
if (file_exists($path)) {
  $imgData = file_get_contents($path);
  header("Content-Type: image/jpeg");
  echo $imgData;
  exit;
}

//
// fetch remote
$url = "http://book.douban.com/isbn/$isbn/";
$data = @file_get_contents($url);

if ($data) {
  $regex = '/<a class="nbg"\s*?href="(.+?)"[\s\S]*?<img src="(.*?)"/i';
  preg_match($regex, $data, $matches);

  if (!empty($matches)) {
    $imgLink = $matches[1];

    if (strpos($imgLink, "update_image") === false) {
      // use small img
      $imgLink = str_replace('/l/', '/s/', $imgLink);
      $imgData = file_get_contents($imgLink);
      file_put_contents($path, $imgData);

      // echo
      header("Content-Type: image/jpeg");
      header("Cache-Control: 2592000");
      echo $imgData;
      exit;
    }
  }
}

header("Location: /assets/book-default.jpg");
