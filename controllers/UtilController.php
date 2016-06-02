<?php
namespace purgman\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Json;

use GearmanClient;

class UtilController extends \yii\console\Controller
{
  public function actionDoPurGman($start,$end){
    $gc=new GearmanClient();
    $gc->addServers($this->module->gman_server);

    $query=(new Query())->from('v3_bid_key')
      ->select('bidid')
      ->andWhere(['between','writedate',$start,$end])
      ->andWhere([
          'bidtype'=>'pur',
          'state'=>'Y',
        ])
      ;
    foreach($query->each(100,$this->module->db46) as $row){
      echo $row['bidid'],PHP_EOL;
      $gc->doBackground('pur_gman',Json::encode([
        'bidid'=>$row['bidid'],
      ]));
    }
  }
}

