<?php

$action= $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => PKG_NAME_LOWER,
    'parent' => 0,
    'controller' => 'index',
    'haslayout' => 1,
    'lang_topics' => 'imex:default',
    'assets' => '',
),'',true,true);

/* load action into menu */
$menu= $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'imex',
    'parent' => 'components',
    'description' => 'imex.menu_desc',
    'namespace' => 'imex',
    'icon' => '',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
),'',true,true);
$menu->addOne($action);
unset($action);

return $menu;