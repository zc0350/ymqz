<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	public function index()
	{
        $dom=$this->th($this->input->get_post("domain",true));
        $data['domain']=$dom;
        $data['list']=$this->db->query("select id,dom,type from myrecord order by sccx desc limit 20")->result_array();
		$this->load->view('index',$data);
	}
	public function whois(){
		$dom=$this->th($this->input->get_post("domain",true));
		$this->fx($dom);
	}

	private function fx($dom){
        if (!$dom){
            header('location:/?domain='.$dom); exit;
        }
        $type=$this->gettype($dom);
        if (!$type['type']){
            header('location:/?domain='.$dom); exit;
        }
        header('location:/'.$type['type'].'/'.$dom);
    }

	public function update(){
        $dom=$this->th($this->input->get_post("domain",true));
        $this->db->delete("myrecord",array("dom"=>$dom));
        $this->fx($dom);
    }

	private function gettype($dom){
        if(strpos($dom,'.')===false && strpos($dom,':')!==false && $this->vv6($dom)) {
            $fh['type']='ipv6';
            $fh['whois']="whois.arin.net";
            $fh['port']=43;
            return $fh;
        }
        else {
            $fgid = str_replace(".", "", $dom);
            if (preg_match("/^\d*$/", $fgid)) {
                $bdc = explode(".", $dom);
                for ($i = 0; $i < count($bdc); $i++) {
                    if ($bdc[$i] > 255) {
                        header('location:/?domain=' . $dom);
                        exit;
                    }
                }
                $fh['type']='ipv4';
                $fh['whois']="whois.arin.net";
                $fh['port']=43;
                return $fh;
            } else {
                $bdc = explode(".", $dom);
                $bdd = count($bdc) - 1;
                $pd = $this->db->query("select * from ltd where ltd='." . $bdc[$bdd] . "'")->row_array();
                if (!$pd['id']) {
                    header('location:/?domain=' . $dom);
                    exit;
                } else{
                    $fh['type']='domain';
                    if ($pd['whois'])  $fh['whois']=$pd['whois'];
                    else $fh['whois']='whois.iana.org';
                    $fh['port']=$pd['po'];
                    return $fh;
                }
            }
        }
    }


	public function result(){
	    $dom=$this->uri->segment(2);
        $domc=$dom;
        $fws=$this->gettype($dom);
        $fw=$fws['type'];
        $who=$fws['whois'];
        $mp=$fws['port'];
        $result='';
        if ($fw=='domain' && strpos($dom,":")){
            $dom1=explode(":",$dom);
            $dom=$dom1[0];
            $who=$dom1[1];
        }
        $data['domain']=$dom;

        if (isset($who)){
            $cx=$this->db->query("select * from myrecord where dom='$domc' limit 1")->row_array();
            if ($cx['id']){
                $data['ycz']=1;
                $result=$cx['result'];
                $data['gxsj']=$cx['gxsj'];
                $this->db->update("myrecord",array("sccx"=>time()),array("id"=>$cx['id']));
            }else{
                $data['ycz']=0;
                $gz=$this->yz(); ///验证如果通过的话允许查询
                if ($gz){
                $result=$this->getwhois($who,$mp,$dom);
                $this->db->insert("myrecord",array("dom"=>$domc,"type"=>$fw,"result"=>$result,"sccx"=>time(),"gxsj"=>time(),"ip"=>$this->getip()));
                }else $result = 'Sorry: System Is Busy, Please Try Again Later !<br>';
            }
        }
        $data['result']=$result;
        $this->load->view('index',$data);
    }

    private function yz(){
        $ip=$this->getip();

        $ti=strtotime("-1 hours");
        $rs=$this->db->query("select count(id) as sl from myrecord where gxsj>=$ti and ip='".$ip."'")->row_array();

        if (isset($rs['sl']) && $rs['sl']>20) return false;

        $ti=strtotime("-10 minute");
        $rs=$this->db->query("select count(id) as sl from myrecord where gxsj>=$ti and ip='".$ip."'")->row_array();
        if (isset($rs['sl']) && $rs['sl']>5) return false;else return true;
    }

    private function getip()
    {
        $onlineip = '';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }


	private function getwhois($who,$mp=43,$dom)
    {
        $result= "<b style='color:#f00'>[Whois服务器: $who]</b><br><br>";
        $fh=$this->fsock($who,$mp,$dom);
        if ($fh) {
            $result.=$fh;
            $serv=$this->getws($fh);
            if ($serv['server'] && $serv['server']!=$who){
                $result.= "<b style='color:#f00'>[Whois服务器: ".$serv['server']."]</b><br><br>";
                $fh=$this->fsock($serv['server'],$serv['port'],$dom);
                if ($fh){
                    $result.=$fh;
                }else $result.='无法连接到WHOIS服务器：'.$serv['server']. '，请稍后尝试！';
            }else $result.='<br><br>未查找到更多的whois服务器，本查询终止。';
        } 	else $result = '无法连接到WHOIS服务器：'.$who. '，请稍后尝试！';
        return $result;
    }

    public function updatewhois(){
        ob_flush();
        $ut=time();
        $pd=$this->db->query("select * from ltd where whois='' order by cs asc limit 20")->result_array();
        foreach ($pd as $rs){
            $this->db->query("update ltd set cs=cs+1,ut=$ut where id=".$rs["id"]);
            $dom=trim($rs["ltd"],".");
            $mp=43;
            $who="whois.iana.org";
            $output=$this->fsock($who,$mp,$dom);
            if (strpos($output, "whois:")!== false){
                $start =strpos($output, "whois:") + strlen("whois:");
                $as=substr($output,$start);
                $end=strpos($as, "\n");
                $bs=str_replace("\r","",trim(substr($as,0,$end)));
                echo $bs."<br>";
                flush();
                if ($bs){
                    $this->db->query("update ltd set whois='$bs',ut=$ut where id=".$rs["id"]);
                }
            }
        }
        echo "<script>location.href='?'</script>";
    }

	private function getws($str){
		if (strpos($str, "whois server:")!== false) $ws="whois server:"; //domain whois
		if (strpos($str, "whois://")!== false)  $ws="whois://";  //ip whois
        if (!isset($ws)) return;
		$start = strpos($str, $ws) + strlen($ws);
		$as=substr($str,$start);
		$end=strpos($as, "\n");
		$bs=str_replace("\r","",trim(substr($as,0,$end)));
		if (!$bs) return;

        $bs=str_replace("https://","",$bs);
        $bs=str_replace("http://","",$bs);
        $bs=str_replace("/","",$bs);

		if (strpos($bs, ":")!== false){
			$bbs=explode(":",$bs);
			$rc['server']=$bbs[0];
			$rc['port']=$bbs[1];
		}else{
			$rc['server']=$bs;
			$rc['port']=43;
		}
		return $rc;
	}

	private function fsock($server,$port=43,$dom){
		if ($coo = fsockopen ($server, $port)) {
			$output="";
			fputs($coo, $dom."\r\n");
			while(!feof($coo)) {
				$output .= fgets($coo);
			}
			fclose($coo);
			if (strpos($output, "<<<")!== false){
				$end=strpos($output,"<<<") + 10;
				$output=substr($output,0,$end);
			}
			return	"<pre>".trim(strtolower(strip_tags($output)))."</pre>";
		}
	}

	private function th($a){
		$a=str_replace("<","",$a);
		$a=str_replace(" ","",$a);
		$a=str_replace("'","",$a);
		$a=str_replace('"',"",$a);
		$a=str_replace(">","",$a);
		$a=str_replace("\\","",$a);
		$a=str_replace("[","",$a);
		$a=str_replace("]","",$a);
		$a=str_replace("|","",$a);
		$a=str_replace("/","",$a);
		$a=str_replace("$","",$a);
		$a=str_replace("%","",$a);
		$a=str_replace("#","",$a);
		$a=str_replace("@","",$a);
		$a=str_replace("!","",$a);
		$a=str_replace("~","",$a);
		$a=str_replace("^","",$a);
		$a=str_replace("&","",$a);
		$a=str_replace("*","",$a);
		$a=str_replace("(","",$a);
		$a=str_replace(")","",$a);
		$a=str_replace("+","",$a);
		$a=str_replace(";","",$a);
		return $a;
	}
	private function vv6($IP)
	{
		return preg_match('/\A(?:(?:(?:[a-f0-9]{1,4}:){6}|
::(?:[a-f0-9]{1,4}:){5}|
(?:[a-f0-9]{1,4})?::(?:[a-f0-9]{1,4}:){4}|
(?:(?:[a-f0-9]{1,4}:){0,1}[a-f0-9]{1,4})?::(?:[a-f0-9]{1,4}:){3}|
(?:(?:[a-f0-9]{1,4}:){0,2}[a-f0-9]{1,4})?::(?:[a-f0-9]{1,4}:){2}|
(?:(?:[a-f0-9]{1,4}:){0,3}[a-f0-9]{1,4})?::[a-f0-9]{1,4}:|
(?:(?:[a-f0-9]{1,4}:){0,4}[a-f0-9]{1,4})?::)(?:[a-f0-9]{1,4}:[a-f0-9]{1,4}|
(?:(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3}
(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]))|
(?:(?:(?:[a-f0-9]{1,4}:){0,5}[a-f0-9]{1,4})?::[a-f0-9]{1,4}|
(?:(?:[a-f0-9]{1,4}:){0,6}[a-f0-9]{1,4})?::))\Z/ix',$IP	);
	}

    private function httppost($url,$arr=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($arr){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function preg_substr($str,$start, $end) // 正则截取函数
    {
        $temp = preg_split($start, $str);
        $content = preg_split($end, $temp[1]);
        return $content[0];
    }
    private function str_substr($str,$start, $end) // 字符串截取函数
    {
        $temp = explode($start, $str, 2);
        $content = explode($end, $temp[1], 2);
        return $content[0];
    }

    private function match_links($document) {
        preg_match_all("'<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))[^>]*>?(.*?)</a>'isx",$document,$links);
        while(list($key,$val) = each($links[2])) {
            if(!empty($val))
                $match['link'][] = $val;
        }
        while(list($key,$val) = each($links[3])) {
            if(!empty($val))
                $match['link'][] = $val;
        }
        while(list($key,$val) = each($links[4])) {
            if(!empty($val))
                $match['content'][] = $val;
        }
        while(list($key,$val) = each($links[0])) {
            if(!empty($val))
                $match['all'][] = $val;
        }
        return $match;
    }

    public function updaterootzone(){
        exit;
        $html=$this->httppost('https://www.iana.org/domains/root/db');

        $arr=$this->match_links($html);

        $link=$arr['link'];
        $dom=$arr['content'];

        $as=array();
        $i=0;
        foreach($link as $key=>$row){
            if (strpos($row,"xn--")===false && strpos($row,"domains/root/db")){
             $as[$i]['link']=$row;
             $as[$i]['zone']=$dom[$key];
             $i++;
            }
        }

        foreach ($as as $row){
            $zone=$row['zone'];
            if (strpos($zone,".")!==false){
            $this->db->insert("ltd",array("ltd"=>$zone,"po"=>43,"ut"=>time()));
            }
        }
        echo "ok";
    }


}
