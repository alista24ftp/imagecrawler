<?php
interface Crawler
{
    public function getHtml();
    public function getJsonStr($html);
}