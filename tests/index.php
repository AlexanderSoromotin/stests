<?php
	include_once "../inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Тесты</title>
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
            <h2 class="my-tests-title">Мои тесты <button style="top: -2px;max-height: 30px !important; padding: 0 20px; margin-left: 10px" class="btn btn-primary create-test">Создать</button></h2>
            <div class="tests-titles">
                <div class="name">Название</div>
                <div class="subject">Комната</div>
                <div class="questions">Вопросов</div>
                <div class="time">Ограничение по времени</div>
                <div class="link">Перейти</div>
            </div>
            <div class="my-tests tests">
                <div class="empty">Вы не создали ещё ни одного теста 👾</div>

                <br><br><br>
            </div>


            <h2>Доступные тесты</h2>
            <div class="tests-titles">
                <div class="name">Название</div>
                <div class="subject">Дисциплина</div>
                <div class="questions">Вопросов</div>
                <div class="time">Ограничение по времени</div>
                <div class="link">Перейти</div>
            </div>
            <div class="available-tests tests">
                <div class="empty">Нет ни одного доступного теста 🧐</div>
            </div>
        </div>
    </main>

	<script type="text/javascript">
        activeHeaderTab("tests")

        function updateTests () {
            getProfileInfo((userData) => {
                console.log("getRooms", userData)

                if (userData["role_id"] != 2) {
                    $(".my-tests").remove();
                    $(".my-tests-title").remove();
                    $(".tests-titles:eq(0)").css({"display": "none"});
                }

                $(".my-tests .test").css({"display": "none"});
                $(".tests-titles:eq(0)").css({"display": "none"});

                $(".available-tests .test").css({"display": "none"});
                $(".tests-titles:eq(1)").css({"display": "none"});

                $.ajax({
                    url: "<?= $link ?>/api/tests.getAll/",
                    data: {
                        token: localStorage.getItem("token")
                    },
                    success: (response) => {
                        console.log("response", response);
                        response = response["response"];

                        if (response["own_tests"].length != 0) {
                            $(".my-tests .test").css({"display": "flex"});
                            $(".tests-titles:eq(0)").css({"display": "flex"});
                            $(".my-tests .empty").css({"display": "none"});
                        }

                        if (response["available_tests"].length != 0) {
                            $(".tests-titles:eq(1)").css({"display": "flex"});
                            $(".available-tests .empty").css({"display": "none"});
                        }

                        for (i in response["own_tests"]) {
                            item = response["own_tests"][i];
                            if (item["time_limit"] == 0) {
                                item["time_limit"] = "нет"
                            } else {
                                item["time_limit"] += " минут"
                            }

                            $(".my-tests").prepend(`
                                <a href="<?= $link ?>/edit-test/?id=${item["id"]}">
                                    <div class="test">
                                        <div class="name">${item["name"]}</div>
                                        <div class="subject">${item["room_name"]}</div>
                                        <div class="questions">${item["questions_number"]}</div>
                                        <div class="time">${item["time_limit"]}</div>
                                        <div class="link"><img src="<?= $icons ?>/arrow-right.svg" alt=""></div>
                                    </div>
                                </a>
                            `);
                        }

                        for (i in response["available_tests"]) {
                            item = response["available_tests"][i];
                            if (item["time_limit"] == 0) {
                                item["time_limit"] = "нет"
                            } else {
                                item["time_limit"] += " минут"
                            }

                            link = '<?= $link ?>/test?id=' + item["id"];
                            testClass = "";
                            if (item["attempts"] <= item["attempts_spent"]) {
                                testClass = "unavailable";
                                link = "#";
                            }

                            $(".available-tests").prepend(`
                                <a href="${link}">
                                    <div class="test ${testClass}">
                                        <div class="name">${item["name"]}</div>
                                        <div class="subject">${item["room_name"]}</div>
                                        <div class="questions">${item["questions_number"]}</div>
                                        <div class="time">${item["time_limit"]}</div>
                                        <div class="link"><img src="<?= $icons ?>/arrow-right.svg" alt=""></div>
                                    </div>
                                </a>
                            `);
                        }
                    }
                })
            });
        }
        updateTests();

        $(".create-test").click(function () {
            $.ajax({
                url: "<?= $link ?>/api/tests.create/",
                data: {
                    token: localStorage.getItem("token")
                },
                success: (response) => {
                    console.log("tests.create", response)
                    updateTests();
                }
            })
        })

	</script>
</body>
</html>