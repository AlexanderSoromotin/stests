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
            <h2 class="own-rooms-title">Мои комнаты <button style="top: -2px;max-height: 30px !important; padding: 0 20px; margin-left: 10px" class="btn create-room btn-primary">Создать</button></h2>
            <div class="own-rooms">
                <div class="empty">Вы не можете создать комнату 🫥</div>
                <div class="rooms-list"></div>
                <br><br><br><br>
            </div>



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
                <div class="members custom-scrollbar"></div>


                <button style="margin-bottom: 20px" class="open-pp btn create-report btn-primary" data-pp-id="pp-create-report">Сформировать отчёт по тестам</button>

                <div>Приглашение</div>
                <div class="copy-field">
                    <input readonly type="text" placeholder="Сылка для приглашения">
                    <div class="copy">
                        <img class="svg" src="<?= $icons ?>/copy.svg" alt="">
                    </div>
                </div>

            </div>
        </div>

        <div id="pp-create-report" class="pp">
            <div class="pp-header">
                <h3 class="pp-title">Создание отчёта</h3>
                <div class="pp-close"><img class="svg" src="<?= $icons ?>/x.svg" alt=""></div>
            </div>
            <div class="pp-content">
                <div>Выберите тесты</div>
                <div class="list">

                </div>
                <button class="btn download-report btn-primary">Создать и скачать</button>
            </div>
        </div>
    </div>

	<script type="text/javascript">
        activeHeaderTab("rooms");

        function downloadFile(url) {
            console.log("Скачивание", url)
            // Создаем временную ссылку
            var link = document.createElement('a');
            link.href = url;
            link.download = url.substr(url.lastIndexOf('/') + 1);

            // Добавляем ссылку на страницу и эмулируем клик
            $(link).appendTo('body');
            link.click();

            // Удаляем ссылку
            $(link).remove();
        }

        $(document).on("click", "#pp-create-report .item", function () {
            if ($(this).hasClass("selected")) {
                $(this).removeClass("selected")
                return;
            }
            $(this).addClass("selected")
        })

        $(".download-report").click(function () {
            testsIds = [];
            count = $("#pp-create-report .list .item.selected").length;
            for (let i = 0; i < count; i++) {
                id = $("#pp-create-report .list .item.selected:eq(" + i + ")").attr("data-test-id");
                testsIds.push(Number(id));
            }
            console.log(testsIds.join());

            if (testsIds.length == 0) {
                return;
            }

            $.ajax({
                url: "<?= $link ?>/api/tests.makeReport/",
                data: {
                    token: localStorage.getItem("token"),
                    tests_ids: testsIds.join()
                },
                success: (response) => {
                    console.log("test.makeReport", response)
                    downloadFile(response["response"]);
                }
            })
        })

        function updateTests (roomId) {
            console.log("updateTests roomId", roomId);

            getProfileInfo((userData) => {
                console.log("updateTests", userData)

                $("#pp-create-report .list .item").remove();

                $.ajax({
                    url: "<?= $link ?>/api/tests.getAll/",
                    data: {
                        token: localStorage.getItem("token"),
                    },
                    success: (response) => {
                        console.log("tests.getAll", response);
                        response = response["response"];

                        for (i in response["own_tests"]) {
                            item = response["own_tests"][i];

                            if (item["room_id"] != roomId) {
                                continue;
                            }

                            $("#pp-create-report .list").append(`
                            <div data-test-id="${item["id"]}" class="item">
                                <div class="name">${item["name"]}</div>
                                <div class="room">${item["room_name"]}</div>
                                <div class="date">${item["available_date"].substr(0, 10).split("-").reverse().join(".")}</div>
                            </div>
                            `);
                        }
                    }
                })
            });
        }

        function getRooms () {
            getProfileInfo((userData) => {
                console.log("getRooms", userData)

                if (userData["role_id"] == 2) {
                    $(".own-rooms .empty").css({"display": "none"});
                } else {
                    $(".own-rooms-title, .own-rooms").remove();
                }

                $(".own-rooms .room").remove();
                $(".joined-rooms .room").remove();


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

                        if (response["joined"].length != 0) {
                            $(".joined-rooms .empty").css({"display": "none"})
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

        $(document).on("click", ".open-pp[data-pp-id='pp-create-report']", function () {
            id = $(this).parents(".pp").attr("data-room-id")
            updateTests(Number(id));
            $(this).parents(".pp").find(".pp-close").click();
        });

        $(document).on("click", ".open-pp[data-pp-id='pp-edit-room']", function () {

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
                        $("#pp-edit-room .member").remove();

                        for (i in response["users"]) {
                            item = response["users"][i];
                            console.log(item)



                            $("#pp-edit-room .members").append(`
                            <div class="member" data-user-id="${item["user_id"]}">
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
                        $("#pp-edit-room").attr("data-room-id", response["room_data"]["id"])
                    }
                })
            }

            $(".pps").css({"display": "flex"})
            $("#" + id).css({"display": "inline"})
        })

        $(document).on("input keyup", "#pp-edit-room .room-name input", function () {
            roomId = $(this).parents("#pp-edit-room").attr("data-room-id")
            name = $(this).val();
            console.log(roomId)

            $.ajax({
                url: "<?= $link ?>/api/rooms.setInfo/",
                data: {
                    token: localStorage.getItem("token"),
                    room_id: roomId,
                    name: name
                },
                success: (response) => {
                    console.log("rooms.setInfo", response);
                }
            })
        })

        $(document).on("click", "#pp-edit-room .member .remove-member", function () {
            userId = Number($(this).parents(".member").attr("data-user-id"));
            roomId = $(this).parents("#pp-edit-room").attr("data-room-id");

            $(this).parents(".member").remove();

            $.ajax({
                url: "<?= $link ?>/api/rooms.removeUser/",
                data: {
                    token: localStorage.getItem("token"),
                    room_id: roomId,
                    user_id: userId
                },
                success: (response) => {
                    console.log("rooms.removeUser", response);
                }
            })
        })

        $(document).on("click", ".copy-field .copy", function () {
            copyToClipboard($(this).parents(".copy-field").find("input").val())
        })

        function copyToClipboard(text) {
            console.log("copying", text);

            var $tempInput = $('<input>');
            $('body').append($tempInput);
            $tempInput.val(text).select();
            document.execCommand('copy');
            $tempInput.remove();
        }

        $(".create-room").click(function () {
            $.ajax({
                url: "<?= $link ?>/api/rooms.create/",
                data: {
                    token: localStorage.getItem("token"),
                },
                success: (response) => {
                    console.log("rooms.create", response);
                    getRooms();
                }
            })
        })


    </script>

    <?php
        include_once "../inc/ui/footer.php";
    ?>
</body>
</html>