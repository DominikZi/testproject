<?php
error_reporting(0);
/*

	Released under DWTFYW-2.0 License (aka CCBY0). Provided as is by Dominik Ziegenhagel (info@ziegenhagel.com)

*/


//get stuff from post
$request=json_decode($_POST["request"]);

//connnect to databases
$db_user=mysqli_connect("localhost","root","","tunnel_user");
$db_sys=mysqli_connect("localhost","root","","tunnel_sys");

//response
$response["version"]="0.91";
$response["hash"]=hash("sha512",rand(0,9e9).uniqid());
$response["request"]=$_POST["request"];
$response["status"]["code"]=1000;
$response["status"]["human"]="continue - no status";
$response["sql"]["error"]="no sql response";

//check loginhash
$loggedin_res=$db_sys->query("select uid from login where hash='".$request->hash."'");
if($request->hash && $loggedin_res) {

	//login succeeded, continue executing sql
	$response["status"]["code"]=2003;
	$response["status"]["human"]="empty resultset";
	$res_user=$db_user->query($request->sql);
	if($res_user) {
	//	$response["sql"]["error"]=$db_user->error_get_last();
		while($row = $res_user->fetch_object()) {
			//add rows seperately
			$response["sql"]["result"][]=$row;
		}
		if(count($response["sql"]["result"])) {
			$response["status"]["code"]=2002;
			$response["status"]["human"]="resultset count";
		}
	} else {
		$response["status"]["code"]=5001;
		$response["status"]["human"]="sql error";
	}
	
} else {
	
	//userlogin
	$sql="select id as uid from user where uname='".$request->login_uname."' and pass='".hash("sha512",$request->login_pass)."'";
	$res=$db_sys->query($sql);
	
	$uid=0;
	while($row=$res->fetch_object()) {
		$uid=$row->uid;
	}
	
	$sql="insert into login(ts,hash,uid) value('".time()."','".$response["hash"]."','".$uid."')";
	
	if($uid && $db_sys->query($sql)) {
	
		//response	
		$response["status"]["code"]=2001;
		$response["status"]["human"]="login created";
		
	} else {
		
		//response	
		$response["status"]["code"]=4001;
		$response["status"]["human"]="login invalid";
	
	}	
	
}


//inform client what happened
echo json_encode($response);


/*
-- --------------------------------------------------------
--
-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 25. Jul 2017 um 09:54
-- Server-Version: 5.6.26
-- PHP-Version: 5.6.12
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



--
-- Datenbank: `tunnel_sys`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `id` int(11) NOT NULL,
  `hash` text NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL,
  `uname` text NOT NULL,
  `pass` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `uname`, `pass`) VALUES
(1, 'ghse15', '366e33d990f6edae2e95ac23b0722cd2f8c56e1e1e68b2cd22878b40e95603edd2f0c45a84c613a0803f5d86b8672a1c312aa3d246886eebc477de5b12d8dd2d');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

*/
?>