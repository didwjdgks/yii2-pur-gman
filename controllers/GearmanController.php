<?php
namespace purgman\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Console;

use GearmanWorker;

use purgman\models\i2\BidKey;
use purgman\models\info21c\V3BidKey;

class GearmanController extends \yii\console\Controller
{
  public function actionIndex(){
    ini_set('memory_limit','128M');
    echo '[database connection]',PHP_EOL;
    echo '  i2db   : '.$this->module->i2db->dsn,PHP_EOL;
    echo '  infodb : '.$this->module->infodb->dsn,PHP_EOL;
    echo '[gearman worker]',PHP_EOL;
    echo '  server   : '.$this->module->gman_server,PHP_EOL;
    echo '  function : "pur_gman"',PHP_EOL;
    echo Console::renderColoredString("%yStart worker...%n"),PHP_EOL;

    $worker=new GearmanWorker();
    $worker->addServers($this->module->gman_server);
    $worker->addFunction('pur_gman',[$this,'pur_gman']);
    while($worker->work());
  }

  public function pur_gman($job){
    $workload=$job->workload();
    $workload=Json::decode($workload);

    $this->module->i2db->close();
    $this->module->infodb->close();

    $bidKey=BidKey::findOne($workload['bidid']);
    if($bidKey===null) return;

    if($bidKey->bidtype!='pur') return;

    $constnm=iconv('euckr','utf-8',$bidKey->constnm);

    echo " [$bidKey->notinum]";
    echo " $constnm";
    echo PHP_EOL;
    echo ' > ';

    $updates=[];
    $updates['opt']=$bidKey->opt;

    // 아파트=26
    switch($bidKey->whereis){
    case '00': // 민간
    case '17': // 나라장터 민간 APT
    case '20': // 국토교통부
      if(($updates['opt']&pow(2,26))==0){
        $updates['opt']=$updates['opt']+pow(2,26);
      }
      break;
    }
    if(($updates['opt']&pow(2,26))>0) echo "[아파트]";

    // 급식=27
    switch($bidKey->whereis){
    case '12': // 학교급식
      if(($updates['opt']&pow(2,27))==0){
        $updates['opt']=$updates['opt']+pow(2,27);
      }
      break;
    }
    if(preg_match('/급식/',$constnm,$m)){
      if(($updates['opt']&pow(2,27))==0){
        $updates['opt']=$updates['opt']+pow(2,27);
      }
    }
    if(($updates['opt']&pow(2,27))>0) echo '[급식]';

    if(!empty($updates)){
      $bidKey->opt=$updates['opt'];
      
      $bidKey->update(true,['opt']);

      $v3BidKey=V3BidKey::findOne($bidKey->bidid);
      if($v3BidKey!==null){
        $v3BidKey->ulevel=$updates['opt'];

        $v3BidKey->update(true,['ulevel']);
      }
    }

    echo PHP_EOL;
  }
}

