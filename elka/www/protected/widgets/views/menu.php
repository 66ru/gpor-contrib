<ul>
<?
foreach ($items as $item)
{
    $opts = array();
    $liClass = $item['active'] ? 'active- gradient1-revert' : '';
    echo '<li class="'.$liClass.'">';
    echo CHtml::link($item['caption'], $item['link'], $opts);
    echo '</li>';
}
?>
</ul>
