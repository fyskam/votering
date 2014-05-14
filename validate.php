<?php header("Content-Type: text/html; charset=utf8");?>

<html>
	<head></head>
	<body>
<?php

//Hämta hastaggen
$hashtag = $_GET[v];

//Anslut till mysqlservern
$con = mysql_connect("localhost", "fyskam", "") OR die(mysql_error());
mysql_select_db("testvotering", $con) OR die(mysql_error());
$TABLE = 'votes';

//Städa hashtaggen
$hashtag = mysql_real_escape_string($hashtag);

//Kolla att hashtaggen finns, och att det finns exakt 1 av den
$result = mysql_query("select hashtag from $TABLE where hashtag='$hashtag'", $con);
$numrows = mysql_num_rows($result);
if ($numrows == 0) die('Kunde inte hitta dig... ');
if ($numrows != 1) die('Ops... Det verkar ha skett en hashkollision. Det är bäst du tar kontakt med NSA.');

//Uppdatera
$sql = "update $TABLE set validated='1' where hashtag='$hashtag'";
mysql_query($sql, $con);
mysql_close($con);
echo "Din röst är räknad, tack för ditt engagemang!";

?>

	</body>
</html>
