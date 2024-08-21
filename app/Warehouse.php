<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\User;

class Warehouse extends Model
{
    protected $table = 'warehouse';
    public $timestamps = false;
    protected $fillable = [
        'name', 'status', 'created_at', 'updated_at', 'deleted', 'deleted_at', 'warehouse_for'
    ];

    public function getData($id)
    {
        $data = Warehouse::find($id);

        return $data;
    }


    protected function getNotificationForWarehouse($flag = "")
    {
        if ($flag != 'All') {
            if (checkNonBoundedWH() == 'Yes') {
                $dataAeropostB = DB::table('aeropost')
                    ->where('move_to_nonbounded_wh', '1')
                    ->where('deleted', '0')
                    ->get()
                    ->toArray();
                $Client = new Clients();
                foreach ($dataAeropostB as $key => $value) {
                    $dataAeropostB[$key]->flagModule = 'Aeropost';
                    $dataAeropostB[$key]->notificationMessage = '#' . $value->file_number . ' has been arrived';

                    $dataAeropostB[$key]->notificationStatus = $value->display_notification_nonbounded_wh;
                    $dataAeropostB[$key]->notificationDateTime = $value->display_notification_nonbounded_wh_datetime;
                    $dataConsignee = $Client->getClientData($value->consignee);
                    if (!empty($dataConsignee))
                        $dataAeropostB[$key]->client = $dataConsignee->company_name;
                    else
                        $dataAeropostB[$key]->client = '-';
                }
                $dataAeropostB = (array) $dataAeropostB;

                $dataCcpackB = DB::table('ccpack')
                    ->where('move_to_nonbounded_wh', '1')
                    ->where('deleted', '0')
                    ->get()
                    ->toArray();

                foreach ($dataCcpackB as $key => $value) {
                    $dataCcpackB[$key]->flagModule = 'CCPack';
                    $dataCcpackB[$key]->notificationMessage = '#' . $value->file_number . ' has been arrived';

                    $dataCcpackB[$key]->notificationStatus = $value->display_notification_nonbounded_wh;
                    $dataCcpackB[$key]->notificationDateTime = $value->display_notification_nonbounded_wh_datetime;
                    if ($value->ccpack_operation_type == 1) {
                        $dataConsignee = $Client->getClientData($value->consignee);
                        if (!empty($dataConsignee))
                            $dataCcpackB[$key]->client = $dataConsignee->company_name;
                        else
                            $dataCcpackB[$key]->client = '-';
                    } else {
                        $dataShipper = $Client->getClientData($value->shipper_name);
                        if (!empty($dataShipper))
                            $dataCcpackB[$key]->client = $dataShipper->company_name;
                        else
                            $dataCcpackB[$key]->client = '-';
                    }
                }
                $dataCcpackB = (array) $dataCcpackB;

                $dataUpsB = DB::table('ups_details')
                    ->where('move_to_nonbounded_wh', '1')
                    ->where('deleted', '0')
                    ->get()
                    ->toArray();

                foreach ($dataUpsB as $key => $value) {
                    $dataUpsB[$key]->flagModule = 'Ups';
                    $dataUpsB[$key]->notificationMessage = '#' . $value->file_number . ' has been arrived';

                    $dataUpsB[$key]->notificationStatus = $value->display_notification_nonbounded_wh;
                    $dataUpsB[$key]->notificationDateTime = $value->display_notification_nonbounded_wh_datetime;
                    if ($value->courier_operation_type == 1) {
                        $dataConsignee = $Client->getClientData($value->consignee_name);
                        if (!empty($dataConsignee))
                            $dataUpsB[$key]->client = $dataConsignee->company_name;
                        else
                            $dataUpsB[$key]->client = '-';
                    } else {
                        $dataShipper = $Client->getClientData($value->shipper_name);
                        if (!empty($dataShipper))
                            $dataUpsB[$key]->client = $dataShipper->company_name;
                        else
                            $dataUpsB[$key]->client = '-';
                    }
                }
                $dataUpsB = (array) $dataUpsB;

                $dataAll = array_merge($dataAeropostB, $dataCcpackB, $dataUpsB);
                return $dataAll;
            } else {
                $userId = auth()->user()->id;
                $getWarehouseOfUser =  DB::table('users')
                    ->select('warehouses')
                    ->where('id', auth()->user()->id)
                    ->first();

                $wh = explode(',', $getWarehouseOfUser->warehouses);

                $dataCargo = DB::table('cargo')
                    //->where('display_notification_warehouse',1)
                    ->where('consolidate_flag', 1)
                    ->whereIn('warehouse', $wh)
                    ->where('deleted', 0)
                    ->get()
                    ->toArray();

                foreach ($dataCargo as $key => $value) {
                    $name = '';
                    if (!empty($value->updated_by) || !empty($value->created_by)) {
                        $dUser = new User;
                        if (!empty($value->created_by))
                            $by = $value->created_by;
                        else
                            $by = $value->updated_by;
                        $dataUsers = $dUser->getUserName($by);
                        $name = $dataUsers->name;
                    }
                    $warehouseName = '';
                    if (!empty($value->warehouse)) {
                        $dWarehouse = new Warehouse;
                        $dataWarehouse = $dWarehouse->getData($value->warehouse);
                        $warehouseName = $dataWarehouse->name;
                    }
                    $dataCargo[$key]->flagModule = 'Cargo';
                    $dataCargo[$key]->notificationMessage = '#' . $value->file_number . ' (' . $value->awb_bl_no . ') : ' . $name . ' has sent file to ' . $warehouseName;

                    $dataCargo[$key]->notificationStatus = $value->display_notification_warehouse;
                    $dataCargo[$key]->notificationDateTime = $value->notification_date_time;
                    $dataCargo[$key]->awb_no = $value->awb_bl_no;
                }
                $dataCargo = (array) $dataCargo;

                $argoInvoice = DB::table('invoices')
                    //->where('display_notification_warehouse_invoice',1)
                    ->where('deleted', 0)
                    ->where('created_by', $userId)
                    ->whereNotNull('cargo_id')
                    ->get()
                    ->toArray();

                foreach ($argoInvoice as $key => $value) {
                    $cargoModel = new Cargo;
                    $cargoData = $cargoModel->getCargoData($value->cargo_id);
                    $name = '';
                    if (!empty($value->invoice_status_changed_by)) {
                        $dUser = new User;
                        $dataUsers = $dUser->getUserName($value->invoice_status_changed_by);
                        $name = $dataUsers->name;
                    }
                    $argoInvoice[$key]->flagModule = 'Invoice';
                    $argoInvoice[$key]->notificationMessage = '#' . $cargoData->file_number . ' (' . $value->awb_no . ') : payment received - ' . $value->payment_status;

                    $argoInvoice[$key]->notificationStatus = $value->display_notification_warehouse_invoice;
                    $argoInvoice[$key]->notificationDateTime = $value->notification_date_time;
                    $argoInvoice[$key]->awb_no = $value->awb_no;
                }

                $argoInvoice = (array) $argoInvoice;

                $dataAll = array_merge($dataCargo, $argoInvoice);


                return $dataAll;
            }
        } else {
            if (checkNonBoundedWH() == 'Yes') {
                $countAeropostB = DB::table('aeropost')
                    ->where('display_notification_nonbounded_wh', 1)
                    ->where('move_to_nonbounded_wh', 1)
                    ->where('deleted', '0')
                    ->count();
                $countCcpackB = DB::table('ccpack')
                    ->where('display_notification_nonbounded_wh', 1)
                    ->where('move_to_nonbounded_wh', 1)
                    ->where('deleted', '0')
                    ->count();
                $countUpsB = DB::table('ups_details')
                    ->where('display_notification_nonbounded_wh', 1)
                    ->where('move_to_nonbounded_wh', 1)
                    ->where('deleted', '0')
                    ->count();

                return $countAeropostB + $countCcpackB + $countUpsB;
            } else {
                $userId = auth()->user()->id;
                $getWarehouseOfUser =  DB::table('users')
                    ->select('warehouses')
                    ->where('id', auth()->user()->id)
                    ->first();

                $wh = explode(',', $getWarehouseOfUser->warehouses);

                $countCargo = DB::table('cargo')
                    ->where('display_notification_warehouse', 1)
                    ->where('consolidate_flag', 1)
                    ->whereIn('warehouse', $wh)
                    ->where('deleted', 0)
                    ->count();

                $countCargoInvoice = DB::table('invoices')
                    ->where('display_notification_warehouse_invoice', 1)
                    ->where('created_by', $userId)
                    ->whereNotNull('cargo_id')
                    ->where('deleted', 0)
                    ->count();


                return $countCargo + $countCargoInvoice;
            }
        }
    }
}
