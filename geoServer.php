<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<title> Geolocation Server </title>
</head>
<body bgcolor="white" Text="black" 
Link="blue" Vlink="red" Alink="lime">
<script type="text/javascript">
function checkInput_newName(){
if(document.inputForm.newName.value==""){
alert("変更したい名前を入力してください。");
return false;
}
return true;
}
</script>
<h1 align="center">位置情報管理サーバ</h1>
<?php
$program_name="geoServer.php";
$loaded=false;
if(isset($_REQUEST["action"])){
if($_REQUEST["action"]=="change"){
echo("<p>登録場所の名前を変更できます。新しい名前を入力してから、変更ボタンをクリックしてください。</p>\r\n");
echo('<form name="inputForm" action="" method="get">');
echo('<input type="hidden" name="slot" value="'.$_REQUEST['slot'].'">');
echo('<input type="hidden" name="action" value="changeProcess">');
echo('<label><input type="text" name="newName" maxlength="20">新しい名前</label>');
echo('<input type="submit" value="変更" onclick="return checkInput_newName();">');
echo('</form>');
echo('<a href='.$program_name.'>キャンセル</a>');
exit();
}
if($_REQUEST["action"]=="delete"){
$buf=file("locations.dat");
if(count($buf)<$_REQUEST["slot"]){
echo("<h2>該当データが見つからなかったため、削除できませんでした。</h2>\r\n");
}else{
unset($buf[$_REQUEST["slot"]]);
$buf=array_values($buf);
$fp=fopen("locations.dat","w");
flock($fp,LOCK_EX);
foreach($buf as $val){
fwrite($fp,$val);
}
flock($fp,LOCK_UN);
fclose($fp);
echo("<h2>削除が完了しました。</h2>");
}
}
if($_REQUEST["action"]=="changeProcess"){
$buf=file("locations.dat");
if(count($buf)<$_REQUEST["slot"]){
echo("<h2>該当データが見つからなかったため、処理を中止しました。</h2>\r\n");
}else{
$tmp=explode(",",$buf[$_REQUEST["slot"]]);
$tmp[2]=$_REQUEST["newName"];
$buf[$_REQUEST["slot"]]=sprintf("%f,%f,%s\r\n",$tmp[0],$tmp[1],$tmp[2]);
$fp=fopen("locations.dat","w");
flock($fp,LOCK_EX);
foreach($buf as $val){
fwrite($fp,$val);
}
flock($fp,LOCK_UN);
fclose($fp);
echo("<h2>変更が完了しました。</h2>");
}
}



}




if(!file_exists("locations.dat")){
$fp=fopen("locations.dat","w");
fclose($fp);
echo("<h2>初期データを作成しました。</h2>");
}
$buf=file("locations.dat");
if(count($buf)==0){
die("<p>現在、登録されている場所はありません。</p>");
}
$list_i=array();
$list_k=array();
$list_name=array();
for($i=0;$i<count($buf);$i++){
sscanf($buf[$i],"%f,%f,%s",$list_i[$i],$list_k[$i],$list_name[$i]);
}
?>
<table summary="登録地点一覧">
<tr> <th>緯度</th> <th>経度</th> <th>登録名</th> <th>変更</th> <th>削除</th> </tr>
<?php
for($i=0;$i<count($list_name);$i++){
echo("<tr> ");
echo("<td>".$list_i[$i]."</td> ");
echo("<td>".$list_k[$i]."</td> ");
echo("<td>".$list_name[$i]."</td> ");
echo("<td><a href=\"".$program_name."?action=change&slot=".$i."\">変更</a></td> ");
echo("<td><a href=\"".$program_name."?action=delete&slot=".$i."\">削除</a></td> ");
echo("</tr>\r\n");
}
echo("</table>");
?>
</body>
</html>