<?php
	include_once "inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>s.tests</title>
	<link rel="stylesheet" type="text/css" href="style.css?v=<?= $styles_ver ?>">
	<link rel="shortcut icon" href="<?= $link ?>/assets/img/findcreek_logo_1.png" type="image/png">
</head>
<body>

	<?php
//		include_once "inc/ui/head.php";
//		include_once "inc/ui/header.php";
	?>


    <script type="text/javascript">
        userToken = localStorage.getItem("token");
        invitation = "<?= $_GET["inv"] ?>";
        console.log("invitation", invitation);

        if (userToken == undefined || userToken == "") {
            if (invitation != "") {
                location.href = "/auth/?inv=" + invitation;

            } else {
                location.href = "/auth";
            }

        } else {
            if (invitation != "") {
                location.href = "/invitation/?inv=" + invitation;

            } else {
                location.href = "/profile";
            }

        }
    </script>
</body>
</html>