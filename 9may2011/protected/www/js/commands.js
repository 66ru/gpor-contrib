
function increaseHeight() {
    /*$("#comments_textarea").toggleClass("textarea-spread");
    $(".increase-height-link").toggleClass("increase-height-link-spread");
    curHeight = $("#comments_textarea").height();
    $("#comments_textarea").height(curHeight + 200 + "px");*/
}


function iframeMessage (response, object_type) {
    var id_img = response.js.img_name;
    surroundText('[' + object_type +']' + id_img +'[/' + object_type +']', '');
}

function actionCommands () {
    // все делается в контексте контейнера
    var container = $(".code_frame");

    // вставка ссылки
    container.find(".url").bind("click", function () {
        $.popup.show({
            title: "Вставка ссылки",
            content: $("#inserted_link").html(),
            width: "240px"
        });
        $(".js_popup_frame").css("width", "350px", "background", "#F9F9F7");
    });

    // вставка ссылки - отмена
    $("img.inserted_link_cancel").live("click", function () {
        $(".js_popup").css("display", "none");
    });

    // вставка ссылки - подтверждение
    $("input.inserted_link_submit").live("click", function () {
        var link = $(".inserted_link_content").attr("value");
        surroundText("[url="+link + "]", "[/url]");
        $(".js_popup").css("display", "none");
    });

    // вставка ссылки пользователя
    container.find(".user").bind("click", function () {
        surroundText('[user]','[/user]');
    });

    // попап для загрузки изображения
    container.find(".photo").bind("click", function () {
        $.popup.show({
            title: 'Загрузка изображения',
            content: "<iframe src='" + comment_form.iFrameUrl +"?object_type=photo#" +
                     encodeURIComponent(document.location.href) + "' scrolling='no' style='width: 350px; height: 260px;' />",
            width: 550
        });

        $.receiveMessage(function(e) {
            surroundText('[photo]'+e.data+'[/photo]', '');
            $.popup.hide();
        }, "http://66.ru");

        $(".js_popup_frame").css("width", "auto");
    });

    // попап для загрузки видео
    container.find(".video").bind("click", function (){
        $.popup.show({
            title: 'Загрузка видео',
            content: "<iframe src='" + comment_form.iFrameUrl +"?object_type=video#" +
                     encodeURIComponent(document.location.href) + "' scrolling='no' style='width: 350px; height: 260px;' />",
            width: 550
        });

        $.receiveMessage(function(e) {
            surroundText('[video]'+e.data+'[/video]', '');
            $.popup.hide();
        }, "http://66.ru");

        $(".js_popup_frame").css("width", "auto");
    });

    // попап для загрузки аудио
    container.find(".audio").bind("click", function () {
        $.popup.show({
            title: 'Загрузка аудио',
            content: "<iframe src='" + comment_form.iFrameUrl +"?object_type=audio#" +
                     encodeURIComponent(document.location.href) + "' scrolling='no' style='width: 350px; height: 260px;' />",
            width: 550
        });

        $.receiveMessage(function(e) {
            surroundText('[audio]'+e.data+'[/audio]', '');
            $.popup.hide();
        }, "http://66.ru");

        $(".js_popup_frame").css("width", "auto");
    });

    // вставка bb-кода
    container.find("img.text").bind("click", function () {
        var textvalue = $(this).attr("textvalue");
        surroundText("[" + textvalue + "]","[/" + textvalue + "]")
    });

    // вставка подката
    container.find("img.uniq").bind("click", function (e) {
        var textvalue = $(this).attr("textvalue");
        surroundText("[---" + textvalue, "---]");
        e.stopPropagation();
    });

    // открытие окна со смайликами
    container.find(".smile").bind("click", function (e) {
        var y = $(this).offset().top;
        $("#smiles_table").css({left: 14 + "px", top: 30 + "px", display: "table"});
        e.stopPropagation();
    });

    // вставка смайлика
    container.find(".commentSmilesForm-smile").live("mousedown", function (e) {
        smileCode = $(this).attr("title");
        $("#smiles_table").css("display", "none");
        surroundText(smileCode, '');
        e.preventDefault();
    });

    // если тыкнули на документ - скрываем попап со смайликами
    $(document).bind("mousedown", function () {
        $("#smiles_table").css("display", "none");
    });
}