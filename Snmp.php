<?php
class Tmonitor_Model_Snmp
{
    const OID_CPU_LOAD_1M = "1.3.6.1.4.1.2021.10.1.3.1";//1 minute Load
    const OID_CPU_LOAD_5M = "1.3.6.1.4.1.2021.10.1.3.2";//5 minute Load
    const OID_CPU_LOAD_15M = "1.3.6.1.4.1.2021.10.1.3.3";//10 minute Load

    const OID_CPU_PER_SYSTEM = "1.3.6.1.4.1.2021.11.10.0";//percentages of system CPU time
    const OID_CPU_PER_IDLE = "1.3.6.1.4.1.2021.11.11.0";//percentages of idle CPU time
    const OID_CPU_PER_USER = "1.3.6.1.4.1.2021.11.9.0";//percentage of user CPU time

    const OID_SWAP_TOTAL = "1.3.6.1.4.1.2021.4.3.0";//Total Swap Size
    const OID_SWAP_AVAILABLE = "1.3.6.1.4.1.2021.4.4.0";//Available Swap Space

    const OID_RAM_TOTAL = "1.3.6.1.4.1.2021.4.5.0";//Total RAM in machine
    //const OID_RAM_USED = "1.3.6.1.4.1.2021.4.6.0";//Total RAM used
    const OID_RAM_FREE = "1.3.6.1.4.1.2021.4.6.0";//Total RAM Free 
    //const OID_RAM_SHARE = "1.3.6.1.4.1.2021.4.13.0";//Total RAM Shared 
    const OID_RAM_BUFFER = "1.3.6.1.4.1.2021.4.14.0";//Total RAM Buffered 
    const OID_RAM_CACHE = "1.3.6.1.4.1.2021.4.15.0";//Total Cached Memory 

    const OID_DISK_PATH = "1.3.6.1.4.1.2021.9.1.2";//Path where the disk is mounted 
    const OID_DISK_DEVICE = "1.3.6.1.4.1.2021.9.1.3";//Path of the device for the partition 
    const OID_DISK_TOTAL = "1.3.6.1.4.1.2021.9.1.6";//Total size of the disk/partion (kBytes) 
    const OID_DISK_AVAILABLE = "1.3.6.1.4.1.2021.9.1.7";//Available space on the disk 
    const OID_DISK_USED = "1.3.6.1.4.1.2021.9.1.8";//Used space on the disk 
    const OID_DISK_PER_USED = "1.3.6.1.4.1.2021.9.1.9";//Percentage of space used on disk 
    const OID_DISK_PER_INODE = "1.3.6.1.4.1.2021.9.1.10";//Percentage of inodes used on disk

    const OID_SYS_RUN_TIME = "1.3.6.1.2.1.25.1.1.0";//System Uptime

    
    protected $_host;
    protected $_community;

    public function __construct($host, $community = "public")
    {
        $this->_host = $host;
        $this->_community = $community;
    }
    /**
     * Base Interface of snmp oid
     */
    public function get_server_info($objectid)
    {
        $a = @snmpget($this->_host, $this->_community, $objectid);
        if ($a) {
            $tmp = explode(":", $a);
            if (count($tmp) > 1) {
                unset($tmp[0]);
                $a = join(":", $tmp);
                if (strpos($a, '"') !== false) {
                    $a = str_replace('"', "", $a);
                }
            }
        }        
        return $a;
    }

    public function get_server_info_list($objectid)
    {
        $a = @snmpwalk($this->_host, $this->_community, $objectid);
        if ($a) {
            $list = array();
            foreach ($a as $index=>$val) {
                $oid = trim(substr($val, strpos($val, ": ")+2), '"');
                $list[$index] = $oid;
            }
            return $list;
        } else {
            return false;
        }
        
    }

    /**
     * cpu使用率，空闲率，系统使用率，用户使用率
     */
    public function getCpuInfo()
    {
        $free = $this->get_server_info(self::OID_CPU_PER_IDLE);
        return array(
            "percent" => floatval(100-$free),
            "free" => floatval($free),
            "system" => floatval($this->get_server_info(self::OID_CPU_PER_SYSTEM)),
            "user" => floatval($this->get_server_info(self::OID_CPU_PER_USER))
        );
    }
    /**
     * 内存使用率，内存总容量，已使用，空闲，共享，缓冲，缓存 
     */
    public function getRamInfo()
    {
        $total = $this->get_server_info(self::OID_RAM_TOTAL);
        $free = $this->get_server_info(self::OID_RAM_FREE);
        $buffer = $this->get_server_info(self::OID_RAM_BUFFER);
        $cache = $this->get_server_info(self::OID_RAM_CACHE);
        $used = $total-$free-$buffer-$cache;
        $percent = ($total!==false)?number_format($used/$total*100, 2):0;
        return array(
            "percent" => $percent,
            "total" => $total,
            "used" => $used,
            "free" => $free,
            //"share" => $this->get_server_info(self::OID_RAM_SHARE),
            "buffer" => $buffer,
            "cache" => $cache
        );
    }
    /**
     * swap总容量和有效容量
     */
    public function getSwapInfo()
    {
        return array(
            "swap_total" => $this->get_server_info(self::OID_SWAP_TOTAL),
            "swap_used" => $this->get_server_info(self::OID_SWAP_AVAILABLE)
        );
    }
    /**
     * 分区挂载点，分区设备名,分区总容量，分区已用容量，
     * 可用容量，已用百分比，inode百分比
     */
    public function getDiskInfo()
    {
        $path_arr = $this->get_server_info_list(self::OID_DISK_PATH);
        $dev_arr = $this->get_server_info_list(self::OID_DISK_DEVICE);
        $total_arr = $this->get_server_info_list(self::OID_DISK_TOTAL);
        $used_arr = $this->get_server_info_list(self::OID_DISK_USED);
        $ava_arr = $this->get_server_info_list(self::OID_DISK_AVAILABLE);
        $pu_arr = $this->get_server_info_list(self::OID_DISK_PER_USED);
        $pi_arr = $this->get_server_info_list(self::OID_DISK_PER_INODE);
        $result = array();
        if ($path_arr) {
            for ($i=0;$i<count($path_arr);$i++) {
                $result[] = array(
                    "path" => $path_arr[$i],
                    "device" => $dev_arr[$i],
                    "total" => $total_arr[$i],
                    "used" => $used_arr[$i],
                    "available" => $ava_arr[$i],
                    "percent_used" => $pu_arr[$i],
                    "percent_inode" => $pi_arr[$i]
                );
            }
        }        
        return $result;
    }
    /**
     * cpu的1，5，15分钟平均负载
     */
    public function getCpuLoad()
    {
        $load5m = $this->get_server_info(self::OID_CPU_LOAD_5M);
        return array(
            "load1m" => floatval($this->get_server_info(self::OID_CPU_LOAD_1M)),
            "load5m" => floatval($this->get_server_info(self::OID_CPU_LOAD_5M)),
            "load15m" => floatval($this->get_server_info(self::OID_CPU_LOAD_15M))
        );
    }

    public function getUptime()
    {
        $runtime = trim($this->get_server_info(self::OID_SYS_RUN_TIME));
        preg_match("/\(([0-9]+)\)/", $runtime, $match);
        $time = $match[1];
        $time = substr($time, 0, -2);
        $day = floor($time/(24*3600));
        $time2 = $time - $day*(24*3600);
        $hour = floor($time2/3600);
        $time3 = $time2 - $hour*3600;
        $minute = floor($time3/60);
        $sec = $time3 - $minute*60;
        return array(
            "string" => $day."天".$hour."小时".$minute."分",
            "time" => $time
        );
    }

}
