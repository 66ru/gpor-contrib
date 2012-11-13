<link href="/css/b-forms.css" rel="stylesheet"  type="text/css" />
<div class="b-container">
    <div class="b-separator b-separator_size_5"></div>
    <h2 class="b-header b-header_size_h2 b-header_margin_left-10">Список деда мороза</h2>
    <table class="table-list-present">
        <?php
        if ($data) {
            foreach ($statuses as $status) {
                echo '<tr><td><a name="'.$status.'"</a></td></tr>';
                foreach ($data[$status] as $item) {
                    echo '<tr>';

                    echo '<td>';
                    echo '<div class="table-list-present__name">'.$item['name'].'</div>';
                    echo '<div class="table-list-present__years">'.$item['age'].' '.StringUtils::pluralEnd($item['age'], array('год', 'года', 'лет')).'</div>';
                    if ($this->isAdmin()) {
                        echo '<div style="margin: 5px 0;"><span class="santaName">'.$item['santaName'].'</span> <span class="santaLink">'.$item['santaLink'].'</span> <a href="#" onclick="showElkaWishForm('.$item['id'].', '.$item['status'].', this); return false;">изменить</a></div>';
                    }
                    echo '</td>';

                    echo '<td>';
                    echo '<div class="table-list-present__desire">'.$item['wish'].'</div>';
                    echo '</td>';

                    echo '<td>';
                    if ($item['status'] == 0) {
                        echo '<div class="table-list-present__status">
                        <div class="b-btn b-btn_color_orange">
                            <ins class="b-btn__crn-left"></ins>
                            <ins class="b-btn__crn-right"></ins>
                            <a class="b-btn__link" href="'.CHtml::normalizeUrl(array('/site/join', 'giftTo' => $item['id'])).'#form"></a>
                            <span class="b-btn__text">Хочу подарить</span>
                        </div>
                    </div>';
                    }
                    elseif ($item['status'] == 10) {
                        echo '<div class="table-list-present__status">Подарок в пути</div>';
                    }
                    elseif ($item['status'] == 20) {
                        echo '<div class="table-list-present__status table-list-present__status-ready">Подарок в офисе</div>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            }
        }
        ?>
    </table>
</div>
<?php
if ($this->isAdmin()) {
    ?>
<div style="display: <?php echo $showForm ? 'block' : 'none'; ?>; position: fixed; top: 50px; left: 200px; width: 600px; padding: 20px; background-color: #F1F5E8;" id="elkaWishForm">
    <div style="margin-bottom: 10px"><a href="#" onclick="hideElkaWishForm(); return false;">закрыть</a></div>
    <div class="name" style="font-weight: bold;"></div>
    <?php echo $cForm->render(); ?>
</div>
<script type="text/javascript">
        var elkaWishForm = $("#elkaWishForm");
        function showElkaWishForm(id, status, el) {
            var div = $(el).closest("div");
            var santaName = div.find(".santaName").html();
            var santaLink = div.find(".santaLink").html();
            var name = div.closest("tr").find(".table-list-present__name").html();
            elkaWishForm.find('#ElkaWishForm_id').val(id);
            elkaWishForm.find(".name").html(name);
            elkaWishForm.find('#ElkaWishForm_santaName').val(santaName);
            elkaWishForm.find('#ElkaWishForm_santaLink').val(santaLink);
            elkaWishForm.find('#ElkaWishForm_status').val(status);
            elkaWishForm.show();
        }
        function hideElkaWishForm() {
            elkaWishForm.hide();
        }
</script>
    <?php
}
?>
