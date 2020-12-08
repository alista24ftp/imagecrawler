<?php
class PatternUtil
{
    public function getByPattern($pattern, $html)
    {
        preg_match($pattern, $html, $res);
        if(empty($res))
            return null;
        return trim($res[1]);
    }

    public function getAllByPattern($pattern, $html)
    {
        if(!preg_match_all($pattern, $html, $res)) return null;
        return $res;
    } 
}