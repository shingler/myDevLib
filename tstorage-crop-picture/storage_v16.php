<?PHP
chdir(dirname(__FILE__));

$configUrl = 'http://127.0.0.1:5002/tsysadmin/conf/entry?id=deploy';

//得到配置的 jsonRaw
$jsonRaw = file_get_contents($configUrl);
if(false === $jsonRaw){
        print "can't get the url of config.\n";
        exit();
}

//分析 json
$jsonArray = json_decode($jsonRaw, true);
if($jsonArray == NULL){
        print "can't decode the data of json raw.";
        exit();
}

//得到基本域名 和 passport 的机器 ip
$baseDomain = $jsonArray['domain'];
if(empty($baseDomain)){
        print "can't parse the domain in public or the ip of passport machine.\n";
        exit();
}

//得到基本端口，默认80
$listenPort = '';
$servicePort = '80';
if(isset($jsonArray['component']['tstorage-crop-picture']['config']['port'])){
    $listenPort = 'Listen '.$jsonArray['component']['tstorage-crop-picture']['config']['port'];
    $servicePort = $jsonArray['component']['tstorage-crop-picture']['config']['port'];
}

//将配置写到配置文件里
$conf_template = file_get_contents('storage_v16.conf.template');
$conf = sprintf($conf_template, $listenPort, $servicePort, $servicePort, $baseDomain, $baseDomain);
file_put_contents('/etc/httpd/conf.d/storage_v16.conf', $conf);

umask(0);
@mkdir("/opt/storage_client", 0777, true);

print 'ok';
?>
