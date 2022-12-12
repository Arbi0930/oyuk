<?php


include 'WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;


WebClientPrint::$wcpCacheFolder = getcwd().'/wcpcache/';

if (file_exists(WebClientPrint::$wcpCacheFolder) == false) {
    //create wcpcache folder
    $old_umask = umask(0);
    mkdir(WebClientPrint::$wcpCacheFolder, 0777);
    umask($old_umask);
}


WebClientPrint::cacheClean(30); 


$urlParts = parse_url($_SERVER['REQUEST_URI']);
if (isset($urlParts['query'])){
    $query = $urlParts['query'];
    parse_str($query, $qs);
    
    //get session id from querystring if any
    $sid = NULL;
    if (isset($qs[WebClientPrint::SID])){
        $sid = $qs[WebClientPrint::SID];
    }
    
    try{
        $reqType = WebClientPrint::GetProcessRequestType($query);
        
        if($reqType == WebClientPrint::GenPrintScript ||
           $reqType == WebClientPrint::GenWcppDetectScript){
           
            $currentAbsoluteURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            $currentAbsoluteURL .= $_SERVER["SERVER_NAME"];
            if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
            {
                $currentAbsoluteURL .= ":".$_SERVER["SERVER_PORT"];
            } 
            $currentAbsoluteURL .= $_SERVER["REQUEST_URI"];
            $currentAbsoluteURL = substr($currentAbsoluteURL, 0, strrpos($currentAbsoluteURL, '?'));
            
            ob_start();
            ob_clean();
            header('Content-type: text/javascript');
            echo WebClientPrint::generateScript($currentAbsoluteURL, $query);
            return;
        } 
        else if ($reqType == WebClientPrint::ClientSetWcppVersion)
        {
          
            if(isset($qs[WebClientPrint::WCPP_SET_VERSION]) && strlen($qs[WebClientPrint::WCPP_SET_VERSION]) > 0){
                WebClientPrint::cacheAdd($sid, WebClientPrint::WCP_CACHE_WCPP_VER, $qs[WebClientPrint::WCPP_SET_VERSION]);
            }
            return;
        }
        else if ($reqType == WebClientPrint::ClientSetInstalledPrinters)
        {
           
            WebClientPrint::cacheAdd($sid, WebClientPrint::WCP_CACHE_PRINTERS, strlen($qs[WebClientPrint::WCPP_SET_PRINTERS]) > 0 ? $qs[WebClientPrint::WCPP_SET_PRINTERS] : '');
            return;
        }
        else if ($reqType == WebClientPrint::ClientSetInstalledPrintersInfo)
        {
            
            $printersInfo = $_POST['printersInfoContent'];
            
            WebClientPrint::cacheAdd($sid, WebClientPrint::WCP_CACHE_PRINTERSINFO, $printersInfo);
            return;
        }
        else if ($reqType == WebClientPrint::ClientGetWcppVersion)
        {
            ob_start();
            ob_clean();
            header('Content-type: text/plain');
            echo WebClientPrint::cacheGet($sid, WebClientPrint::WCP_CACHE_WCPP_VER);
            return;    
        }
        else if ($reqType == WebClientPrint::ClientGetInstalledPrinters)
        {
            
            ob_start();
            ob_clean();
            header('Content-type: text/plain');
            echo base64_decode(WebClientPrint::cacheGet($sid, WebClientPrint::WCP_CACHE_PRINTERS));
            return;
        }    
        else if ($reqType == WebClientPrint::ClientGetInstalledPrintersInfo)
        {
            
            ob_start();
            ob_clean();
            header('Content-type: text/plain');
            echo base64_decode(WebClientPrint::cacheGet($sid, WebClientPrint::WCP_CACHE_PRINTERSINFO));
            return;
        }    
    }
    catch (Exception $ex)
    {
        throw $ex;
    }
    
} 
