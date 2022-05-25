<?php
error_reporting(0);

//$start = "http://localhost/practice/crowl.html";
//$start = "https://pubmed.ncbi.nlm.nih.gov/";
//$start = "https://www.embase.com/";
//$start = "https://emedicine.medscape.com/";
//$start = "https://www.mayoclinic.org/";
//$start = "https://www.cancer.gov/";
//$start = "https://www.who.int/";
//$start = "https://www.cdc.gov/";
//$start = "https://www.bupa.co.uk/";
$start = "https://selecthealth.org/blog/2016/08/25-important-medical-terms-you-need-to-know";
$pdo = new PDO('mysql:host=127.0.0.1;dbname=miniproject5','root','');

$already_crawled = Array();
$crawling = Array();

function get_details($url){
  $options = Array('http'=>Array('method'=>"GET", 'headers'=>"User-Agent : MiniProject/5\n"));
  $context = stream_context_create($options);

  $doc = new DOMDocument();
  @$doc->loadHTML(@file_get_contents($url, false, $context));

  $title = $doc->getElementsByTagName ("title");
  $title = $title->item(0)->nodeValue;
  //echo $title."\n";
  $description = "";
  $keywords = "";
  $metas = $doc->getElementsByTagName("meta");
  for($i = 0;$i<$metas->length;$i++){

    $meta = $metas->item($i);
    if($meta->getAttribute("name") == strtolower("description"))
      $description = $meta->getAttribute("content");
    if($meta->getAttribute("name") == strtolower("keywords"))
      $keywords = $meta->getAttribute("content");
  }


  return '{"Title": "'.str_replace("\n","",$title).'","Description":"'.str_replace("\n","",$description).'","Keywords":"'.str_replace("\n","",$keywords).'","URL":"'.$url.'"}';

}

function follow_links($url){
  global $already_crawled;
  global $crawling;
  global $pdo;

  $options = Array('http'=>Array('method'=>"GET", 'headers'=>"User-Agent : MiniProject"));
  $context = stream_context_create($options);

  $doc = new DOMDocument();
  @$doc->loadHTML(@file_get_contents($url, false, $context));

  $linklist = $doc->getElementsByTagName("a");
  foreach($linklist as $link){
    $l = $link->getAttribute("href");

    if(substr($l,0,1) == "/" && substr($l,0,2)<>"//"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
    }elseif(substr($l,0,2)=="//"){
      $l = parse_url($url)["scheme"].":".$l;
    }elseif(substr($l,0,2)=="./"){
      $l = parse_url($url)["scheme"].":"."//".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l,1);
    }elseif(substr($l,0,1)=="#"){
      $l = parse_url($url)["scheme"].":"."//".parse_url($url)["host"].parse_url($url)["path"].$l;
    }elseif(substr($l,0,11)=="javascript:"){
      continue;
    }elseif(substr($l,0,5)<>"https"||substr($l,0,4)<>"http"){
      $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
    }
    if(!in_array($l, $already_crawled)){
      $already_crawled[] = $l;
      $crawling[] = $l;
      $details = json_decode(get_details($l));
      echo md5($details->URL)." ";
      $rows = $pdo->query("SELECT * FROM `indx` WHERE url_hash='".md5($details->URL)."'");
      $rows = $rows->fetchColumn();
      //echo $rows."\n";
      $params = array(':title'=>$details->Title,':description'=>$details->Description,':keywords'=>$details->Keywords,':url'=>$details->URL,'url_hash'=>md5($details->URL));
      if($rows>0){
        if(!is_null($params[':title']) && !is_null($params[':description']) && $params[':title'] != ''){
        $result = $pdo->prepare("UPDATE `indx` VALUES('', title=:title, description=:description, keywords=:keywords, url=:url, url_hash:url_hash WHERE url_hash=:url_hash)");
        $result = $result->execute($params);
        }
      }else{
        if(!is_null($params[':title']) && !is_null($params[':description']) && $params[':title'] != ''){
        $result = $pdo->prepare("INSERT INTO `indx` VALUES('', :title, :description, :keywords, :url, :url_hash)");
        $result = $result->execute($params);
        }
      }
      //print_r($details)."\n";
      //echo get_details($l);
      //echo "\n";
    }
  }
  array_shift($crawling);
  foreach($crawling as $site){
    follow_links($site);
    }
}
follow_links($start);
//$pdo->query("SELECT * FROM 'index'");
?>
