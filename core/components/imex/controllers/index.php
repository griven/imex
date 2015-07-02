<?php 
//if (!$modx->hasPermission('test_action')){
//  return $modx->error->failure($modx->lexicon('access_denied'));
//}

require_once dirname(dirname(__FILE__)).'/model/imex.class.php';

$imex = new ImEx($modx);

$imex->config['assetsUrl'] = MODX_ASSETS_URL.'components/imex/';
$imex->config['connectorUrl'] = MODX_ASSETS_URL.'components/imex/connector.php';
$imex->config['filesImportPath'] = 'imex/import/';

$modx->addPackage('imex', MODX_CORE_PATH.'components/imex/model/');
$modx->lexicon->load('imex:default');

$modx->regClientCSS(MODX_ASSETS_URL.'components/imex/css/mgr.css');
$modx->regClientStartupScript(MODX_ASSETS_URL.'components/imex/js/mgr/imex.js');
$modx->regClientStartupScript(MODX_ASSETS_URL.'components/imex/js/mgr/widgets/home.panel.js');
$modx->regClientStartupScript(MODX_ASSETS_URL.'components/imex/js/mgr/sections/index.js');
$modx->regClientStartupHTMLBlock('<script type="text/javascript">
imex.config = '.$modx->toJSON($imex->config).';
imex.request = '.$modx->toJSON($_GET).';
</script>');


return '<div id="imex-panel-home-div"></div>';

