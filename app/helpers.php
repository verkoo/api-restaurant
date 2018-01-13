<?php

function toFloat($num) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');

    if (! $dotPos && ! $commaPos){
        return $num;
    }
    if ($dotPos && ! $commaPos){
        if (strlen($num) - $dotPos > 3){
            return str_replace(".","",$num);
        }
        return floatval($num);
    }
    $num = str_replace(".","",$num);
    $num = str_replace(",",".",$num);

    return floatval($num);
}

function toCents($num) {
    $num = $num ?: 0;
    return toFloat($num)  * 100;
}

function getDateWithFourDigitsYear($date)
{
    $parts = explode('/', $date);

    if (strlen($parts[2]) == 2) {
        $century = substr(date('Y'),0,2);
        return "{$parts[0]}/{$parts[1]}/{$century}{$parts[2]}";
    }
    if (strlen($parts[2]) > 4) {
        $year = substr($parts[2],0,4);
        return "{$parts[0]}/{$parts[1]}/{$year}";
    }

    return $date;
}

function getMainRoute() 
{
    return \Request::segment(1);
}

function getAssociatedModel($route, $id, $namespace = '\Verkoo\Common\Entities\\')
{
//    if ($route === 'orders') $namespace = '\App\Entities\\';
    $model_name = '';
    $words = explode('-', $route);
    foreach ($words as $word) {
        $model_name .= ucfirst(str_singular($word));
    }

    $class = $namespace . $model_name;
    $model = new $class();
    $model = $model->find($id);

    return $model;
}

function getNextNumber($table, $serie = 1, $date_field = 'created_at', $number_field = 'number'){
    $result =  \Illuminate\Support\Facades\DB::table($table)
        ->select($number_field)
        ->where('serie', $serie)
        ->whereYear($date_field, '=',  date('Y'))
        ->orderBy($number_field,'DESC')
        ->get();

    $number = ( $result->isEmpty() ? 0 : $result->first()->number );

    $number = $number ? $number + 1 : 1;
    $formatted_number = str_pad($number,4,'0', STR_PAD_LEFT);

    return ($number == 1 ? date('y') . $formatted_number : $formatted_number );
}