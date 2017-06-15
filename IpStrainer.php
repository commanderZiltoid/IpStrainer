<?php

class IpStrainer {
    
    private $save_directory; // where to save the .dat files
    
    private $ip;
    private $ttl = 10; // low long should the strainer.dat file live before refreshing
    private $requests = 10; // number of requests allowed within the ttl
    private $bantime = 500; // how long to ban the ip in minutes 100 = 1 minute
    
    private $strainer;
    private $banned;
    
    function __construct($ip) {
        
        $this->save_directory = $_SERVER['PHP_LIB_PATH'] . '/IpStrainer';
        $this->ip = $ip;
        
        if(!file_exists($this->save_directory . '/strainer.dat')){
            
            $this->buildStrainerFile();
            
        }
        
        $strainer = unserialize(file_get_contents($this->save_directory . '/strainer.dat'));
        if(time() > ($strainer['build_time'] + $this->ttl)){
            
            unlink($this->save_directory . '/strainer.dat');
            $this->buildStrainerFile();
            
        }
        
        if(!file_exists($this->save_directory . '/banned.dat')){
            
            $this->buildBannedFile();
            
        }
        
        $this->strainer = unserialize(file_get_contents($this->save_directory . '/strainer.dat'));
        $this->banned = unserialize(file_get_contents($this->save_directory . '/banned.dat'));
        
    }
    
    public function verifyIP(){
        
        $occurances = array_count_values($this->strainer['ips']);
        if((array_key_exists($this->ip, $occurances)) && 
                ($occurances[$this->ip] > $this->requests) &&
                (!$this->in_array_r($this->ip, $this->banned))){
            
            $this->banned[] = array(
                'ban_time'  => time(),
                'address'   => $this->ip
            );
            
        } 
        
        foreach($this->banned as $key => $ip){
            if(($ip['address'] == $this->ip)){
                if(time() < ($ip['ban_time'] + $this->bantime)){
                    $this->save();
                    return false;
                } else {
                    unset($this->banned[$key]);
                }
            }
        }
        
        $this->strainer['ips'][] = $this->ip;
        
        $this->save();
        return true;

    }
    
    
    private function save(){
        
        file_put_contents($this->save_directory . '/strainer.dat', serialize($this->strainer));
        file_put_contents($this->save_directory . '/banned.dat', serialize($this->banned));
        
    }
    
    private function buildStrainerFile(){
        
        $data = array(
            'build_time'    => time(),
            'ips'           => array()
        );
        file_put_contents($this->save_directory . '/strainer.dat', serialize($data));
        
    }
    
    private function buildBannedFile(){
        
        $data = array();
        file_put_contents($this->save_directory . '/banned.dat', serialize($data));
        
    }
    
    // https://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
    private function in_array_r($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }
    
}