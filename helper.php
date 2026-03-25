<?php
/**
 * Helper class for mod_vertical_megamenu
 * Joomla 5 — Direct DB query
 *
 * Параметры колонки задаются через поле "CSS Class" пункта меню
 * (вкладка "Link Type" → "Link CSS Style"):
 *   vmm-col-1   → колонка 1
 *   vmm-col-2   → колонка 2
 *   vmm-col-3   → колонка 3
 *   vmm-badge-NEW   → бейдж с текстом NEW
 *
 * Либо через поле Note (если оно доступно): column=2|badge=NEW
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;

class ModVerticalMegamenuHelper
{
    public static function getMenuItems(string $menuType, int $startLevel, int $endLevel): array
    {
        $db          = Factory::getDbo();
        $app         = Factory::getApplication();
        $lang        = $app->getLanguage()->getTag();
        $isMultilang = Multilanguage::isEnabled();

        $query = $db->getQuery(true);
        $query->select([
            $db->quoteName('m.id'),
            $db->quoteName('m.menutype'),
            $db->quoteName('m.title'),
            $db->quoteName('m.alias'),
            $db->quoteName('m.note'),
            $db->quoteName('m.path'),
            $db->quoteName('m.link'),
            $db->quoteName('m.type'),
            $db->quoteName('m.level'),
            $db->quoteName('m.language'),
            $db->quoteName('m.browserNav'),
            $db->quoteName('m.access'),
            $db->quoteName('m.params'),
            $db->quoteName('m.home'),
            $db->quoteName('m.img'),
            $db->quoteName('m.parent_id'),
        ])
        ->from($db->quoteName('#__menu', 'm'))
        ->where($db->quoteName('m.menutype')   . ' = ' . $db->quote($menuType))
        ->where($db->quoteName('m.published')  . ' = 1')
        ->where($db->quoteName('m.parent_id')  . ' > 0')
        ->where($db->quoteName('m.client_id')  . ' = 0')
        ->order($db->quoteName('m.lft') . ' ASC');

        if ($startLevel > 0) {
            $query->where($db->quoteName('m.level') . ' >= ' . (int) $startLevel);
        }
        if ($endLevel > 0) {
            $query->where($db->quoteName('m.level') . ' <= ' . (int) $endLevel);
        }

        if ($isMultilang) {
            $query->where(
                '(' .
                $db->quoteName('m.language') . ' = ' . $db->quote($lang) .
                ' OR ' .
                $db->quoteName('m.language') . ' = ' . $db->quote('*') .
                ')'
            );
        }

        $db->setQuery($query);
        $rawItems = $db->loadObjectList('id');

        if (empty($rawItems)) {
            return [];
        }

        // Active item (frontend only)
        $activeItem = null;
        $activePath = [];

        if ($app->isClient('site')) {
            $menu       = $app->getMenu();
            $activeItem = $menu->getActive();
            $activePath = $activeItem ? array_map('intval', $activeItem->tree) : [];
        }

        $user       = $app->getIdentity();
        $userLevels = $user ? $user->getAuthorisedViewLevels() : [1];

        $result = [];

        foreach ($rawItems as $item) {
            if ($item->access && !in_array((int) $item->access, $userLevels)) {
                continue;
            }

            // Parse params JSON
            $paramsObj = null;
            if (!empty($item->params)) {
                $decoded   = json_decode($item->params);
                $paramsObj = is_object($decoded) ? $decoded : null;
            }
            $item->paramsObj = $paramsObj;

            $item->isActive  = in_array((int) $item->id, $activePath);
            $item->isCurrent = ($activeItem && (int) $item->id === (int) $activeItem->id);

            // VMM params — читаем из CSS-класса, потом из Note
            $item->vmm_column = self::getColumnFromParams($item);
            $item->vmm_badge  = self::getBadgeFromParams($item);
            $item->vmm_icon   = self::getItemParam($item, 'icon',  '');
            $item->vmm_image  = self::getItemParam($item, 'image', '');

            $item->flink = self::buildLink($item);

            $result[$item->id] = $item;
        }

        return array_values($result);
    }

    /**
     * Читает номер колонки.
     *
     * Способ 1 (рекомендуемый): CSS Class пункта меню
     *   Вкладка "Link Type" → поле "Link CSS Style"
     *   Введите: vmm-col-2
     *   (vmm-col-1, vmm-col-2, vmm-col-3, vmm-col-4)
     *
     * Способ 2 (fallback): поле Note
     *   column=2
     */
    private static function getColumnFromParams(object $item): int
    {
        // Способ 1: ищем vmm-col-N в link_css_classes из params JSON
        if (!empty($item->paramsObj)) {
            $cssClass = $item->paramsObj->link_css_classes ?? '';
            if (!empty($cssClass) && preg_match('/\bvmm-col-(\d+)\b/', $cssClass, $m)) {
                return (int) $m[1];
            }
        }

        // Способ 2: поле Note: column=2
        if (!empty($item->note)) {
            foreach (explode('|', $item->note) as $pair) {
                $parts = explode('=', trim($pair), 2);
                if (count($parts) === 2 && trim($parts[0]) === 'column') {
                    return (int) trim($parts[1]);
                }
            }
        }

        return 0;
    }

    /**
     * Читает текст бейджа.
     *
     * Способ 1: CSS Class пункта меню
     *   vmm-badge-NEW   → бейдж "NEW"
     *   vmm-badge-SALE  → бейдж "SALE"
     *   vmm-badge-ХИТ   → бейдж "ХИТ"
     *
     * Способ 2: поле Note
     *   badge=NEW
     */
    private static function getBadgeFromParams(object $item): string
    {
        // Способ 1: vmm-badge-ТЕКСТ в link_css_classes
        if (!empty($item->paramsObj)) {
            $cssClass = $item->paramsObj->link_css_classes ?? '';
            if (!empty($cssClass) && preg_match('/\bvmm-badge-([A-Za-zА-Яа-яЁё0-9]+)\b/u', $cssClass, $m)) {
                return $m[1];
            }
        }

        // Способ 2: Note: badge=NEW
        if (!empty($item->note)) {
            foreach (explode('|', $item->note) as $pair) {
                $parts = explode('=', trim($pair), 2);
                if (count($parts) === 2 && trim($parts[0]) === 'badge') {
                    return trim($parts[1]);
                }
            }
        }

        return '';
    }

    /**
     * Универсальный читатель параметра из Note поля
     */
    private static function getItemParam(object $item, string $key, $default = '')
    {
        if (!empty($item->paramsObj)) {
            $val = $item->paramsObj->{'vmm_' . $key} ?? null;
            if ($val !== null && $val !== '') {
                return $val;
            }
        }

        if (!empty($item->note)) {
            foreach (explode('|', $item->note) as $pair) {
                $parts = explode('=', trim($pair), 2);
                if (count($parts) === 2 && trim($parts[0]) === $key) {
                    return trim($parts[1]);
                }
            }
        }

        return $default;
    }

    private static function buildLink(object $item): string
    {
        if (in_array($item->type, ['separator', 'heading'], true)) {
            return '#';
        }
        if ($item->type === 'url') {
            return $item->link ?: '#';
        }
        if (!empty($item->link)) {
            try {
                return Route::_($item->link . '&Itemid=' . $item->id, false);
            } catch (\Exception $e) {
                return $item->link;
            }
        }
        return '#';
    }

    /**
     * Строит дерево из плоского списка.
     * Корневые = те, чей parent_id НЕ входит в загруженный набор.
     */
    public static function buildTree(array $items): array
    {
        $tree = [];
        $byId = [];

        foreach ($items as $item) {
            $item->children  = [];
            $byId[$item->id] = $item;
        }

        foreach ($items as $item) {
            if (isset($byId[$item->parent_id])) {
                $byId[$item->parent_id]->children[] = $item;
            } else {
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * Распределяет дочерние пункты по колонкам.
     */
    public static function distributeColumns(array $children, int $maxColumns): array
    {
        $columns = [];
        for ($c = 1; $c <= $maxColumns; $c++) {
            $columns[$c] = [];
        }

        $hasColumnDef = false;
        foreach ($children as $child) {
            if ((int) $child->vmm_column > 0) {
                $hasColumnDef = true;
                break;
            }
        }

        if ($hasColumnDef) {
            foreach ($children as $child) {
                $col = (int) $child->vmm_column;
                if ($col < 1 || $col > $maxColumns) {
                    $col = 1;
                }
                $columns[$col][] = $child;
            }
        } else {
            $total    = count($children);
            $colCount = max(1, min($maxColumns, $total));
            $base     = (int) floor($total / $colCount);
            $extra    = $total % $colCount;
            $idx      = 0;

            for ($c = 1; $c <= $colCount; $c++) {
                $size = $base + ($c <= $extra ? 1 : 0);
                for ($i = 0; $i < $size; $i++) {
                    if (isset($children[$idx])) {
                        $columns[$c][] = $children[$idx];
                        $idx++;
                    }
                }
            }
        }

        return array_values(array_filter($columns, static fn($col) => !empty($col)));
    }

    public static function getItemClasses(object $item): string
    {
        $classes = ['vmm-item'];

        if (!empty($item->children))   { $classes[] = 'has-mega'; }
        if (!empty($item->isActive))   { $classes[] = 'active'; }
        if (!empty($item->isCurrent))  { $classes[] = 'current'; }
        if (!empty($item->vmm_badge))  { $classes[] = 'has-badge'; }
        if (!empty($item->vmm_icon))   { $classes[] = 'has-icon'; }

        if (in_array($item->type, ['separator', 'heading'], true)) {
            $classes[] = 'separator';
        }

        return implode(' ', $classes);
    }
}
