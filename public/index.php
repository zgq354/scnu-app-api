<?php

define('BASE_URL', 'https://scnuapp.tql.today');

require __DIR__ . '/../vendor/autoload.php';

$klein = new \Klein\Klein();

/**
 * 获取某书籍可借数量与封面图
 */
$klein->respond('GET', '/library/lend-avl-cover', function ($request, $response) {
  $marcNo = $request->param('marcNo');
  $isbn = $request->param('isbn');

  $response->header('Access-Control-Allow-Origin', '*');
  if (!$marcNo && !$isbn) {
    $response->json([
      'error' => true,
      'msg' => 'params error',
    ]);
    return;
  }
  $url = "http://202.116.41.246:8080/opac/ajax_isbn_marc_no.php?marc_no=$marcNo&isbn=$isbn";

  // fetch
  $data = file_get_contents($url);
  $dataObj = json_decode($data);
  $imgURL = $dataObj->image;

  if (!$imgURL || strpos($imgURL, 'nobook.jpg') !== false) {
    $imgURL = false;
  } else {
    $imgURL = preg_replace("/http:\/\/img\d\.(doubanio.com\/.*)/", "http://img1.$1", $imgURL);
    $path = pathinfo($imgURL);
    $filename = $path['basename'];

    // cache
    $filepath = __DIR__ . '/cover/' . $filename;
    if (!file_exists($filepath)) {
      file_put_contents($filepath, file_get_contents($imgURL));
    }
    $imgURL = BASE_URL . '/cover/' . $filename;
  }

  $lendAvlStr = preg_replace("/<b>(.+?)<\/b>/i", "", $dataObj->lendAvl);
  [$total, $aval] = explode('<br>', $lendAvlStr);

  $output = [
    'image' => $imgURL,
    'bookNum' => [
      'avaliable' => $aval,
      'total' => $total,
    ],
  ];

  $response->json($output);
});

/**
 * 搜索书籍
 */
$klein->respond('GET', '/library/search', function ($request, $response) {
  $keywords = $request->param('keywords');
  $pageSize = $request->param('pageSize', 10);
  $pageCount = $request->param('pageCount', 1);

  $response->header('Access-Control-Allow-Origin', '*');

  if (!$keywords) {
    $response->json([
      'error' => true,
      'msg' => 'empty keywords',
    ]);
    return;
  }

  $paramObj = json_decode('{
    "searchWords": [
      {
        "fieldList": [
          {
            "fieldCode": "",
            "fieldValue": "test"
          }
        ]
      }
    ],
    "filters": [],
    "limiter": [],
    "sortField": "relevance",
    "sortType": "desc",
    "pageSize": 1,
    "pageCount": 1,
    "locale": "zh_CN",
    "first": true
  }');

  $paramObj->searchWords[0]->fieldList[0]->fieldValue = $keywords;
  $paramObj->pageSize = $pageSize;
  $paramObj->pageCount = $pageCount;

  $client = new GuzzleHttp\Client();
  $res = $client->post('http://202.116.41.246:8080/opac/ajax_search_adv.php', [
    GuzzleHttp\RequestOptions::JSON => $paramObj,
  ]);

  $response->json(json_decode((string) $res->getBody()));
});

$klein->dispatch();
