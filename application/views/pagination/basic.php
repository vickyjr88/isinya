<div class="pagination">
<!-- 
	<?php if ($first_page !== FALSE): ?>
		<a href="<?php echo $page->url($first_page) ?>"><?php echo __('First') ?></a>
	<?php else: ?>
		<a><?php echo __('First') ?></a>
	<?php endif ?> -->
    <ul>
        <?php if ($previous_page !== FALSE): ?>
            <li><a href="<?php echo $page->url($previous_page) ?>">&laquo; <?php //echo __('Previous') ?></a></li>
        <?php else: ?>
            <li class="disabled"><a>&laquo; <?php //echo __('Previous') ?></a></li>
        <?php endif ?>
    
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    
            <?php if ($i == $current_page): ?>
                <li class="active"><a><strong><?php echo $i ?></strong></a></li>
            <?php else: ?>
                        <?php if(abs($current_page - $i) <= 4): // I want to print 4 link after/before current link ?>
                <li><a href="<?php echo $page->url($i) ?>"><?php echo $i ?></a></li>
                        <?php endif ?>
            <?php endif ?>
    
        <?php endfor ?>
    
        <?php if ($next_page !== FALSE): ?>
           <li><a href="<?php echo $page->url($next_page) ?>"><?php //echo __('Next') ?> &raquo;</a></li>
        <?php else: ?>
            <li class="disabled"><a><?php //echo __('Next ') ?> &raquo;</a></li>
        <?php endif ?>   
    </ul>
	

	<!-- <?php if ($last_page !== FALSE): ?>
		<a href="<?php echo $page->url($last_page) ?>"><?php echo __('Last') ?></a>
	<?php else: ?>
		<a><?php echo __('Last') ?></a>
	<?php endif ?> -->

</div><!-- .pagination -->