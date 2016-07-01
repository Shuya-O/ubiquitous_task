<?php
if(isset($_REQUEST["action"])){//JSとの通信処理用のコマンド軍
if($_REQUEST["action"]=="register"){//JSから登録するようにといわれた
if(!isset($_REQUEST["i"]) or !isset($_REQUEST["k"])) die("Insaficient parameter");//位置情報がないってどういうこっちゃねん
//パラメータはちゃんとあるらしいから追記
$fp=fopen("locations.dat","a");
flock($fp,LOCK_EX);
fwrite($fp,"\r\n".sprintf("%f,%f,新規登録地点",$_REQUEST["i"],$_REQUEST["k"]));
flock($fp,LOCK_UN);
fclose($fp);
die("OK");
}//追加した

if($_REQUEST["action"]=="retrieve"){//JSから情報がほしいといわれた
die(file_get_contents("locations.dat"));//持ってる情報をぶん投げる
}
}//JSとの通信処理終了
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<title> Geolocation Server </title>
</head>
<body bgcolor="white" Text="black" 
Link="blue" Vlink="red" Alink="lime">
<script type="text/javascript">
function checkInput_newName(){//入力ボックスが空だったら弾く
if(document.inputForm.newName.value==""){
alert("変更したい名前を入力してください。");
return false;
}
return true;
}
</script>
<h1 align="center">位置情報管理サーバ</h1>
<?php
$program_name="geoServer.php";//自分自身のファイル名
if(isset($_REQUEST["action"])){//処理がリクエストされた

if($_REQUEST["action"]=="change"){//名前を変更したいらしいので、新しい名前を聞く
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
if($_REQUEST["action"]=="delete"){//削除したいらしいので、問答無用で消しちゃう
$buf=file("locations.dat");
if(count($buf)<$_REQUEST["slot"]){//該当箇所にデータがない
echo("<h2>該当データが見つからなかったため、削除できませんでした。</h2>\r\n");
}else{//データあった
unset($buf[$_REQUEST["slot"]]);//該当箇所を削除
$buf=array_values($buf);//隙間が空いてしまったのを詰める
$fp=fopen("locations.dat","w");
flock($fp,LOCK_EX);
foreach($buf as $val){//書き込み処理
fwrite($fp,$val);
}
flock($fp,LOCK_UN);
fclose($fp);
echo("<h2>削除が完了しました。</h2>");
}
}
if($_REQUEST["action"]=="changeProcess"){//変更したい名前を決めてくれたらしいので、実際に変更処理をする
$buf=file("locations.dat");
if(count($buf)<$_REQUEST["slot"]){//該当データがない
echo("<h2>該当データが見つからなかったため、処理を中止しました。</h2>\r\n");
}else{
$tmp=explode(",",$buf[$_REQUEST["slot"]]);//とりあえずコンマでぶった切る
$tmp[2]=$_REQUEST["newName"];//名前だけ書き換え
$buf[$_REQUEST["slot"]]=sprintf("%f,%f,%s\r\n",$tmp[0],$tmp[1],$tmp[2]);//もとの書式に戻してから再配置
$fp=fopen("locations.dat","w");
flock($fp,LOCK_EX);
foreach($buf as $val){//書いて
fwrite($fp,$val);
}
flock($fp,LOCK_UN);
fclose($fp);//完了
echo("<h2>変更が完了しました。</h2>");
}
}



}//データ処理ここまで



//ここからはページ読み込みで実行される
if(!file_exists("locations.dat")){//データベースがない
$fp=fopen("locations.dat","w");
fclose($fp);
echo("<h2>初期データを作成しました。</h2>");
}
$buf=file("locations.dat");
if(count($buf)==0){//なんにも登録されてない
die("<p>現在、登録されている場所はありません。</p>");
}
//配列確保
$list_i=array();//緯度
$list_k=array();//経度
$list_name=array();//登録名
for($i=0;$i<count($buf);$i++){
sscanf($buf[$i],"%f,%f,%s",$list_i[$i],$list_k[$i],$list_name[$i]);//配列にデータを読み込む
}
?>
<table summary="登録地点一覧">
<tr> <th>緯度</th> <th>経度</th> <th>登録名</th> <th>変更</th> <th>削除</th> </tr>
<?php
for($i=0;$i<count($list_name);$i++){//表を出力
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