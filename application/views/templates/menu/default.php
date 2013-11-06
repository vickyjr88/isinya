<?php defined('SYSPATH') or die('No direct script access.');
$parsed_sections = array();
?>
<ul class="nav nav-list">

	<?php foreach ($menu->get_visible_items() as $item):?>
	<?php if ($item->section && !in_array($item->section, $parsed_sections)): ?>
		<li class="nav-header"><?php echo $item->section ?></li>
		<?php $parsed_sections[] = $item->section; ?> 
	<?php endif; ?>
	<?php // Is this a dropdown-menu with sibling links?
	 if ($item->has_siblings()):?>

		<li class="dropdown  <?php echo $item->get_classes()?>" title="<?php echo $item->tooltip?>">
			<a href="#"
			   class="dropdown-toggle"
			   data-toggle="dropdown"><?php echo $item->title?><b class="caret"></b>
			</a>
			<ul class="dropdown-menu">
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
		<li class="<?php echo $item->get_classes()?>">
			<?php echo (string) $item?>
		</li>

		<?php endif ?>

	<?php endforeach?>
</ul>