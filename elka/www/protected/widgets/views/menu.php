<ul>
<?php
$i = 1;
foreach ($items as $item)
{
    if ($item['active']) {
        echo '<span class="elka13Menu-el__cur">'.$item['caption'].'</span>';
    }
    else {
        $opts = array();
        echo CHtml::link($item['caption'], $item['link'], $opts);
    }

    if ($i < count($items)) {
        echo '&nbsp;|&nbsp;';
    }
    $i++;
}
?>
</ul>
