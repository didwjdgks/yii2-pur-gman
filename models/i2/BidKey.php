<?php
namespace purgman\models\i2;

use purgman\Module;

class BidKey extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_key';
  }

  public static function getDb(){
    return Module::getInstance()->i2db;
  }
}

