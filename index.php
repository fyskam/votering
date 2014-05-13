<?php header("Content-Type: text/html; charset=utf8");?>

<html>
	<head>
		<title>FysKams voteringssystem</title>
		<meta html-equiv="Content-Type" content="text/html;charset=utf-8"/>
	</head>

	<body>
		<form action="post.php" method="post">
			<table>
				<tr><td width="100">Labbrock</td><td><input type="radio" name="vote" value="1"/></td></tr>
				<tr><td width="100">Overall</td><td><input type="radio" name="vote" value="2"/></td></tr>
				<tr><td width="100">Modifierad skamkappa</td><td><input type="radio" name="vote" value="3"/></td></tr>
				<tr><td width="100">Blankt</td><td><input type="radio" name="vote" value="4"/></td></tr>
				<tr><td width="100">Förnamn*</td><td><input type="text" name="fname"/></td></td>
				<tr><td width="100">Efternamn*</td><td><input type="text" name="lname"/></td></td>
				<tr><td width="100">Födelsedatum*</td><td><input type="text" name="nr"/></td></td>
			</table>
			<input type="submit" value="Lägg min röst"/>
			<p>* Dessa fält är obligatoriska för att kontrollera att den som röstar finns med i medlemsregistret och alltså är röstberättigad enligt FysKams stadgar. Ingen åsiktsregistrering sker. Vid frågor kontakta Informationsansvarig på <a href="mailto:nvf-info@utn.se">nvf-info@utn.se</a>.</p>
		</form>
	</body>
</html>
