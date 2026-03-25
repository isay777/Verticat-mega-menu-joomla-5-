<?php
/**
 * mod_vertical_megamenu — Entry point
 * Joomla 5 compatible
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Load helper
require_once __DIR__ . '/helper.php';

$app   = Factory::getApplication();
$doc   = $app->getDocument();
$modId = 'mod-vmm-' . $module->id;

// -----------------------------------------------------------------------
// Read parameters
// -----------------------------------------------------------------------
$menuType         = $params->get('menutype', 'mainmenu');
$startLevel       = (int) $params->get('startLevel', 1);
$endLevel         = (int) $params->get('endLevel', 3);
$maxColumns       = (int) $params->get('max_columns', 3);
$menuWidth        = (int) $params->get('menu_width', 260);
$megaWidth        = (int) $params->get('mega_width', 750);
$mobileBreakpoint = (int) $params->get('mobile_breakpoint', 768);
$showIcons        = (bool) $params->get('show_icons', 1);
$showBadges       = (bool) $params->get('show_badges', 1);
$trigger          = $params->get('trigger', 'hover');

// -----------------------------------------------------------------------
// Load menu data
// -----------------------------------------------------------------------
$items = ModVerticalMegamenuHelper::getMenuItems($menuType, $startLevel, $endLevel);
$tree  = ModVerticalMegamenuHelper::buildTree($items);

if (empty($tree)) {
    return;
}

// -----------------------------------------------------------------------
// Per-instance CSS variables
// -----------------------------------------------------------------------
$doc->addStyleDeclaration(
    "#$modId{--vmm-menu-width:{$menuWidth}px;--vmm-mega-width:{$megaWidth}px;}"
);

// -----------------------------------------------------------------------
// Load CSS and JS — simple direct URL approach (no WebAssetRegistry)
// This avoids all WebAsset API compatibility issues across Joomla 4/5
// -----------------------------------------------------------------------
$baseUrl  = Uri::root(true) . '/modules/mod_vertical_megamenu';
$cssFile  = __DIR__ . '/css/megamenu.css';
$jsFile   = __DIR__ . '/js/megamenu.js';
$cssVer   = file_exists($cssFile) ? filemtime($cssFile) : '1';
$jsVer    = file_exists($jsFile)  ? filemtime($jsFile)  : '1';

$doc->addStyleSheet($baseUrl . '/css/megamenu.css?v=' . $cssVer);
$doc->addScript($baseUrl . '/js/megamenu.js?v=' . $jsVer, [], ['defer' => true]);

// -----------------------------------------------------------------------
// Render template
// -----------------------------------------------------------------------
require ModuleHelper::getLayoutPath('mod_vertical_megamenu', $params->get('layout', 'default'));
