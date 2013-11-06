<?php defined('SYSPATH') or die('No direct script access.');
?>
<ul class="nav nav-list">

	<?php foreach ($menu->get_visible_items() as $item):?>
	<?php // Is this a dropdown-menu with sibling links?
	 if ($item->has_siblings()):?>

		<li>
			<a href="#"
			   title="<?php echo $item->tooltip?>"
			   class="dropdown-toggle"><?php echo str_ireplace('</i>', '</i><span class="menu-text">', $item->title ) . '</span>'?>
			   <b class="arrow icon-angle-down"></b>
			</a>
			<ul class="submenu">
				<?php foreach ($item->siblings as $subitem): ?>
				<li>
					<?php echo (string) $subitem?>
				</li>
				<?php endforeach?>
			</ul>
		</li>

		<?php else:
		// No, this is a "normal", single-level menu
		?>
		<li>
			<?php echo str_ireplace(array('</i>', '</a>'), array('</i><span class="menu-text">', '</span></a>'), (string) $item) ?>
		</li>

		<?php endif ?>

	<?php endforeach?>
</ul>