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
            <h1>Регистрация 😌</h1>
            <br><br><br>
            <input name="surname" placeholder="Введите фамилию" type="login">
            <input name="name" placeholder="Введите имя" type="login">
            <input name="patronymic" placeholder="Введите отчество" type="login">
            <input name="login" placeholder="Введите логин" type="login">
            <input name="password" placeholder="Введите пароль" type="password">
            <input name="confirm-password" placeholder="Повторите пароль" type="password">
            <button class="btn btn-primary">Зарегистрироваться</button>
            <div class="reg"><a href="../auth">У меня есть аккаунт</a></div>
        </div>
	</main>

	<script type="text/javascript">
        userToken = localStorage.getItem("token");
        if (userToken != undefined) {
            location.href = "../";
        }

        $(".form button.btn-primary").click(function () {
            if ($(this).hasClass("loading")) {
                return;
            }
            $(this).addClass("loading");
            $(this).text("Регистрация...");
            let name = $('.form input[name="name"]').val();
            let surname = $('.form input[name="surname"]').val();
            let patronymic = $('.form input[name="patronymic"]').val();
            let login = $('.form input[name="login"]').val();
            let password = $('.form input[name="password"]').val();
            let confirm_password = $('.form input[name="confirm-password"]').val();

            if (name.replaceAll(" ", "").length == 0 || surname.replaceAll(" ", "").length == 0 || patronymic.replaceAll(" ", "").length == 0 || login.replaceAll(" ", "").length == 0 || password.replaceAll(" ", "").length == 0 || confirm_password.replaceAll(" ", "").length == 0) {
                return;
            }

            if (password != confirm_password) {
                return;
            }

            $.ajax({
                url: "<?= $link ?>/api/auth.register/",
                method: "post",
                data: {
                    name: name,
                    surname: surname,
                    patronymic: patronymic,
                    login: login,
                    password: password,
                    confirm_password: confirm_password
                },
                success: (response) => {
                    console.log("auth.register", response);
                    if (response["response"]) {
                        localStorage.setItem("token", response["response"]);
                        location.href = "../";
                    }
                }
            })
        })


	</script>
</body>
</html>