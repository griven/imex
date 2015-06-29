<?php 
/**
 * ImEx
 *
 * Based on catalogfill 2.0.2-pl
 *
 * @author aks <aks@localhost>
 * @package imex
 * @version 0.0.1-rc2
 */

class ImEx {
  /**
   *
   * @param object $modx
   * @param array $config
   */

  function __construct(modX & $modx, $config = array( )) {
  
    $this->modx = &$modx;
    
    $default = array(
      'content_row'=>array(
        'ID'=>array(
          'object'=>'content','field'=>'id',
        ),'Title'=>array(
          'object'=>'content','field'=>'pagetitle',
        )
      ),'imp_content_default'=>array(
        'content'=>array(
          'published'=>1,'template'=>1,'createdon'=>time(),
        ),'tv'=>array( ),
      ),'imp_category_default'=>array(
        'content'=>array(
          'published'=>1,'template'=>1,'createdon'=>time(),
        ),'tv'=>array( ),
      ),'context'=>'web',
		'include_captions'=>true,
		'batch_import'=>500,
		'include_parent_categories'=>false,
        'include_child_categories'=>true,
		'delete_subcategories'=>true,
		'imp_update_on_field'=>'id',
        'imp_update_on_tv'=>false,
		'imp_autoalias'=>true,
		'exp_containers'=>true,
		'exp_delete_file'=>false,
        'csv_charset'=>'UTF-8',
		'imp_testmode'=>false,
		'imp_update_create'=>true,
		'imp_create_notification'=>false,
      //
		'files_import_dir'=>MODX_ASSETS_PATH.'components/imex/files/import/',
		'files_export_dir'=>MODX_ASSETS_PATH.'components/imex/files/export/',
        'files_export_dir_url'=>MODX_ASSETS_URL.'components/imex/files/export/',
		'files_config_dir'=>MODX_ASSETS_PATH.'components/imex/configs/',
      
    );
    
    $this->config = array_merge($default, $config);
    
    $this->modx->addPackage('imex',MODX_CORE_PATH.'components/imex/model/');
  }
  
  /**
   * Устанавливает контекст операции
   *
   * @param object $parentID ID документа или название контекста
   * @return integer правильный ID родителя
   */

  function setContext($parentID) {
  
    if (is_numeric($parentID)) {
      $parent = $this->modx->getObject('modResource', $parentID);
      $this->config['context'] = $parent->get('context_key');
    } else {
      $this->config['context'] = $parentID;
      $parentID = intval($parentID);
    }
    
    $this->config['parent_id'] = $parentID;
    
    return $parentID;
  }
  
  /**
   * Быстро получает ветвь ресурсов одним запросом, только id и pagetitle
   *
   * @param integer $parentID родительской категории
   * @param boolean $up [optional] направление поиска
   * @param array $filters [optional] фильтры
   * @param integer $depth [optional] глубина
   * @return array
   */

  function getResourceTree($parentID, $up = TRUE, $filters = array( ), $depth = 10) {
  
    $IDs = array_merge(array(
      (int) $parentID
    ), $up ? $this->modx->getParentIds($parentID, $depth, array(
      'context'=>$this->config['context']
    )) : $this->modx->getChildIds($parentID, $depth, array(
      'context'=>$this->config['context']
    )));
    
    $query = $this->modx->newQuery('modResource');
    $query->where(array(
      $up ? 'id:IN' : 'parent:IN'=>$IDs,'context_key'=>$this->config['context'],
    ));
    
    $query->andCondition($filters);
    
    $query->select($this->modx->getSelectColumns('modResource', 'modResource', '', array(
      'id','pagetitle'
    )));
    
    $resources = $this->modx->getCollection('modResource', $query);
    
    $tree = array( );
    foreach ($resources as $id=>$resource) {
      $tree[$resource->get('id')] = $resource->get('pagetitle');
    }
    
    return $tree;
  }
  
  /**
   * Получает дерево ресурсов на указанную глубину, с родительскими категориями
   *
   * @param object $parentID ID родительской категории
   * @param object $depth [optional] Глубина поисска
   * @param object $categories [optional] Существующие родительские категории
   * @return array
   */

  function getContentTree($parentID, $depth = 10, $categories = array( )) {
    $contents = array( );
    if ($depth < 0) {
      return $contents;
    }
    
    $query = $this->modx->newQuery('modResource');
    $query->where(array(
      'parent'=>$parentID,'context_key'=>$this->config['context'],
    ));
    
    $query->select($this->modx->getSelectColumns('modResource', 'modResource'));
    
    $query->sortby('`menuindex`', 'ASC');
    
    $resources = $this->modx->getCollection('modResource', $query);
    
    foreach ($resources as $id=>$resource) {
    
      $children = $this->getContentTree($id, $depth - 1, array_merge($categories, array(
        $resource->get('id')=>$resource->get('pagetitle')
      )));
      
      if (!count($children) or $this->config['exp_containers']) {
        $content = $resource->toArray();
        $content['categories'] = $categories;
        $contents[$id] = $content;
      }
      
      $contents += $children;
    }
    return $contents;
  }
  
  /**
   * Получает все документы от указанной категории
   *
   * @param integer $parentID ID родительской категории
   * @return array
   */

  function getContents($parentID) {
  
    if ($this->config['include_parent_categories']) {
      $parent_categories = $this->getResourceTree($parentID, TRUE, array(
        'isfolder'=>1,'context_key'=>$this->config['context'],
      ));
    } else {
      $parent_categories = array( );
    }
    
    if ($this->config['include_child_categories']) {
      $depth = 10;
    } else {
      $depth = 0;
    }
    
    return $this->getContentTree($parentID, $depth, $parent_categories);
  }
  
  /**
   * Получает список TV документов
   *
   * @param array $contentIDs Список ID документов
   * @return array
   */

  function getTVs($contentIDs) {
    $tvs = array( );
    if (count($contentIDs)) {
      $query = $this->modx->newQuery('modTemplateVarResource');
      $query->leftJoin('modTemplateVar', 'modTemplateVar', array(
        '`modTemplateVar`.`id` = `modTemplateVarResource`.`tmplvarid`'
      ));
      $query->where(array(
        'contentid:IN'=>$contentIDs
      ));
      $query->select($this->modx->getSelectColumns('modTemplateVarResource', 'modTemplateVarResource', '', array(
        'id','tmplvarid','contentid','value'
      )));
      $query->select($this->modx->getSelectColumns('modTemplateVar', 'modTemplateVar', '', array(
        'name'
      )));
      
      $tvsRes = $this->modx->getCollection('modTemplateVarResource', $query);
      
      foreach ($tvsRes as $tvID=>$tvRes) {
        $tvs[$tvRes->get('contentid')][$tvRes->get('tmplvarid')] = $tvs[$tvRes->get('contentid')][$tvRes->get('name')] = $tvRes->get('value');
      }
    }
    return $tvs;
  }
  
  /**
   * Получает шаблонов документов
   *
   * @param array $contentIDs Список ID документов
   * @return array
   */

  function getTemplates($contentIDs) {
    $tpls = array( );
    if (count($contentIDs)) {
      $query = $this->modx->newQuery('modTemplate');
      $query->leftJoin('modResource', 'modResource', array(
        '`modTemplate`.`id` = `modResource`.`template`'
      ));
      $query->where(array(
        '`modResource`.`id`:IN'=>$contentIDs
      ));
      $query->select($this->modx->getSelectColumns('modTemplate', 'modTemplate', '', array(
        'templatename'
      )));
      $query->select($this->modx->getSelectColumns('modResource', 'modResource', '', array(
        'id','template'
      )));
      
      $tplsRes = $this->modx->getCollection('modResource', $query);
      
      foreach ($tplsRes as $tvID=>$tplRes) {
        $tpls[$tplRes->get('template')][] = $tpls[$tplRes->get('templatename')][] = $tplRes->get('id');
      }
    }
    
    return $tpls;
  }
  
  /**
   * Создает TV для документа
   *
   * @param array $contentID ID документа
   * @param array $tvs Список TV для этого документа
   * @return
   */

  function setTVs($contentID, $tvs) {
    foreach ($tvs as $id=>$value) {
      if (is_numeric($id)) {
        $query = $this->modx->newQuery('modTemplateVarResource');
        $query->where(array(
          '`tmplvarid`'=>$id,'`contentid`'=>$contentID,
        ));
        $resource = $this->modx->getObject('modTemplateVarResource', $query);
        
        if (!isset($resource)) {
          $resource = $this->modx->newObject('modTemplateVarResource');
          $resource->set('tmplvarid', $id);
          $resource->set('contentid', $contentID);
        }
        
        $resource->set('value', $value);
        $resource->save();
      }
    }
  }
  
  /**
   * Создает структуру категорий
   *
   * @param array $categories Цепочка категорий до товара
   * @return integer ID последней дочерней категории
   */

  function setCategories($categories) {
  
    /** @var array $category_cache Кэш существующих/созданных категорий */

    static $category_cache = array( );
    
    $lastID = $this->config['parent_id'];
    
    foreach ($categories as $name) {
      if (isset($category_cache[$name])) {
        $lastID = $category_cache[$name];
      } else {
        $query = $this->modx->newQuery('modResource');
        
        $query->where(array(
          'pagetitle'=>$name,'context_key'=>$this->config['context'],'parent'=>$lastID,
        ));
        
        $resource = $this->modx->getObject('modResource', $query);
        
        if (!isset($resource)) {
          $resource = $this->modx->newObject('modResource', $this->config['imp_category_default']['content']);
          $resource->set('context_key', $this->config['context']);
          $resource->set('pagetitle', $name);
          $resource->set('parent', $lastID);
          $resource->save();
        }
        
        $lastID = $resource->get('id');
        
        $this->setTVs($lastID, $this->config['imp_category_default']['tv']);
        
        $category_cache[$name] = $lastID;
      }
    }
    
    return $lastID;
  }

  /**
   * Создает документ
   *
   * @param array $content Содержимое документа
   * @param array $tvs TV документа
   * @return integer
   */

  function setContent($content, $tvs) {

    if ($this->config['imp_update']) {
      if ($this->config['imp_update_on_tv'] and isset($tvs[$this->config['imp_update_on_tv']])) {
      
        $query = $this->modx->newQuery('modResource');
        
        $query->leftJoin('modTemplateVarResource', 'modTemplateVarResource', array(
          '`modResource`.`id` = `modTemplateVarResource`.`contentid`',
        ));
        
        $query->leftJoin('modTemplateVar', 'modTemplateVar', array(
          '`modTemplateVar`.`id` = `modTemplateVarResource`.`tmplvarid`'
        ));
        
        $query->where(array(
          array(
            '`modTemplateVarResource`.`tmplvarid`'=>$this->config['imp_update_on_tv'],'OR:`modTemplateVar`.`name`'=>$this->config['imp_update_on_tv']
          ),'`modTemplateVarResource`.`value`'=>$tvs[$this->config['imp_update_on_tv']],'`modResource`.`context_key`'=>$this->config['context'],
        ));
        
        $resource = $this->modx->getObject('modResource', $query);
      }
      
      if ($this->config['imp_update_on_field'] and isset($content[$this->config['imp_update_on_field']])) {
        $query = $this->modx->newQuery('modResource');
        $query->where(array(
          '`modResource`.`'.$this->config['imp_update_on_field'].'`'=>$content[$this->config['imp_update_on_field']],'`modResource`.`context_key`'=>$this->config['context'],
        ));
        $resource = $this->modx->getObject('modResource', $query);
      }
      
      if (isset($resource)) {
        $resource->fromArray($content);
      } elseif ($this->config['imp_update_create']) {
        $resource = $this->modx->newObject('modResource', $content);
      } else {
		$this->modx->log(
			modX::LOG_LEVEL_INFO,
			$this->modx->lexicon('imex.skipped_resource',array('id' => $content['id']))
		);
        return 0;
      }
      
    } else {
      $resource = $this->modx->newObject('modResource', $content);
    }

    if (!isset($content['parent'])) {
      if (count($content['categories'])) {
        $parentID = $this->setCategories($content['categories']);
        $resource->set('parent', $parentID);
      } else {
        $resource->set('parent', $this->config['parent_id']);
      }
    }

	// создаем алиас
	if($resource->alias == ''){
		$resource->set('alias', $resource->cleanAlias($content['pagetitle']));
	}

	// проверяем есть ли ресурс с таким путем?
	$duplicateId = $resource->isDuplicateAlias();
	if($duplicateId){
		$this->modx->log(
			modX::LOG_LEVEL_ERROR,
			$this->modx->lexicon('imex.duplicate_alias', array(
				'alias' => $resource->getAliasPath(),
				'duplicate_id' => $duplicateId,
				'new_id' => $content['id'],
			))
		);
		return 0;
	}

    $resource->save();
    
    $contentID = $resource->get('id');
    
    $this->setTVs($contentID, $tvs);
	if($this->config['imp_create_notification']){
		$this->modx->log(
			modX::LOG_LEVEL_INFO,
			$this->modx->lexicon('imex.resourse_created', array(
				'new_id' => $contentID,
				'alias' => $resource->getAliasPath(),
			))
		);
	}
    return $contentID;
  }
  
  /**
   * Экспорт товаров в CVS файл
   *
   * @param integer $parentID ID родительской категории
   * @return array Результат экпорта
   */

  function exportCSV($parentID) {
  
    $parentID = $this->setContext($parentID);
    
    $contents = $this->getContents($parentID);
    
    if (count($contents) == 0) {
      return FALSE;
    }
    
    $tvs = $this->getTVs(array_keys($contents));
    
    $file_name = $this->config['config_name'].'_'.date('Y-m-d_H-i-s').'.csv';
    $file_path = $this->config['files_export_dir'].$file_name;
    
    $out = array(
      'count'=>0,'filepath'=>$this->config['files_export_dir_url'].$file_name,'filename'=>$file_name,
    );

	$this->__cleanFileDir();
    $file_handler = fopen($file_path, 'x+');

	// добавляем BOM для корректной работы CSV в MS Excel
	if (strtoupper($this->config['csv_charset']) == 'UTF-8') {
		fwrite($file_handler,b"\xEF\xBB\xBF" );
	}
    
    if ($this->config['include_captions']) {
      $row = array( );
      
      if (strtoupper($this->config['csv_charset']) != 'UTF-8') {
        foreach ($this->config['content_row'] as $name=>$config)
          $row[] = iconv('UTF-8', $this->config['csv_charset'], $name);
      } else {
        foreach ($this->config['content_row'] as $name=>$config)
          $row[] = $name;
      }

      fputcsv($file_handler, $row, ';', '"');
    }
    
    unset($val, $row);
    
    foreach ($contents as $id=>$content) {
    
      $row = array( );
      
      foreach ($this->config['content_row'] as $name=>$config) {
      
        if ($config['object'] == 'content') {
          $field = isset($content[$config['field']]) ? $content[$config['field']] : '';
        } else if ($config['object'] == 'tv') {
          $field = isset($tvs[$id][$config['field']]) ? $tvs[$id][$config['field']] : '';
        } else if ($config['object'] == 'category') {
          $field = array_shift($content['categories']);
        } else if ($config['object'] == 'categories') {
          $field = implode('/', $content['categories']);
        }
        
        imex_filter_export($name, $field);
        
        if ($this->config['csv_charset'] != 'UTF-8') {
          $field = iconv('UTF-8', $this->config['csv_charset'], $field);
        }
        
        $row[] = $field;
      }
      
      fputcsv($file_handler, $row, ';', '"');
      $out['count']++;
      
    }
    
    fclose($file_handler);
    unset($file_handler);
    
    return $out;
  }
  
  /**
   * Импорт данных из CSV файла
   *
   * @param integer $parentID
   * @param string $file_name
   * @param integer $skip
   * @return array  'pos'=> с какой строки начинать считывать(1-я строка, номер 1), 'lines_count'=> сколько всего строк
   */
  function importCSV($parentID, $file_name, $skip = 0) {
  
    $parentID = $this->setContext($parentID);
    
    $csv_file = $this->config['files_import_dir'].$file_name;
    
    if (file_exists($csv_file)) {
    
      $file_handler = fopen($csv_file, 'r');
      
      $pos = 1;
      
      if ($this->config['include_captions']) {
        $row = fgetcsv($file_handler, 0, ';', '"');
        $pos++;
        if (strtoupper($this->config['csv_charset']) != 'UTF-8') {
          foreach ($row as & $name)
            $name = iconv($this->config['csv_charset'], 'UTF-8', $name);
        }
      } else {
        $row = array_keys($this->config['content_row']);
      }
      
      $start = ($this->config['include_captions'] && $skip == 0) ? 1 : $skip;
      $max_line = count(file($csv_file));
      
      if ($this->config['batch_import']) {
        $finish = min($start + $this->config['batch_import'] - 1, $max_line);
      }
      
      $fields_map = array( );
      
      foreach (array_keys($this->config['content_row']) as $name_conf) {
        foreach ($row as $id_file=>$name_file) {
          if (strcasecmp($name_conf, $name_file) == 0) {
            $fields_map[$name_conf] = $id_file;
          }
        }
      }
      
      $tv_map = array( );
      
      $resources = $this->modx->getCollection('modTemplateVar');
      foreach ($resources as $id=>$resource) {
        $tv_map[$resource->get('name')] = $id;
      }
      
      foreach ($this->config['imp_content_default']['tv'] as $key=>$value) {
        $this->config['imp_content_default']['tv'][isset($tv_map[$key]) ? $tv_map[$key] : $key] = $value;
      }
      
      foreach ($this->config['imp_category_default']['tv'] as $key=>$value) {
        $this->config['imp_category_default']['tv'][isset($tv_map[$key]) ? $tv_map[$key] : $key] = $value;
      }
      
      while ($pos < $start) {
        fgetcsv($file_handler, 0, ';', '"');
        $pos++;
      }
      
      while ($pos <= $finish ) {
		if(false === $row = fgetcsv($file_handler, 0, ';', '"')){
			die('Error in csv import');
		}
        $content = array_merge($this->config['imp_content_default']['content'], array(
          'categories'=>array( ),
          // !!!
          'context_key'=>$this->config['context'],
        ));
        
        $tvs = array_merge($this->config['imp_content_default']['tv'], array( ));
        
        foreach ($this->config['content_row'] as $name=>$config) {
        
          if (!isset($fields_map[$name])) {
            continue;
          }
          
          $field = $row[$fields_map[$name]];
          
          if (strtoupper($this->config['csv_charset']) != 'UTF-8') {
            $field = iconv($this->config['csv_charset'], 'UTF-8', $field);
          }
          
          imex_filter_import($name, $field);
          
          if ($config['object'] == 'content') {
            $content[$config['field']] = $field;
          } else if ($config['object'] == 'tv') {
            $tvs[@$tv_map[$config['field']]] = $tvs[$config['field']] = $field;
          } else if ($config['object'] == 'category') {
            $content['categories'][] = empty($field) ? NULL : $field;
          } else if ($config['object'] == 'categories') {
            $content['categories'] = empty($field) ? $content['categories'] : $content['categories'] + explode('/', $field);
          }
        }
        
        $this->setContent($content, $tvs);
        
        $pos++;
      }
      
      fclose($file_handler);
    }
    
    $out = array(
      'pos'=>$pos,'lines_count'=>$max_line,
    );
    
    return $out;
  }
  
  /**
   * Экспорт товаров в XLS файл
   *
   * @param integer $parentID ID родительской категории
   * @param string $xls_type [optional] Версия Excel
   * @return array Результат экпорта
   */

  function exportXLS($parentID, $xls_type = 'Excel5') {
  
    $parentID = $this->setContext($parentID);
    
    $contents = $this->getContents($parentID);
    
    if (count($contents) == 0) {
      $this->message = 'В выбранной категории нет продуктов.';
      return FALSE;
    }
    
    $tvs = $this->getTVs(array_keys($contents));
    
    $file_name = $this->config['config_name'].'_'.date('Y-m-d_H-i-s').($xls_type == 'Excel5' ? '.xls' : '.xlsx');
    $file_path = $this->config['files_export_dir'].$file_name;
    
    $out = array(
      'count'=>0,'filepath'=>$this->config['files_export_dir_url'].$file_name,'filename'=>$file_name,
    );

	$this->__cleanFileDir();
	
    require_once realpath(dirname(__FILE__)).'/PHPExcel.php';
    
    $objPHPExcel = new PHPExcel();
    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
    
    //write header row
    if ($this->config['include_captions']) {
      $col_idx = 0;
      foreach ($this->config['content_row'] as $name=>$config) {
        $objWorksheet->setCellValueByColumnAndRow($col_idx, 1, $name);
        $objWorksheet->getStyleByColumnAndRow($col_idx, 1)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
        $objWorksheet->getColumnDimension($col_idx)->setAutoSize(true);
        $col_idx++;
      }
    }
    unset($key, $val);
    
    $row_idx = $this->config['include_captions'] ? 2 : 1;
    
    foreach ($contents as $id=>$content) {
      $col_idx = 0;
      
      foreach ($this->config['content_row'] as $name=>$config) {
      
        if ($config['object'] == 'content') {
          $field = isset($content[$config['field']]) ? $content[$config['field']] : '';
        } else if ($config['object'] == 'tv') {
          $field = isset($tvs[$id][$config['field']]) ? $tvs[$id][$config['field']] : '';
        } else if ($config['object'] == 'category') {
          $field = array_shift($content['categories']);
        } else if ($config['object'] == 'categories') {
          $field = implode('/', $content['categories']);
        } else {
          $field = '';
        }
        
        imex_filter_export($name, $field);
        
        $objWorksheet->setCellValueByColumnAndRow($col_idx, $row_idx, $field);
        
        $col_idx++;
      }
      $row_idx++;
      
    }
    
    if ($xls_type == 'Excel5') {
      $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    } else {
      $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    }
    
    $objWriter->save($file_path);
    
    $out['count'] = $row_idx;
    
    return $out;
    
  }
  
  /**
   * Импорт данных из XLS файла
   *
   * @param integer $parentID
   * @param string $file_name
   * @param string $xls_type
   * @param integer $skip
   * @return array  'pos'=> с какой строки начинать считывать(1-я строка, номер 1), 'lines_count'=> сколько всего строк
   */
  function importXLS($parentID, $file_name, $xls_type = 'Excel5', $skip = 0) {
  
    $parentID = $this->setContext($parentID);
    
    $xls_file = $this->config['files_import_dir'].$file_name;
    
    $out = array(
      'pos'=>0,'lines_count'=>0
    );
    
    if (file_exists($xls_file)) {
    
      require_once realpath(dirname(__FILE__)).'/PHPExcel.php';
      
      if ($xls_type == 'Excel2007') {
        $objReader = new PHPExcel_Reader_Excel2007();
      } else {
        $objReader = new PHPExcel_Reader_Excel5();
      }
      
      $objPHPExcel = $objReader->load($xls_file);
      $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
      $objWorksheet = $objPHPExcel->getActiveSheet();
      $highestRow = $objWorksheet->getHighestRow();
      // columnIndexFromString возвращает буквенный индекс!!!
      $highestColumn = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn());
      
      if ($this->config['include_captions']) {
        $start = 2;
        $fields_map = array( );
        
        foreach (array_keys($this->config['content_row']) as $id_conf=>$name_conf) {
          for ($col_idx = 0; $col_idx < $highestColumn; $col_idx++) {
            $name_file = $objWorksheet->getCellByColumnAndRow($col_idx, 1)->getValue();
            if (strcasecmp($name_conf, $name_file) == 0) {
              $fields_map[$name_conf] = $col_idx;
            }
          }
        }
      } else {
        $start = 1;
        $fields_map = array_combine(range(1, count($this->config['content_row'])), array_keys($this->config['content_row']));
      }
      
      if ($skip > 0) {
        $start = $skip;
      }
      
      $tv_map = array( );
      
      $resources = $this->modx->getCollection('modTemplateVar');
      foreach ($resources as $id=>$resource) {
        $tv_map[$resource->get('name')] = $id;
      }
      
      foreach ($this->config['imp_content_default']['tv'] as $key=>$value) {
        $this->config['imp_content_default']['tv'][isset($tv_map[$key]) ? $tv_map[$key] : $key] = $value;
      }
      
      foreach ($this->config['imp_category_default']['tv'] as $key=>$value) {
        $this->config['imp_category_default']['tv'][isset($tv_map[$key]) ? $tv_map[$key] : $key] = $value;
      }
      
      $finish = $highestRow;
      
      if ($this->config['batch_import']) {
        $finish = min($start + $this->config['batch_import'] - 1, $finish);
      }

      for ($pos = $start; $pos <= $finish; $pos++) {
        $content = array_merge($this->config['imp_content_default']['content'], array(
          'categories'=>array( ),
          // !!!
          'context_key'=>$this->config['context'],
        ));
        
        $tvs = array_merge($this->config['imp_content_default']['tv'], array( ));
        
        foreach ($this->config['content_row'] as $name=>$config) {
        
          if (!isset($fields_map[$name])) {
            continue;
          }
          
          $field = $objWorksheet->getCellByColumnAndRow($fields_map[$name], $pos)->getValue();
          
          imex_filter_import($name, $field);
          
          if ($config['object'] == 'content') {
            $content[$config['field']] = $field;
          } else if ($config['object'] == 'tv') {
            $tvs[$tv_map[$config['field']]] = $tvs[$config['field']] = $field;
          } else if ($config['object'] == 'category') {
            $content['categories'][] = empty($field) ? NULL : $field;
          } else if ($config['object'] == 'categories') {
            $content['categories'] = empty($field) ? $content['categories'] : $content['categories'] + explode('/', $field);
          }
          
        }
        
        $this->setContent($content, $tvs);

      }
      
      $out = array(
        'pos' => $pos, 'lines_count' => $highestRow,
      );
    }
    return $out;
  }
  
  /**
   * Сбрасываем auto_increment ID
   *
   */

  function clearAutoIncrement($model) {
  
    $stmt = $this->modx->query("SELECT MAX(id) FROM {$this->modx->getTableName($model)}");
    $maxID = (integer) $stmt->fetch(PDO::FETCH_COLUMN);
    $stmt->closeCursor();
    
    if (!$maxID) {
      $maxID = 0;
    }
    
    $maxID++;
    
    $this->modx->query("ALTER TABLE {$this->modx->getTableName($model)} AUTO_INCREMENT = {$maxID}");
    
  }
  
  /**
   * Очищаем категорию товаров
   *
   * @param int $parentID
   */

  function clearResources($parentID) {
    $out = array( );
    
    $parentID = $this->setContext($parentID);
    
    //удаляем документы
    if ($this->config['delete_subcategories']) {
      $depth = 10;
    } else {
      $depth = 0;
    }
    
    $contentIDs = array_keys($this->getResourceTree($parentID, FALSE, array(
      'isfolder'=>0
    ), $depth));
    
    $result = $this->modx->removeCollection('modTemplateVarResource', array(
      'contentid:IN'=>$contentIDs
    ));
    $result = $this->modx->removeCollection('modResource', array(
      'id:IN'=>$contentIDs
    ));

    //удаляем категории
    if ($this->config['delete_subcategories']) {
      $contentIDs = array_keys($this->getResourceTree($parentID, FALSE, array(
        'isfolder'=>1
      )));
      
      $result = $this->modx->removeCollection('modTemplateVarResource', array(
        'contentid:IN'=>$contentIDs
      ));
      $result = $this->modx->removeCollection('modResource', array(
        'id:IN'=>$contentIDs
      ));
    }
    
    $this->clearAutoIncrement('modResource');
    $this->clearAutoIncrement('modTemplateVarResource');
    
    return $out;
  }
  
  /**
   * Очищаем директорию от файлов
   *
   * @param string $dir
   */

  function clearDir($dir_path) {
    $out = array(
      'count'=>0
    );
    $dir = opendir(realpath($dir_path));
    while ($f = readdir($dir)) {
      if (is_file($dir_path.$f)) {
        unlink($dir_path.$f);
        $out['count']++;
      }
    }
    closedir($dir);
    return $out;
  }
  
  /**
   
   * Составляем список конфигурационных файлов
   *
   * @return array
   */

  function listConfigs() {
    $output = array( );
    $dir = opendir(realpath($this->config['files_config_dir']));
    while ($f = readdir($dir)) {
      if (is_file($this->config['files_config_dir'].$f))
        $output[] = array(
          'id'=>substr($f, 0, -4),'name'=>substr($f, 0, -4)
        );
    }
    closedir($dir);
    return $output;
  }

  /**
   * Очищаем папку файлов от предыдущих результатов экспорта и от загруженных файлов
   */
  private function __cleanFileDir(){
	$file_list = scandir($this->config['files_export_dir']);
	$file_dir = $this->config['files_export_dir'];
	foreach($file_list as $file_name){
		if(is_file($file_dir . $file_name)){
			unlink($file_dir . $file_name);
		}
	}
}
  
  /**
   * Составляем список файлов для импорта
   *
   * @return array
   */
  function listFiles() {
    $output = array( );
    $dir = opendir(realpath($this->config['files_import_dir']));
    while ($f = readdir($dir)) {
      if (is_file($this->config['files_import_dir'].$f))
        $output[] = array(
          'id'=>$f,'name'=>$f
        );
    }
    closedir($dir);
    return $output;
  }
  
}
