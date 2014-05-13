<?php header("Content-Type: text/html; charset=utf8");?>

<html>
	<body>
		<?php
		switch ($_POST[vote]) {
			case 1:
				echo "Du röstade på Labbrock!";
				break;
			case 2:
				echo "Du röstade på Overall!";
				break;
			case 3:
				echo "Du röstade på en modifierad variant av Skamkappan!";
				break;
			case 4:
				echo "Du röstade blankt!";
				break;
			default:
				die('Du måste rösta på något...');
				break;
		}
		?>
	</body>
</html>
	
