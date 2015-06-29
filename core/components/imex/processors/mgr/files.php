<?php 
/**
 * File operations
 *
 * @package imex
 * @subpackage processors
 */
 
require_once MODX_CORE_PATH."components/imex/model/imex.class.php";

$imex = new ImEx($modx);

switch ($scriptProperties['type']) {
  case 'import':
    $list = $imex->listFiles();
    return $this->outputArray($list, count($list));
  case 'config':
    $list = $imex->listConfigs();
    return $this->outputArray($list, count($list));
  case 'delete':
    $out = $imex->clearDir($imex->config['files_import_dir']);
    return $modx->error->success('', $out);
}