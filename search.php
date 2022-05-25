<!DOCTYPE html>
<html>
<head>
  <title> <?php echo $_GET['q']; ?></title>
  <link rel="stylesheet" type="text/css" href="./style.css">
  <style>
  html{
    font-size:20px;
    margin:0 100px;
  }
  </style>
</head>
<body>
  <div id="searchbox">
    <h1><span id="h1p">Medi</span>Search</h1>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
        <input type="text" placeholder="Let's Go" name="q" value="<?php echo $_GET['q']; ?>" id="sh">
        <input type="submit" name="search" value="GO" id="sb">
      </form>
  </div>
  <div id = "results">

<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=miniproject5','root','');
$search = $_GET['q'];
$searche = explode(" ", $search);
$x = 0;
$construct = "";
foreach($searche as $term){
  $x++;
  if($x==1){
    $construct.="title LIKE '%$term%' OR description LIKE '%$term%' OR keywords LIKE '%$term%'";
  }
  else{
    $construct.=" OR title LIKE '%$term%' OR description LIKE '%$term%' OR keywords LIKE '%$term%'";
  }
}
//echo "<pre>";
$results = $pdo->query("SELECT * FROM `indx` WHERE $construct");

if($results->rowCount()==0){
  echo "0 results found! <hr /><br /><br />";
} else {
  echo $results->rowCount()." results found! <hr /><br /><br />";
}
foreach($results->fetchAll() as $r){
  $t = $r["url"];
  echo "<a href=$t>".$r['title']."</a>"."<br />";
  if($r["description"] == ""){
    echo "No description available."."<br />";
  }else{
  echo $r["description"]."<br />";
  }
  echo $r["url"]."<br /><br /><br />";
  echo "<hr />";
}
//print_r($results->fetchAll());

//"<a href=$r['url']>"
 ?>
</div>
