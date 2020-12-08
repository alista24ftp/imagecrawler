<?php
class ImageHandler
{
    protected $mimeTypes;

    public function __construct()
    {
        $this->mimeTypes = [
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpg',
            'gif' => 'image/gif',
            'png' => 'image/png',
        ];
    }

    // Downloads file from src and place into dest_folder
    // Returns full downloaded file path or null upon failure
    public function download($src, $src_host, $img_name=null, $dest_root, $dest_folder="downloads/images")
    {
        $transformed_src = $this->transformImageName($src, $src_host);
        if(is_null($img_name)) return null; //$img_name = "";
        $dest_path = "/$dest_folder";
        $full_dest_path = $dest_root . $dest_path;
        $full_img_name = "$full_dest_path/$img_name";
        if(!file_exists($full_dest_path) || !is_dir($full_dest_path)){
            if(!mkdir($full_dest_path, 0777, true)){
                return null;
            }
        }
        $ch = curl_init($transformed_src);
        $fp = fopen($full_img_name, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $full_img_name;
    }

    // Generates full URL of image path
    // eg. '/uploads/image.jpeg' becomes 'http://imghost.com/uploads/image.jpeg'
    // eg. '//imghost.com/uploads/image.jpeg' becomes 'http://imghost.com/uploads/image.jpeg'
    private function transformImageName($img_name, $img_host)
    {
        if(empty($img_name)) return '';
        $new_name = trim($img_name);
        if(empty($new_name) || strlen($new_name) < 3) return "";
        if(strtolower(substr($new_name, 0, 4)) == "http") return $new_name;
        if($new_name[0] == '/' && $new_name[1] == '/') return "http:$new_name";
        if($new_name[0] == '/') return $img_host . $new_name;
        return "$img_host/$new_name";
    }

    // Gets file extension
    public function getExtension($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return $ext;
    }

    public function getMimeType($file_path)
    {
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        return array_key_exists($ext, $this->mimeTypes) ? $this->mimeTypes[$ext] : null;
    }
}