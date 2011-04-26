$(document).ready(function () {
try
{
    addScript("http://cdn.connect.mail.ru/js/loader.js");

    addScript("", "VK.init({apiId: " + $("#vk_init").attr("apiId") + ", onlyWidgets: true});")

    $("#apiIdvk").after($("<script>" + "VK.init({apiId: " + $('#apiIdvk').attr('apiId') + ", onlyWidgets: true})" +"</script>"));
    $("#vk_like").html(VK.Share.button(false, {type: "round", text: "Это интересно"}));
    var lj_like = $("#lj_like");
    var lj_href_value = "http://www.livejournal.com/update.bml?subject=" + encodeURIComponent(lj_like.attr("simpletitle")) +"&amp;event=" + encodeURIComponent(lj_like.attr("simpletitle")) + " <a target=_blank href=" + encodeURIComponent(lj_like.attr("outerhostname")) + "><br>ссылка на новость</a>";
    $("#lj_like").html('<a target="_blank" style="position:relative;top:-1px;" title="Создать пост в LiveJournal" alt="Создать пост в LiveJournal" href="' + lj_href_value + '"><img src="' + lj_like.attr("src") + '" /></a>');


    $(".j-facebook").html("<fb:like layout='button_count'></fb:like>");

    $("#tw_like").html("<a href='http://twitter.com/share' class='twitter-share-button' data-text='" + $("#tw_like").attr("data") + "' data-count='horizontal' data-via='66ru'>Tweet</a>");

    $("#mail_like").html(
           "<a target='_blank' class='mrc__plugin_like_button' href='http://connect.mail.ru/share' rel='{\"type\" : \"button\", \"width\" : \"100\", \"show_text\" : \"true\"}'>Рассказать</a>"

    );

   function addScript (url, text) {
       var script   = document.createElement("script");
       script.type  = "text/javascript";
       if (url)  {
        script.src   = url;
       }
       script.text  = text || "";
       document.body.appendChild(script);
   }
}
catch(e)
{}
});