<?php

class Translation {

    private static $translate_items=array();

    public static function getText($id, $default="", $type=""){
        if($id === "") return $default;
        if(empty(self::$translate_items)){
            $translate_objs=DBTranslation::getInstance()->setFields(array())->load();
            foreach($translate_objs as $translate_obj){
                $item_id=$translate_obj->getTranslateId();
                $text=$translate_obj->getTranslateText();
                self::$translate_items[$item_id]=$text;
            }
        }
        $ret=isset(self::$translate_items[$id]) ? self::$translate_items[$id] : "";
        $ret=$ret !== "" ? $ret : $default;
        if($type !== "nowrap") $ret=create_tag("span", array("id" => "lang_".$id, "class" => "translate-items"), $ret);
        return $ret;
    }

    public static function editText($id, $text){
        if(!User::getInstance()->isAdmin()) return false;
        $translate_objs=DBTranslation::getInstance()->loadByQuery("WHERE translate_id='$id'");
        $translate_obj=reset($translate_objs);
        $translate_obj->setSaveEnabled(true);
        $translate_obj->setTranslateId($id);
        $translate_obj->setTranslateText($text);
        $translate_obj->save();
        return true;
    }

    public static function processAttributes($translate_item){
        $translate_id=isset($translate_item["id"]) ? $translate_item["id"] : "";
        $text_default=isset($translate_item["default"]) ? $translate_item["default"] : "";
        $translate_type=isset($translate_item["type"]) ? $translate_item["type"] : "none";
        return self::getText($translate_id, $text_default, $translate_type);
    }

    public static function displayEdit(&$content){
        $result="";
        if(User::getInstance()->isAdmin()) $result=get_template("translate_edit");
        $content=str_replace("::translate_edit::", $result, $content);
    }

    public static function replaceText(&$content){
        $matches=array();
        preg_match_all("@lang\((.*)\)@sU", $content, $matches);
        foreach($matches[1] as $index => $value){
            if(isset($matches[0][$index])){
                $text=self::processAttributes(parse_attributes($value));
                $content=str_replace($matches[0][$index], $text, $content);
            }
        }
    }

    public static function replaceAll(&$content){
        self::replaceText($content);
        self::displayEdit($content);
    }

}