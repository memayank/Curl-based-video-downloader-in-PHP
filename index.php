<?php

if(isset($_GET['id'])){
   $videoId = $_GET['id']; 
}
$videoId = "SMmj_qAbyeM";

 

$videoFetchUrl = "http://www.youtube.com/get_video_info?&video_id=" . $videoId. "&asv=3&el=detailpage&hl=en_US";



$curl = curl_init($videoFetchUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$videoData = curl_exec($curl);
curl_close($curl);

$vidArr;
parse_str($videoData,$vidArr);

$vidArr = json_decode(json_encode($vidArr));

if(!isset($vidArr->url_encoded_fmt_stream_map)){
    die("Unable to download this video");
}

$streamFormats = explode(',',$vidArr->url_encoded_fmt_stream_map);






parse_str($streamFormats[0],$format);



if (isset($video_info->adaptive_fmts)) {
    $streamSFormats = explode(",", $video_info->adaptive_fmts);
    $pStreams = parseStream($streamSFormats);
}
$cStreams = parseStream($streamFormats);

function parseStream($stream){
    $avialable_formats = [];
    foreach($stream as $format){
        parse_str($format,$format_info);
        if (isset($format_info['bitrate'])){
            $quality = isset($format_info['quality_label'])?$format_info['quality_label']:round($format_info['bitrate']/1000). 'k';
        }
        else{
            $quality = isset($format_info['quality'])?$format_info['quality']:'';
        }
        switch ($quality) {
            case 'hd720':
                $quality = "720p";
                break;
            case 'medium':
                $quality = "360p";
                break;
            case 'small':
                $quality = "240p"; // May Less
                break;
        }
        $type = explode(";", $format_info['type']);
        $type= $type[0];
        switch ($type) {
            case 'video/3gpp':
                $type = "3GP";
                break;
            case 'video/mp4':
                $type = "MP4";
                break;
             case 'video/webm':
                $type = "WebM";
                break;
        }
        $available_formats[] = [
            'itag' =>  $format_info['itag'],
            'quality' => $quality,
            'type' => $type,
            'url' => $format_info['url'],
            'size' => getSize($format_info['url']),

        ];
    }
    return $available_formats;
}


function getSize($url)
{

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $r = curl_exec($ch);

    foreach (explode("\n", $r) as $header) {
        if (strpos($header, 'Content-Length:') === 0) {
            return intval(intval(trim(substr($header, 16)))/ (1024*1024)) . " MB";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Video Title</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
  
 <div class="container">   
   <div class="video">
   <?php
    echo '<iframe width="100%" height="400px" src="https://www.youtube.com/embed/'.$videoId. '"frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
   ?>
   
    <div class="download">
        <?php
         foreach($cStreams as $streams){
             $streams = json_decode(json_encode($streams));
             echo $streams -> quality ."  ".$streams->size."  "."<a href='$streams->url'>Download</a>";         
         }
        ?>
    </div>      
    </div>
    
 </div>
   
</body>

</html>
