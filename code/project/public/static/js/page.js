$(document).ready(function () {

    $(".GoClass").click(function () {
        if ($(".PageInput").val() != "") {
            if (isNaN($(".PageInput").val())) {
                alert("输入页数错误/Input Error Pages");
                return;
            }

            if (parseInt($(".PageInput").val()) > parseInt($(".MaxPage").text())) {
                alert("输入页数错误/Input Error Pages");
                return;
            }
            if (parseInt($(".PageInput").val()) <= 0) {
                alert("输入页数错误/Input Error Pages");
                return;
            }
            if (Math.ceil($(".PageInput").val()) != parseInt($(".PageInput").val())) {
                alert("输入页数错误/Input Error Pages");
                return;
            }
            var pathUrl = window.location.pathname;
            window.location.href = pathUrl + '?page=' + $(".PageInput").val();
        }

    });
});
