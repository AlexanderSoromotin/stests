<header>
	<div class="header">
		<div class="col col-1">
			<!-- <a href=""> -->
				<h1><a href="<?= $link ?>">s-tests</a></h1>
			<!-- </a> -->
		</div>
		<div class="col col-2">
			<ul>
				<a href="<?= $link ?>/tests">
					<li data-menu="tests">Тесты</li>
				</a>
				<a href="<?= $link ?>/rooms">
					<li data-menu="rooms">Комнаты</li>
				</a>
				<a href="<?= $link ?>/profile">
					<li data-menu="profile">Профиль</li>
				</a>
<!--				<a href="--><?//= $link ?><!--?system=mate">-->
<!--					<li class="mate">Тесты</li>-->
<!--				</a>-->
			</ul>
		</div>
        <div class="col col-2">
            <div class="user-name"></div>
            <a href="<?= $link ?>/profile">
                <div class="user-image">
                    <div class="image">
                        <img src="https://findcreek.com/assets/img/unknown-user.png" alt="">
                    </div>
                </div>
            </a>
            <div class="logout">
                <img class="svg" src="<?= $icons ?>/logout.svg" alt="">
            </div>
        </div>
	</div>
</header>

<style>
    header {
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: var(--header_background_color);
        /*border-bottom: 1px solid var(--main_border_color);*/
        height: 80px;
        z-index: 500;
    }
    header .header {
        position: relative;
        height: 100%;
        width: 1060px;
        padding: 0 30px;
        display: flex;
        justify-content: space-between;
    }
    header .header .col {
        display: flex;
        align-items: center;
    }
    header .header .col-1 a {
        text-decoration: none;
    }
    header .header .col-1 h1 {
        position: relative;
        font-weight: 600;
        height: 100%;
        display: flex;
        align-items: center;
        font-size: 30px;
    }
    header .user-name {
        margin-right: 15px;
        color: #00000080;
    }
    header .col-2 .user-image .image {
        position: relative;
        width: 40px;
        height: 40px;
        overflow: hidden;
        border-radius: 50%;
        border: 1px solid #00000030;
    }
    header .col-2 .logout {
        height: 40px;
        width: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-left: 10px;
        cursor: pointer;

    }
    header .col-2 .logout img {
        transition: .1s;
        opacity: .5;
    }
    header .col-2 .logout:hover img {
        opacity: 1;
    }

    header .col-2 .user-image .image img {
        position: relative;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    header .header ul {
        position: relative;
        display: flex;
        list-style-type: none;
        height: 100%;
        /*margin-left: 100px;*/
    }
    header .header ul li {
        transition: .1s;
        position: relative;
        display: flex;
        height: 100%;
        padding: 0 30px;
        justify-content: center;
        align-items: center;
        /*color: #00000080;*/
        color: #000;
        font-weight: 400;
        font-size: 18px;
    }
    header .header ul li::after {
        transition: .2s;
        position: absolute;
        /*width: calc(100% - 60px);*/
        width: 0;
        bottom: 25px;
        left: 50%;
        transform: translateX(-50%);
        height: 2px;
        background-color: #000;
        content: "";
    }
    header .header ul li:hover::after {
        width: calc(100% - 60px) !important;
    }
    header .header ul li.active::after {
        width: calc(100% - 60px) !important;
    }
    header .header .active {
        color: #000;
    }
    header .header ul li:hover {
        /*background-color: var(--header_active_tab_color);*/
        color: #000;
    }
    header .header ul a {
        text-decoration: none;
    }



</style>

<script type="text/javascript">
	function activeHeaderTab (pageName) {
		$(`header li`).removeClass('active')
        $(`header li[data-menu="${pageName}"]`).addClass('active')
	}

    $(".logout").click(() => {
        localStorage.removeItem("token");
        location.href = "<?= $link ?>";
    })

    function getProfileInfo (callback) {
        $.ajax({
            url: "<?= $link ?>/api/account.getInfo/",
            data: {
                token: encodeURI(localStorage.getItem("token"))
            },
            success: (response) => {
                console.log("account.getInfo", response);
                response = response["response"][0];
                callback(response);
            }
        })
    }

    function updateProfileInfo () {
        getProfileInfo((userData) => {
            $("header .user-name").text(`${userData["surname"]} ${userData["name"].substr(0, 1)}. ${userData["patronymic"].substr(0, 1)}.`);
        });

    }

    updateProfileInfo();
</script>