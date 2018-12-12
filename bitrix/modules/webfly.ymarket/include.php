<?
//Simple agent
function wfYmarketAgent() {
    $agentFolder = COption::GetOptionString("webfly.ymarket", "agentFolder", "/y-market/", false, false);
    BXClearCache(true, "/y-market/");
    BXClearCache(true, $agentFolder);
    if ($_SERVER['SERVER_NAME'])
        $serverpath = $_SERVER['SERVER_NAME'];
    else
        $serverpath = COption::GetOptionString("main", "server_name");
    //if server_name is ip: ger server_name from site settings
    $s_res = preg_match("/^\\d{1,3}\.\\d{1,3}\.\\d{1,3}\.\\d{1,3}$/",$serverpath);
    if ($s_res==1){
        $serverpath = COption::GetOptionString("main", "server_name");
    }
    $ch = curl_init();

// set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $serverpath . $agentFolder);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 40);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 0);


// grab URL and pass it to the browser
    curl_exec($ch);

// close cURL resource, and free up system resources
    curl_close($ch);
    return "wfYmarketAgent();";
}

//Agent for infinite templates
function wfYmarketAgentInfinite() {
    $agentFolder = COption::GetOptionString("webfly.ymarket", "agentFolder", "/y-market/", false, false);
    if ($_SERVER['SERVER_NAME'])
        $serverpath = $_SERVER['SERVER_NAME'];
    else
        $serverpath = COption::GetOptionString("main", "server_name");
     //if server_name is ip: ger server_name from site settings
    $s_res = preg_match("/^\\d{1,3}\.\\d{1,3}\.\\d{1,3}\.\\d{1,3}$/",$serverpath);
    if ($s_res==1){
        $serverpath = COption::GetOptionString("main", "server_name");
    }
    $urlMain = $serverpath . $agentFolder;
    BXClearCache(true, "/y-market/");
    BXClearCache(true, $agentFolder);

    $ch = curl_init();
    do {
        if ($matches[1])
            $url = $urlMain . "?WF_PAGE=" . $matches[1];
        else
            $url = $urlMain;
        $content = WFcurlExec($url, $ch);

        $content = strip_tags($content, "<script>");

        preg_match('@webflyJsPage\s*\=\s*(\d+)@', $content, $matches);
    }while ($matches[1]);

// close cURL resource, and free up system resources
    curl_close($ch);
    return "wfYmarketAgentInfinite();";
}
//function for infinite templates agent
function WFcurlExec($url, $ch) {

// set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 300);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 0);

// grab URL and pass it to the browser
    $content = curl_exec($ch);
    return $content;
}

/*
  function yaMarketAgent() {
  BXClearCache(true, "/y-market");
  $obHttp = new CHTTP();
  $obHttp->setFollowRedirect(true);
  $obHttp->setRedirectMax(20);
  $obHttp->HTTPQuery('GET', 'http://site_name.ru/marketypb/');
  return "yaMarketAgent();";
  }
 */
?>