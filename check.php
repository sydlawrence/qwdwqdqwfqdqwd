<?php

$face_config = array(
  "key" => "33bc5c62f55d4c8ea5654c979e7dd55b",
  "secret" => "90d1fc553f20b83b662899a79e333739",
);



function namespaces() {
  return array(
    "sydlawrence@facebook.com",
  );
}




//load the client PHP library
require_once(__DIR__ . "/FaceRestClient.php");
//initialize API object with API key and Secret
$api = new FaceRestClient($face_config['key'], $face_config['secret']);
//the list of private-namespace UIDs to train and search for
$uids = namespaces();
//train the face.com index with the new uids (need to be called only once)
foreach ($uids as $uid)
    train($api, $uid);
//search in untagged photos for these uids (can be called multiple times)
search($api, $uids);
/*
    adds the passed uid to the face.com index
*/
function train(&$api, $uid)
{
    //obtain photos where this uid appears in (limit to 30)
    $urls = getTrainingUrls($uid);
    $urls = array_splice($urls, 0, 30);
    //run face detection on all images
    $tags = $api->faces_detect($urls);
    if (empty($tags->photos))
        return false;
    //build a list of tag ids for training
    $tids = array();
    foreach ($tags->photos as $photo)
    {
        //skip errors
        if (empty($photo->tags))
            continue;
        //skip photos with multiple faces (want to make sure only the uid appears)
        if (count($photo->tags) > 1)
            continue;
        $tid = $photo->tags[0]->tid;
        $tids[] = $tid;
    }
    //if faces were detected, save them for this uid
    if (count($tids) > 0)
        $api->tags_save($tids, $uid, $uid);
    //train the index with the newly saved tags
    $api->faces_train($uid);
    //all done, recognition for $uid can now begin
    return true;
}
/*
    searches for uids within photos
*/
function search(&$api, $uids)
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
            print_r($response);
            foreach ($response->photos as $photo)
            {
                //skip empty tags and errors
                if (empty($photo->tags))
                    continue;
                $url = $photo->url;
                //echo all found tags
                foreach ($photo->tags as $tag)
                {
                    if (!empty($tag->uids))
                    {
                        //only interested in highest score for this tag
                        $uid = $tag->uids[0]->uid;
                        $conf = $tag->uids[0]->confidence;
                        //only print if confidence is higher than recommended threshold
                        if ($conf >= $tag->threshold)
                            echo "$uid was found in url $url ($conf % confidence)n";
                    }
                }
            }
            $urls = array();
        }
    }
}
function getTrainingUrls($uid)
{
    //return an array of urls pointing to known photos of the uid
    return array();
}
function getPhotoUrls()
{
    //return an array of urls pointing to any photos on the web
    //to search in for the trained $uids
    return array(
      "http://farm6.static.flickr.com/5256/5430291989_9e51c5418c.jpg",
    );
}
?>