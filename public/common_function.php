<?php

use Illuminate\Support\Facades\DB;

function pre($array, $no = '')
{
    echo '<pre>';
    print_r($array);
    if (!$no)
        exit;
}


function checkloggedinuserdata()
{
    $dataUserLoggedIn = DB::table('cashcredit_detail_type')->where('id', auth()->user()->department)->first();
    if ($dataUserLoggedIn->name == 'Agent') {
        return 'Agent';
    } else if ($dataUserLoggedIn->name == 'Cashier' || $dataUserLoggedIn->name == 'Caissière') {
        return 'Cashier';
    } else if ($dataUserLoggedIn->name == 'Warehouse') {
        return 'Warehouse';
    } else if ($dataUserLoggedIn->name == 'Customer Care') {
        return 'Customer Care';
    } else if ($dataUserLoggedIn->name == 'Logistics') {
        return 'Logistics';
    } else if ($dataUserLoggedIn->name == 'Recouvrement') {
        return 'Recouvrement';
    } else if ($dataUserLoggedIn->name == 'MKTG and Sales') {
        return 'MKTG and Sales';
    } else if ($dataUserLoggedIn->name == 'Documentation') {
        return 'Documentation';
    } else if ($dataUserLoggedIn->name == 'Audit') {
        return 'Audit';
    } else if ($dataUserLoggedIn->name == 'Comptabilité') {
        return 'Comptabilité';
    } else if ($dataUserLoggedIn->name == 'Invoice') {
        return 'Invoice';
    } else if ($dataUserLoggedIn->name == 'Operator') {
        return 'Operator';
    } else if ($dataUserLoggedIn->name == 'Manager') {
        return 'Manager';
    } else {
        return 'Other';
    }
}

function checkNonBoundedWH()
{
    $getNonBoundedWH = DB::table('warehouse')
        ->select('id')
        ->where('name', Config::get('app.nonBoundedWHName'))
        ->first();
    if (!empty($getNonBoundedWH)) {
        $nonBoundedId = $getNonBoundedWH->id;
        $getWarehouseOfUser =  DB::table('users')
            ->whereRaw("FIND_IN_SET($nonBoundedId,warehouses)")
            ->where('id', auth()->user()->id)
            ->count();
        if ($getWarehouseOfUser > 0)
            return 'Yes';
        else
            return 'No';
    } else {
        return 'No';
    }
}

// MASTER ARRAY OF LANGUAGE

//$langArray = ['en'=>'English','ja'=>'Japanese'];
//pre($langArray);
