<?php
	include_once "../inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Комнаты</title>
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
            <h2>Вас пригласили в комнату ИСП-12-13 2024 АБ</h2>

            <div class="inv">
                <div class="block">
                    <div data-room-id="1" class="room">
                    <div class="room-name">ИСП-12-13 2024 АБ</div>
                    <div class="room-content">
                        <div class="room-data">
                            <div class="param">Руководитель:</div>
                            <div class="val user-name">Пыжин А. С</div>
                        </div>

                        <div class="room-data ">
                            <div class="param">Участников:</div>
                            <div class="val users-number">12</div>
                        </div>

                        <button class="btn btn-primary">Вступить</button>
                        <div class="empty">Вы уже состоите в этой комнате</div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </main>

	<script type="text/javascript">
        activeHeaderTab("rooms");

        $.ajax({
            url: "<?= $link ?>/api/rooms.getInfoByInvitation/",
            data: {
                token: localStorage.getItem("token"),
                invitation: "<?= $_GET["inv"] ?>"
            },
            success: (response) => {
                console.log("rooms.getInfoByInvitation", response)

                response = response["response"];

                $(".inv .block .room-name").text(`${response["room_data"]["name"]}`)
                $(".inv .block .user-name").text(`${response["room_data"]["room_admin_surname"]} ${response["room_data"]["room_admin_name"].substr(0, 1)}. ${response["room_data"]["room_admin_patronymic"].substr(0, 1)}.`)
                $(".inv .block .users-number").text(`${response["users_number"]}`)

                if (response["can_join"] == 1) {
                    $(".inv .block .empty").remove()
                } else {
                    $(".inv .block .btn").remove()

                }
            }
        })

        $(".block .btn").click(function () {
            $.ajax({
                url: "<?= $link ?>/api/rooms.join/",
                data: {
                    token: localStorage.getItem("token"),
                    invitation: "<?= $_GET["inv"] ?>"
                },
                success: (response) => {
                    console.log("rooms.join", response)
                    location.href = "../rooms/";
                }
            })
        })
    </script>

    <?php
        include_once "../inc/ui/footer.php";
    ?>
</body>
</html>