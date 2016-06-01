<?php
namespace purgman\models\info21c;

use purgman\Module;

class V3BidKey extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_key';
  }

  public static function getDb(){
    return Module::getInstance()->infodb;
  }
}

