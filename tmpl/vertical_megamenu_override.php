<?php
/**
 * Template override for mod_menu — vertical_megamenu layout
 * Place this file at: /templates/YOUR_TEMPLATE/html/mod_menu/vertical_megamenu.php
 *
 * This acts as a fallback that renders a simplified vertical menu
 * compatible with the mod_vertical_megamenu CSS classes.
 *
 * @package    Joomla.Site
 * @subpackage mod_menu
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
?>
<nav class="vertical-megamenu" role="navigation" aria-label="<?php echo htmlspecialchars($module->title); ?>">
    <ul class="vmm-list" role="menubar" aria-orientation="vertical">
        <?php foreach ($list as $item) : ?>
            <?php
            $active = $item->id == $default ? ' active' : '';
            $level  = $item->level - $startLevel;
            if ($level < 0 || $level > 2) continue;
            ?>
            <li class="vmm-item level-<?php echo (int)$level; ?><?php echo $active; ?>" role="menuitem">
                <a
                    href="<?php echo htmlspecialchars($item->flink, ENT_COMPAT, 'UTF-8'); ?>"
                    class="vmm-link<?php echo $active; ?>"
                    <?php if (!empty($item->browserNav)) : ?>
                        target="_blank"
                        rel="noopener noreferrer"
                    <?php endif; ?>
                >
                    <span class="vmm-title"><?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
