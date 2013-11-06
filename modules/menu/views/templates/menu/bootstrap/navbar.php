<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Navigation item template for Twitter Bootstrap main navbar.
 * Render the output inside div.navbar>div.navbar-inner>.container
 *
 * @link http://twitter.github.com/bootstrap/components.html#navbar
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @author Ando Roots <ando@sqroot.eu>
 * @since 2.0
 * @package Kohana/Menu
 * @copyright (c) 2012, Ando Roots
 */
?>
<ul class="nav">

	<?php foreach ($menu->get_visible_items() as $item):

	// Is this a dropdown-menu with sibling links?
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