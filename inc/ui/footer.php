<footer>
</footer>

<style>


</style>

<script type="text/javascript">
    $(".pps .pp-close").click(function () {
        console.log($(this).parents(".pp"))
        $(this).parents(".pp").css({"display": "none"})
        $(".pps").css({"display": "none"})
    })

    $(document).on("click", ".open-pp", function () {

        id = $(this).attr("data-pp-id");
        console.log(id)

        $(".pps").css({"display": "flex"})
        $("#" + id).css({"display": "inline"})
    })
</script>