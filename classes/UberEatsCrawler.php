<?php
class UberEatsCrawler implements Crawler
{
    public function __construct($url, $patternUtil, $imgHandler)
    {
        $this->url = $url;
        $this->patternUtil = $patternUtil;
        $this->imgHandler = $imgHandler;
    }

    public function getHtml()
    {
        $context = stream_context_create(array('http'=>array('ignore_errors'=>true)));
        $html = @file_get_contents($this->url, false, $context);
        $html = preg_replace("/[\t\n\r]+/", "", $html);
        return $html;
    }

    public function getJsonStr($html)
    {
        $json = $this->patternUtil->getByPattern("/id=\"__REDUX_STATE__\">(.*?)<\//", $html);
        if(!is_null($json)){
            $json = preg_replace("/\\\u0022/", "\"", $json);
        }
        return $json;
    }

    public function getItemUuids($json)
    {
        $itemUuids = [];
        $uuidMatches = $this->patternUtil->getAllByPattern("/itemUuids\":\[(.*?)\]/", $json);
        if(!empty($uuidMatches)){
            foreach($uuidMatches[1] as $k => $match){
                $match = preg_replace("/\"/", "", $match);
                $uuids = explode(",", $match);
                $itemUuids = array_merge($itemUuids, $uuids);
            }
        }
        return $itemUuids;
    }

    public function getAllImgs($rootpath)
    {
        $images = [];
        $json = $this->getJsonStr($this->getHtml());
        $itemUuids = $this->getItemUuids($json);
        foreach($itemUuids as $uuid){
            $title = $this->patternUtil->getByPattern("/$uuid\":\{.*?title\":\"(.*?)\"/", $json);
            $imageUrl = $this->patternUtil->getByPattern("/$uuid\":\{.*?imageUrl\":\"(.*?)\"/", $json);
            if(!empty($title) && !empty($imageUrl)){
                $title = preg_replace("/%25/", "%", $title);
                $title = preg_replace("/\\\u0026/", "&", $title);
                $ext = $this->imgHandler->getExtension($imageUrl);
                $img_name = "$title.$ext";
                // download image into images folder in the format '<title>.<ext>'
                $img_path = $this->imgHandler->download($imageUrl, 'https://d1ralsognjng37.cloudfront.net', $img_name, $rootpath);
                if(!empty($img_path)){
                    //array_push($images, $img_path);
                    $images[$img_name] = $img_path;
                }
            }
        }
        return $images;
    }
}