<?php
session_start();

$face_config = array(
  "key" => "33bc5c62f55d4c8ea5654c979e7dd55b",
  "secret" => "90d1fc553f20b83b662899a79e333739",
);







//load the client PHP library
require_once("FaceRestClient.php");
require_once("facebook.php");



//initialize API object with API key and Secret
$api = new FaceRestClient($face_config['key'], $face_config['secret']);


$session_id = $_SESSION['fb_access_token'];
$session_id = explode('|',$session_id);
$session_id = $session_id[0];

//2377433777

// fb login
$api->setFBUser($_SESSION['fb_user']->id,$session_id,$_SESSION['fb_access_token']);



//the list of private-namespace UIDs to train and search for
$uids = namespaces();

$threshold = 1;


//search in untagged photos for these uids (can be called multiple times)
search($api, $uids,$threshold);




function render_possible($uid,$url,$confidence) {
    $id = str_replace('@facebook.com','',$uid);
    $fb_data = file_get_contents('https://graph.facebook.com/'.$id."?access_token=".$_SESSION['fb_access_token']);
    $fb_data = json_decode($fb_data);
    $name = $fb_data->name;
    
    
    echo "<p>$name was <b>found</b> in url $url ($confidence % confidence)</p>";
    
    echo "<img src='http://graph.facebook.com/".$id."/picture?type=large'> = <img src='".$url."'/>";
    
    
    

}



/*
    searches for uids within photos
*/
function search(&$api, $uids,$threshold)
{
    //obtain list of photos to search in
    $photoUrls = getPhotoUrls();
    $urls = array();
    $count = 0;
    foreach ($photoUrls as $photoUrl)
    {
        //max photos per recognition call is 30, so break photos to groups if needed
        $urls[] = $photoUrl;
        $count++;
        if (($count % 30) == 0 || $count == count($photoUrls))
        {
            $response = $api->faces_recognize($urls, $uids);
            
           // echo "<pre>".print_r($response,true)."</pre>";
            
            foreach ($response->photos as $photo)
            {
                //skip empty tags and errors
               // if (empty($photo->tags))
                 //   continue;
                $url = $photo->url;
                //echo all found tags
                foreach ($photo->tags as $tag)
                {
                    
                    if (!empty($tag->uids))
                    {
                        //only interested in highest score for this tag
                        foreach ($tag->uids as $ui) {
                          $uid = $ui->uid;
                          $conf = $ui->confidence;
                          //only print if confidence is higher than recommended threshold
                          if ($conf >= $threshold)
                            render_possible($uid,$url,$conf);
                        }
                    }
                }
            }
            $urls = array();
        }
    }
}

function getPhotoUrls()
{
    //return an array of urls pointing to any photos on the web
    //to search in for the trained $uids
    include('images.php');
    return $images;
    
}

function namespaces() {
  return array(
    "friends@facebook.com",
    $_SESSION['fb_user']->id."@facebook.com"
  );
}





?>