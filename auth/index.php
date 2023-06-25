<?php
	include_once "../inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Вход в аккаунт</title>
	<link rel="stylesheet" type="text/css" href="style.css<?= $cssVer ?>">
	<link rel="shortcut icon" href="<?= $link ?>/assets/img/findcreek_logo_1.png" type="image/png">
</head>
<body>
	<?php
		include_once "../inc/ui/head.php";
//		include_once "../../inc/header.php";
	?>

	<main>
        <div class="form login-form">
            <h1>Привет! 😇</h1>
            <br><br><br>
            <input placeholder="Введите логин" type="login">
            <input placeholder="Введите пароль" type="password">
            <button class="btn btn-primary">Войти</button>
            <div class="reg"><a href="../reg">У меня нет аккаунта</a></div>
        </div>
	</main>

	<script type="text/javascript">
        userToken = localStorage.getItem("token");
        invitation = "<?= $_GET["inv"] ?>";
        if (userToken != undefined && userToken != "" && userToken != "") {
            location.href = "../";
        }

        $(".btn").click(() => {
            let login = $(".form input:eq(0)").val();
            let password = $(".form input:eq(1)").val();

            if (login.replaceAll(" ", "").length == 0) {
                return;
            }

            if (password.replaceAll(" ", "").length == 0) {
                return;
            }

            $.ajax({
                url: "<?= $link ?>/api/auth.getToken/",
                method: "post",
                data: {
                    login: login,
                    password: password
                },
                success: (response) => {
                    console.log("auth.getToken", response);
                    if (typeof(response["response"]) == "string") {
                        localStorage.setItem("token", response["response"]);
                        if (invitation != "") {
                            location.href = "../?inv=" + invitation;
                        } else {
                            location.href = "../";
                        }

                    } else {
                        $(".form button").css({"background-color": "red"})
                        setTimeout(() => {
                            $(".form button").css({"background-color": "#000"})

                        }, 300)
                    }
                }
            })
        })


	</script>
</body>
</html>