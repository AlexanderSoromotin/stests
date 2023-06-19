<?php
	include_once "../inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Профиль</title>
	<link rel="stylesheet" type="text/css" href="style.css<?= $cssVer ?>">
	<link rel="shortcut icon" href="<?= $link ?>/assets/img/findcreek_logo_1.png" type="image/png">
</head>
<body>
	<?php
		include_once "../inc/ui/head.php";
		include_once "../inc/ui/header.php";
	?>

	<main>
        <div class="container">
            <div class="profile-info">
                <div class="col user-image">
                    <div class="image">
                        <img src="https://findcreek.com/assets/img/unknown-user.png" alt="">
                    </div>
                </div>

                <div class="user-info">
                    <div class="col user-name">
                        <input type="text" value="" placeholder="Введите фамилию">
                        <input type="text" value="" placeholder="Введите имя">
                        <input type="text" value="" placeholder="Введите Отчество">
                    </div>

                    <div class="col user-more">
                        <div>
                            <input style="border: none" type="text" value="" readonly placeholder="Тут будет Ваш логин">
                            <div class="role">
                                <img class="svg" src="<?= $icons ?>/crown.svg" alt="">
                                <div class="role-name">Тут будет указана Ваша роль</div>
                            </div>
                        </div>

                        <button class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
            </div>
            <br><br><br><br>
            <h2>Пройденные тесты</h2>
            <div class="empty">Тут ничего нет 😶‍🌫️</div>
        </div>
    </main>

	<script type="text/javascript">
        activeHeaderTab("profile");

        function getProfileInfo () {
            $.ajax({
                url: "<?= $link ?>/api/account.getInfo/",
                data: {
                    token: encodeURI(localStorage.getItem("token"))
                },
                success: (response) => {
                    console.log("account.getInfo", response);
                    response = response["response"][0];
                    $(".user-info input:eq(0)").val(response["surname"]);
                    $(".user-info input:eq(1)").val(response["name"]);
                    $(".user-info input:eq(2)").val(response["patronymic"]);
                    $(".user-info input:eq(3)").val(response["login"]);
                    $(".user-info .role-name").text(response["role_name"]);
                }
            })
        }
        getProfileInfo();

	</script>
</body>
</html>