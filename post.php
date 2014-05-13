<?php header("Content-Type: text/html; charset=utf8");?>

<html>
	<body>
<?php
//Kontrollera att de obligatoriska fälten är ifyllda.
if ($_POST[fname] == '' || $_POST[lname] == '' || $_POST[nr] == '') { die('Fält med * är OBLIGATORISKA!'); }

//Kontrollera att födelsedatumet är ifyllt på formen ÅÅÅÅ-MM-DD eller ÅÅÅÅMMDD
if (!(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$_POST[nr]) || preg_match("/^[0-9]{8}$/",$_POST[nr]))) { die('Ogiltigt födelsedatum?'); }

//Om födelsedatumet är ifyllt på formen ÅÅÅÅMMDD gör om det till ÅÅÅÅ-MM-DD
if (preg_match("/^[0-9]{8}$/",$_POST[nr])) {
	$first = preg_split("/[0-9]{4}$/",$_POST[nr],-1,PREG_SPLIT_NO_EMPTY);
	$second = preg_split("/^[0-9]{4}/",$_POST[nr],-1,PREG_SPLIT_NO_EMPTY);
	$second = preg_split("/[0-9]{2}$/",$second[0],-1,PREG_SPLIT_NO_EMPTY);
	$third = preg_split("/^[0-9]{6}/",$_POST[nr],-1,PREG_SPLIT_NO_EMPTY);
	$_POST[nr] = $first[0] . '-' . $second[0] . '-' . $third[0];
}

//Anslut till mysqlservern
$con = mysql_connect("localhost", "fyskam", "") OR die('Kunde inte ansluta till mysqlservern: ' . mysql_error());

//Använd medlemsregistet för att kontrollera röstlänged
mysql_select_db("register", $con) OR die(mysql_error());
$TABLE="members";

//Allt verkar stämma, lagra $_POST-variablerna i lokala variabler. Vi skyddar oss också mot sql incjection.
$firstname = mysql_real_escape_string($_POST[fname]);
$lastname = mysql_real_escape_string($_POST[lname]);
$bday = mysql_real_escape_string($_POST[nr]);

//var_dump($firstname);


//Hämta rätt inlägg i databasen
$sql = "select * from $TABLE where firstname='$firstname' and lastname='$lastname' and persnr='$bday'";
$result = mysql_query($sql, $con);
//var_dump($result);

//Kontrollera så att vi har hittat något
if (!$result) die('Vi kan inte matcha dina uppgifter i medlemsregistet. Är du medlem? (' . mysql_error() . ')');

$row = mysql_fetch_array($result);
//var_dump($row);

//Dubbelkolla
if (!$row) die('Är du verkligen medlem? ('.mysql_error().')');

//Nu börjar vi bli paranoida, men trippelkolla för säkerhets skull
if ( $row['firstname'] != $firstname || $row['lastname'] != $lastname || $row['persnr'] != $bday ) die('Du verkar inte finnas i register. Kolla dina uppgifter');

//Byt databas till rösterna
mysql_select_db("testvotering", $con) OR die(mysql_error());
$TABLE='votes';

//Nollställ rösterna
$labbrock = 0;
$overall = 0;
$skamkappa = 0;
$blankt = 0;

//Kolla vad vi faktiskt har röstat på
	switch ($_POST[vote]) {
		case 1:
			//echo "Du röstade på Labbrock!";
			$labbrock = 1;
			break;
		case 2:
			//echo "Du röstade på Overall!";
			$overall = 1;
			break;
		case 3:
			//echo "Du röstade på en modifierad variant av Skamkappan!";
			$skamkappa = 1;
			break;
		case 4:
			//echo "Du röstade blankt!";
			$blankt = 1;
			break;
		default:
			die('Du måste rösta på något...');
			break;
	}

//Spara rösten, samt uppgifterna från medlemsregistret och vilket datum rösten registrerades.
$date = date("Y-m-d");
$mail = mysql_real_escape_string($row['mail']);
$sql = "insert into $TABLE (lastname, firstname, persnr, mail, registred, labbrock, overall, skamkappa, blank) values ('$lastname', '$firstname', '$bday', '$mail', '$date', '$labbrock', '$overall', '$skamkappa', '$blankt')";
mysql_query($sql, $con) OR die(mysql_error());

echo "Tack för din röst!";

mysql_close($con);
?>
	</body>
</html>
	
