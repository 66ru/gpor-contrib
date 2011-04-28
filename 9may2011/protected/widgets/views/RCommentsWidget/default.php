<?
$index = array(
    0 => array()
);

function getUser($commentArr) {
    $select = User::model();
    $criteria = $select->getDbCriteria();
    $criteria->limit = 1;
    $criteria->order = 'RANDOM()';

    return $select->find();
}

foreach($comments as &$item) {
    $parentId = isset($item['parentCommentId']) ? $item['parentCommentId'] : 0;
    $parentId = intval($parentId);// корневой это NULL, а не 0

    if(!isset($index[$parentId])) {
        $index[$parentId] = array();
    }

    $index[$parentId][$item['id']] = &$item;
    $item['user'] = $this->getUserForRComment($item);
}

$app = Yii::App();
$user = $app->user;

?>

<!--стандартный блок комментариев с новостей 66-->
<div id="comments-<?php echo $this->objectTypeCode;?>-<?php echo $this->objectId;?>" class="js_comments context">
    <div class="b-sep"></div>

    <?php if(!$user->isGuest){?>
    <div id="comment-form" style="display: none;">  <?php //скрыта, потому что сначала, пока страница не загрузилась - выглядит страшно!! ?>
    <form action="" method="post">
        <fieldset>
            <textarea id="comments_textarea" name="comment[content]" rows="10"></textarea>
        </fieldset>
    </form>
    </div>

	<script type="text/javascript">
$.fn.commentForm = function (obj) {
    var obj = obj || {};
    comment_form.build();

    comment_form.submit(obj.submit || function () {});

    comment_form.setIframeUrl(obj.urlFrame || "");

    var defaultSmiles = [];
    var smiles = defaultSmiles.concat (obj.smiles || []);
    comment_form.smiles(smiles);

    var defaultCommands = [
        // стилей текста
        {
            containerIndex: 0,
            type: "text",
            textvalue: "b",
            src: "/img/b.gif",
            title: "Полужирный"
        },
        {
            containerIndex: 0,
            type: "text",
            textvalue: "i",
            src: "/img/i.gif",
            title: "Курсив"
        },
        {
            containerIndex: 0,
            type: "text",
            textvalue: "s",
            src: "/img/s.gif",
            title: "Зачёркнутый"
        },

        // блок команд отвечающих за расположение текста
        {
            containerIndex: 1,
            type: "text",
            textvalue: "left",
            src: "/img/left.gif",
            title: "Выравнивание по левому краю"
        },
        {
            containerIndex: 1,
            type: "text",
            textvalue: "center",
            src: "/img/center.gif",
            title: "Выравнивание по центру"
        },
        {
            containerIndex: 1,
            type: "text",
            textvalue: "right",
            src: "/img/right.gif",
            title: "Выравнивание по правому краю"
        },

        // блок мультимедиа
        {
            containerIndex: 2,
            type: "popup",
            textvalue: "url",
            src: "/img/link.gif",
            title: "Вставить ссылку"
        },
        {
            containerIndex: 2,
            type: "popup",
            textvalue: "photo",
            src: "/img/pic.gif",
            title: "Вставить изображение"
        },
        {
            containerIndex: 2,
            type: "popup",
            textvalue: "video",
            src: "/img/video.gif",
            title: "Вставить видео"
        },


        // смайлики и цитата
        {
            containerIndex: 3,
            type: "text",
            textvalue: "quote",
            src: "/img/quote.gif",
            title: "Цитировать"
        },
        {
            containerIndex: 3,
            type: "popup",
            textvalue: "smile",
            src: "/img/smile.gif",
            title: "Смайлики"
        }
    ];

    var commands = defaultCommands.concat(obj.commands || []);
    comment_form.commands(commands);
}
	</script>

    <script type="text/javascript">
    //<![CDATA[
        var commentForm;
        $(document).ready(function () {
            $(this).commentForm({
                urlFrame: 'http://66.ru/new66_upload_gate.php'
            });
            commentForm = $('#comment-form');
            commentForm.find('form').attr('action', '');
            commentForm.find('.js_comment_form').css('display', 'block');
            commentForm.find('.js_comment_form').append('<input type="hidden" name="comment[parentCommentId]" value="0" />');
            commentForm.data('parentCommentId', commentForm.find('input[name="comment[parentCommentId]"]'));
            commentForm.show();

            $('.js_comment-to-comment').click(function(){
                var re = new RegExp('[0-9]+');
                var tmp = $(this).attr('id').match(re);

                if(!tmp)
                    return;

                var commentId = tmp[0];
                commentForm.data('parentCommentId').val(commentId)
                commentForm.appendTo($(this).parent().next());
                return false;
            });
        });
    //]]>
    </script>
    <?php } else { ?>
    <table class="leave-greeting">
    <tr>
        <td class="leave-greeting__link"><a id="linkOpenFormGreeting" href="#">Оставить свой комментарий</a>
        </td>
        <td class="leave-greeting__stat"></td>
    </tr>
    <tr id="expandForm" style="">
        <td colspan="2">
            <a href="http://66.ru/login?location=http://9may2011.local<?php echo $_SERVER['REQUEST_URI'];?>">Авторизуйтесь</a>, чтобы оставить свой
            комментарий
        </td>
    </tr>
    </table>
    <script type="text/javascript">
    //<![CDATA[
        $('#expandForm').hide();
        $('#linkOpenFormGreeting').click(function(){
           $('#expandForm').toggle();
           return false;
        });
    //]]>
    </script>
    <?php } ?>

    <?function printComments(&$index, &$comments, $key = 0){?>
        <? if(!isset($index[$key])) return; ?>
        <?foreach($index[$key] as $item){?>
            <div id="c<?php echo $item['id'];?>" class="js_comment  ie_layout" comremoved="0" style="display: block;">
              <div class="comment_head rc5">
               <div class="comment_head_avatar"><img alt="" src="<?php echo $item['user']->getAvatar();?>"></div>
                   <a href="<?php echo $item['user']->getProfileLink();?>" class="js_user js_user-1 js_user-f-off "><?php echo $item['user']->getUsername();?></a>
                   <i class="comment_head_date"><?php echo DateHelper::formatRusDate($item['createTime']);?></i>
                   <?/*<a href="default.php#" title="Игнорировать сообщения этого пользовтаеля" class="buttons_report_small comment_head_icon">Игнорировать сообщения этого пользователя</a>
                   <form method="post" class="inline-block comment_head_icon js_comment_remove" action="/comments/commentAjax/deleteComment/">
                         <input type="hidden" value="123731" name="commentId" class="forms_hidden">
                         <input type="submit" value="Удалить" title="Удалить комментарий" name="delete" class="forms_submit  buttons_remove_small comment_head_buttons_remove_small">
                   </form>*/?>
                   <a title="Ссылка на комментарий" href="#c<?php echo $item['id'];?>" class="buttons_anchor_small comment_head_icon">Ссылка</a>
                   <?/*<a style="display: none;" class="show_bad_comment" href="default.php#"><i></i>показать комментарий</a>*/?>
                   <?/*<a href="default.php#" class="comment_head_next-new">следующий новый<span class="comment_head_next-new-pic"></span></a>*/?>
               </div>
               <div class="comment_content content"><?php echo $item['contentParsed'];?></div>
               <div class="comment_foot context">
                   <a href="#c<?php echo $item['id'];?>" class="comment_foot_answer js_comment-to-comment" id="c<?php echo $item['id'];?>">ответить</a>
                   <div class="hr comment_foot_hr"><hr></div>
               </div>
                <div class="comment-form"></div>
                <div class="js_comment-sublevel_wrap" id="c<?php echo $item['id'];?>s">
                    <?php printComments($index, $comments, $item['id']); ?>
                </div>
            </div>
        <?}?>
    <?}?>

    <?php printComments($index, $comments); ?>

    <? /* <div style="display: block;" class="all-comments-wrap"><span class="all-comments">Все комментарии</span> (27)</div> */?>



</div>
