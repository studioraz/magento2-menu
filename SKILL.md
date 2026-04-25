---
name: hyva-snowdog-menu-layouts
description: Understand Snowdog_Menu architecture and implement or override Hyvä menu templates using only PHTML, Tailwind CSS, and Alpine.js. Use this skill when creating custom Hyvä menus, overriding Snowdog menu templates, or wiring a new menu identifier to a dedicated template set.
---

# Understand and Implement Snowdog Menu Layouts in Hyvä

## Overview

This skill teaches the foundational architecture of the `Snowdog_Menu` Magento 2 module and the correct workflow for overriding or creating frontend menu templates for Hyvä themes.

Use this skill when you need to:

- understand how `Snowdog\Menu\Block\Menu` loads and renders menu trees
- override the default Snowdog menu template in a Hyvä theme
- create a brand-new menu layout for a specific menu identifier
- build desktop, mobile, footer, dropdown, mega menu, or drawer patterns with Hyvä

This skill is strictly limited to the Hyvä frontend stack:

- **Tailwind CSS** for styling
- **Alpine.js** for interactivity
- **PHTML templates** for markup and rendering logic

Do **not** use Luma-era frontend technologies such as LESS, RequireJS, jQuery, Knockout, or Magento UI components.

## Context and Architecture

### Snowdog Menu rendering flow

The central frontend block is:

- `Snowdog\Menu\Block\Menu`

Its responsibility is to:

- load a menu by identifier via the menu repository
- fetch menu nodes via the node repository
- group nodes by `level` and `parent_id`
- expose helper methods for template rendering
- resolve menu-specific templates through `Snowdog\Menu\Model\TemplateResolver`

Relevant methods in `Snowdog\Menu\Block\Menu`:

- `getMenu()`
- `getNodes($level = 0, $parent = null)`
- `getNodesTree($level = 0, $parent = null)`
- `renderMenuNode($node)`
- `renderSubmenu($nodes, $parentNode, $level = 0)`

### Node hierarchy

Snowdog Menu stores menu items as a tree.

Each node belongs to a hierarchy defined by:

- **level** — depth in the tree
- **parent_id** — the parent node reference
- **is_parent** — whether child nodes exist

In practice:

- top-level items are fetched with `$block->getNodes()`
- child items are fetched with `$block->getNodes($childLevel, $node)`
- recursive rendering is usually done by passing child nodes into `renderSubmenu()`

This means Hyvä templates should think in terms of:

- root menu container
- level-based rendering
- parent/child relationships
- recursive submenu composition

### Node types

Snowdog Menu supports multiple node types. Common examples include:

- `category`
- `category_child`
- `product`
- `cms_page`
- `cms_block`
- `custom_url`
- `wrapper`

Each node type has its own rendering block and usually its own `.phtml` template.

Typical template locations:

- `view/frontend/templates/menu/node_type/category.phtml`
- `view/frontend/templates/menu/node_type/product.phtml`
- `view/frontend/templates/menu/node_type/cms_page.phtml`
- `view/frontend/templates/menu/node_type/custom_url.phtml`

In Hyvä, the same pattern applies, but the markup must remain Tailwind- and Alpine-based.

### Template resolution model

Snowdog does not rely on a single hardcoded template only.

`Snowdog\Menu\Model\TemplateResolver` maps a menu identifier to a menu-specific template path. For example, if the layout XML passes:

```xml
<argument name="menu" xsi:type="string">hyva-topmenu-desktop</argument>
```

then the block can resolve templates from a menu-specific directory such as:

```text
Snowdog_Menu::hyva-topmenu-desktop/menu.phtml
Snowdog_Menu::hyva-topmenu-desktop/menu/sub_menu.phtml
Snowdog_Menu::hyva-topmenu-desktop/menu/node_type/category.phtml
```

Inside a Hyvä theme, that maps to:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-topmenu-desktop/menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-topmenu-desktop/menu/sub_menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-topmenu-desktop/menu/node_type/category.phtml
```

This is the key mechanism for creating isolated menu layouts per menu identifier.

## Rules and Constraints

### Required stack

Use only:

- **PHTML**
- **Tailwind CSS**
- **Alpine.js**

### Forbidden stack

Never introduce or reference:

- LESS
- RequireJS
- jQuery
- Knockout.js
- Magento Luma UI components
- `data-mage-init`
- AMD modules for menu behavior

### Hyvä implementation rules

When building templates:

- keep interaction logic inside the `.phtml` template with Alpine directives
- keep styling in Tailwind utility classes
- prefer semantic HTML and accessible navigation markup
- use `x-data`, `x-show`, `x-ref`, `x-collapse`, `@click`, `@keydown.escape`, and `x-transition` where needed
- escape output correctly with Magento escaper methods
- preserve Snowdog's node tree logic instead of flattening menu data

## Workflow

### Workflow 1: Override the default Snowdog template in a Hyvä theme

Use this when you want to replace the module's base menu template globally.

#### Step 1: Create the theme override path

Add the file here:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu.phtml
```

This overrides the module default:

```text
Snowdog_Menu::menu.phtml
```

#### Step 2: If needed, override submenu and node type templates too

Common override targets:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/sub_menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/node_type/category.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/node_type/product.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/node_type/cms_page.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/node_type/custom_url.phtml
```

#### Step 3: Build the UI with Tailwind and Alpine only

Inside the override:

- use a root Alpine component for open/closed state
- render root nodes with `$block->getNodes()`
- render nested nodes through `$block->renderSubmenu()`
- keep classes in Tailwind utilities

#### Step 4: Keep menu rendering compatible with Snowdog internals

Do not rewrite the storage model.

Instead:

- respect node levels
- preserve parent/child recursion
- use `renderMenuNode()` for node-type-specific rendering
- use `renderSubmenu()` for child collections

### Workflow 2: Create a new template set for a specific menu identifier

Use this when a new menu needs its own isolated layout, such as:

- mega menu
- off-canvas mobile menu
- footer accordion menu
- account navigation drawer
- editorial landing-page menu

#### Step 1: Choose a unique menu identifier

Example:

```text
hyva-custom-mega-menu
```

This identifier is passed to the block with layout XML and becomes the template folder name.

#### Step 2: Add or update layout XML

Create or update the appropriate layout handle in your Hyvä theme or custom module.

Example:

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="header.container">
            <block
                name="custom.hyva.snowdog.menu"
                class="Snowdog\Menu\Block\Menu"
                after="-">
                <arguments>
                    <argument name="menu" xsi:type="string">hyva-custom-mega-menu</argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

#### Step 3: Create the menu-specific template files

Add:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-mega-menu/menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-mega-menu/menu/sub_menu.phtml
```

Optional node type overrides:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-mega-menu/menu/node_type/category.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-mega-menu/menu/node_type/custom_url.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-mega-menu/menu/node_type/product.phtml
```

Optional custom submenu variants:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-mega-menu/menu/custom/sub_menu/three-column.phtml
```

#### Step 4: Render the tree according to the node hierarchy

In `menu.phtml`:

- fetch root nodes with `$block->getNodes()`
- detect children with `$block->getNodes($childrenLevel, $node)`
- mark parents with `$node->setIsParent((bool) $children)`
- output the node via `$block->renderMenuNode($node)`
- output descendants via `$block->renderSubmenu($children, $node, $childrenLevel)`

#### Step 5: Add Alpine behavior for the layout pattern

Examples:

- desktop dropdown: `openSubmenuId`
- mobile drawer: `open`
- accordion sections: per-node booleans
- nested desktop mega menu: `openLevel2SubmenuId`

#### Step 6: Style with Tailwind utilities only

Examples:

- layout: `flex`, `grid`, `container`, `gap-*`, `w-*`
- visibility: `hidden`, `block`, `lg:block`, `lg:hidden`
- overlays and drawers: `fixed`, `absolute`, `top-full`, `inset-0`, `z-*`
- panel styling: `bg-white`, `shadow-*`, `rounded-*`, `border-*`

## Assets and Scripts

### Foundational PHTML example

Use this as a generic starting point for a multi-level Hyvä Snowdog menu template.

```php
<?php
declare(strict_types=1);

use Magento\Framework\Escaper;
use Snowdog\Menu\Block\Menu;

/** @var Menu $block */
/** @var Escaper $escaper */

$menu = $block->getMenu();
$uniqueId = '_' . uniqid();
?>

<?php if ($menu): ?>
    <script>
        'use strict';

        const initSnowdogMenu<?= $escaper->escapeHtml($uniqueId) ?> = () => ({
            openSubmenuId: null,
            toggleSubmenu(id) {
                this.openSubmenuId = this.openSubmenuId === id ? null : id
            }
        })
    </script>

    <nav
        x-data="initSnowdogMenu<?= $escaper->escapeHtml($uniqueId) ?>()"
        class="relative"
        aria-label="<?= $escaper->escapeHtmlAttr(__('Main menu')) ?>"
    >
        <ul class="flex flex-col gap-2 lg:flex-row lg:items-center lg:gap-6">
            <?php foreach ($block->getNodes() as $node): ?>
                <?php
                $childrenLevel = $node->getLevel() + 1;
                $children = $block->getNodes($childrenLevel, $node);
                $node->setIsParent((bool) $children);
                $nodeId = (string) $node->getId();
                ?>
                <li class="relative">
                    <div class="flex items-center gap-2">
                        <div class="group">
                            <?= /* @noEscape */ $block->renderMenuNode($node) ?>
                        </div>

                        <?php if ($children): ?>
                            <button
                                type="button"
                                class="inline-flex items-center rounded p-2 text-sm"
                                :aria-expanded="openSubmenuId === '<?= /* @noEscape */ $nodeId ?>' ? 'true' : 'false'"
                                @click="toggleSubmenu('<?= /* @noEscape */ $nodeId ?>')"
                            >
                                <span class="sr-only">
                                    <?= $escaper->escapeHtml(__('Toggle submenu')) ?>
                                </span>
                                <span aria-hidden="true">+</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($children): ?>
                        <div
                            x-show="openSubmenuId === '<?= /* @noEscape */ $nodeId ?>'"
                            x-transition
                            x-cloak
                            class="mt-2 rounded-lg border border-gray-200 bg-white p-4 shadow-lg lg:absolute lg:left-0 lg:top-full lg:min-w-64"
                        >
                            <?= /* @noEscape */ $block->renderSubmenu($children, $node, $childrenLevel) ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
```

### Foundational submenu example

Use this as a generic recursive submenu renderer.

```php
<?php
declare(strict_types=1);

use Magento\Framework\Escaper;
use Snowdog\Menu\Block\Menu;

/** @var Menu $block */
/** @var Escaper $escaper */
?>

<?php if ($block->getMenu()): ?>
    <?php
    $parentNode = $block->getParentNode();
    $title = $parentNode ? $parentNode->getTitle() : '';
    ?>

    <ul class="space-y-3" title="<?= $escaper->escapeHtmlAttr($title) ?>">
        <?php foreach ($block->getSubmenuNodes() as $node): ?>
            <?php
            $childrenLevel = $node->getLevel() + 1;
            $children = $block->getNodes($childrenLevel, $node);
            $node->setIsParent((bool) $children);
            ?>
            <li>
                <div class="group">
                    <?= /* @noEscape */ $block->renderMenuNode($node) ?>
                </div>

                <?php if ($children): ?>
                    <div class="mt-3 ml-4 border-l border-gray-200 pl-4">
                        <?= /* @noEscape */ $block->renderSubmenu($children, $node, $childrenLevel) ?>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
```

### Generic layout XML snippet

Use this snippet to assign a dedicated Snowdog menu identifier to a custom Hyvä layout.

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="header.container">
            <block
                name="vendor.theme.custom.menu"
                class="Snowdog\Menu\Block\Menu"
                after="-">
                <arguments>
                    <argument name="menu" xsi:type="string">hyva-custom-menu</argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

Then create:

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu/sub_menu.phtml
```

## Recommended File Map

### Global override

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/sub_menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/menu/node_type/category.phtml
```

### Menu-specific override

```text
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu/sub_menu.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu/node_type/category.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu/node_type/custom_url.phtml
app/design/frontend/Vendor/Theme/Snowdog_Menu/templates/hyva-custom-menu/menu/custom/sub_menu/three-column.phtml
```

## References and Resources

- Snowdog Menu README: `README.md`
- Snowdog Menu wiki: `https://github.com/SnowdogApps/magento2-menu/wiki`
- Hyvä demo menu mentioned by the module README: `https://menu-hyva.snowdog.dev/`
- Snowdog frontend Hyvä examples in this module:
  - `view/frontend/layout/default_hyva.xml`
  - `view/frontend/templates/hyva-topmenu-desktop/menu.phtml`
  - `view/frontend/templates/hyva-topmenu-mobile/menu.phtml`
  - `view/frontend/templates/hyva-menu-footer/menu.phtml`

## Important Guidelines

1. Always start from `Snowdog\Menu\Block\Menu` and its node tree methods.
2. Build new layouts around menu identifiers, not hardcoded one-off templates.
3. Keep behavior in Alpine and markup in PHTML.
4. Keep styling in Tailwind utilities or Hyvä-compatible Tailwind sources only.
5. Never reintroduce Luma JavaScript or CSS patterns.
6. Preserve recursive rendering for nested menu levels.
7. Override node-type templates when a layout needs different link, image, badge, or wrapper markup.
8. Prefer accessible buttons, `aria-expanded`, `aria-hidden`, keyboard escape handling, and focus management for interactive menus.
9. Use the existing Hyvä examples in this repository as the baseline implementation pattern.
