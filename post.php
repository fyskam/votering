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

//Hämta rätt inlägg i databasen
$sql = "select * from $TABLE where firstname='$firstname' and lastname='$lastname' and persnr='$bday'";
$result = mysql_query($sql, $con);

//Kontrollera så att inget har fått fel och vi har hittat något
if (!$result) die(mysql_error());
$numrows = mysql_num_rows($result);
if ($numrows == 0) die('Vi kan inte matcha dina uppgifter i registret. Försök igen.');

//Om vi har fått något annat än exakt 1 träff har något gått fel
if ($numrows != 1) die('Vi verkar ha fått fler än en träff på dina uppgifter. Kontakta Informationsansvarig på <a href="mailto:nvf-info@utn.se">nvf-info@utn.se</a>.');

$row = mysql_fetch_array($result);

//Dubbelkolla
if (!$row) die('Är du verkligen medlem? ('.mysql_error().')');

//Nu börjar vi bli paranoida, men trippelkolla för säkerhets skull
if ( strtolower($row['firstname']) != strtolower($firstname) || strtolower($row['lastname']) != strtolower($lastname) || $row['persnr'] != $bday ) die('Du verkar inte finnas i register. Kolla dina uppgifter');

//Byt databas till rösterna
mysql_select_db("votering", $con) OR die(mysql_error());
$TABLE='votes';

//Kontrollera så att vi inte röstar två gånger
$sql = "select * from $TABLE where firstname='$firstname' and lastname='$lastname' and persnr='$bday'";
$result = mysql_query($sql, $con);
if (mysql_num_rows($result) != 0) die('Du verkar redan ha röstat.');

//Nollställ rösterna
$labbrock = 0;
$overall = 0;
$skamkappa = 0;
$blankt = 0;

//Kolla vad vi faktiskt har röstat på
	switch ($_POST[vote]) {
		case 1:
			$labbrock = 1;
			break;
		case 2:
			$overall = 1;
			break;
		case 3:
			$skamkappa = 1;
			break;
		case 4:
			$blankt = 1;
			break;
		default:
			die('Du måste rösta på något...');
			break;
	}

//Generera en saltad hashtag med mailaddressen som används till att validera rösterna senare. Saltet är ett slumpmässigt 256 byte långt hexadecimalt tal.
$mail = mysql_real_escape_string($row['mail']);
$salt = bin2hex(openssl_random_pseudo_bytes(256));
$hashtag = hash('sha256', $mail.$salt);

//Spara rösten, samt uppgifterna från medlemsregistret och vilket datum rösten registrerades.
$date = date("Y-m-d");
$sql = "insert into $TABLE (lastname, firstname, persnr, mail, registred, labbrock, overall, skamkappa, blank, validated, hashtag) values ('$lastname', '$firstname', '$bday', '$mail', '$date', '$labbrock', '$overall', '$skamkappa', '$blankt', '0', '$hashtag')";
mysql_query($sql, $con) OR die(mysql_error());

//Kolla så att allting gick okej
if (mysql_affected_rows() == -1) die('Ett oväntat fel inträffade, försök igen. Om problemet kvarstår kontakta Informationsansvarig på <a href=\"mailto:nvf-info@utn.se\">nvf-info@utn.se</a>.');
if (mysql_affected_rows() != 1) die('Ett oväntat fel inträffade, försök igen. Om problemet kvarstår kontakta Informationsansvarig på <a href=\"mailto:nvf-info@utn.se\">nvf-info@utn.se</a>.');
mysql_close($con);

//Skicka ut valideringsmailet
$file = fopen("message.txt", "w");
$message = "Hej!\n\nVårat system har upptäckt att någon har använt ditt namn för att lägga en röst på http://fyskam.fysik.uu.se/votering. Om personen var du, var vänlig validera din röst genom att klicka på följande länk:\nhttp://130.243.138.114/votering/validate.php?v=$hashtag\nannars var vänlig ignorera detta mail.";
fwrite($file, $message);
fclose($file);
$cmd = "mail -s \"Var vänlig validera din röst\" $mail < message.txt";
shell_exec($cmd);

//Försäkra oss om att filen är tom
$file = fopen("message.txt", "w");
fwrite($file, "");
fclose($file);

echo "Tack för din röst!</br></br>Du kommer inom kort på ett bekräftelsemail med en länk som du måste följa innan rösten räknas. Kontrollera så att mailet inte har sorterats som skräppost. Om du inte fått mailet inom några timmar ta kontakt med Informationsansvarig på <a href=\"mailto:nvf-info@utn.se\">nvf-info@utn.se</a>.";

?>
	</body>
</html>
	
