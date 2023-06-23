<?php
	include_once "../inc/config.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Тест</title>
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
<!--            <h2>Тест</h2>-->
            <div class="pages">

                <div class="page showing" data-page-name="test-info">
                    <div class="test-block loading">
                        <div class="content">
                            <h2></h2>
                            <h4></h4>
                            <ul>
                                <li>Количество попыток: <span data-attempts="1"></span></li>
                                <li><span data-time="1"></span></li>
                                <li>Комната: <span data-room="1"></span></li>
                                <li>Количество вопросов: <span data-questions="1"></span></li>
                            </ul>
                            <button class="btn btn-primary">Начать</button>
                        </div>
                    </div>
                </div>

                <div class="page result-page hidden">
                    <div class="result">
                        <div class="content">
                            <h2>Тест такой-то</h2>
                            <h4>Описание теста</h4>
                            <ul>
                                <li>Время прохождения: <span data-time>12:03</span></li>
                                <li>Правильных ответов: <span data-right-answers>12 из 15</span></li>
                                <li>Оценка: <span data-score>80</span></li>
                            </ul>
                            <button class="btn go-to-profile btn-primary">Перейти в профиль</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

	<script type="text/javascript">
        activeHeaderTab("tests")

        const testId = Number("<?= $_GET["id"] ?>");
        console.log("test id", testId)

        $.ajax({
            url: "<?= $link ?>/api/tests.getInfo/",
            data: {
                token: localStorage.getItem("token"),
                test_id: testId
            },
            success: (response) => {
                console.log("tests.getInfo", response);
                response = response["response"];

                time_limit = "";
                if (response["time_limit"] == 0) {
                    time_limit = "Нет ограничений по времени";
                } else {
                    time_limit = "Время на тест: " + response["time_limit"] + " минут";
                }

                $(".test-block").removeClass("loading")
                $(".test-block .content h2").text(response["name"])
                $(".test-block .content h4").text(response["description"])
                $('.test-block span[data-attempts="1"]').text(response["attempts"])
                $('.test-block span[data-time="1"]').text(time_limit)
                $('.test-block span[data-room="1"]').text(response["room_name"])
                $('.test-block span[data-questions="1"]').text(response["questions_number"])

                $(".result h2").text("Результат. " + response["name"])
                $(".result h4").text(response["description"])
                window.title = response["name"];
                localStorage.setItem("time_limit", response["time_limit"]);
            },
            error: (response) => {
                console.log("error tests.getInfo", response);
            }
        })

        var timer = setInterval(() => {}, 1000);
        function startTimer (minutes) {
            if (minutes == 0) {
                $(".question-block .test-time").css({"display": "none"});
                return;
            }
            console.log("started timer", minutes)
            let seconds = minutes * 60;
            timer = setInterval(() => {
                if (seconds <= 0) {
                    $(".question-block .test-time").text("Время закончилось");
                    clearInterval(timer);
                    return;
                }
                seconds--;
                // console.log("seconds", seconds);

                let outMinutes = Math.floor(seconds / 60);
                let outSeconds = seconds % 60;
                $(".question-block .test-time").text(outMinutes + ":" + outSeconds);
            }, 1000);
        }

        function stopTimer () {
            clearInterval(timer);
            let time = $(".question-block .test-time:eq(0)").text().split(":");
            let minutes = Number(time[0]);
            let seconds = Number(time[1]);

            console.log(time)

            seconds += minutes * 60;

            return seconds;
        }

        $(".test-block button").click(() => {
            $.ajax({
                url: "<?= $link ?>/api/tests.start/",
                data: {
                    token: localStorage.getItem("token"),
                    test_id: testId
                },
                success: (response) => {
                    console.log("tests.start", response);
                    response = response["response"];
                    if (response) {
                        for (i in response) {
                            item = response[i];
                            answers = "";

                            for (j in item["answers"]["answers"]) {
                                answer = item["answers"]["answers"][j];

                                answers += `
                                    <div class="answer">
                                        <input name="${item["id"]}" type="radio" id="${item["id"]}_${j}">
                                        <label for="${item["id"]}_${j}">${answer}</label>
                                    </div>
                                  `;

                            }
                            $(".pages").append(`
                            <div class="page hidden" data-question-number="${(Number(i) + 1)}" data-page-name="test-info">
                                <div class="question-block">
                                    <div class="test-data">
                                        <div class="question-number">Вопрос ${++i} из ${response.length}</div>
                                        <div class="test-time"></div>
                                    </div>
                                    <h3>${item["title"]}</h3>
                                    <div class="answers">
                                        ${answers}
                                    </div>
                                    <button class="btn btn-primary next-question blocked">Далее</button>
                                </div>
                            </div>`)
                        }

                        $(".pages .showing").removeClass("showing").addClass("completed").index();
                        $(".pages .page[data-question-number='1']").removeClass("hidden").addClass("showing");
                        startTimer(Number(localStorage.getItem("time_limit")))

                    }
                },
                error: (response) => {
                    console.log("error tests.getInfo", response);
                }
            })
        })

        $(document).on("click", ".question-block input", function () {
            $(this).parents(".question-block").find("button").removeClass("blocked");
        })

        $(document).on("click", ".question-block .end-test", function () {
            let answers = [];
            for (let i = 1; i <= $(".pages .question-block").length; i++) {
                id = $(".page[data-question-number='" + i + "'] input[type=radio]:checked").attr("id");
                answers.push(id)
            }
            console.log("answers", answers)

            $(".pages .showing").removeClass("showing").addClass("completed");

            time = Number(localStorage.getItem("time_limit") * 60) - stopTimer();
            console.log("time", time);
            minutes = Math.floor(time / 60);
            seconds = time % 60;

            if (seconds < 10) {
                seconds = "0" + seconds;
            }

            $.ajax({
                url: "<?= $link ?>/api/tests.addResult/",
                data: {
                    token: localStorage.getItem("token"),
                    answers: JSON.stringify(answers),
                    time: time,
                    test_id: testId
                },
                success: (response) => {
                    console.log("response", response)
                    response = response["response"];
                    $(".pages .result-page span[data-time]").text(minutes + ":" + seconds)
                    $(".pages .result-page span[data-right-answers]").text(response["right_answers"] + " из " + $(".question-block").length)
                    $(".pages .result-page span[data-score]").text(response["score"] + " из 100")
                    $(".pages .result-page").removeClass("hidden").addClass("showing");

                }
            })
        })

        $(document).on("click", ".question-block .next-question", function () {
            if ($(this).hasClass("blocked")) {
                return;
            }
            let questionNumber = Number($(".pages .showing").attr("data-question-number"));
            $(".pages .showing").removeClass("showing").addClass("completed");

            console.log("current questionNumber", questionNumber)
            console.log(".page[data-question-number='" + (questionNumber + 1) + "']", $(".pages .page[data-question-number='" + (questionNumber + 1) + "']"))

            $(".pages .page[data-question-number='" + (questionNumber + 1) + "']").removeClass("hidden").addClass("showing");
            if (questionNumber == $(".pages .question-block").length - 1) {
                $(".pages .page.showing .next-question").text("Закончить").addClass("end-test").removeClass("next-question");

            }



        })

        $(".go-to-profile").click(() => {
            location.href = "<?= $link ?>/profile";
        })



	</script>
</body>
</html>