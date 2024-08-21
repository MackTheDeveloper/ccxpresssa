<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Config;
use App\Cargo;
use App\User;

class Cashier extends Model
{

    protected function getNotificationForCashier($flag = "")
    {
        if ($flag != 'All') {
            $userId = auth()->user()->id;
            $expenseNoti = DB::table('expenses')
                //->where('display_notification_cashier',1)
                ->where(function ($query) {
                    $query->whereNotNull('cargo_id')
                        ->orWhereNotNull('ups_details_id')
                        ->orWhereNotNull('ups_master_id')
                        ->orWhereNotNull('house_file_id')
                        ->orWhereNotNull('aeropost_id')
                        ->orWhereNotNull('aeropost_master_id')
                        ->orWhereNotNull('ccpack_id')
                        ->orWhereNotNull('ccpack_master_id');
                })
                ->where('deleted', '0')
                ->where('cashier_id', $userId)
                ->where('expense_request', 'Approved');
            if ($flag == 'displayOnBell') {
                $expenseNoti = $expenseNoti->limit(100)->orderBy('notification_date_time', 'desc')->get()->toArray();
            } else {
                $expenseNoti = $expenseNoti->get()->toArray();
            }

            foreach ($expenseNoti as $key => $value) {

                if (!empty($value->ups_details_id)) {
                    $modalCommonC = new Ups;
                    $dataCommonC = $modalCommonC->getUpsData($value->ups_details_id);
                    $expenseNoti[$key]->flagModule = 'UpsExpense';
                } else if (!empty($value->ups_master_id)) {
                    $modalCommonC = new UpsMaster;
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
                    $modalCommonC = new AeropostMaster;
                    $dataCommonC = $modalCommonC->getMasterAeropostData($value->aeropost_master_id);
                    $expenseNoti[$key]->flagModule = 'AeropostMasterExpense';
                } else if (!empty($value->ccpack_id)) {
                    $modalCommonC = new ccpack;
                    $dataCommonC = $modalCommonC->getccpackdetail($value->ccpack_id);
                    $expenseNoti[$key]->flagModule = 'ccpackExpense';
                } else if (!empty($value->ccpack_master_id)) {
                    $modalCommonC = new CcpackMaster;
                    $dataCommonC = $modalCommonC->getMasterCcpackData($value->ccpack_master_id);
                    $expenseNoti[$key]->flagModule = 'CcpackMasterExpense';
                } else {
                    $modalCommonC = new Cargo;
                    $dataCommonC = $modalCommonC->getCargoData($value->cargo_id);
                    $expenseNoti[$key]->flagModule = 'CargoExpense';
                }

                $modalUser = new User;
                if ($value->expense_request == 'Approved')
                    $dataUser = $modalUser->getUserName($value->approved_by);
                else
                    $dataUser = $modalUser->getUserName($value->created_by);

                if (empty($dataUser)) {
                    $approvedByName = '';
                } else {
                    $approvedByName = $dataUser->name;
                }


                $expenseNoti[$key]->notificationMessage = '#' . $dataCommonC->file_number . ' (' . $value->bl_awb . ') : Expense status has been changed to ' . $value->expense_request . ' - Changed By ' . $approvedByName;

                if (!empty($value->ups_details_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_ups;
                else if (!empty($value->ups_master_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_ups_master;
                else if (!empty($value->house_file_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_house_file_expense;
                else if (!empty($value->aeropost_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_aeropost;
                else if (!empty($value->aeropost_master_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_aeropost_master;
                else if (!empty($value->ccpack_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_ccpack;
                else if (!empty($value->ccpack_master_id))
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier_for_ccpack_master;
                else
                    $expenseNoti[$key]->notificationStatus = $value->display_notification_cashier;

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

            $expenseAdministrativeNoti = DB::table('other_expenses')
                ->where('deleted', '0')
                ->where('cashier_id', $userId)
                ->where('expense_request', 'Approved')
                ->whereNotNull('approved_by')
                ->get()
                ->toArray();


            foreach ($expenseAdministrativeNoti as $key => $value) {

                $modalUser = new User;
                $dataUser = $modalUser->getUserName($value->approved_by);
                $approvedByName = $dataUser->name;
                $expenseAdministrativeNoti[$key]->flagModule = 'administrationExpense';


                $expenseAdministrativeNoti[$key]->notificationMessage = "Administration Expense status has been changed to " . $value->expense_request . ' - Changed By ' . $approvedByName . " (voucher #" . $value->voucher_number . ")";
                $expenseAdministrativeNoti[$key]->notificationStatus = $value->display_notification_cashier;

                $expenseAdministrativeNoti[$key]->notificationDateTime = $value->notification_date_time;
            }
            $expenseAdministrativeNoti = (array) $expenseAdministrativeNoti;

            $dataAll = array_merge($expenseNoti, $expenseAdministrativeNoti);
            return $dataAll;
        } else {
            $userId = auth()->user()->id;
            $countExpenseNoti = DB::table('expenses')
                ->where(function ($query) {
                    $query->where('display_notification_cashier', 1)
                        ->orWhere('display_notification_cashier_for_ups', 1)
                        ->orWhere('display_notification_cashier_for_ups_master', 1)
                        ->orWhere('display_notification_cashier_for_house_file_expense', 1)
                        ->orWhere('display_notification_cashier_for_aeropost', 1)
                        ->orWhere('display_notification_cashier_for_aeropost_master', 1)
                        ->orWhere('display_notification_cashier_for_ccpack', 1)
                        ->orWhere('display_notification_cashier_for_ccpack_master', 1);
                })
                ->where(function ($query) {
                    $query->whereNotNull('cargo_id')
                        ->orWhereNotNull('ups_details_id')
                        ->orWhereNotNull('ups_master_id')
                        ->orWhereNotNull('house_file_id')
                        ->orWhereNotNull('aeropost_id')
                        ->orWhereNotNull('aeropost_master_id')
                        ->orWhereNotNull('ccpack_id')
                        ->orWhereNotNull('ccpack_master_id');
                })
                ->where('deleted', '0')
                ->where('cashier_id', $userId)
                ->where('expense_request', 'Approved')
                ->count();

            $countAdministrativeExpenseNoti = DB::table('other_expenses')
                ->where('display_notification_cashier', 1)
                ->where('deleted', 0)
                ->where('cashier_id', $userId)
                ->where('expense_request', 'Approved')
                ->whereNotNull('approved_by')
                ->count();

            return $countExpenseNoti + $countAdministrativeExpenseNoti;
        }
    }
}
