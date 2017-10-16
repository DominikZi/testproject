<?php
error_reporting(0);
session_start();
/*

	Released under DWTFYW-2.0 License (aka CCBY0). Provided as is by Dominik Ziegenhagel (info@ziegenhagel.com)

*/


if($_GET["action"]=="logout") {
	$_SESSION["hash"]=null;
	echo("<meta http-equiv=refresh content=0,?>");
}


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $_POST["curl"] );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array("request"=>json_encode(array("hash"=>$_SESSION["hash"],"login_uname"=>$_POST["login_uname"],"login_pass"=>hash("sha512",$_POST["login_pass"]),"sql"=>$_POST["sql"]))));                                                                        
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                        
$output = curl_exec($ch);
curl_close($ch);   

$response=json_decode($output,1);
if($response["status"]["code"]==2001) {
	$_SESSION["hash"]=$response["hash"];
}



echo "<h2>SQl Tunnel GUI V0.91</h2><form method=post>";
if(strlen($_SESSION["hash"])) { 
	echo "Login-hash: ".$_SESSION["hash"]." (<a href=?action=logout>logout</a>)";
}
echo "<input type=url name=curl style=width:100% value='http://localhost/sql_tunnel_ajax.php' placeholder='URI to server-sided PHP file'><hr>
";

echo "<div style=background:hsla(220,100%,50%,.1);padding:20px;>";

//gui
if(!strlen($_SESSION["hash"])) {
	?>
		<b>LOGIN:</b><br>
		<input name="login_uname" placeholder="Username" value="ghse15"><br>
		<input name="login_pass" placeholder="Password" value="ghse1516"><br>
		<input type="submit" value="Login">
	<?php
} else {
	?>
		<b>SQL REQUEST:</b><br>
		<input style=width:100%  name="sql" placeholder="SQL query">
		<input type="submit" value="Execute">
	<?php
}
echo "</div></form><hr>RESPONSE<pre>";


echo "<div style='border-left:5px solid orange;padding:10px;'>";echo print_r(json_decode($output)); echo"</div><div style='padding:10px;border-left:5px solid cornflowerblue'>".$output."</div></pre><hr>";

?>
