<?php

date_default_timezone_set('UTC');
get_external_feed("50a");

function get_external_feed($id) {
  $feeds = [
    '50' => 'https://docs.google.com/spreadsheets/u/1/d/e/2PACX-1vRxJjf0wMdj_DBWSWLRsYbxxEFUynje2hmItvmePXDCO_3UVLk52GtCgnOBIJHA035mLCzn4vUpWzEX/pub?gid=874548057&single=true&output=csv', 
    '50a' => 'https://script.google.com/a/area59aa.org/macros/s/AKfycbxC7DJPMuw2sV-qIz_Ylp-IL_DuB6KKpqkLTs9J1DPq9MhEJF-PsqW9Frt-4-7ON7aXtg/exec?id=50'
  ];
  
  // check ID and  URL
 // $id = $request['feed_id'];
  $url = $feeds[$id];
  //if ($url == null) {
    //wp_send_json_error(new WP_Error('invalid_feed_id', 'No JSON Feed found for ID: ' . $id));
  //  return;
 // }
  //print_r($url);
 
  // get JSON 
  $resp = get_json_feed($url, true);
  //if($resp['error'] != '')  {
  //  wp_send_json_error(new WP_Error('json_error', array('error' => $resp['msg'], 'error_detail' => $resp['error'])));
 //   return;
 // }
 

  // send JSON
 var_export($resp['json']);
}

function get_json_feed($url, $isFeed = false, $proxy = '', $ipprx = '')
{
  if ($url == '') return array('json' => '', 'msg' => '', 'error' => '');

 $ch = curl_init();
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_FAILONERROR, true);
 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
 curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
 curl_setopt($ch, CURLOPT_TIMEOUT, 30);
 if ($ipprx != '') curl_setopt($ch, CURLOPT_PROXY, $ipprx);
 $resp = ['error' => '', 'body' => curl_exec($ch)];
 //var_dump($resp);
 $rc = curl_errno($ch);
 if ($rc == '0') {
    $rc = '200';
  }
   else {
    $msg = curl_error($ch);
    $resp['error']  = 'cURL: ' . $msg;
    if (empty($resp['http_response'])) $resp['http_response'] = $resp;
    if ($rc == '22') $rc = substr($msg, -3);
  }
  curl_close($ch);
 

  // specific http errors
  if ($rc == '404') {
    $msg = 'ERROR 404 | Page not found.';
    $err = text_clean(print_r($resp['http_response'], true));
  } else if ($rc == '401') {
    $msg = 'ERROR 401 | Unauthorized.';
    $err = text_clean(print_r($resp['http_response'], true));
  }
  // other http errors
  else if ($rc != '200') {
    $msg = 'ERROR ' . $rc . ' | ' . text_clean(print_r($resp['error'], true));
    $err = text_clean(print_r($resp['http_response'], true));
  }
  // google sheet
  else if (preg_match('/google.+(?:export.+format|\/pub\?.+output)=csv/i', $url) && ($json = csv_json($resp['body']))) {
    // check for "slug" in feed
    if (array_key_exists('slug', $json[0]) || array_key_exists('Slug', $json[0])) {
      $count = count($json);
    }
    // multiple rows; likely data format error
    else if (count($json) > 1) {
      $msg = 'INVALID data format returned by feed.';
      $err = $json;
    }
    // single row; generic error
    else {
      $msg = 'ERROR loading Google Sheet.';
      $err = text_clean(print_r($resp['body'], true), true);
    }
  }
  // no JSON found
  else if (substr($resp['body'], 0, 2) != '[{') {
    $msg = 'INVALID data format | No JSON data found.';
    $err = text_clean(print_r($resp['body'], true), true);
  }
  // JSON feed data
  else if ($json = json_decode($resp['body'], true)) {
    // check for "slug" in feed
    if (array_key_exists('slug', $json[0]) || array_key_exists('Slug', $json[0])) {
      $count = count($json);
    }
    // invalid JSON data format
    else {
      $msg = 'ERROR parsing feed data.  ' . print_r($json['error'], true);
      $err = text_clean(print_r($resp['body'], true));
    }
  }
  // JSON parse error
  else {
    $msg = 'JSON Error Code: <b>' . json_last_error() . '</b> | ' . json_last_error_msg();
    $err = text_clean(print_r($resp['body'], true), true);
  }

  // return JSON and any messages
  return array('json' => $json, 'count' => $count, 'msg' => $msg, 'error' => $err);
}

function csv_json($arr)
{
  $dayList = [
    'Sunday' => '0', 'Monday' => '1', 'Tuesday' => '2', 'Wednesday' => '3'
  , 'Thursday' => '4', 'Friday' => '5', 'Saturday' => '6'];
  
  $fieldList = [
    'Name' => 'name',
    'Slug' => 'slug',
    'Day' => 'day',
    'Time' => 'time', 
    'End Time' => 'end_time',
    'Time Formatted' => 'time_formatted', 
    'Types' => 'types', 
    'Notes' => 'notes',
    'Location' => 'location',
    'Location Notes' => 'location_notes',
    'Address' => 'formatted_address',
    'Region' => 'region', 
    'Conference URL' => 'conference_url',
    'Conference Phone' => 'conference_phone',
    'Conference URL Notes' => 'conference_url_notes', 
    'Group' => 'group',
    'District' => 'district',
    'Updated' => 'updated',
    'PayPal' => 'paypal',  
    'Square' => 'square',
    'Venmo' => 'venmo',
    'Website' => 'website', 
    'Email' => 'email',
    'Phone' => 'phone',
    'Group Notes' => 'group_notes',
    'Conference Phone Notes' => 'conference_phone_notes',
    'Latitude' => 'latitude',
    'Longitude' => 'longitude',
    'Coordinates' => 'coordinates',
    'Approximate' => 'approximate',
    'Districts' => 'districts', 
    'Regions' => 'regions',
    'Sub District' => 'sub_district',
    'Sub Region' => 'sub_region',
    'Edit URL' => 'edit_url'
  ];
  
  $meetingType = [
    'Big Book' => 'B',
    'Discussion' => 'D',
    'Literature' => 'LIT',
    'Meditation' => 'MED',
    'Beginner' => 'BE',
    'Newcomer' => 'BE',
    'Open' => 'O',
    'Open Discussion' => 'O,D', 
    'Open/Discussion' => 'O,D', 
    'Speaker' => 'SP',
    'Step' => 'ST', 
    'Step/Tradition' => 'ST,TR', 
    'Tradition' => 'TR',
    'Grapevine' => 'GR',
    'As Bill Sees It' => 'ABSI',
    'Came to Believe' => 'LIT', 
    'Daily Reflections' => 'DR',
    'Living Sober' => 'LS',
    '11th Step' => '11',
    '11th Step Meditation' => '11',
    '12 Steps & 12 Traditions' => '12x12',
    '12 & 12' => '12x12',
    'Online' => 'ONL',
    'Online Discussion' => 'ONL,D',
    'Birthday' => 'H',
    'Breakfast' => 'BRK',
    'Candlelight' => 'CAN',
    'Outdoor' => 'OUT',
    'Men' => 'M',
    'Women' => 'W',
    'Professionals' => 'P',
    'Secular' => 'A',
    'Seniors' => 'SEN',
    'Young People' => 'Y',
    'Gay' => 'G',
    'Lesbian' => 'L',
    'LGBTQ' => 'LGBTQ',
    'Transgender' => 'T',
    'People of Color' => 'POC',
    'American Sign Language' => 'ASL',
    'Babysitting Available' => 'BA',
    'Child-Friendly' => 'CF',
    'Wheelchair Access' => 'X',
    'Wheelchair-Accessible Bathroom' => 'XB',
    'Cross Talk Permitted' => 'XT',
    'Digital Basket' => 'DB',
    'Dual Diagnosis' => 'DD',
    'Fragrance Free' => 'FF',
    'Smoking Permitted' => 'SM',
    'Non-Smoking' => 'NS',
    'Closed' => 'C',
    'Location Temporarily Closed' => 'TC',
    'Concurrent with Al-Anon' => 'AL-AN',
    'Concurrent with Alateen' => 'AL',
    'English' => 'EN',
    'French' => 'FR',
    'Indigenous' => 'NDG',
    'Italian' => 'ITA',
    'Japanese' => 'JA',
    'Korean' => 'KOR',
    'Native American' => 'N',
    'Polish' => 'POL',
    'Portuguese' => 'POR',
    'Punjabi' => 'PUN',
    'Russian' => 'RUS',
    'Spanish' => 'S',
   ];

  $csv =  explode("\n", $arr);  
  $h = preg_replace_callback('~^-?\K.*$~', fn($c) => $fieldList[$c[0]] ?? $c[0], str_getcsv(array_shift($csv)));
  $data = array_map(fn ($r) => array_combine($h, str_getcsv($r)), array_slice($csv, 0, 1));
  //$data = array_map(fn ($r) => array_combine($h, str_getcsv($r)), $csv);
  
  foreach ($data as &$row) {
    // 24-hour time
    foreach (array_filter(['time', 'end_time'], fn($x) => array_key_exists($x, $row)) as $col) {
      $row[$col] = date('H:i', strtotime($row[$col]));
    }
    // day name to number
    foreach (array_filter(['day'], fn($x) => array_key_exists($x, $row)) as $col) {
      $row[$col] = $dayList[$row[$col]] ?? $row[$col];
    }
    // meeting types to codes
    foreach (array_filter(['types'], fn($x) => array_key_exists($x, $row)) as $col) {
      $types = [];
      // loop through each raw meeeting type
      foreach (array_map("trim", explode(",", preg_replace('/(?:Meeting|Study)/i', '', $row[$col]))) as $x) { 
        $m = $meetingType[$x];
        if ($m != null) {
          // loop through each code (if map to multiple)
          foreach(array_map("trim", explode(",", $m)) as $t) {
            array_push($types, $t);
          }
        }
      }
      // push final array of types
      $row[$col] = $types;
    }
  }
  
  $json = json_decode(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE), true);
  return $json;
}

function text_clean($s, $full = false)
{
  if ($full) $s = str_replace(">", ">\n", str_replace(" \n", "", str_replace("  ", " ", str_replace(" ", " ", $s))));
  return trim(htmlspecialchars($s));
}

function json_clean($s) {
  $s = preg_replace('/(?:\s|\\n|&.{1,5};)+/', ' ', str_replace("=>", "=|", $s));
  return trim(htmlspecialchars($s));
}

?>
