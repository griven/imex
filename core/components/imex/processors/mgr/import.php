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

require_once MODX_CORE_PATH."components/imex/model/imex.class.php";

if ($data['imp_type'] == 'clean_parent') {

  //очистка родительской категории
  if ( empty($data['parent_res'])) {
    return $modx->error->failure($modx->lexicon('imex.mess_no_parent'));
  }
	
  $imex = new ImEx($modx);
  
  $out = $imex->clearResources($data['parent_res']);
  
} else {

  if ( empty($data['parent_res'])) {
    return $modx->error->failure($modx->lexicon('imex.mess_no_parent'));
  }
  
  if ( empty($data['config_file'])) {
    return $modx->error->failure($modx->lexicon('imex.mess_no_conf'));
  }
  
  if ( empty($data['imp_file'])) {
    return $modx->error->failure($modx->lexicon('imex.mess_no_file'));
  }

  // E_STRICT workaround
  $file_ext = explode('.', $data['imp_file']);
  $file_ext = strtolower(array_pop($file_ext));

  if (!in_array($file_ext, array(
    'csv','xls','xlsx'
  ))) {
    return $modx->error->failure($modx->lexicon('imex.mess_file_not_support'));
  }
  
  $imex_config = require MODX_ASSETS_PATH.'components/imex/configs/'.$data['config_file'].'.php';
  $imex_config['config_name'] = $data['config_file'];
  
  $imex = new ImEx($modx, $imex_config);
  
  $imex->config['imp_update'] = ($data['imp_type'] == 'update');

if($data['skip'] == 0){
	$modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('imex.import_start'));
}
  if ($file_ext == 'csv') {
    $out = $imex->importCSV($data['parent_res'], $data['imp_file'], $data['skip']);
  } else if ($file_ext == 'xls') {
    $out = $imex->importXLS($data['parent_res'], $data['imp_file'], 'Excel5', $data['skip']);
  } else if ($file_ext == 'xlsx') {
    $out = $imex->importXLS($data['parent_res'], $data['imp_file'], 'Excel2007', $data['skip']);
  }
}

if($out['pos'] <= $out['lines_count']){
	$modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('imex.import_progress', array(
		'current' => $out['pos'] - 1,
		'total' => $out['lines_count'],
		'percent' => round(($out['pos'] - 1) / $out['lines_count'] * 100),
	)));
}
if($out['pos'] > $out['lines_count']){
	$modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('imex.import_complete'));
}
return $modx->error->success('', $out);
