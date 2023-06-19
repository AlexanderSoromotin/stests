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
            <h2>Мои комнаты <button style="top: -2px;max-height: 30px !important; padding: 0 20px; margin-left: 10px" class="btn btn-primary">Создать</button></h2>
            <div class="own-rooms">
                <div class="empty">Вы не можете создать комнату 🫥</div>
                <div class="rooms-list"></div>
            </div>

            <br><br><br><br>

            <h2>Комнаты</h2>
            <div class="joined-rooms">
                <div class="empty">Вы не состоите ни в одной комнате 🖐🏿</div>
                <div class="rooms-list"></div>
            </div>
        </div>
    </main>

    <div class="pps">
        <div class="background"></div>
        <div id="pp-edit-room" class="pp">
            <div class="pp-header">
                <h3 class="pp-title">Комната ИСП-1,2 2020 БО</h3>
                <div class="pp-close"><img class="svg" src="<?= $icons ?>/x.svg" alt=""></div>
            </div>
            <div class="pp-content">
                <div>Название</div>
                <div class="room-name">
                    <input type="text" placeholder="Название комнаты">
                </div>



                <div>Участники</div>
                <div class="members custom-scrollbar"> </div>



                <div>Приглашение</div>
                <div class="copy-field">
                    <input type="text" placeholder="Сылка для приглашения">
                    <div class="copy">
                        <img class="svg" src="<?= $icons ?>/copy.svg" alt="">
                    </div>
                </div>

                <button class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>

	<script type="text/javascript">
        activeHeaderTab("rooms");

        function getRooms () {
            getProfileInfo((userData) => {
                console.log("getRooms", userData)
                if (userData["role_id"] == 2) {
                    $(".own-rooms .empty").remove();
                }

                $.ajax({
                    url: "<?= $link ?>/api/account.getRooms/",
                    data: {
                        token: localStorage.getItem("token")
                    },
                    success: (response) => {
                        console.log("account.getRooms", response);

                        response = response["response"];

                        for (i in response["own"]) {
                            item = response["own"][i];
                            $(".own-rooms .rooms-list").append(`
                            <div data-pp-id="pp-edit-room" data-room-id="${item["id"]}" class="room open-pp" >
                                <div class="room-name">${item["name"]}</div>
                                <div class="room-content">
                                    <div class="room-data user-name">
                                        <div class="param">Руководитель:</div>
                                        <div class="val">${response["user_name"]["surname"]} ${response["user_name"]["name"].substr(0, 1)}. ${response["user_name"]["patronymic"].substr(0, 1)}.</div>
                                    </div>

                                    <div class="room-data user-name">
                                        <div class="param">Участников:</div>
                                        <div class="val">${item["users"]}</div>
                                    </div>
                                </div>
                            </div>`);
                        }

                        for (i in response["joined"]) {
                            item = response["joined"][i];
                            $(".joined-rooms .rooms-list").append(`
                            <div data-room-id="${item["id"]}" class="room" >
                                <div class="room-name">${item["room_name"]}</div>
                                <div class="room-content">
                                    <div class="room-data user-name">
                                        <div class="param">Руководитель:</div>
                                        <div class="val">${item["room_admin_surname"]} ${item["room_admin_name"].substr(0, 1)}. ${item["room_admin_patronymic"].substr(0, 1)}.</div>
                                    </div>

                                    <div class="room-data user-name">
                                        <div class="param">Участников:</div>
                                        <div class="val">${item["users"]}</div>
                                    </div>
                                </div>
                            </div>`);
                        }
                    }
                })
            });
        }
        getRooms();

        $(document).on("click", ".open-pp", function () {

            id = $(this).attr("data-pp-id");
            console.log(id)

            if (id == "pp-edit-room") {
                $("#pp-edit-room .member").remove();
                let roomID = $(this).attr("data-room-id")
                $.ajax({
                    url: "<?= $link ?>/api/rooms.getInfo/",
                    data: {
                        room_id: roomID
                    },
                    success: (response) => {
                        console.log("rooms.getInfo", response);
                        response = response["response"];
                        for (i in response["users"]) {
                            item = response["users"][i];
                            console.log(item)

                            $("#pp-edit-room .members").append(`
                            <div class="member">
                                <div class="member-info">
                                    <div data-user-id="${item["user_id"]}" class="name">${item["user_surname"]} ${item["user_name"]} ${item["user_patronymic"]}</div>
                                    <div class="login">@${item["user_login"]}</div>
                                </div>
                                <div class="remove-member"><img class="svg" src="<?= $icons ?>/x.svg" alt=""></div>
                            </div>
                            `);
                        }
                        $("#pp-edit-room .copy-field input").val("<?= $link ?>/?inv="+ encodeURI(response["room_data"]["invitation_code"]))
                        $("#pp-edit-room .pp-title").text("Комната "+ response["room_data"]["name"])
                        $("#pp-edit-room .room-name input").val(response["room_data"]["name"])
                    }
                })
            }

            $(".pps").css({"display": "flex"})
            $("#" + id).css({"display": "inline"})
        })

	</script>

    <?php
        include_once "../inc/ui/footer.php";
    ?>
</body>
</html>