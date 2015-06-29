<?php 
/**
 * @package imex
 * @subpackage processors
 */
 
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set("max_execution_time", "1200"); //20 min.
ini_set("max_input_time", "1200"); //20 min.
ini_set('memory_limit', '128M');
ini_set('auto_detect_line_endings', 1);
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, 'ru_RU.UTF-8');

$modx->getService('lexicon', 'modLexicon');
$modx->lexicon->load($modx->config['manager_language'].':imex:default');

$data = json_decode($scriptProperties['data'], true);

if ( empty($data['parent_exp'])) {
  return $modx->error->failure($modx->lexicon('imex.mess_no_parent'));
}
if ( empty($data['config_file'])) {
  return $modx->error->failure($modx->lexicon('imex.mess_no_conf'));
}

require_once MODX_CORE_PATH."components/imex/model/imex.class.php";

$imex_config = require MODX_ASSETS_PATH.'components/imex/configs/'.$data['config_file'].'.php';
$imex_config['config_name'] = $data['config_file'];
$imex = new ImEx($modx, $imex_config);

$out = FALSE;

switch ($data['exp_type']) {
  case 'csv':
    $out = $imex->exportCSV($data['parent_exp']);
    break;
  case 'xls':
    $out = $imex->exportXLS($data['parent_exp'], 'Excel5');
    break;
  case 'xlsx':
    $out = $imex->exportXLS($data['parent_exp'], 'Excel2007');
    break;
}

if ($out === FALSE) {
  return $modx->error->failure($modx->lexicon('imex.mess_no_elements'));
}

return $modx->error->success('', $out);
