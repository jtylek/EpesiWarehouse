<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

Utils_RecordBrowserCommon::new_record_field('premium_ecommerce_drupal',
			array('name' => _M('Import orders on payment step'), 			'type'=>'checkbox', 'required'=>false, 'extra'=>false, 'visible'=>true));
