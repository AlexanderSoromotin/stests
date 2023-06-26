<?php
	include_once "../inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Редактирование теста</title>
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
            <div data-test-id="0" class="block test-info">
                <h2>Редактирование теста</h2>
                <br><br>
                <div class="input">
                    <label for="">Название теста</label>
                    <input name="test_name" type="text" placeholder="Введите название теста">
                </div>

                <div class="input">
                    <label for="">Описание теста</label>
                    <textarea name="test_description" rows="4" type="text" placeholder="Введите описание теста"></textarea>
                </div>

                <div class="input">
                    <label for="">Ограничение по времени</label>
                    <input name="test_time_limit" type="number" placeholder="Введите количество минут">
                </div>

                <div class="input">
                    <label for="">Количество попыток</label>
                    <input name="test_attempts" type="number" placeholder="Введите разрешённое количество попыток">
                </div>

                <div class="input">
                    <label for="">Дата тестирования</label>
                    <input name="test_date" type="date" placeholder="Выберите дату тестирования">
                </div>

                <div class="rooms">
                    <h4>Доступно для комнаты</h4>
                    <select name="room" id="">
                    </select>
                </div>
            </div>

            <div class="download-report">
                <button class="btn btn-primary">Скачать отчёт</button>
            </div>

            <center><h2 style="margin-bottom: 20px">Вопросы</h2></center>
            <div class="questions">

            </div>

            <div class="block add-question">
                <img src="<?= $icons ?>/plus.svg" alt="">
            </div>

        </div>
    </main>

	<script type="text/javascript">
        activeHeaderTab("tests")

        const testId = Number("<?= $_GET["id"] ?>");
        console.log("test id", testId)

        $(document).on("click", ".input .make-right-answer", function () {
            $(this).parents(".question").find(".input.right").removeClass("right");
            $(this).parents(".input").addClass("right");
        });

        var rooms = [];
        var roomsSelectHTML = "";
        $.ajax({
            url: "<?= $link ?>/api/account.getRooms/",
            data: {
                token: localStorage.getItem("token"),
            },
            success: (response) => {
                console.log("account.getRooms", response);
                response = response["response"];

                for (i in response["own"]) {
                    item = response["own"][i];
                    rooms.push(item);
                    roomsSelectHTML += `<option data-room-id='${item["id"]}'>${item["name"]}</option>`;
                }
                getTest();
            }
        })

        function addQuestion () {
            $.ajax({
                url: "<?= $link ?>/api/tests.addQuestion/",
                data: {
                    token: localStorage.getItem("token"),
                    test_id: testId
                },
                success: (response) => {
                    console.log("tests.addQuestion", response);
                    response = response["response"];

                    $(".questions").append(`
                       <div data-question-id="${response}" class="block question">
                            <div class="input">
                                <label for="">Текст вопроса</label>
                                <textarea name="question_${response}" rows="4" type="text" placeholder="Введите текст вопроса"></textarea>
                            </div>

                            <div class="input">
                                <label for="">Ответ 1</label>
                                <input name="answer_${response}_1" rows="4" type="text" placeholder="Введите текст ответа 1">
                                <div class="right-answer" data-answer-number="1">Это правильный ответ</div>
                                <div class="make-right-answer" data-answer-number="1">Сделать правильным</div>
                            </div>

                            <div class="input right">
                                <label for="">Ответ 2</label>
                                <input name="answer_${response}_2" rows="4" type="text" placeholder="Введите текст ответа 2">
                                <div class="right-answer" data-answer-number="2">Это правильный ответ</div>
                                <div class="make-right-answer" data-answer-number="2">Сделать правильным</div>
                            </div>

                            <div class="input">
                                <label for="">Ответ 3</label>
                                <input name="answer_${response}_3" rows="4" type="text" placeholder="Введите текст ответа 3">
                                <div class="right-answer" data-answer-number="3">Это правильный ответ</div>
                                <div class="make-right-answer" data-answer-number="3">Сделать правильным</div>
                            </div>

                            <div class="input">
                                <label for="">Ответ 4</label>
                                <input name="answer_${response}_4" rows="4" type="text" placeholder="Введите текст ответа 4">
                                <div class="right-answer" data-answer-number="4">Это правильный ответ</div>
                                <div class="make-right-answer" data-answer-number="4">Сделать правильным</div>
                            </div>

                        </div>
                    `)
                }
            })

        }

        function getTest () {
            $.ajax({
                url: "<?= $link ?>/api/tests.getInfo/",
                data: {
                    token: localStorage.getItem("token"),
                    test_id: testId
                },
                success: (response) => {
                    console.log("tests.getInfo", response);
                    response = response["response"];


                    $(".test-info").attr("data-test-id", response["id"])
                    $("input[name='test_name']").val(response["name"])
                    $("textarea[name='test_description']").val(response["description"])
                    $("input[name='test_time_limit']").val(response["time_limit"])
                    inheritRoom = `<option data-room-id="${response["room_id"]}">${response["room_name"]}</option>`;
                    room = `${inheritRoom}${roomsSelectHTML.replaceAll(inheritRoom, "")}`;
                    // console.log("room", room)
                    $("select[name='room']").append(room)

                    $("input[name='test_date']").val(response["available_date"].substr(0, 10))
                    $("input[name='test_attempts']").val(response["attempts"])

                    $.ajax({
                        url: "<?= $link ?>/api/tests.getQuestions/",
                        data: {
                            token: localStorage.getItem("token"),
                            test_id: testId
                        },
                        success: (response) => {
                            console.log("tests.getQuestions", response);
                            response = response["response"];

                            for (k in response) {
                                item2 = response[k];
                                // console.log("item2", item2)

                                answersHTML = "";
                                answrs = item2["answers"]["answers"];
                                for (j in answrs) {
                                    item3 = answrs[j];

                                    right = "";
                                    if (j == item2["answers"]["right_answer_number"]) {
                                        right = "right";
                                    }
                                    answersHTML += `
                                    <div class="input ${right}">
                                        <label for="">Ответ ${j}</label>
                                        <input name="answer_${item2["id"]}_${j}" rows="4" value="${item3}" type="text" placeholder="Введите текст ответа ${j}">
                                        <div class="right-answer" data-answer-number="${j}">Это правильный ответ</div>
                                        <div class="make-right-answer" data-answer-number="${j}">Сделать правильным</div>
                                    </div>
                                    `;
                                }

                                $(".questions").append(`
               <div data-question-id="${item2["id"]}" class="block question">
                    <div class="input">
                        <label for="">Текст вопроса</label>
                        <textarea name="question_${item2["id"]}" rows="4" type="text"" placeholder="Введите текст вопроса">${item2["title"]}</textarea>
                    </div>

                    ${answersHTML}
                </div>
            `)
                            }


                        }
                    })
                }
            })
        }

        function saveTest() {
            let name = $("input[name='test_name']").val();
            let description = $("textarea[name='test_description']").val();
            let timeLimit = $("input[name='test_time_limit']").val();
            let attempts = $("input[name='test_attempts']").val();
            let date = $("input[name='test_date']").val();

            let roomId = $(".test-info select option:selected").attr("data-room-id");

            console.log(name, description, timeLimit, roomId)

            $.ajax({
                url: "<?= $link ?>/api/tests.updateTest/",
                data: {
                    token: localStorage.getItem("token"),
                    test_id: testId,
                    name: name,
                    description: description,
                    time_limit: timeLimit,
                    attempts: attempts,
                    date: date,
                    room_id: roomId
                },
                success: (response) => {
                    console.log("tests.updateTest", response);
                }
            })
        }

        function saveQuestion(id) {
            let title  = $(`.question[data-question-id="${id}"] textarea`).val();
            let details = {
                "answers": {},
                "right_answer_number": 0,
                "mix_answers": 1
            };

            for (let i = 1; i <= 4; i++) {
                let input =  $(`.question[data-question-id="${id}"] .input:eq(${i})`);
                details.answers[i] = input.find("input").val();
                if (input.hasClass("right")) {
                    details.right_answer_number = i;
                }
            }

            $.ajax({
                url: "<?= $link ?>/api/tests.updateQuestion",
                data: {
                    token: localStorage.getItem("token"),
                    question_id: id,
                    title: title,
                    details: details
                },
                success: (response) => {
                    console.log("tests.updateQuestion", response);
                }
            })
        }

        $(".add-question").click(() => {
            addQuestion();
        })

        $(document).on("keyup", ".question .input input, .question .input textarea", function () {
            id = Number($(this).parents(".question").attr("data-question-id"));
            saveQuestion(id)
        })
        $(document).on("click", ".question .input .make-right-answer", function () {
            id = Number($(this).parents(".question").attr("data-question-id"));
            saveQuestion(id)
        })


        $(document).on("keyup", ".test-info input, .test-info textarea", function () {
            id = Number($(this).parents(".test-info").attr("data-test-id"));
            saveTest(id)
        })
        $(document).on("change", ".test-info select, .test-info input[type='date']", function () {
            id = Number($(this).parents(".test-info").attr("data-test-id"));
            saveTest(id)
        })

        //function downloadFile(url) {
        //    console.log("Скачивание", url)
        //    // Создаем временную ссылку
        //    var link = document.createElement('a');
        //    link.href = url;
        //    link.download = url.substr(url.lastIndexOf('/') + 1);
        //
        //    // Добавляем ссылку на страницу и эмулируем клик
        //    $(link).appendTo('body');
        //    link.click();
        //
        //    // Удаляем ссылку
        //    $(link).remove();
        //}
        //
        //$(".download-report button").click(function () {
        //    $.ajax({
        //        url: "<?//= $link ?>///api/tests.makeReport/",
        //        data: {
        //            token: localStorage.getItem("token"),
        //            test_id: testId
        //        },
        //        success: (response) => {
        //            console.log("test.makeReport", response)
        //            downloadFile(response["response"]);
        //        }
        //    })
        //})


	</script>
</body>
</html>