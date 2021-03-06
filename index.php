<?php
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
error_reporting(0); //抑制所有错误信息
@header("content-Type: text/html; charset=utf-8"); //语言强制
ob_start();
date_default_timezone_set('Asia/Shanghai');//此句用于消除时间差

$title = 'PHP探针';
$version = "v1.0"; //版本号

define('HTTP_HOST', preg_replace('~^www\.~i', '', $_SERVER['HTTP_HOST']));

$time_start = microtime_float();

function memory_usage()
{
  $memory  = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
  return $memory;
}

// 计时
function microtime_float()
{
  $mtime = microtime();
  $mtime = explode(' ', $mtime);
  return $mtime[1] + $mtime[0];
}

//单位转换
function formatsize($size)
{
  $danwei=array(' B ',' K ',' M ',' G ',' T ');
  $allsize=array();
  $i=0;

  for($i = 0; $i <5; $i++)
  {
    if(floor($size/pow(1024,$i))==0){break;}
  }

  for($l = $i-1; $l >=0; $l--)
  {
    $allsize1[$l]=floor($size/pow(1024,$l));
    $allsize[$l]=$allsize1[$l]-$allsize1[$l+1]*1024;
  }

  $len=count($allsize);

  for($j = $len-1; $j >=0; $j--)
  {
    $fsize=$fsize.$allsize[$j].$danwei[$j];
  }
  return $fsize;
}

function valid_email($str)
{
  return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

//检测PHP设置参数
function show($varName)
{
  switch($result = get_cfg_var($varName))
  {
    case 0:
      return '<font color="red">×</font>';
    break;

    case 1:
      return '<font color="green">√</font>';
    break;

    default:
      return $result;
    break;
  }
}

//保留服务器性能测试结果
$valInt = isset($_POST['pInt']) ? $_POST['pInt'] : "未测试";
$valFloat = isset($_POST['pFloat']) ? $_POST['pFloat'] : "未测试";
$valIo = isset($_POST['pIo']) ? $_POST['pIo'] : "未测试";

if ($_GET['act'] == "phpinfo")
{
  phpinfo();
  exit();
}
elseif($_POST['act'] == "整型测试")
{
  $valInt = test_int();
}
elseif($_POST['act'] == "浮点测试")
{
  $valFloat = test_float();
}
elseif($_POST['act'] == "IO测试")
{
  $valIo = test_io();
}
//网速测试-开始
elseif($_POST['act']=="开始测试")
{
?>
  <script language="javascript" type="text/javascript">
    var acd1;
    acd1 = new Date();
    acd1ok=acd1.getTime();
  </script>
  <?php
  for($i=1;$i<=100000;$i++)
  {
    echo "<!--567890#########0#########0#########0#########0#########0#########0#########0#########012345-->";
  }
  ?>
  <script language="javascript" type="text/javascript">
    var acd2;
    acd2 = new Date();
    acd2ok=acd2.getTime();
    window.location = '?speed=' +(acd2ok-acd1ok)+'#w_networkspeed';
  </script>
<?php
}
//网速测试-结束

//网络速度测试
if(isset($_POST['speed']))
{
  $speed=round(100/($_POST['speed']/1000),2);
}
elseif($_GET['speed']=="0")
{
  $speed=6666.67;
}
elseif(isset($_GET['speed']) and $_GET['speed']>0)
{
  $speed=round(100/($_GET['speed']/1000),2); //下载速度：$speed kb/s
}
else
{
  $speed="<font color=\"red\">&nbsp;未探测&nbsp;</font>";
}

function isfun($funName = '')
{
  if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return '错误';
    return (false !== function_exists($funName)) ? '<font color="green">√</font>' : '<font color="red">×</font>';
}

//整数运算能力测试
function test_int()
{
  $timeStart = gettimeofday();
  for($i = 0; $i < 3000000; $i++)
  {
    $t = 1+1;
  }
  $timeEnd = gettimeofday();
  $time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
  $time = round($time, 3)."秒";
  return $time;
}

//浮点运算能力测试
function test_float()
{
  //得到圆周率值
  $t = pi();
  $timeStart = gettimeofday();

  for($i = 0; $i < 3000000; $i++)
  {
    //开平方
    sqrt($t);
  }

  $timeEnd = gettimeofday();
  $time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
  $time = round($time, 3)."秒";
  return $time;
}

//IO能力测试
function test_io()
{
  $fp = @fopen(PHPSELF, "r");
  $timeStart = gettimeofday();
  for($i = 0; $i < 10000; $i++)
  {
    @fread($fp, 10240);
    @rewind($fp);
  }
  $timeEnd = gettimeofday();
  @fclose($fp);
  $time = ($timeEnd["usec"]-$timeStart["usec"])/1000000+$timeEnd["sec"]-$timeStart["sec"];
  $time = round($time, 3)."秒";
  return($time);
}

//linux系统探测
$sysInfo = sys_linux();

function sys_linux()
{
    // CPU
    if (false === ($str = @file("/proc/cpuinfo"))) return false;
    $str = implode("", $str);
    @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
    @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
    @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
    @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
    if (false !== is_array($model[1]))
  {
        $res['cpu']['num'] = sizeof($model[1]);
    /*
        for($i = 0; $i < $res['cpu']['num']; $i++)
        {
            $res['cpu']['model'][] = $model[1][$i].'&nbsp;('.$mhz[1][$i].')';
            $res['cpu']['mhz'][] = $mhz[1][$i];
            $res['cpu']['cache'][] = $cache[1][$i];
            $res['cpu']['bogomips'][] = $bogomips[1][$i];
        }*/
    if($res['cpu']['num']==1)
      $x1 = '';
    else
      $x1 = ' ×'.$res['cpu']['num'];
    $mhz[1][0] = ' | 频率:'.$mhz[1][0];
    $cache[1][0] = ' | 二级缓存:'.$cache[1][0];
    $bogomips[1][0] = ' | Bogomips:'.$bogomips[1][0];
    $res['cpu']['model'][] = $model[1][0].$mhz[1][0].$cache[1][0].$bogomips[1][0].$x1;
        if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
        if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
        if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
        if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
  }

    // NETWORK

    // UPTIME
    if (false === ($str = @file("/proc/uptime"))) return false;
    $str = explode(" ", implode("", $str));
    $str = trim($str[0]);
    $min = $str / 60;
    $hours = $min / 60;
    $days = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0) $res['uptime'] = $days."天";
    if ($hours !== 0) $res['uptime'] .= $hours."小时";
    $res['uptime'] .= $min."分钟";

    // MEMORY
    if (false === ($str = @file("/proc/meminfo"))) return false;
    $str = implode("", $str);
    preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
  preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

    $res['memTotal'] = round($buf[1][0]/1024, 2);
    $res['memFree'] = round($buf[2][0]/1024, 2);
    $res['memBuffers'] = round($buffers[1][0]/1024, 2);
  $res['memCached'] = round($buf[3][0]/1024, 2);
    $res['memUsed'] = $res['memTotal']-$res['memFree'];
    $res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;

    $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
  $res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
    $res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0; //真实内存使用率

  $res['memCachedPercent'] = (floatval($res['memCached'])!=0)?round($res['memCached']/$res['memTotal']*100,2):0; //Cached内存使用率

    $res['swapTotal'] = round($buf[4][0]/1024, 2);
    $res['swapFree'] = round($buf[5][0]/1024, 2);
    $res['swapUsed'] = round($res['swapTotal']-$res['swapFree'], 2);
    $res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;

    // LOAD Board
    if (glob("/sys/devices/virtual/dmi/id/board_name")) {
        $res['boardVendor'] = file('/sys/devices/virtual/dmi/id/board_vendor')[0];
        $res['boardName'] = file('/sys/devices/virtual/dmi/id/board_name')[0];
        $res['boardVersion'] = file('/sys/devices/virtual/dmi/id/board_version')[0];
    }

    // LOAD BIOS
    if (glob("/sys/devices/virtual/dmi/id/bios_vendor")) {
        $res['BIOSVendor'] = file('/sys/devices/virtual/dmi/id/bios_vendor')[0];
        $res['BIOSVersion'] = file('/sys/devices/virtual/dmi/id/bios_version')[0];
        $res['BIOSDate'] = file('/sys/devices/virtual/dmi/id/bios_date')[0];
    }

    // LOAD DISK
    if (glob("/sys/class/block/s*/device/model")) {
        $res['diskModel'] = file(glob("/sys/class/block/s*/device/model")[0])[0];
        $res['diskVendor'] = file(glob("/sys/class/block/s*/device/vendor")[0])[0];
    }

    // LOAD AVG
    if (false === ($str = @file("/proc/loadavg"))) return false;
    $str = explode(" ", implode("", $str));
    $str = array_chunk($str, 4);
    $res['loadAvg'] = implode(" ", $str[0]);

    return $res;
}

//比例条
function bar($percent)
{
?>
  <div class="bar"><div class="barli" style="width:<?php echo $percent?>%">&nbsp;</div></div>
<?php
}

$uptime = $sysInfo['uptime']; //在线时间
$stime = date('Y-m-d H:i:s'); //系统当前时间

//硬盘
$dt = round(@disk_total_space(".")/(1024*1024*1024),3); //总
$df = round(@disk_free_space(".")/(1024*1024*1024),3); //可用
$du = $dt-$df; //已用
$hdPercent = (floatval($dt)!=0)?round($du/$dt*100,2):0;

$load = $sysInfo['loadAvg'];  //系统负载


//判断内存如果小于1G，就显示M，否则显示G单位
if($sysInfo['memTotal']<1024)
{
  $memTotal = $sysInfo['memTotal']." M";
  $mt = $sysInfo['memTotal']." M";
  $mu = $sysInfo['memUsed']." M";
  $mf = $sysInfo['memFree']." M";
  $mc = $sysInfo['memCached']." M"; //cache化内存
  $mb = $sysInfo['memBuffers']." M";  //缓冲
  $st = $sysInfo['swapTotal']." M";
  $su = $sysInfo['swapUsed']." M";
  $sf = $sysInfo['swapFree']." M";
  $swapPercent = $sysInfo['swapPercent'];
  $memRealUsed = $sysInfo['memRealUsed']." M"; //真实内存使用
  $memRealFree = $sysInfo['memRealFree']." M"; //真实内存空闲
  $memRealPercent = $sysInfo['memRealPercent']; //真实内存使用比率
  $memPercent = $sysInfo['memPercent']; //内存总使用率
  $memCachedPercent = $sysInfo['memCachedPercent']; //cache内存使用率
}
else
{
  $memTotal = round($sysInfo['memTotal']/1024,3)." G";
  $mt = round($sysInfo['memTotal']/1024,3)." G";
  $mu = round($sysInfo['memUsed']/1024,3)." G";
  $mf = round($sysInfo['memFree']/1024,3)." G";
  $mc = round($sysInfo['memCached']/1024,3)." G";
  $mb = round($sysInfo['memBuffers']/1024,3)." G";
  $st = round($sysInfo['swapTotal']/1024,3)." G";
  $su = round($sysInfo['swapUsed']/1024,3)." G";
  $sf = round($sysInfo['swapFree']/1024,3)." G";
  $swapPercent = $sysInfo['swapPercent'];
  $memRealUsed = round($sysInfo['memRealUsed']/1024,3)." G"; //真实内存使用
  $memRealFree = round($sysInfo['memRealFree']/1024,3)." G"; //真实内存空闲
  $memRealPercent = $sysInfo['memRealPercent']; //真实内存使用比率
  $memPercent = $sysInfo['memPercent']; //内存总使用率
  $memCachedPercent = $sysInfo['memCachedPercent']; //cache内存使用率
}

//网卡流量
$strs = @file("/proc/net/dev");

for ($i = 2; $i < count($strs); $i++ )
{
  preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
  $NetOutSpeed[$i] = $info[10][0];
  $NetInputSpeed[$i] = $info[2][0];
  $NetInput[$i] = formatsize($info[2][0]);
  $NetOut[$i]  = formatsize($info[10][0]);
}

//ajax调用实时刷新
if ($_GET['act'] == "rt")
{
  $arr=array('useSpace'=>"$du",'freeSpace'=>"$df",'hdPercent'=>"$hdPercent",'barhdPercent'=>"$hdPercent%",'TotalMemory'=>"$mt",'UsedMemory'=>"$mu",'FreeMemory'=>"$mf",'CachedMemory'=>"$mc",'Buffers'=>"$mb",'TotalSwap'=>"$st",'swapUsed'=>"$su",'swapFree'=>"$sf",'loadAvg'=>"$load",'uptime'=>"$uptime",'freetime'=>"$freetime",'bjtime'=>"$bjtime",'stime'=>"$stime",'memRealPercent'=>"$memRealPercent",'memRealUsed'=>"$memRealUsed",'memRealFree'=>"$memRealFree",'memPercent'=>"$memPercent%",'memCachedPercent'=>"$memCachedPercent",'barmemCachedPercent'=>"$memCachedPercent%",'swapPercent'=>"$swapPercent",'barmemRealPercent'=>"$memRealPercent%",'barswapPercent'=>"$swapPercent%",'NetOut2'=>"$NetOut[2]",'NetOut3'=>"$NetOut[3]",'NetOut4'=>"$NetOut[4]",'NetOut5'=>"$NetOut[5]",'NetOut6'=>"$NetOut[6]",'NetOut7'=>"$NetOut[7]",'NetOut8'=>"$NetOut[8]",'NetOut9'=>"$NetOut[9]",'NetOut10'=>"$NetOut[10]",'NetInput2'=>"$NetInput[2]",'NetInput3'=>"$NetInput[3]",'NetInput4'=>"$NetInput[4]",'NetInput5'=>"$NetInput[5]",'NetInput6'=>"$NetInput[6]",'NetInput7'=>"$NetInput[7]",'NetInput8'=>"$NetInput[8]",'NetInput9'=>"$NetInput[9]",'NetInput10'=>"$NetInput[10]",'NetOutSpeed2'=>"$NetOutSpeed[2]",'NetOutSpeed3'=>"$NetOutSpeed[3]",'NetOutSpeed4'=>"$NetOutSpeed[4]",'NetOutSpeed5'=>"$NetOutSpeed[5]",'NetInputSpeed2'=>"$NetInputSpeed[2]",'NetInputSpeed3'=>"$NetInputSpeed[3]",'NetInputSpeed4'=>"$NetInputSpeed[4]",'NetInputSpeed5'=>"$NetInputSpeed[5]");
  $jarr=json_encode($arr);
  $_GET['callback'] = htmlspecialchars($_GET['callback']);
  echo $_GET['callback'],'(',$jarr,')';
  exit;
}

//ajax调用计算CPU使用率
if ($_GET['act'] == "cpu")
{
  $duration = 1;

  $stat1=array_slice(preg_split('/\s+/', trim(file('/proc/stat')[0])), 1);
  sleep($duration);
  $stat2=array_slice(preg_split('/\s+/', trim(file('/proc/stat')[0])), 1);

  $diff=array_map(function ($x,$y) {return intval($y)-intval($x);}, $stat1, $stat2);
  $total=array_sum($diff)/100;

  $cpu=array();
  $cpu['user'] = $diff[0]/$total;
  $cpu['nice'] = $diff[1]/$total;
  $cpu['sys'] = $diff[2]/$total;
  $cpu['idle'] = $diff[3]/$total;
  $cpu['iowait'] = $diff[4]/$total;
  $cpu['irq'] = $diff[5]/$total;
  $cpu['softirq'] = $diff[6]/$total;
  $cpu['steal'] = $diff[7]/$total;

  $jarr=json_encode($cpu);
  $_GET['callback'] = htmlspecialchars($_GET['callback']);
  echo $_GET['callback'],'(',$jarr,')';
  exit;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $_SERVER['SERVER_NAME']; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Powered by: Yahei.Net -->
<style type="text/css">
<!--
* {font-family: "Microsoft Yahei",Tahoma, Arial; }
body{text-align: center; margin: 0 auto; padding: 0; background-color:#fafafa;font-size:12px;font-family:Tahoma, Arial}
h1 {font-size: 26px; padding: 0; margin: 0; color: #333333; font-family: "Lucida Sans Unicode","Lucida Grande",sans-serif;}
h1 small {font-size: 11px; font-family: Tahoma; font-weight: bold; }
a{color: #666; text-decoration:none;}
a.black{color: #000000; text-decoration:none;}
table{width:100%;clear:both;padding: 0; margin: 0 0 10px;border-collapse:collapse; border-spacing: 0;
box-shadow: 1px 1px 1px #CCC;
-moz-box-shadow: 1px 1px 1px #CCC;
-webkit-box-shadow: 1px 1px 1px #CCC;
-ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=2, Direction=135, Color='#CCCCCC')";}
th{padding: 3px 6px; font-weight:bold;background:#dedede;color:#626262;border:1px solid #cccccc; text-align:left;}
tr{padding: 0; background:#FFFFFF;}
td{padding: 3px 6px; border:1px solid #CCCCCC;}
.w_logo{height:25px;text-align:center;color:#333;FONT-SIZE: 15px; width:13%; }
.w_top{height:25px;text-align:center; width:8.7%;}
.w_top:hover{background:#dadada;}
.w_foot{height:25px;text-align:center; background:#dedede;}
input{padding: 2px; background: #FFFFFF; border-top:1px solid #666666; border-left:1px solid #666666; border-right:1px solid #CCCCCC; border-bottom:1px solid #CCCCCC; font-size:12px}
input.btn{font-weight: bold; height: 20px; line-height: 20px; padding: 0 6px; color:#666666; background: #f2f2f2; border:1px solid #999;font-size:12px}
.bar {border:1px solid #999999; background:#FFFFFF; height:5px; font-size:2px; width:89%; margin:2px 0 5px 0;padding:1px; overflow: hidden;}
.bar_1 {border:1px dotted #999999; background:#FFFFFF; height:5px; font-size:2px; width:89%; margin:2px 0 5px 0;padding:1px; overflow: hidden;}
.barli_red{background:#ff6600; height:5px; margin:0px; padding:0;}
.barli_blue{background:#0099FF; height:5px; margin:0px; padding:0;}
.barli_green{background:#36b52a; height:5px; margin:0px; padding:0;}
.barli_black{background:#333; height:5px; margin:0px; padding:0;}
.barli_1{background:#999999; height:5px; margin:0px; padding:0;}
.barli{background:#36b52a; height:5px; margin:0px; padding:0;}
#page {width: 960px; padding: 0 auto; margin: 0 auto; text-align: left;}
#header{position:relative; padding:5px;}
.w_small{font-family: Courier New;}
.w_number{color: #f800fe;}
.sudu {padding: 0; background:#5dafd1; }
.suduk { margin:0px; padding:0;}
.resYes{}
.resNo{color: #FF0000;}
.word{word-break:break-all;}
-->
</style>
<script src="//lib.sinaapp.com/js/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function(){getJSONData();});
var OutSpeed2=<?php echo floor($NetOutSpeed[2]) ?>;
var OutSpeed3=<?php echo floor($NetOutSpeed[3]) ?>;
var OutSpeed4=<?php echo floor($NetOutSpeed[4]) ?>;
var OutSpeed5=<?php echo floor($NetOutSpeed[5]) ?>;
var InputSpeed2=<?php echo floor($NetInputSpeed[2]) ?>;
var InputSpeed3=<?php echo floor($NetInputSpeed[3]) ?>;
var InputSpeed4=<?php echo floor($NetInputSpeed[4]) ?>;
var InputSpeed5=<?php echo floor($NetInputSpeed[5]) ?>;
function getJSONData()
{
  setTimeout("getJSONData()", 1000);
  $.getJSON('?act=rt&callback=?', displayData);
}
function ForDight(Dight,How)
{
  if (Dight<0){
    var Last=0+"B/s";
  }else if (Dight<1024){
    var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+"B/s";
  }else if (Dight<1048576){
    Dight=Dight/1024;
    var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+"K/s";
  }else{
    Dight=Dight/1048576;
    var Last=Math.round(Dight*Math.pow(10,How))/Math.pow(10,How)+"M/s";
  }
  return Last;
}
function displayData(dataJSON)
{
  $("#useSpace").html(dataJSON.useSpace);
  $("#freeSpace").html(dataJSON.freeSpace);
  $("#hdPercent").html(dataJSON.hdPercent);
  $("#barhdPercent").width(dataJSON.barhdPercent);
  $("#TotalMemory").html(dataJSON.TotalMemory);
  $("#UsedMemory").html(dataJSON.UsedMemory);
  $("#FreeMemory").html(dataJSON.FreeMemory);
  $("#CachedMemory").html(dataJSON.CachedMemory);
  $("#Buffers").html(dataJSON.Buffers);
  $("#TotalSwap").html(dataJSON.TotalSwap);
  $("#swapUsed").html(dataJSON.swapUsed);
  $("#swapFree").html(dataJSON.swapFree);
  $("#swapPercent").html(dataJSON.swapPercent);
  $("#loadAvg").html(dataJSON.loadAvg);
  $("#uptime").html(dataJSON.uptime);
  $("#freetime").html(dataJSON.freetime);
  $("#stime").html(dataJSON.stime);
  $("#bjtime").html(dataJSON.bjtime);
  $("#memRealUsed").html(dataJSON.memRealUsed);
  $("#memRealFree").html(dataJSON.memRealFree);
  $("#memRealPercent").html(dataJSON.memRealPercent);
  $("#memPercent").html(dataJSON.memPercent);
  $("#barmemPercent").width(dataJSON.memPercent);
  $("#barmemRealPercent").width(dataJSON.barmemRealPercent);
  $("#memCachedPercent").html(dataJSON.memCachedPercent);
  $("#barmemCachedPercent").width(dataJSON.barmemCachedPercent);
  $("#barswapPercent").width(dataJSON.barswapPercent);
  $("#NetOut2").html(dataJSON.NetOut2);
  $("#NetOut3").html(dataJSON.NetOut3);
  $("#NetOut4").html(dataJSON.NetOut4);
  $("#NetOut5").html(dataJSON.NetOut5);
  $("#NetOut6").html(dataJSON.NetOut6);
  $("#NetOut7").html(dataJSON.NetOut7);
  $("#NetOut8").html(dataJSON.NetOut8);
  $("#NetOut9").html(dataJSON.NetOut9);
  $("#NetOut10").html(dataJSON.NetOut10);
  $("#NetInput2").html(dataJSON.NetInput2);
  $("#NetInput3").html(dataJSON.NetInput3);
  $("#NetInput4").html(dataJSON.NetInput4);
  $("#NetInput5").html(dataJSON.NetInput5);
  $("#NetInput6").html(dataJSON.NetInput6);
  $("#NetInput7").html(dataJSON.NetInput7);
  $("#NetInput8").html(dataJSON.NetInput8);
  $("#NetInput9").html(dataJSON.NetInput9);
  $("#NetInput10").html(dataJSON.NetInput10);
  $("#NetOutSpeed2").html(ForDight((dataJSON.NetOutSpeed2-OutSpeed2),3)); OutSpeed2=dataJSON.NetOutSpeed2;
  $("#NetOutSpeed3").html(ForDight((dataJSON.NetOutSpeed3-OutSpeed3),3)); OutSpeed3=dataJSON.NetOutSpeed3;
  $("#NetOutSpeed4").html(ForDight((dataJSON.NetOutSpeed4-OutSpeed4),3)); OutSpeed4=dataJSON.NetOutSpeed4;
  $("#NetOutSpeed5").html(ForDight((dataJSON.NetOutSpeed5-OutSpeed5),3)); OutSpeed5=dataJSON.NetOutSpeed5;
  $("#NetInputSpeed2").html(ForDight((dataJSON.NetInputSpeed2-InputSpeed2),3)); InputSpeed2=dataJSON.NetInputSpeed2;
  $("#NetInputSpeed3").html(ForDight((dataJSON.NetInputSpeed3-InputSpeed3),3)); InputSpeed3=dataJSON.NetInputSpeed3;
  $("#NetInputSpeed4").html(ForDight((dataJSON.NetInputSpeed4-InputSpeed4),3)); InputSpeed4=dataJSON.NetInputSpeed4;
  $("#NetInputSpeed5").html(ForDight((dataJSON.NetInputSpeed5-InputSpeed5),3)); InputSpeed5=dataJSON.NetInputSpeed5;
}
$(document).ready(function(){getCPUJSONData();});
function getCPUJSONData()
{
  setTimeout("getCPUJSONData()", 2000);
  $.getJSON('?act=cpu&callback=?', displayCPUData);
}
function displayCPUData(dataJSON)
{
  $("#cpuUSER").html(dataJSON.user.toFixed(1));
  $("#cpuSYS").html(dataJSON.sys.toFixed(1));
  $("#cpuNICE").html(dataJSON.nice.toFixed(1));
  $("#cpuIDLE").html(dataJSON.idle.toFixed(1).substring(0,4));
  $("#cpuIOWAIT").html(dataJSON.iowait.toFixed(1));
  $("#cpuIRQ").html(dataJSON.irq.toFixed(1));
  $("#cpuSOFTIRQ").html(dataJSON.softirq.toFixed(1));
  $("#cpuSTEAL").html(dataJSON.steal.toFixed(1));
  
  usage = 100 - (dataJSON.idle+dataJSON.iowait);
  if (usage > 75)
    $("#barcpuPercent").width(usage+'%').removeClass().addClass('barli_black');
  else if (usage > 50)
    $("#barcpuPercent").width(usage+'%').removeClass().addClass('barli_red');
  else if (usage > 25)
    $("#barcpuPercent").width(usage+'%').removeClass().addClass('barli_blue');
  else
    $("#barcpuPercent").width(usage+'%').removeClass().addClass('barli_green');
}
-->
</script>
</head>
<body>

<a name="w_top"></a>

<div id="page">

<table>
  <tr>
    <th class="w_logo">PHP探针</th>
    <th class="w_top"><a href="/files/">文件下载</a></th>
    <th class="w_top"><a href="/wol/">远程唤醒</a></th>
    <th class="w_top"><a href="/admin/">路由管理</a></th>
    <th class="w_top"><a href="/shell/">Shell in a box</a></th>
  </tr>
</table>

<!--服务器相关参数-->
<table>
  <tr><th colspan="4">服务器参数</th></tr>
  <tr>
    <td>服务器域名/IP地址</td>
    <td colspan="3"><?php echo @get_current_user();?> - <?php echo $_SERVER['SERVER_NAME'];?>(<?php if('/'==DIRECTORY_SEPARATOR){echo $_SERVER['SERVER_ADDR'];}else{echo @gethostbyname($_SERVER['SERVER_NAME']);} ?>)&nbsp;&nbsp;你的IP地址是：<?php echo @$_SERVER['REMOTE_ADDR'];?></td>
  </tr>
  <tr>
    <td>服务器标识</td>
    <td colspan="3"><?php if($sysInfo['win_n'] != ''){echo $sysInfo['win_n'];}else{echo @php_uname();};?></td>
  </tr>
  <tr>
    <td width="13%">服务器操作系统</td>
    <td width="37%"><?php $release_info = parse_ini_file("/etc/lsb-release"); echo $release_info["DISTRIB_DESCRIPTION"];?> &nbsp;内核版本：<?php if('/'==DIRECTORY_SEPARATOR){$os = explode(' ',php_uname()); echo $os[2];}else{echo $os[1];} ?></td>
    <td width="13%">服务器解译引擎</td>
    <td width="37%"><?php echo $_SERVER['SERVER_SOFTWARE'];?></td>
  </tr>
  <tr>
    <td>服务器语言</td>
    <td><?php echo getenv("HTTP_ACCEPT_LANGUAGE");?></td>
    <td>服务器端口</td>
    <td><?php echo $_SERVER['SERVER_PORT'];?></td>
  </tr>
  <tr>
    <td>服务器主机名</td>
    <td><?php if('/'==DIRECTORY_SEPARATOR ){echo $os[1];}else{echo $os[2];} ?></td>
    <td>绝对路径</td>
    <td><?php echo $_SERVER['DOCUMENT_ROOT']?str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']):str_replace('\\','/',dirname(__FILE__));?></td>
  </tr>
  <tr>
    <td>管理员邮箱</td>
    <td><?php echo $_SERVER['SERVER_ADMIN'];?></td>
    <td>探针路径</td>
    <td><?php echo str_replace('\\','/',__FILE__)?str_replace('\\','/',__FILE__):$_SERVER['SCRIPT_FILENAME'];?></td>
  </tr>
</table>

<table>
  <tr><th colspan="6">服务器实时数据</th></tr>
  <tr>
    <td width="13%" >服务器当前时间</td>
    <td width="37%" ><span id="stime"><?php echo $stime;?></span></td>
    <td width="13%" >服务器已运行时间</td>
    <td width="37%" colspan="3"><span id="uptime"><?php echo $uptime;?></span></td>
  </tr>
  <tr>
    <td width="13%">CPU型号 [<?php echo $sysInfo['cpu']['num'];?>核]</td>
    <td width="87%" colspan="5"><?php echo $sysInfo['cpu']['model'];?></td>
  </tr>
<?php if (isset($sysInfo['boardVendor'])) : ?>
  <tr>
    <td width="13%">主板型号</td>
    <td width="37%"><?php echo $sysInfo['boardVendor'] . " " . $sysInfo['boardName'] . " " . $sysInfo['boardVersion'];?></td>
    <td width="13%">主板BIOS</td>
    <td width="37%"><?php echo $sysInfo['BIOSVendor'] . " " . $sysInfo['BIOSVersion'] . " " . $sysInfo['BIOSDate'];?></td>
  </tr>
<?php endif; ?>
<?php if (isset($sysInfo['diskModel'])) : ?>
  <tr>
    <td width="13%">硬盘型号</td>
    <td width="87%" colspan="5"><?php echo $sysInfo['diskModel'] . " " . $sysInfo['diskVendor'];?></td>
  </tr>
<?php endif; ?>
  <tr>
    <td>CPU使用状况</td>
    <td colspan="5">
      <font id="cpuUSER" color="#CC0000">0.0</font> user, 
      <font id="cpuSYS" color="#CC0000">0.0</font> sys, 
      <font id="cpuNICE">0.0</font> nice, 
      <font id="cpuIDLE" color="#CC0000">99.9</font> idle, 
      <font id="cpuIOWAIT">0.0</font> iowait, 
      <font id="cpuIRQ">0.0</font> irq, 
      <font id="cpuSOFTIRQ">0.0</font> softirq, 
      <font id="cpuSTEAL">0.0</font> steal 
      <div class="bar"><div id="barcpuPercent" class="barli_green" style="width: 100%;">&nbsp;</div> </div>
    </td>
  </tr>
  <tr>
    <td>内存使用状况</td>
    <td colspan="5">
<?php
$tmp = array(
    'memTotal', 'memUsed', 'memFree', 'memPercent',
    'memCached', 'memRealPercent',
    'swapTotal', 'swapUsed', 'swapFree', 'swapPercent'
);
foreach ($tmp AS $v) {
    $sysInfo[$v] = $sysInfo[$v] ? $sysInfo[$v] : 0;
}
?>
          物理内存：共
          <font color='#CC0000'><?php echo $memTotal;?> </font>
           , 已用
          <font color='#CC0000'><span id="UsedMemory"><?php echo $mu;?></span></font>
          , 空闲
          <font color='#CC0000'><span id="FreeMemory"><?php echo $mf;?></span></font>
          , 使用率
      <span id="memPercent"><?php echo $memPercent;?></span>
          <div class="bar"><div id="barmemPercent" class="barli_green" style="width:<?php echo $memPercent?>%" >&nbsp;</div> </div>
<?php
//判断如果cache为0，不显示
if($sysInfo['memCached']>0)
{
?>
      Cache化内存为 <span id="CachedMemory"><?php echo $mc;?></span>
      , 使用率
          <span id="memCachedPercent"><?php echo $memCachedPercent;?></span>
      % | Buffers缓冲为  <span id="Buffers"><?php echo $mb;?></span>
          <div class="bar"><div id="barmemCachedPercent" class="barli_blue" style="width:<?php echo $memCachedPercent?>%" >&nbsp;</div></div>

          真实内存使用
          <span id="memRealUsed"><?php echo $memRealUsed;?></span>
      , 真实内存空闲
          <span id="memRealFree"><?php echo $memRealFree;?></span>
      , 使用率
          <span id="memRealPercent"><?php echo $memRealPercent;?></span>
          %
          <div class="bar_1"><div id="barmemRealPercent" class="barli_1" style="width:<?php echo $memRealPercent?>%" >&nbsp;</div></div>
<?php
}
//判断如果SWAP区为0，不显示
if($sysInfo['swapTotal']>0)
{
?>
          SWAP区：共
          <?php echo $st;?>
          , 已使用
          <span id="swapUsed"><?php echo $su;?></span>
          , 空闲
          <span id="swapFree"><?php echo $sf;?></span>
          , 使用率
          <span id="swapPercent"><?php echo $swapPercent;?></span>
          %
          <div class="bar"><div id="barswapPercent" class="barli_red" style="width:<?php echo $swapPercent?>%" >&nbsp;</div> </div>

<?php
}
?>
    </td>
  </tr>
  <tr>
    <td>硬盘使用状况</td>
    <td colspan="5">
    总空间 <?php echo $dt;?>&nbsp;G，
    已用 <font color='#333333'><span id="useSpace"><?php echo $du;?></span></font>&nbsp;G，
    空闲 <font color='#333333'><span id="freeSpace"><?php echo $df;?></span></font>&nbsp;G，
    使用率 <span id="hdPercent"><?php echo $hdPercent;?></span>%
    <div class="bar"><div id="barhdPercent" class="barli_black" style="width:<?php echo $hdPercent;?>%" >&nbsp;</div> </div>
  </td>
  </tr>
    <tr>
    <td>系统平均负载</td>
    <td colspan="5" class="w_number"><span id="loadAvg"><?php echo $load;?></span></td>
  </tr>
</table>

<?php if (false !== ($strs = @file("/proc/net/dev"))) : ?>
<table>
    <tr><th colspan="5">网络使用状况</th></tr>
<?php for ($i = 2; $i < count($strs); $i++ ) : ?>
<?php preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );?>
     <tr>
        <td width="13%"><?php echo $info[1][0]?> : </td>
        <td width="29%">入网: <font color='#CC0000'><span id="NetInput<?php echo $i?>"><?php echo $NetInput[$i]?></span></font></td>
    <td width="14%">实时: <font color='#CC0000'><span id="NetInputSpeed<?php echo $i?>">0B/s</span></font></td>
        <td width="29%">出网: <font color='#CC0000'><span id="NetOut<?php echo $i?>"><?php echo $NetOut[$i]?></span></font></td>
    <td width="14%">实时: <font color='#CC0000'><span id="NetOutSpeed<?php echo $i?>">0B/s</span></font></td>
    </tr>
<?php endfor; ?>
</table>
<?php endif; ?>

<?php if (0 < count($strs = array_splice(@file("/proc/net/arp"), 1))) : ?>
<table>
    <tr><th colspan="5">网络邻居</th></tr>
<?php $seen = array(); ?>
<?php for ($i = 0; $i < count($strs); $i++ ) : ?>
<?php $info = preg_split('/\s+/', $strs[$i]); ?>
<?php if ('0x2' == $info[2] && !isset($seen[$info[3]])) : ?>
<?php $seen[$info[3]] = true; ?>
     <tr>
        <td width="13%"><?php echo gethostbyaddr($info[0]);?> </td>
        <td width="29%">MAC: <font color='#CC0000'><?php  echo $info[3];?></font></td>
        <td width="14%">类型: <font color='#CC0000'><?php echo $info[1]=='0x1'?'ether':$info[1];?></font></td>
        <td width="29%">接口: <font color='#CC0000'><?php echo $info[5];?></font></td>
    </tr>
<?php endif; ?>
<?php endfor; ?>
</table>
<?php endif; ?>

<?php
$events = array();
$i = 0;
$lines = str_split(file_get_contents('/var/log/wtmp'), 384);
foreach ($lines as $line)
{
  preg_match('/(.{4})(.{4})(.{32})(.{4})(.{32})(.{256})(.{4})(.{4})(.{4})(.{4})(.{4})/', $line, $matches);
  $events[$i] = array();
  $events[$i]['type'] = unpack('I', $matches[1])[1];
  $events[$i]['pid'] = unpack('I', $matches[2])[1];
  $events[$i]['line'] = trim($matches[3]);
  $events[$i]['inittab'] = $matches[4];
  $events[$i]['user'] = trim($matches[5]);
  $events[$i]['host'] = trim($matches[6]);
  $events[$i]['t1'] = $matches[7];
  $events[$i]['t2'] = $matches[8];
  $events[$i]['gmtime'] = unpack('I', $matches[9])[1];
  $events[$i]['t4'] = $matches[10];
  $events[$i]['t5'] = $matches[11];
  $i++;
}
$events2 = array();
foreach ($events as $event)
{
  if ($event['user'] == '')
     continue;
  switch ($event['type'])
  {
  case 7:
    $events2[$event['line']] = $event;
    break;
  case 8:
    unset($events2[$event['line']]);
    break;
  }
}
?>

<?php if (0 < count($events2)) : ?>
<table>
    <tr><th colspan="6">已登录用户</th></tr>
<?php foreach ($events2 as $event ) : ?>
     <tr>
        <td width="15%"><?php echo $event['user'];?></td>
        <td width="15%">TTY: <font color='#CC0000'><?php echo $event['line'];?></font></td>
        <td width="25%">源地址: <font color='#CC0000'><?php echo $event['host'];?></font></td>
        <td width="15%">开始于: <font color='#CC0000'><?php echo gmstrftime('%m-%d %H:%M', $event['gmtime']);?></font></td>
        <td width="15%">空闲: <font color='#CC0000'><?php echo '';?></font></td>
        <td width="15%">当前命令: <font color='#CC0000'><?php echo $event['pid'];?></font></td>
    </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>


<a name="w_performance"></a><a name="bottom"></a>
<form action="<?php echo $_SERVER['PHP_SELF']."#bottom";?>" method="post">
<!--服务器性能检测-->
<table>
  <tr><th colspan="5">服务器性能检测</th></tr>
  <tr align="center">
    <td width="19%">参照对象</td>
    <td width="17%">整数运算能力检测<br />(1+1运算300万次)</td>
    <td width="17%">浮点运算能力检测<br />(圆周率开平方300万次)</td>
    <td width="17%">数据I/O能力检测<br />(读取10K文件1万次)</td>
    <td width="30%">CPU信息</td>
  </tr>
  <tr align="center">
    <td align="left">美国 LinodeVPS</td>
    <td>0.357秒</td>
    <td>0.802秒</td>
    <td>0.023秒</td>
    <td align="left">4 x Xeon L5520 @ 2.27GHz</td>
  </tr>
  <tr align="center">
    <td align="left">美国 PhotonVPS.com</td>
    <td>0.431秒</td>
    <td>1.024秒</td>
    <td>0.034秒</td>
    <td align="left">8 x Xeon E5520 @ 2.27GHz</td>
  </tr>
  <tr align="center">
    <td align="left">德国 SpaceRich.com</td>
    <td>0.421秒</td>
    <td>1.003秒</td>
    <td>0.038秒</td>
    <td align="left">4 x Core i7 920 @ 2.67GHz</td>
  </tr>
  <tr align="center">
    <td align="left">美国 RiZie.com</td>
    <td>0.521秒</td>
    <td>1.559秒</td>
    <td>0.054秒</td>
    <td align="left">2 x Pentium4 3.00GHz</td>
  </tr>
  <tr align="center">
    <td align="left">埃及 CitynetHost.com</td>
    <td>0.343秒</td>
    <td>0.761秒</td>
    <td>0.023秒</td>
    <td align="left">2 x Core2Duo E4600 @ 2.40GHz</td>
  </tr>
  <tr align="center">
    <td align="left">美国 IXwebhosting.com</td>
    <td>0.535秒</td>
    <td>1.607秒</td>
    <td>0.058秒</td>
    <td align="left">4 x Xeon E5530 @ 2.40GHz</td>
  </tr>
  <tr align="center">
    <td>本台服务器</td>
    <td><?php echo $valInt;?><br /><input class="btn" name="act" type="submit" value="整型测试" /></td>
    <td><?php echo $valFloat;?><br /><input class="btn" name="act" type="submit" value="浮点测试" /></td>
    <td><?php echo $valIo;?><br /><input class="btn" name="act" type="submit" value="IO测试" /></td>
    <td></td>
  </tr>
</table>
<input type="hidden" name="pInt" value="<?php echo $valInt;?>" />
<input type="hidden" name="pFloat" value="<?php echo $valFloat;?>" />
<input type="hidden" name="pIo" value="<?php echo $valIo;?>" />

<a name="w_networkspeed"></a>
<!--网络速度测试-->
<table>
  <tr><th colspan="3">网络速度测试</th></tr>
  <tr>
    <td width="19%" align="center"><input name="act" type="submit" class="btn" value="开始测试" />
        <br />
  向客户端传送1000k字节数据<br />
  带宽比例按理想值计算
  </td>
    <td width="81%" align="center" >

  <table align="center" width="550" border="0" cellspacing="0" cellpadding="0" >
    <tr >
    <td height="15" width="50">带宽</td>
  <td height="15" width="50">1M</td>
    <td height="15" width="50">2M</td>
    <td height="15" width="50">3M</td>
    <td height="15" width="50">4M</td>
    <td height="15" width="50">5M</td>
    <td height="15" width="50">6M</td>
    <td height="15" width="50">7M</td>
    <td height="15" width="50">8M</td>
    <td height="15" width="50">9M</td>
    <td height="15" width="50">10M</td>
    </tr>
   <tr>
    <td colspan="11" class="suduk" ><table align="center" width="550" border="0" cellspacing="0" cellpadding="0" height="8" class="suduk">
    <tr>
      <td class="sudu"  width="<?php
  if(preg_match("/[^\d-., ]/",$speed))
    {
      echo "0";
    }
  else{
      echo 550*($speed/11000);
    }
    ?>"></td>
      <td class="suduk" width="<?php
  if(preg_match("/[^\d-., ]/",$speed))
    {
      echo "550";
    }
  else{
      echo 550-550*($speed/11000);
    }
    ?>"></td>
    </tr>
    </table>
   </td>
  </tr>
  </table>
  <?php echo (isset($_GET['speed']))?"下载1000KB数据用时 <font color='#cc0000'>".$_GET['speed']."</font> 毫秒，下载速度："."<font color='#cc0000'>".$speed."</font>"." kb/s，需测试多次取平均值，超过10M直接看下载速度":"<font color='#cc0000'>&nbsp;未探测&nbsp;</font>" ?>

    </td>
  </tr>
</table>
</form>

<a name="w_php"></a>
<table>
  <tr><th colspan="4">PHP相关参数</th></tr>
  <tr>
    <td width="32%">PHP信息（phpinfo）：</td>
    <td width="18%">
    <?php
    $phpSelf = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $disFuns=get_cfg_var("disable_functions");
    ?>
    <?php echo (preg_match("/phpinfo/i",$disFuns))? '<font color="red">×</font>' :"<a href='$phpSelf?act=phpinfo' target='_blank'>PHPINFO</a>";?>
    </td>
    <td width="32%">PHP版本（php_version）：</td>
    <td width="18%"><?php echo PHP_VERSION;?></td>
  </tr>
  <tr>
    <td>PHP运行方式：</td>
    <td><?php echo strtoupper(php_sapi_name());?></td>
    <td>脚本占用最大内存（memory_limit）：</td>
    <td><?php echo show("memory_limit");?></td>
  </tr>
  <tr>
    <td>PHP安全模式（safe_mode）：</td>
    <td><?php echo show("safe_mode");?></td>
    <td>POST方法提交最大限制（post_max_size）：</td>
    <td><?php echo show("post_max_size");?></td>
  </tr>
  <tr>
    <td>上传文件最大限制（upload_max_filesize）：</td>
    <td><?php echo show("upload_max_filesize");?></td>
    <td>浮点型数据显示的有效位数（precision）：</td>
    <td><?php echo show("precision");?></td>
  </tr>
  <tr>
    <td>脚本超时时间（max_execution_time）：</td>
    <td><?php echo show("max_execution_time");?>秒</td>
    <td>socket超时时间（default_socket_timeout）：</td>
    <td><?php echo show("default_socket_timeout");?>秒</td>
  </tr>
  <tr>
    <td>PHP页面根目录（doc_root）：</td>
    <td><?php echo show("doc_root");?></td>
    <td>用户根目录（user_dir）：</td>
    <td><?php echo show("user_dir");?></td>
  </tr>
  <tr>
    <td>dl()函数（enable_dl）：</td>
    <td><?php echo show("enable_dl");?></td>
    <td>指定包含文件目录（include_path）：</td>
    <td><?php echo show("include_path");?></td>
  </tr>
  <tr>
    <td>显示错误信息（display_errors）：</td>
    <td><?php echo show("display_errors");?></td>
    <td>自定义全局变量（register_globals）：</td>
    <td><?php echo show("register_globals");?></td>
  </tr>
  <tr>
    <td>数据反斜杠转义（magic_quotes_gpc）：</td>
    <td><?php echo show("magic_quotes_gpc");?></td>
    <td>"&lt;?...?&gt;"短标签（short_open_tag）：</td>
    <td><?php echo show("short_open_tag");?></td>
  </tr>
  <tr>
    <td>"&lt;% %&gt;"ASP风格标记（asp_tags）：</td>
    <td><?php echo show("asp_tags");?></td>
    <td>忽略重复错误信息（ignore_repeated_errors）：</td>
    <td><?php echo show("ignore_repeated_errors");?></td>
  </tr>
  <tr>
    <td>忽略重复的错误源（ignore_repeated_source）：</td>
    <td><?php echo show("ignore_repeated_source");?></td>
    <td>报告内存泄漏（report_memleaks）：</td>
    <td><?php echo show("report_memleaks");?></td>
  </tr>
  <tr>
    <td>自动字符串转义（magic_quotes_gpc）：</td>
    <td><?php echo show("magic_quotes_gpc");?></td>
    <td>外部字符串自动转义（magic_quotes_runtime）：</td>
    <td><?php echo show("magic_quotes_runtime");?></td>
  </tr>
  <tr>
    <td>打开远程文件（allow_url_fopen）：</td>
    <td><?php echo show("allow_url_fopen");?></td>
    <td>声明argv和argc变量（register_argc_argv）：</td>
    <td><?php echo show("register_argc_argv");?></td>
  </tr>
  <tr>
    <td>Cookie 支持：</td>
    <td><?php echo isset($_COOKIE)?'<font color="green">√</font>' : '<font color="red">×</font>';?></td>
    <td>拼写检查（ASpell Library）：</td>
    <td><?php echo isfun("aspell_check_raw");?></td>
  </tr>
   <tr>
    <td>高精度数学运算（BCMath）：</td>
    <td><?php echo isfun("bcadd");?></td>
    <td>PREL相容语法（PCRE）：</td>
    <td><?php echo isfun("preg_match");?></td>
   <tr>
    <td>PDF文档支持：</td>
    <td><?php echo isfun("pdf_close");?></td>
    <td>SNMP网络管理协议：</td>
    <td><?php echo isfun("snmpget");?></td>
  </tr>
   <tr>
    <td>VMailMgr邮件处理：</td>
    <td><?php echo isfun("vm_adduser");?></td>
    <td>Curl支持：</td>
    <td><?php echo isfun("curl_init");?></td>
  </tr>
   <tr>
    <td>SMTP支持：</td>
    <td><?php echo get_cfg_var("SMTP")?'<font color="green">√</font>' : '<font color="red">×</font>';?></td>
    <td>SMTP地址：</td>
    <td><?php echo get_cfg_var("SMTP")?get_cfg_var("SMTP"):'<font color="red">×</font>';?></td>
  </tr>
</table>

<table>
  <tr>
    <td class="w_foot"><A href="https://github.com/phuslu/cmdhere" target="_blank"><?php echo $title.$version;?></A></td>
    <td class="w_foot"><?php $run_time = sprintf('%0.4f', microtime_float() - $time_start);?>Processed in <?php echo $run_time?> seconds. <?php echo memory_usage();?> memory usage.</td>
    <td class="w_foot"><a href="#w_top">返回顶部</a></td>
  </tr>
</table>

</body>
</html>

