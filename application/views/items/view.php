<?php echo $html->link('Back','items/viewall'); ?>
<h2><?php echo $todo[0]['item_name']?></h2>

<a class="big" href="../../../items/delete/<?php echo $todo[0]['id']?>">
<span class="item">
Delete this item
</span>
</a>