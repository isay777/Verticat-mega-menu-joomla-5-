<?php
/**
 * Default layout — mod_vertical_megamenu
 * Joomla 5
 */

defined('_JEXEC') or die;

if (empty($tree)) {
    return;
}

function vmmEsc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<nav
    id="<?php echo vmmEsc($modId); ?>"
    class="vertical-megamenu"
    role="navigation"
    aria-label="<?php echo vmmEsc($module->title ?: 'Catalog'); ?>"
    <?php echo ($trigger === 'click') ? 'data-trigger="click"' : ''; ?>
>
    <ul class="vmm-list" role="menubar" aria-orientation="vertical">

    <?php foreach ($tree as $item) :
        $hasChildren = !empty($item->children);
        $cls         = ModVerticalMegamenuHelper::getItemClasses($item);
        $href        = vmmEsc($item->flink ?? '#');
        $panelId     = 'vmm-panel-' . (int)$module->id . '-' . (int)$item->id;
        $columns     = $hasChildren
                       ? ModVerticalMegamenuHelper::distributeColumns($item->children, $maxColumns)
                       : [];
    ?>
        <li
            class="<?php echo $cls; ?>"
            role="menuitem"
            <?php if ($hasChildren) : ?>
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="<?php echo $panelId; ?>"
            <?php endif; ?>
        >
            <a
                href="<?php echo $href; ?>"
                class="vmm-link<?php echo !empty($item->isCurrent) ? ' current' : ''; ?>"
                <?php if ((int)($item->browserNav ?? 0) === 1) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
            >
                <?php if ($showIcons && !empty($item->vmm_icon)) : ?>
                    <span class="vmm-icon" aria-hidden="true"><?php echo $item->vmm_icon; ?></span>
                <?php elseif (!empty($item->vmm_image)) : ?>
                    <img class="vmm-img" src="<?php echo vmmEsc($item->vmm_image); ?>" alt="" aria-hidden="true" loading="lazy">
                <?php endif; ?>

                <span class="vmm-title"><?php echo vmmEsc($item->title); ?></span>

                <?php if ($showBadges && !empty($item->vmm_badge)) : ?>
                    <span class="vmm-badge"><?php echo vmmEsc($item->vmm_badge); ?></span>
                <?php endif; ?>

                <?php if ($hasChildren) : ?>
                    <span class="vmm-arrow" aria-hidden="true">
                        <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </a>

            <?php if ($hasChildren && !empty($columns)) : ?>
            <div
                id="<?php echo $panelId; ?>"
                class="vmm-mega-panel"
                role="region"
                aria-label="<?php echo vmmEsc($item->title); ?> submenu"
            >
                <div class="vmm-mega-inner" data-cols="<?php echo count($columns); ?>">

                <?php foreach ($columns as $colItems) : ?>
                    <div class="vmm-mega-column">
                        <ul class="vmm-sub-list" role="menu">

                        <?php foreach ($colItems as $child) :
                            $chref   = vmmEsc($child->flink ?? '#');
                            $hasGc   = !empty($child->children);
                            $chcls   = 'vmm-sub-item';
                            if ($hasGc)                    $chcls .= ' has-l3';
                            if (!empty($child->isActive))  $chcls .= ' active';
                            if (!empty($child->isCurrent)) $chcls .= ' current';
                        ?>
                            <li class="<?php echo $chcls; ?>" role="menuitem">
                                <a
                                    href="<?php echo $chref; ?>"
                                    class="vmm-sub-link"
                                    <?php if ((int)($child->browserNav ?? 0) === 1) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                                >
                                    <?php if ($showIcons && !empty($child->vmm_icon)) : ?>
                                        <span class="vmm-icon" aria-hidden="true"><?php echo $child->vmm_icon; ?></span>
                                    <?php endif; ?>
                                    <span class="vmm-sub-title"><?php echo vmmEsc($child->title); ?></span>
                                    <?php if ($showBadges && !empty($child->vmm_badge)) : ?>
                                        <span class="vmm-badge vmm-badge--sm"><?php echo vmmEsc($child->vmm_badge); ?></span>
                                    <?php endif; ?>
                                </a>

                                <?php if ($hasGc) : ?>
                                <ul class="vmm-level3" role="menu">
                                    <?php foreach ($child->children as $gc) :
                                        $gcref = vmmEsc($gc->flink ?? '#');
                                        $gccls = 'vmm-level3-item';
                                        if (!empty($gc->isCurrent)) $gccls .= ' current';
                                    ?>
                                        <li class="<?php echo $gccls; ?>" role="menuitem">
                                            <a href="<?php echo $gcref; ?>" class="vmm-level3-link"
                                               <?php if ((int)($gc->browserNav ?? 0) === 1) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                                            >
                                                <?php echo vmmEsc($gc->title); ?>
                                                <?php if ($showBadges && !empty($gc->vmm_badge)) : ?>
                                                    <span class="vmm-badge vmm-badge--xs"><?php echo vmmEsc($gc->vmm_badge); ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>

                            </li>
                        <?php endforeach; ?>

                        </ul>
                    </div>
                <?php endforeach; ?>

                </div>
            </div>
            <?php endif; ?>

        </li>
    <?php endforeach; ?>

    </ul>
</nav>
