<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Config;
use App\Cargo;
use App\User;

class Agent extends Model
{

    protected function getNotificationForAgent($flag = "")
    {
        if ($flag != 'All') {
            $userId = auth()->user()->id;
            $expenseNoti = DB::table('expenses')
                //->where('display_notification_agent',1)
                ->whereNotNull('disbursed_by')
                ->where('expense_request', 'Disbursement done1')
                ->where('request_by', $userId)
                ->where('deleted', 0);
            /* ->get()
                ->toArray(); */
            if ($flag == 'displayOnBell') {
                $expenseNoti = $expenseNoti->limit(50)->orderBy('notification_date_time', 'desc')->get()->toArray();
            } else {
                $expenseNoti = $expenseNoti->get()->toArray();
            }

            foreach ($expenseNoti as $key => $value) {
                if (!empty($value->ups_details_id)) {
                    $modalCommonC = new Ups;
                    $dataCommonC = $modalCommonC->getUpsData($value->ups_details_id);
                    $expenseNoti[$key]->flagModule = 'UpsExpense';
                } else if (!empty($value->ups_master_id)) {
                    $modalCommonC = new UpsMaster();
                    $dataCommonC = $modalCommonC->getMasterUpsData($value->ups_master_id);
                    $expenseNoti[$key]->flagModule = 'UpsMasterExpense';
                } else if (!empty($value->house_file_id)) {
                    $modalCommonC = new HawbFiles;
                    $dataCommonC = $modalCommonC->getHouseFileData($value->house_file_id);
                    $expenseNoti[$key]->flagModule = 'houseFileExpense';
                } else if (!empty($value->aeropost_id)) {
                    $modalCommonC = new Aeropost;
                    $dataCommonC = $modalCommonC->getAeropostData($value->aeropost_id);
                    $expenseNoti[$key]->flagModule = 'aeropostExpense';
                } else if (!empty($value->aeropost_master_id)) {
                    $modalCommonC = new AeropostMaster();
                    $dataCommonC = $modalCommonC->getMasterAeropostData($value->aeropost_master_id);
                    $expenseNoti[$key]->flagModule = 'AeropostMasterExpense';
                } else if (!empty($value->ccpack_id)) {
                    $modalCommonC = new ccpack;
                    $dataCommonC = $modalCommonC->getccpackdetail($value->ccpack_id);
                    $expenseNoti[$key]->flagModule = 'ccpackExpense';
                } else if (!empty($value->ccpack_master_id)) {
                    $modalCommonC = new CcpackMaster();
                    $dataCommonC = $modalCommonC->getMasterCcpackData($value->ccpack_master_id);
                    $expenseNoti[$key]->flagModule = 'CcpackMasterExpense';
                } else {
                    $modalCommonC = new Cargo;
                    $dataCommonC = $modalCommonC->getCargoData($value->cargo_id);
                    $expenseNoti[$key]->flagModule = 'CargoExpense';
                }

                $modalUser = new User;
                /* if($value->expense_request == 'Approved')
                    $dataUser = $modalUser->getUserName($value->approved_by);
                else
                    $dataUser = $modalUser->getUserName($value->updated_by); */
                $dataUser = $modalUser->getUserName($value->disbursed_by);

                if (empty($dataUser)) {
                    $cashierName = '';
                } else {
                    $cashierName = $dataUser->name;
                }


                $expenseNoti[$key]->notificationMessage = '#' . (!empty($dataCommonC->file_number) ? $dataCommonC->file_number : '') . ' (' . $value->bl_awb . ') : Expense status has been changed to ' . $value->expense_request . ' - Changed By ' . $cashierName;

                $expenseNoti[$key]->notificationStatus = $value->display_notification_agent;
                $expenseNoti[$key]->notificationDateTime = $value->notification_date_time;
                $expenseNoti[$key]->awb_no = $value->bl_awb;
                if (!empty($dataCommonC)) {
                    $Client = new Clients();
                    if (!empty($value->ups_details_id)) {
                        if ($dataCommonC->courier_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->ups_master_id)) {
                        if ($dataCommonC->ups_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->aeropost_master_id)) {
                        $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                        if (!empty($dataShipper))
                            $expenseNoti[$key]->client = $dataShipper->company_name;
                        else
                            $expenseNoti[$key]->client = '-';
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->aeropost_id)) {
                        $expenseNoti[$key]->client = $value->consignee;
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->ccpack_id)) {
                        if ($dataCommonC->ccpack_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->ccpack_master_id)) {
                        $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                        if (!empty($dataShipper))
                            $expenseNoti[$key]->client = $dataShipper->company_name;
                        else
                            $expenseNoti[$key]->client = '-';
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else {
                        if ($dataCommonC->cargo_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else if ($dataCommonC->cargo_operation_type == 2) {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    }
                } else {
                    $expenseNoti[$key]->client = '-';
                }
            }
            $expenseNoti = (array) $expenseNoti;

            $dataCargo = DB::table('cargo')
                //->where('display_notification',1)
                ->where('agent_id', auth()->user()->id)
                ->where('deleted', 0);
            /* ->get()
                ->toArray(); */

            if ($flag == 'displayOnBell') {
                $dataCargo = $dataCargo->limit(25)->orderBy('notification_date_time', 'desc')->get()->toArray();
            } else {
                $dataCargo = $dataCargo->get()->toArray();
            }

            foreach ($dataCargo as $key => $value) {
                $Client = new Clients();
                /* if(!empty($value->warehouse_status))
                {
                    $dataCargo[$key]->flagModule = 'CargoWarehouseFileStatusChanged';
                    $dataCargo[$key]->notificationMessage = '#'.$value->file_number.' ('.$value->awb_bl_no.') : Warehouse Status has been changed to '.Config::get('app.warehouseStatus')[$value->warehouse_status];
                }else{   
                    $flg = ($value->cargo_operation_type == 1 ? 'IMPORT' : ($value->cargo_operation_type == 2 ? 'EXPORT' : 'LOCALE'));
                    $dataCargo[$key]->flagModule = 'Cargo File Assigned';
                    $dataCargo[$key]->notificationMessage = '#'.$value->file_number.' ('.$value->awb_bl_no.') : Cargo File has been assigned to you.';
                } */

                $flg = ($value->cargo_operation_type == 1 ? 'IMPORT' : ($value->cargo_operation_type == 2 ? 'EXPORT' : 'LOCALE'));
                $dataCargo[$key]->flagModule = 'Cargo File Assigned';
                $dataCargo[$key]->notificationMessage = '#' . $value->file_number . ' (' . $value->awb_bl_no . ') : Cargo File has been assigned to you.';

                $dataConsignee = $Client->getClientData($value->consignee_name);
                if (!empty($dataConsignee))
                    $dataCargo[$key]->client = $dataConsignee->company_name;
                else
                    $dataCargo[$key]->client = '-';

                $dataCargo[$key]->notificationStatus = $value->display_notification;
                $dataCargo[$key]->notificationDateTime = $value->notification_date_time;
                $dataCargo[$key]->awb_no = $value->awb_bl_no;
            }

            $dataCargo = (array) $dataCargo;

            $dataCourier = DB::table('ups_details')
                //->where('display_notification',1)
                ->where('agent_id', auth()->user()->id)
                ->where('deleted', 0);
            /* ->get()
                ->toArray(); */

            if ($flag == 'displayOnBell') {
                $dataCourier = $dataCourier->limit(25)->orderBy('notification_date_time', 'desc')->get()->toArray();
            } else {
                $dataCourier = $dataCourier->get()->toArray();
            }

            foreach ($dataCourier as $key => $value) {
                $Client = new Clients();
                $dataCourier[$key]->flagModule = 'UPS File Assigned';
                $dataCourier[$key]->notificationMessage = '#' . $value->file_number . ' (' . $value->awb_number . ') : UPS File has been assigned to you.';

                $dataConsignee = $Client->getClientData($value->consignee_name);
                if (!empty($dataConsignee))
                    $dataCourier[$key]->client = $dataConsignee->company_name;
                else
                    $dataCourier[$key]->client = '-';

                $dataCourier[$key]->notificationStatus = $value->display_notification;
                $dataCourier[$key]->notificationDateTime = $value->notification_date_time;
                $dataCourier[$key]->awb_no = $value->awb_number;
            }

            $dataCourier = (array) $dataCourier;

            $dataAll = array_merge($expenseNoti, $dataCargo, $dataCourier);
            return $dataAll;
        } else {

            $userId = auth()->user()->id;
            $countExpenseNoti = DB::table('expenses')
                ->where('display_notification_agent', 1)
                ->whereNotNull('disbursed_by')
                ->where('expense_request', 'Disbursement done1')
                ->where('request_by', $userId)
                ->where('deleted', 0)
                ->count();

            $countCargo = DB::table('cargo')
                ->where('display_notification', 1)
                ->where('agent_id', auth()->user()->id)
                ->where('deleted', 0)
                ->count();

            $countCourier = DB::table('ups_details')
                ->where('display_notification', 1)
                ->where('agent_id', auth()->user()->id)
                ->where('deleted', 0)
                ->count();

            return $countExpenseNoti + $countCargo + $countCourier;
        }
    }
}
