<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php if (isset($domain) && $domain)echo $domain." - ";else echo '域名whois查询|IP whois查询|域名详细信息查询 - ';?>Whois信息查询系统</title>
	<meta name="keywords" content="<?php if (isset($domain) && $domain)echo $domain.",whois查询,Whois详细信息,域名信息查询,IP信息查询";else echo 'whois查询,域名信息查询,IP信息查询,域名WHOIS查询,IP whois查询,IPV4 whois查询,IPV6 whois查询';?>">
	<meta name="description" content="<?php if (isset($domain) && $domain)echo $domain." - Whois详细信息，$domain IPV4信息，$domain IPV6信息，$domain 域名详细信息,域名信息查询,IP信息查询。";else echo ' ymqz.cn 提供whois查询，域名WHOIS查询，IP whois查询,IPV4 whois查询,IPV6 whois查询服务,域名信息查询,IP信息查询';?>">
	<link href="/images/whois.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="door">
	<A href="http://www.ymqz.cn/">站点主页</A>
	|
	<A href="http://www.xqns.com/" target=_blank>小强域名解析</A>
</div>
<div class="logo">
	<div class="logo2"></div>
</div>
<div class="whois">
	<form class="form01" action="/whois" method="post">
		<div class="whois_body">
			<div class="whois_01">请输入域名或IP：</div>
			<div class="whois_rimleft"></div>
			<input type="text" class="whois_rim" name="domain" value="<?php if (isset($domain)) echo $domain;?>" />
			<input name="GO" value="GO" type="image" title="查询" alt="查询" src="/images/whois_button.gif"  border="0" style="float:left;" onClick=submit();><br /><br /><br />
			查询域名WHOIS请不要输入 www.
			IPV4 或者IPV6请输入合法有效的IP地址</div>
		<?php if (isset($domain) && $domain){?>
			<div class="whois_result">
				<div class="wr">
						<?php if (!isset($result)){?>
						系统暂时不支持您的域名的WHOIS查询： <?php echo $domain;?>
					<?php }else {
                            if ($ycz==1) echo '本数据更新时间为：'.date("Y-m-d H:i:s",$gxsj).'，您可以获取点击这里 <a href="javascript:void(0)" onclick="update(\''.$domain.'\')" ref="nofollow" style="color: #f00">《获取最新信息》</a>。<br><br>';
                            echo $result;
                        }?>
					</div>  </div>  <?php }else{
			?>
		<div class="whois_result">
			<div class="wr">
				最近查询：<?php $i=0;
				foreach($list as $row){
					if ($i>0) echo '|';
					?> <a href="/<?php echo $row['type'];?>/<?php echo $row['dom'];?>"><?php echo $row['dom'];?></a> <?php
				$i++;
				}?>
			</div>  </div>
		<?php }?>
		<div class="whois_bottom"></div>
	</form>
</div>
<?php if (!isset($result)){?>
<div class="whois_ad">
	<div>小提示：本站系统支持特殊域名查询，可以直接获取WHOIS服务器，试试在域名之前输入等号，例如： =baidu.com ；<br />
		本系统支持指定WHOIS服务器查询，试试域名与服务器之间加入冒号，例如： google.com:whois.markmonitor.com</div>
	<div>支持国别域名查询，例如：baidu.cn / cz.tn</div>
	<div>非主流后缀查询，例如：nic.active / web.zone</div>
	<div>（本系统系统支持多达1400种后缀的域名查询）</div>
	<div>支持IPV4 WHOIS查询，例如：202.102.128.1</div>
	<div>支持IPV6 WHOIS查询，例如格式：2401:2e00::</div>
</div>
<?php }?>
<script>
    function update(domain) {
        window.location.href='/update?domain='+domain;
    }
</script>
<div class="copyright">Copyright &copy; 2013 ymqz.cn &nbsp; 本站率先支持IPV6 WHOIS查询（支持域名WHOIS，IPV4 whois，IPV6 whois查询）<br><br>版权所有 南宁新正网络科技有限公司</div>
</body>
</html>