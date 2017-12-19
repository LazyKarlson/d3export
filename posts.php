<?php
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;
$filename = strftime('posts_%m_%d_%Y.xml');
header('Content-Disposition: attachment;filename=' . $filename);
header('Content-Type: text/xml');
//$handle = fopen($filename, 'w');
$first_str = "<?xml version='1.0' encoding='UTF-8'?>\n<ns0:feed xmlns:ns0=\"http://www.w3.org/2005/Atom\">\n<ns0:generator>Blogger</ns0:generator>\n";
echo $first_str;
//fwrite($handle, $first_str);
$curl = new Curl();
$username = "LazyKarlson";
$url = "https://d3.ru/api/users/".$username."/posts/";
$curl->get($url);

if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
	 $pages = $curl->response->page_count;
	 //for ($i = 1; $i <= $pages; $i++){
	 for ($i = 2; $i <= 2; $i++){
	 	$curl->get($url, array(
    	'page' => $i,
		));
		saveToXML($curl->response->posts, $handle);
	}
}

//function saveToXML($posts, $file){
function saveToXML($posts){

	 foreach ($posts as &$value) {
	 	if (empty($value->is_hidden)){
	 		if (count($value->tags) > 0){
				$tags = createTags($value->tags);
				}
			else { $tags = "";}
	 		$entry = '<ns0:entry>'."\n".'<ns0:category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/blogger/2008/kind#post" />'."\n";
	 		$entry .= $tags;
	 		$entry .= "<ns0:id>".$value->id."</ns0:id>"."\n";
	 		$entry .= "<ns0:title type=\"html\">".$value->data->title."</ns0:title>"."\n";
	 		$entry .= timestampToDate($value->created);
	 		switch ($value->data->type) {
			    case "link":
			        $xml = linkToXML($value);
			        break;
			    case "gallery":
			        $xml = galleryToXML($value);
			        break;
			    case "article":
			        $xml = articleToXML($value);
			        break;
			}
			$entry .= $xml;
			$entry .= "</ns0:entry>"."\n";
      echo $entry;
			//fwrite($file, $entry);
	 	}
	}
}

function linkToXML($value){
	$header = '';
	$link = '';
	$link_title = '';
	$description = '';
	$post_img = '';
	if (isset($value->data->link->type)){
	switch ($value->data->link->type) {
			    case "image":
			        $header = "<img src='".$value->data->link->url."'>";
			        break;
			    case "web":
			        $link = $value->data->link->info->original_url;
			        $link_title = $value->data->link->info->title;
			        $description = $value->data->link->info->description;
			        if (isset($value->main_image_url))
			        	$post_img ="<img src='".$value->main_image_url."'>";
			        else
			         	$post_img = '';
			        $header = "<a href='".$link."'><h3>".$link_title."</h3><br />".$description."</a><br />".$post_img;
			        break;
			    case "embed":
			        $header = embedVideo($value->data->link->url);
			        break;
			}
	}
	elseif (isset($value->main_image_url)){
			        	$header ="<img src='".$value->main_image_url."'>";}
			        else {
			         	$header = '';
			        }

	$text = $header;
	$text .= $value->data->text;
	$text .= "<br />Опубликовано на <a href='".$value->_links[1]->href."'>".$value->domain->url."</a>";
	$text = '<ns0:content type="html">'.stripText($text).'</ns0:content>'."\n";
	return $text;
}

function galleryToXML($value){
  $gallery ="";
 foreach ($value->data->gallery as &$image){
  $gallery .= "<img src='".$image->url."'><br /><h4>".$image->text."<h4><br />";
 }
  $text = "<h3>".$value->data->subtitle."<h3><br />";
  $text .= $value->data->text;
  $text .= $gallery;
	$text .= "<br />Опубликовано на <a href='".$value->_links[1]->href."'>".$value->domain->url."</a>";
	$text = '<ns0:content type="html">'.stripText($text).'</ns0:content>'."\n";
	return $text;
}

function articleToXML($value){
  $article ="";
  foreach ($value->data->blocks as &$block){
    switch ($block->type) {
  			    case "image":
  			        $article .= "<img src='".$block->url."' align='".$block->align."'><br /><h4>".$block->text."<h4><br />";
  			        break;
  			    case "text":
  			        $article .= $block->text;
  			        break;
  			    case "embed":
  			        $article .= embedVideo($block->url);
  			        break;
  			}
  }
  $text = "<h3>".$value->data->subtitle."<h3><br />";
  $text .= $article;
  $text .= "<br />Опубликовано на <a href='".$value->_links[1]->href."'>".$value->domain->url."</a>";
  $text = '<ns0:content type="html">'.stripText($text).'</ns0:content>'."\n";
  return $text;
  }


function embedVideo($url){
	$iframe = '&lt;iframe allowfullscreen="" frameborder="0" height="270" src="'.$url.'" width="480"&gt;&lt;/iframe&gt;';
	return $iframe;
}

function timestampToDate($timestamp){
	$pub_date = date("Y-m-d\TH:i:s", $timestamp);
	$published = "<ns0:published>".$pub_date.".001+03:00</ns0:published>"."\n";
	return $published;
}

function stripText($text){
	$text = preg_replace("/</", "&lt;",preg_replace("/>/", "&gt;",$text));
	return $text;
}

function createTags($tags){
	$term = '';
	foreach ($tags as &$tag) {
		$term .= "<ns0:category scheme='http://www.blogger.com/atom/ns#' term='".$tag."'/>"."\n";
	}
	return $term;
}
echo "</ns0:feed>";
//fwrite($handle, "</ns0:feed>");
//fclose($handle);
