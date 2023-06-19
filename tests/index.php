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
            <h2>Доступные тесты</h2>
            <div class="tests-titles">
                <div class="name">Название</div>
                <div class="subject">Дисциплина</div>
                <div class="questions">Вопросов</div>
                <div class="time">Ограничение по времени</div>
                <div class="link">Перейти</div>
            </div>
            <div class="tests">
                <a href="#">
                    <div class="test">
                        <div class="name">Насколько развиты ваши интуитивные способности?</div>
                        <div class="subject">Биология</div>
                        <div class="questions">15</div>
                        <div class="time">20 минут</div>
                        <div class="link"><img src="<?= $icons ?>/arrow-right.svg" alt=""></div>
                    </div>
                </a>

                <a href="#">
                    <div class="test">
                        <div class="name">Насколько развиты ваши интуитивные способности?</div>
                        <div class="subject">Биология</div>
                        <div class="questions">15</div>
                        <div class="time">20 минут</div>
                        <div class="link"><img src="<?= $icons ?>/arrow-right.svg" alt=""></div>
                    </div>
                </a>

                <a href="#">
                    <div class="test">
                        <div class="name">Насколько развиты ваши интуитивные способности?</div>
                        <div class="subject">Биология</div>
                        <div class="questions">15</div>
                        <div class="time">20 минут</div>
                        <div class="link"><img src="<?= $icons ?>/arrow-right.svg" alt=""></div>
                    </div>
                </a>
            </div>
        </div>
    </main>

	<script type="text/javascript">
        activeHeaderTab("tests")

	</script>
</body>
</html>