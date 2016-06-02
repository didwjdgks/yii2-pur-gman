<?php
namespace purgman\models\i2;

use purgman\Module;

class LogBidTriggerI extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'log_bid_trigger_i';
  }

  public static function getDb(){
    return Module::getInstance()->i2db;
  }
}

