<?php
/*
    Template Name: JSON-Validate
*/

$proxy = $_GET['proxy'];
$ipprx = $_GET['ipprx'];
$qs = preg_replace('/[\?&]*(?:proxy=.{0,3}|ipprx=.{0,22})(?:&|$)/i', '', urldecode($_SERVER['QUERY_STRING']));
$url = preg_replace('/[\?&]*url=(.+)/i', '\1', $qs);
$url = preg_replace('/(?:\/edit\??.*|\/view\??.*)/i', '/export?format=csv', $url);
if ($url == 'url=') $url = '';
if (preg_match('/^\s*http.{1,5}\/\/[a-z0-9]{0,10}.?area59aa\.org/i', $url)) {
  $url = preg_match('/meeting.guide.v1.external.feed./i', $url) ? $url : '';
}
$count = ($url != '') ? 0 : -1;

$ipsrc = file_get_contents('https://ipecho.net/plain', false) . ($proxy == '1' ? '&nbsp; ➜ &nbsp;' . $ipprx : '');
$ipdst = gethostbyname(parse_url($url, PHP_URL_HOST));
$ipinfo = ' | &nbsp;&nbsp;&nbsp;'  . $ipsrc . '&nbsp; ➜ &nbsp;' . $ipdst;

$resp = get_json_feed($url, $proxy, $ipprx);
$json = $resp['json'];
$count = $resp['count'];
$msg = $resp['msg'];
$err = $resp['error'];

?>

<!DOCTYPE html>
<html>

<head>
  <title>Area 59 JSON Feed Validator</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://meetingguide.org/css/app.css">
  <link rel="mask-icon" href="https://meetingguide.org/img/meeting-guide-favicon.png" color="#00437c">
  <link rel="icon" type="image/png" href="https://meetingguide.org/img/meeting-guide-favicon.png">
  <style type="text/css">
    aside {
      display: none !important;
    }
  </style>
  <script>
    window.onload = function() {
      history.pushState({}, null, unescape(location.href).replace(/[\?&]ipprx=$/, '').replace(/[\?&]url=$/i, ''));
    }

    function proxyClick() {
      chk = document.getElementById('proxy').checked;
      document.getElementById('divProxy').style.display = chk ? "block" : "none";
      document.getElementById('proxy').value = chk ? "1" : "";
      if (!chk) document.getElementById('ipprx').value = "";
    }
  </script>
</head>

<body class="validate">

  <nav class="navbar navbar-expand-lg fixed-top navbar-dark" id="navbar">
    <div class="container" style="line-height:0;">
      <a class="navbar-brand" href="/">Area 59 JSON Validator</a>
      <div style="height:20px; margin-left:55px; color: white; font-size:20px"><?php echo $ipsrc ?>
      </div>
      <div class="collapse navbar-collapse" id="main-nav"> </div>
    </div>
  </nav>

  <main>
    <div class="container page">
      <div class="row">
        <div class="col-md-12">
          <p class="lead" style="margin-top:-10px; margin-bottom:10px; font-weight:350; line-height:25px;" ; style="margin-top:-10px">
            This validator checks if the <span style="font-weight:450">Area 59</span> website can load a JSON feed.</p>
          <p style="line-height:25px;">For more info on the Meeting Guide API, check out the <a href="https://github.com/meeting-guide/spec" zoompage-fontsize="17">specification</a>.</p>
          <form action="/jsonvalidate" method="get">
            <div class="input-group">
              <input type="url" name="url" class="form-control" value="<?php echo $url; ?>" placeholder="https://distirctwebsite.org/jsonfeed/">
              <div class="input-group-append">
                <input type="submit" class="btn btn-outline-secondary" value="Check Feed">
              </div>
              <div class="input-group-append" style="margin-left:15px; width:25px">
                <input type="checkbox" id="proxy" name="proxy" onclick="proxyClick();" class="form-control" <?php echo $proxy == 1 ? "checked" : "";  ?> value="<?php echo $proxy; ?>" placeholder="ipaddr:port">
              </div>
            </div>
            <div id="divProxy" style="margin:10px 0 10px; display:<?php echo $proxy == 1 ? 'block' : 'none'; ?>;">
              <input style="display:inline-block; width:185px;" type=" text" id="ipprx" name="ipprx" class="form-control" value="<?php echo $ipprx; ?>" placeholder="ipaddr:port">
            </div>
          </form>
           <?php 
          if ($count > 0) {
            echo '<div class="alert alert-success" style="font-weight:500; font-size:17px">The feed is <b>valid</b> and returned <b>' . $count . '</b> meetings</div>';
            echo '<div class="lead" style="font-size:13px; margin:-10px 0 5px 0; line-height:1.1em">' . $url . $ipinfo . '</div>';
            echo '<pre id="output"><code class="language-json">' . print_r($json, true) . '</code></pre>';
          } 
          else if ($count == 0) {
            echo '<div class="alert alert-danger" style="font-weight:500; font-size:17px">' . $msg . $ipinfo . '</div>';
            echo '<div class="lead" style="font-size:13px; margin:-10px 0 5px 0; line-height:1.1em">' . $url . '</div>';
            echo '<pre id="output"><code class="language-html">' . print_r($err, true) . '</code></pre>';
          } ?>
      </div>
    </div>
  </main>

</body>

</html>