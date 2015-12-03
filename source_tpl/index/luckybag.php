<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>新春开福袋</title>
	<meta http-equiv="X-UA-Compatible" content="IE=9">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="pragma" content="no-cache"> 
	<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"> 
	<meta http-equiv="expires" content="0"> 
	<meta name="author" content="Doduo">
	<meta name="copyright" content="口袋大学城">
	<meta name="robots" content="index">
	<meta name="description" content="">
	<meta name="keywords" content="">
	{eval $t = rand(1,10000);}
	<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="{ROOT_RELATIVE}/source_tpl/css/jquery-ui-1.10.3.custom.css" type="text/css">
	<script src="{ROOT_RELATIVE}/source_tpl/js/jquery_2_0_3.js"></script>
	<script src="{ROOT_RELATIVE}/source_tpl/js/jquery-ui-1.10.3.custom.js"></script>
	<script src="{ROOT_RELATIVE}/source_tpl/js/library.js?t=1"></script>
	<style>
		body { margin: 0; padding: 0; color: #eb9500; font-size: 12px; }
		ul { list-style-type: none; }
		a { color: #eb9500; text-decoration: underline; }
		#hd { height: 282px; background: #920e00; }
		#logo { height: 282px; background: url({ROOT_IMAGE}/other/luckybag/logo.jpg); }
		#md, #operate { background: #9b1303; height: 375px; }
		#bt { background: #7f0f02; padding: 30px 0 30px 0; }
		#operate { background: url({ROOT_IMAGE}/other/luckybag/icon.png) 600px center no-repeat; line-height: 25px; padding-left: 60px; width: 920px; margin: 0 auto; }
		#logo, #prize { width: 960px; margin: 0 auto; }
		#open { cursor: pointer; font-size: 18px; font-family: 'Microsoft Yahei'; padding: 15px 50px 15px 50px; background: #5d0b01; border-radius: 8px; float: left; }
		#status { font-size: 18px; font-family: 'Microsoft Yahei'; margin: 20px 0 20px 0; }
		#status span { font-size: 22px; }
		#prize li { float: left; margin: 16px; width: 121px; text-align: center; }
		.item { width: 121px; height: 121px; background: #5d0b01; border-radius: 8px; margin-bottom: 10px; background-position: center center; background-repeat: no-repeat; }
		#ft { width: 100%; text-align: center; padding: 50px 0 0 0; font-family: Tahoma; line-height: 20px; }
	</style>
	<script>
		$(function() {
			var DISABLED = !1;
			$('*').unselectable().undraggable();
			$('#open').on('click', function() {
				if(DISABLED) return;
				DISABLED = !0;
				ajax('?index=luckybag&process=open', function(i) {
					if(i.quantity) $('#status span').html(i.quantity);
					DISABLED = !1;
				});
			});
		});
	</script>
</head>
<body>

	<div id="hd"><div id="logo"></div></div>
	
	<div id="md">
		<div id="operate">
			<img width="245" height="35" src="{ROOT_IMAGE}/other/luckybag/title.png" style="margin-top:60px"><p>
			参与新春活动，赢抽福袋机会，开启下列中的福袋，可以获得丰厚奖品~<br>
			每个会员最多从<a href="thread-6431-1-1.html" target="_BLANK">抢楼</a>、<a href="thread-6432-1-1.html" target="_BLANK">猜桌面活动</a>中获得14个福袋。<br>
			每个福袋的奖品是随机的。
			<div id="status">你还剩余 <span>$trainer[lbagnum]</span> 个福袋</div>
			<div id="open">开启福袋</div>
		</div>
	</div>
	
	<div id="bt">
		<div id="prize">
			<div style="margin: 0 auto;width:733px;"><img width="783" height="33" src="{ROOT_IMAGE}/other/luckybag/titleb.png"></div>
			<ul>
				<li><div class="item" style="background-image:url({ROOT_IMAGE}/other/luckybag/prize-1.jpg)"></div>公/内测学习装置</li>
				<li><div class="item" style="background-image:url({ROOT_IMAGE}/other/luckybag/prize-2.jpg)"></div>公/内测安闲铃铛</li>
				<li><div class="item" style="background-image:url({ROOT_IMAGE}/other/luckybag/prize-3.jpg)"></div>内测探测器</li>
				<li><div class="item" style="background-image:url({ROOT_IMAGE}/other/luckybag/prize-4.jpg)"></div>精灵球</li>
				<li><div class="item" style="background-image:url({ROOT_IMAGE}/other/luckybag/prize-5.jpg)"></div>进化道具</li>
				<li><div class="item" style="background-image:url({ROOT_IMAGE}/other/luckybag/prize-6.jpg)"></div>回复道具</li>
			</ul>
			<br clear="both">
		</div>
		<div id="ft">
			目前时间：<!--{echo date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);}-->MU: <!--{echo Kit::Memory(memory_get_usage(true))}--><br>
			<!--{if $trainer['uid'] == 8 && debuginfo()}-->Processed in 0 second(s), 0 queries. <br><!--{/if}-->
			请使用现代浏览器（如<a href="http://www.google.com/chrome" target="_blank">谷歌浏览器</a>）访问以取得最佳效果。<br>
			Copyright &copy; 2010-2014 <a href="forum.php" target="_blank">PokeUniv</a>(Pet). Version $system[version].
		</div>
	</div>
	
	<div id="lyr-alert"></div>
	
</body>
</html>