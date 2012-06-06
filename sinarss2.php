<?php

// 新浪微博RSS Feed生成器草根用户版， 作者 @williamlong [ http://www.williamlong.info ]
// mod by cydu 20120607

if (ini_get('display_errors')) {
    ini_set('display_errors', 0);
}

$username=$_GET["userid"]; // request any username with '?id='
if ( empty($username) ) {
	$username='1639780001';    // <-- change this to your username!
} else {
	// Make sure username request is alphanumeric
	$username=ereg_replace("[^A-Za-z0-9]", "", $username);
}
$feedURL='http://v.t.sina.com.cn/widget/widget_blog.php?height=500&skin=wd_01&showpic=1&uid='.$username;

$C = new Collection();
$C->url = $feedURL;
$C->startFlag = '<div id="content_all" class="wgtList">';
$C->endFlag   = '<div id="rolldown" class="wgtMain_bot">';
$C->init();
$C->regExp = "|<p class=\"wgtCell_txt\">(.*)</p>(.*)<a href=\"(.*)\" title=\"\" target=\"_blank\" class=\"link_d\">|Uis";

$C->parse();

header("Content-type:application/xml");

echo '<?xml version="1.0" encoding="utf8"?>';

?>
<rss version="2.0">
	<channel>
		<title>weiborss</title>
		<link>weibo.com/cydu</link>
		<description>rss feed for cydu's weibo.com, backup of weibo.com/cydu </description>
		<language>zh-cn</language> 
<?php 
for ($i=0;$i<=9;$i++) { 
	$tguid=$C->result[$i][3];
	$tcon=strip_tags($C->result[$i][1]);
if (!empty($tcon)) {
?>
     <item>
		<title><?php echo $tcon; ?></title>
		<description><![CDATA[<?php echo $tcon; ?>]]></description>
		<pubDate>2010-02-06T08:4<?php echo 9-$i; ?>:04Z</pubDate>
		<guid><?php echo $tguid; ?></guid>
		<link><?php echo $tguid; ?></link>
	</item>
<?php
}

} ?>
	</channel>
</rss>
<?php

class Collection{
//入口 公有
var $url;       //欲分析的url地址
var $content; //读取到的内容
var $regExp; //要获取部分的正则表达式
var $codeFrom; //原文的编码
var $codeTo; //欲转换的编码
var $timeout;        //等待的时间

var $startFlag;       //文章开始的标志 默认为0       在进行条目时，只对$startFlag 和 $endFlag之间的文字块进行搜索。
var $endFlag;       //文章结束的标志 默认为文章末尾 在进行条目时，只对$startFlag 和 $endFlag之间的文字块进行搜索。  
var $block;        //$startFlag 和 $endFlag之间的文字块
//出口 私有
var $result;       //输出结果

//初始化收集器
function init(){
       if(empty($url))
       $this->getFile();
       $this->convertEncoding();
}
//所需内容
function parse(){
       $this->getBlock();
       preg_match_all($this->regExp, $this->block ,$this->result,PREG_SET_ORDER);
       return $this->block;
}
//错误处理
function error($msg){
       echo $msg;
}
//读取远程网页 如果成功，传回文件；如果失败传回false
function getFile(){
		//使用SAE的用户可以用下面两个替换
		//$f = new SaeFetchurl();
		//$datalines = $f->fetch($this->url);
       $datalines = @file($this->url);
             if(!$datalines){
        $this->error("can't read the url:".$this->url);
                 return false;
       } else {

        //SAE用户请用注释中的语句
		//$importdata = $datalines;
        $importdata = implode('', $datalines); 
        $importdata = str_replace(array ("\r\n", "\r"), "\n", $importdata);                                        

		$this->content = $importdata;
	   }
          }
       //获取所需要的文字块
       function getBlock(){
       if(!empty($this->startFlag))
        $this->block = substr($this->content,strpos($this->content,$this->startFlag));
       if(!empty($this->endFlag))
        $this->block = substr($this->block,0,strpos($this->block,$this->endFlag));
       }
       //内容编码的转换
       function convertEncoding(){
       if(!empty($this->codeTo))
        $this->codeFrom = mb_detect_encoding($this->content);
       //如果给定转换方案，才执行转换。
       if(!empty($this->codeTo))
        $this->content = mb_convert_encoding($this->content,$this->codeTo,$this->codeFrom) or $this->error("can't convert Encoding");
       }
}//end of class

