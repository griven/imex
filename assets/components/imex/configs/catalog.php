<?php 
return array(
  /** 
   *  разбивка по столбцам при импорте и экспорте (content|tv|category|categories)
   *  object=>content, field=>[название поля] - поля ресурса
   *  object=>tv, field=>[название TV или ID TV] - TV ресурса
   *  object=>category - родительский ресурсы. каждая следующая такая колонка выводит заголовок следующего от корня ресурса
   *  object=>categories - все родительские ресурсы, склеенные слешем. если до этого был вывод category, то оставшиеся ресурсы
   */
   
  'content_row'=>array(
    // выводим поле id ресурса в колонку с названием ID
    'ID'=>array(
      'object'=>'content','field'=>'id',
    ),
	//Артикул товара
    'Артикул'=>array(
      'object'=>'tv','field'=>'sku',
    ),
    // выводим родительские ресурсы, разделенные слешем ресурса id в колонку с названием 'Путь категорий'
    'Путь категорий'=>array(
      'object'=>'categories',
    ),
    // выводим заголовок ресурса
    'Наименование'=>array(
      'object'=>'content','field'=>'pagetitle',
    ),
    //
    'Краткое описание'=>array(
      'object'=>'content','field'=>'introtext'
    ),
    //
    'Контент'=>array(
      'object'=>'content','field'=>'content',
    ),
    // выводим TV c названием price в колонку 'Цена'
    'Цена'=>array(
      'object'=>'tv','field'=>'price',
    ),
    //
    'Картинка 1'=>array(
      'object'=>'tv','field'=>'image',
    ),
    //
    'Картинка 2'=>array(
      'object'=>'tv','field'=>'image2',
    ),
    //
    'Картинка 3'=>array(
      'object'=>'tv','field'=>'image3',
    ),
	//
    'Картинка 4'=>array(
      'object'=>'tv','field'=>'image4',
    ),
	//
    'Картинка 5'=>array(
      'object'=>'tv','field'=>'image5',
    ),
	//Выводится ли товар для яндекс.маркета
    'Яндекс.маркет'=>array(
      'object'=>'tv','field'=>'market',
    ),
	//ID шаблона товар(3) или категория(2)
    'Шаблон'=>array(
      'object'=>'content','field'=>'template',
    ),
	//Товар(0) или категория(1)
    'Контейнер'=>array(
      'object'=>'content','field'=>'isfolder',
    ),
	//Ключевые слова, используются для SЕО
    'Ключевые слова'=>array(
      'object'=>'tv','field'=>'keywords',
    )
  ),
  
  //значения по умолчанию при импорте (ресурсы)
  'imp_content_default'=>array(
    //поля ресурса
    'content'=>array(
      //
      'published'=>1,
      //
      'template'=>4,
      //
      'createdon'=>time()
    ),
    //TV ресурса (id=>value)
    'tv'=>array(
      'price'=>0.00,
    )
  ),
  
  //значения по умолчанию при импорте (ресурсы-контейнеры)
  'imp_category_default'=>array(
    //поля ресурса
    'content'=>array(
      //
      'published'=>1,
      //
      'template'=>3,
      //
      'isfolder'=>true,
      //
      'createdon'=>time()
    ),
    //TV ресурса (id=>value)
    'tv'=>array(
    )
  ),
  
  //контекст
  //ВСЕГДА ИСПОЛЬЗУЕТСЯ ВЫБРАННЫЙ КОНТЕКСТ
  'context'=>'web',
  
  //тестирование конфигурации (без записи в БД)
  //ПОКА НЕ РЕАЛИЗОВАНО
  //'imp_testmode'=>false,
  
  //первая строка - названия полей
  'include_captions'=>true,
  
  //число ресурсов, импортируемых за один раз (загрузка по группам). 0 - не ограничивать.
  'batch_import'=>5,
  
  //включить родительские контейнеры (полный путь до корня контекста)
  'include_parent_categories'=>false,
  
  //включить все нижележащие контейнеры с содержимым
  'include_child_categories'=>true,
  
  //удалять дочерние категории при очистке и обновлении каталога
  //ПОКА НЕ РЕАЛИЗОВАНО
  //'delete_subcategories'=>true,
  
  //по какому полю проверять соответствие ресурса при обновлении, указать название поля. false - не проверять
  'imp_update_on_field'=>'id',
  
  //по какому TV проверять соответствие ресурса при обновлении. указать название или ID TV. false - не проверять.
  'imp_update_on_tv'=>false,
  
  //автоматически генерировать псевдоним (alias) при импорте
  //false - выключено, true - генерировать с переводом в транслит; 'notranslit' - генерировать без перевода в транслит.
  //ПОКА НЕ РЕАЛИЗОВАНО
  //'imp_autoalias'=>true,
  
  //удалить файл после экспорта (скачивания)
  //ПОКА НЕ РЕАЛИЗОВАНО
  //'exp_delete_file'=>false,
  
  //экспортировать контейнеры (категории) наравне с ресурсами
  'exp_containers'=>true,
  
  //кодировка CSV-файла, лучше не менять, иначе MS EXCEL 2010 не прочитает (:
  'csv_charset'=>'UTF-8',
);

//функция для фильтрации значений при ИМПОРТЕ

function imex_filter_import($name, &$field) {
  $field = trim($field);
}

//функция для фильтрации значений при ЭКСПОРТЕ

function imex_filter_export($name, &$field) {
  $field = trim($field);
}
