<?php

namespace App\Facades\Helpers;
use Html;

class Form
{
    public function open(array $options = [])
    {
        $method = isset($options['method']) ? $options['method'] : 'POST';
        $action = isset($options['action']) ? $options['action'] : '';
        $action = isset($options['url']) ? $options['url'] : '';
        unset($options['method']);
        unset($options['action']);
        unset($options['url']);
        return Html::form($method, $action)->attributes($options)->open();
    }
    
    // public function model($model, array $options = [])
    // {

    // }
    
    // public function setModel($model)
    // {

    // }
    
    // public function getModel()
    // {

    // }
    
    public function close()
    {
        return Html::form()->close();
    }
    
    // public function token()
    // {

    // }
    
    public function label($name, $value = null, $options = [], $escape_html = true)
    {
        return Html::label($value, $name)->attributes($options);
    }
    
    // public function input($type, $name, $value = null, $options = [])
    // {

    // }
    
    public function text($name, $value = null, $options = [])
    {
        return Html::text($name, $value)->attributes($options);
    }
    
    public function password($name, $options = [])
    {
        return Html::password($name)->attributes($options);
    }
    
    // public function range($name, $value = null, $options = [])
    // {

    // }
    
    public function hidden($name, $value = null, $options = [])
    {
        return Html::hidden($name, $value)->attributes($options);
    }
    
    // public function search($name, $value = null, $options = [])
    // {

    // }
    
    public function email($name, $value = null, $options = [])
    {
        return Html::email($name, $value)->attributes($options);
    }
    
    // public function tel($name, $value = null, $options = [])
    // {

    // }
    
    public function number($name, $value = null, $options = [])
    {
        return Html::number($name, $value)->attributes($options);
    }
    
    // public function date($name, $value = null, $options = [])
    // {

    // }
    
    // public function datetime($name, $value = null, $options = [])
    // {

    // }
    
    // public function datetimeLocal($name, $value = null, $options = [])
    // {

    // }
    
    // public function time($name, $value = null, $options = [])
    // {

    // }
    
    // public function url($name, $value = null, $options = [])
    // {

    // }
    
    // public function week($name, $value = null, $options = [])
    // {

    // }
    
    public function file($name, $options = [])
    {
        return Html::file($name)->attributes($options);
    }
    
    public function textarea($name, $value = null, $options = [])
    {
        return Html::textarea($name, $value)->attributes($options);
    }
    
    public function select(
        $name,
        $list = [],
        $selected = null,
        array $selectAttributes = [],
        array $optionsAttributes = [],
        array $optgroupsAttributes = []
    ) 
    {
        $placeholder = isset($selectAttributes['placeholder']) ? $selectAttributes['placeholder'] : '';
        $disabled = (isset($selectAttributes['disabled']) && ($selectAttributes['disabled']==true || $selectAttributes['disabled']==1)) ? $selectAttributes['disabled'] : '';
        if (isset($selectAttributes['disabled']) && ($selectAttributes['disabled'] !== true && $selectAttributes['disabled'] !== 1)){
            unset($selectAttributes['disabled']);
        }
        unset($selectAttributes['placeholder']);
        if($placeholder){
            $html = Html::select($name, $list, $selected)->attributes($selectAttributes)->placeholder($placeholder);
        }else{
            $html = Html::select($name, $list, $selected)->attributes($selectAttributes);
        }
        return $html;
    }

    // public function selectRange($name, $begin, $end, $selected = null, $options = [])
    // {

    // }
    
    // public function selectYear()
    // {

    // }
    
    // public function selectMonth($name, $selected = null, $options = [], $format = '%B')
    // {

    // }
    
    // public function getSelectOption($display, $value, $selected, array $attributes = [], array $optgroupAttributes = [])
    // {

    // }
    
    public function checkbox($name, $value = 1, $checked = null, $options = [])
    {
        return Html::checkbox($name, $checked, $value)->attributes($options);
    }
    
    public function radio($name, $value = null, $checked = null, $options = [])
    {
        return Html::radio($name, $checked, $value)->attributes($options);
    }
    
    // public function reset($value, $attributes = [])
    // {

    // }
    
    public function image($url, $name = null, $attributes = [])
    {
        return Html::img($url,$name)->attributes($attributes);
    }
    
    // public function month($name, $value = null, $options = [])
    // {

    // }
    
    // public function color($name, $value = null, $options = [])
    // {

    // }
    
    // public function submit($value = null, $options = [])
    // {

    // }
    
    // public function button($value = null, $options = [])
    // {

    // }
    
    // public function datalist($id, $list = [])
    // {

    // }
    
    // public function getIdAttribute($name, $attributes)
    // {

    // }
    
    // public function getValueAttribute($name, $value = null)
    // {

    // }
    
    // public function considerRequest($consider = true)
    // {

    // }
    
    // public function old($name)
    // {

    // }
    
    // public function oldInputIsEmpty()
    // {

    // }
    
    // public function getSessionStore()
    // {

    // }
    
    // public function setSessionStore(Session $session)
    // {

    // }
}

