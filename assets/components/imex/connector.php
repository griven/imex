<?php 
/**
 * ImEx Connector
 *
 * @package imex
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$modx->addPackage('imex', MODX_CORE_PATH.'components/imex/model/');
$modx->lexicon->load('imex:default');

/* handle request */
$modx->request->handleRequest(array(
  'processors_path'=>MODX_CORE_PATH.'components/imex/processors/mgr/','location'=>''
));

