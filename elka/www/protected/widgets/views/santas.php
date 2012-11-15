<div class="b-container b-container_color_grey">
    <div class="b-container__inner">
        <div class="b-separator b-separator_size_10"></div>
        <h2 class="b-header b-header_size_h2">Выражаем благодарность</h2>
											
        <div class="elka2013Users">
            <?php
            if ($items) {
                foreach ($items as $item) {
                    if ($item['santaLink']) {
                        echo '<a class="b-link-user" target="_blank" href="'.$item['santaLink'].'">'.$item['santaName'].'</a>&nbsp; &nbsp; ';
                    }
                    else {
                        echo '<span>'.$item['santaName'].'</span>&nbsp; &nbsp; ';
                    }
                }
            }
            ?>
        </div>
											
        <div class="b-separator b-separator_size_20"></div>
    </div>
</div>
