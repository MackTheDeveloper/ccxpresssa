<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\CashCreditDetailType;
use App\User;
use App\Clients;
use App\HawbFiles;
use App\Currency;
use App\Warehouse;
use Config;

class Activities extends Model
{
    protected $table = 'activities';
    public $timestamps = false;
    protected $fillable = [
        'type', 'related_id', 'user_id', 'from_value', 'to_value', 'created_on', 'updated_on', 'description', 'cash_credit_flag'
    ];

    protected function log($event, $flag, $request)
    {
        //pre($request);	

        if ($event == 'create') {
            $modelActivity = new Activities;
            if ($flag == 'user')
                $modelActivity->type = 'user';
            else if ($flag == 'client')
                $modelActivity->type = 'client';
            else if ($flag == 'ups')
                $modelActivity->type = 'ups';
            else if ($flag == 'upsMaster')
                $modelActivity->type = 'upsMaster';
            else if ($flag == 'cargo')
                $modelActivity->type = 'cargo';
            else if ($flag == 'houseFile')
                $modelActivity->type = 'houseFile';
            else if ($flag == 'aeropost')
                $modelActivity->type = 'aeropost';
            else if ($flag == 'aeropostMaster')
                $modelActivity->type = 'aeropostMaster';
            else if ($flag == 'ccpack')
                $modelActivity->type = 'ccpack';
            else if ($flag == 'ccpackMaster')
                $modelActivity->type = 'ccpackMaster';
            else if ($flag == 'permission')
                $modelActivity->type = 'permission';
            else if ($flag == 'cargoexpense')
                $modelActivity->type = 'cargoexpense';
            else if ($flag == 'upsexpense')
                $modelActivity->type = 'upsexpense';
            else if ($flag == 'upsMasterExpense')
                $modelActivity->type = 'upsMasterExpense';
            else if ($flag == 'aeropostexpense')
                $modelActivity->type = 'aeropostexpense';
            else if ($flag == 'aeropostMasterExpense')
                $modelActivity->type = 'aeropostMasterExpense';
            else if ($flag == 'ccpackexpense')
                $modelActivity->type = 'ccpackexpense';
            else if ($flag == 'ccpackMasterExpense')
                $modelActivity->type = 'ccpackMasterExpense';
            else if ($flag == 'houseFileExpense')
                $modelActivity->type = 'houseFileExpense';
            else if ($flag == 'administrationExpense')
                $modelActivity->type = 'administrationExpense';
            else if ($flag == 'cargoinvoice')
                $modelActivity->type = 'cargoinvoice';
            else if ($flag == 'housefileinvoice')
                $modelActivity->type = 'housefileinvoice';
            else if ($flag == 'upsinvoice')
                $modelActivity->type = 'upsinvoice';
            else if ($flag == 'upsMasterInvoice')
                $modelActivity->type = 'upsMasterInvoice';
            else if ($flag == 'aeropostinvoice')
                $modelActivity->type = 'aeropostinvoice';
            else if ($flag == 'aeropostMasterInvoice')
                $modelActivity->type = 'aeropostMasterInvoice';
            else if ($flag == 'ccpackinvoice')
                $modelActivity->type = 'ccpackinvoice';
            else if ($flag == 'ccpackMasterInvoice')
                $modelActivity->type = 'ccpackMasterInvoice';

            $modelActivity->related_id = $request->id;
            $modelActivity->user_id = Auth::user()->id;

            if ($flag == 'user') {
                $modelActivity->description = "User has been created";
            } else if ($flag == 'client') {
                $modelActivity->description = "Client has been created";
            } else if ($flag == 'cargoexpense' || $flag == 'upsexpense' || $flag == 'upsMasterExpense' || $flag == 'aeropostexpense' || $flag == 'aeropostMasterExpense' || $flag == 'ccpackexpense' || $flag == 'ccpackMasterExpense') {
                $modelActivity->description = "Expense has been added for File <strong>#" . ucfirst($request->flagExpense) . "</strong>";
            } else if ($flag == 'houseFileExpense') {
                $modelActivity->description = "House File Expense has been added for File <strong>#" . ucfirst($request->flagExpense) . "</strong>";
            } else if ($flag == 'administrationExpense') {
                $modelActivity->description = "Administration Expense has been added";
            } else if ($flag == 'cargo') {
                $modelActivity->description = ucfirst($request->flagExpense) . " has been created.";
            } else if ($flag == 'houseFile') {
                $modelActivity->description = "House file has been created.";
            } else if ($flag == 'aeropost') {
                $modelActivity->description = "Aeropost file has been created.";
            } else if ($flag == 'ccpack') {
                $modelActivity->description = ucfirst($request->flagFile) . " has been created.";
            } else if ($flag == 'permission') {
                $modelActivity->description = ucwords(str_replace('_', ' ', $request->slug)) . " permission has been assigned.";
            } else if ($flag == 'cargoinvoice' || $flag == 'housefileinvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'upsinvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'upsMasterInvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'aeropostinvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'aeropostMasterInvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'ccpackinvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'ccpackMasterInvoice') {
                $modelActivity->description = "Invoice #" . $request->bill_no . " has been generated.";
            } else if ($flag == 'upsMaster') {
                $modelActivity->description = "UPS Master #" . $request->file_number . " has been created.";
            } else if ($flag == 'aeropostMaster') {
                $modelActivity->description = "Aeropost Master #" . $request->file_number . " has been created.";
            } else if ($flag == 'ccpackMaster') {
                $modelActivity->description = "CCPack Master #" . $request->file_number . " has been created.";
            } else {
                $modelActivity->description = "New courier file (" . $request->file_number . ") has been added";
            }

            $modelActivity->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivity->save();
        }
        if ($event == 'update') {
            if ($flag == 'ups') {
                
                if (!empty($request->tdate))
                    $request->tdate = date('Y-m-d', strtotime($request->tdate));
                else
                    $request->tdate = '';

                if (!empty($request->arrival_date))
                    $request->arrival_date = date('Y-m-d', strtotime($request->arrival_date));
                else
                    $request->arrival_date = '';
            }
            if ($flag == 'upsMaster') {
                if (!empty($request->arrival_date))
                    $request->arrival_date = date('Y-m-d', strtotime($request->arrival_date));
                else
                    $request->arrival_date = '';
            }
            if ($flag == 'aeropostMaster') {
                if (!empty($request->arrival_date))
                    $request->arrival_date = date('Y-m-d', strtotime($request->arrival_date));
                else
                    $request->arrival_date = '';
            }
            if ($flag == 'ccpackMaster') {
                if (!empty($request->arrival_date))
                    $request->arrival_date = date('Y-m-d', strtotime($request->arrival_date));
                else
                    $request->arrival_date = '';
            }
            if ($flag == 'cargo' || $flag == 'houseFile') {
                if (!empty($request->opening_date))
                    $request->opening_date = date('Y-m-d', strtotime($request->opening_date));
                else
                    $request->opening_date = '';

                
                if ($request->cargo_operation_type == '1')
                {
                    if (!empty($request->arrival_date))
                        $request->arrival_date = date('Y-m-d', strtotime($request->arrival_date));
                    else
                        $request->arrival_date = '';
                }
            }
            if ($flag == 'aeropost') {
                if (!empty($request->date))
                    $request->date = date('Y-m-d', strtotime($request->date));
                else
                    $request->date = '';
                $request->flight_date_time = date('Y-m-d H:i:s', strtotime($request->flight_date_time));
            }
            if ($flag == 'ccpack') {
                if (!empty($request->arrival_date))
                    $request->arrival_date = date('Y-m-d', strtotime($request->arrival_date));
                else
                    $request->arrival_date = '';
            }
            if ($flag == 'upsexpense' || $flag == 'upsMasterExpense' || $flag == 'aeropostMasterExpense' || $flag == 'ccpackMasterExpense' || $flag == 'cargoexpense' || $flag == 'houseFileExpense' || $flag == 'administrationExpense') {
                $request->exp_date = date('Y-m-d', strtotime($request->exp_date));
            }
            if ($flag == 'cargoinvoice' || $flag == 'upsinvoice' || $flag == 'upsMasterInvoice' || $flag == 'aeropostMasterInvoice' || $flag == 'ccpackMasterInvoice' || $flag == 'housefileinvoice' || $flag == 'aeropostinvoice' || $flag == 'ccpackinvoice') {
                $request->date = date('Y-m-d', strtotime($request->date));
            }

            $changes = $request->isDirty() ? $request->getDirty() : false;

            if ($changes) {
                foreach ($changes as $attr => $value) {
                    $oldValue = '';
                    $newValue = '';
                    $dataModified = 1;
                    $modelActivity = new Activities;
                    if ($flag == 'user')
                        $modelActivity->type = 'user';
                    else if ($flag == 'client')
                        $modelActivity->type = 'client';
                    else if ($flag == 'ups')
                        $modelActivity->type = 'ups';
                    else if ($flag == 'upsMaster')
                        $modelActivity->type = 'upsMaster';
                    else if ($flag == 'cargo')
                        $modelActivity->type = 'cargo';
                    else if ($flag == 'houseFile')
                        $modelActivity->type = 'houseFile';
                    else if ($flag == 'aeropost')
                        $modelActivity->type = 'aeropost';
                    else if ($flag == 'aeropostMaster')
                        $modelActivity->type = 'aeropostMaster';
                    else if ($flag == 'ccpack')
                        $modelActivity->type = 'ccpack';
                    else if ($flag == 'ccpackMaster')
                        $modelActivity->type = 'ccpackMaster';
                    else if ($flag == 'invoicepaymentstatus')
                        $modelActivity->type = 'invoice';
                    else if ($flag == 'upsexpense')
                        $modelActivity->type = 'upsexpense';
                    else if ($flag == 'upsMasterExpense')
                        $modelActivity->type = 'upsMasterExpense';
                    else if ($flag == 'aeropostMasterExpense')
                        $modelActivity->type = 'aeropostMasterExpense';
                    else if ($flag == 'ccpackMasterExpense')
                        $modelActivity->type = 'ccpackMasterExpense';
                    else if ($flag == 'cargoexpense')
                        $modelActivity->type = 'cargoexpense';
                    else if ($flag == 'houseFileExpense')
                        $modelActivity->type = 'houseFileExpense';
                    else if ($flag == 'administrationExpense')
                        $modelActivity->type = 'administrationExpense';
                    else if ($flag == 'cargoinvoice')
                        $modelActivity->type = 'cargoinvoice';
                    else if ($flag == 'upsinvoice')
                        $modelActivity->type = 'upsinvoice';
                    else if ($flag == 'upsMasterInvoice')
                        $modelActivity->type = 'upsMasterInvoice';
                    else if ($flag == 'housefileinvoice')
                        $modelActivity->type = 'housefileinvoice';
                    else if ($flag == 'aeropostinvoice')
                        $modelActivity->type = 'aeropostinvoice';
                    else if ($flag == 'aeropostMasterInvoice')
                        $modelActivity->type = 'aeropostMasterInvoice';
                    else if ($flag == 'ccpackMasterInvoice')
                        $modelActivity->type = 'ccpackMasterInvoice';
                    else if ($flag == 'ccpackinvoice')
                        $modelActivity->type = 'ccpackinvoice';

                    if ($flag == 'upsexpense' || $flag == 'upsMasterExpense' || $flag == 'aeropostMasterExpense' || $flag == 'ccpackMasterExpense' || $flag == 'cargoexpense' || $flag == 'houseFileExpense')
                        $modelActivity->related_id = $request->expense_id;
                    else
                        $modelActivity->related_id = $request->id;
                    $modelActivity->user_id = Auth::user()->id;

                    if ($flag == 'user') {
                        if ($attr == 'department') {
                            $deptData = CashCreditDetailType::getData($request->getOriginal($attr));
                            $oldValue = $deptData->name;
                            $deptData = CashCreditDetailType::getData($request->$attr);
                            $newValue = $deptData->name;
                        } else if ($attr == 'status') {
                            $oldValue = ($request->getOriginal($attr) == 1) ? 'Active' : 'Inactive';
                            $newValue = ($request->$attr == 1) ? 'Active' : 'Inactive';
                        } else {
                            $oldValue = $request->getOriginal($attr);
                            if (empty($oldValue))
                                $oldValue = '""';
                            $newValue = $request->$attr;
                        }
                    } else if ($flag == 'client') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'status') {
                            $oldValue = ($request->getOriginal($attr) == 1) ? 'Active' : 'Inactive';
                            $newValue = ($request->$attr == 1) ? 'Active' : 'Inactive';
                        } elseif ($attr == 'country') {
                            $oldCountry = Country::getData($oldValue);
                            $newCountry = Country::getData($request->$attr);
                            $oldValue = !empty($oldCountry->name) ? $oldCountry->name : "-";
                            $newValue = !empty($newCountry->name) ? $newCountry->name : "-";
                        } elseif ($attr == 'flag_prod_tax_type') {
                            $oldValue = ($oldValue == 1) ? 'Yes' : 'No';
                            $newValue = ($request->$attr == 1) ? 'Yes' : 'No';
                            $attr = 'TCA Applicable';
                        } else {
                            $oldValue = $request->getOriginal($attr);
                            if (empty($oldValue))
                                $oldValue = '""';
                            $newValue = $request->$attr;
                        }
                    } else if ($flag == 'ups') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'tdate' || $attr == 'arrival_date') {
                            if ($oldValue == '""')
                                $oldValue = '""';
                            else
                                $oldValue = date('d-m-Y', strtotime($oldValue));

                            if (!empty($request->$attr))
                                $newValue = date('d-m-Y', strtotime($request->$attr));
                            else
                                $newValue = '""';

                            if ($oldValue == '""' && $newValue == '""')
                                $dataModified = 0;
                        } elseif ($attr == 'agent_id') {
                            $modelUser = new User;
                            $oldAgent = $modelUser->getUserName($oldValue);
                            $newAgent = $modelUser->getUserName($request->$attr);
                            $oldValue = !empty($oldAgent->name) ? $oldAgent->name : "-";
                            $newValue = !empty($newAgent->name) ? $newAgent->name : "-";
                            $attr = 'Agent';
                        } elseif ($attr == 'billing_party') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $newBillingParty = $modelClient->getClientData($request->$attr);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                        } elseif ($attr == 'agent_id') {
                            $modelUser = new User;
                            $oldAgent = $modelUser->getUserName($oldValue);
                            $newAgent = $modelUser->getUserName($request->$attr);
                            $oldValue = !empty($oldAgent->name) ? $oldAgent->name : "-";
                            $newValue = !empty($newAgent->name) ? $newAgent->name : "-";
                            $attr = 'Agent';
                        } elseif ($attr == 'ups_scan_status') {
                            if (!empty($oldValue) && $oldValue != '""') {
                                $oldValue = isset(Config::get('app.ups_new_scan_status')[$oldValue]) ? Config::get('app.ups_new_scan_status')[$oldValue] : '';
                                $newValue = isset(Config::get('app.ups_new_scan_status')[$request->$attr]) ? Config::get('app.ups_new_scan_status')[$request->$attr] : '';
                                $attr = 'File Status';
                            }
                            
                        } elseif ($attr == 'consignee_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Consignee';
                        } elseif ($attr == 'shipper_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Shipper';
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'upsMaster' || $flag == 'aeropostMaster' || $flag == 'ccpackMaster') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'arrival_date') {
                            if ($oldValue == '""')
                                $oldValue = '""';
                            else
                                $oldValue = date('d-m-Y', strtotime($oldValue));

                            if (!empty($request->$attr))
                                $newValue = date('d-m-Y', strtotime($request->$attr));
                            else
                                $newValue = '""';

                            if ($oldValue == '""' && $newValue == '""')
                                $dataModified = 0;
                        } elseif ($attr == 'agent_id') {
                            $modelUser = new User;
                            $oldAgent = $modelUser->getUserName($oldValue);
                            $newAgent = $modelUser->getUserName($request->$attr);
                            $oldValue = !empty($oldAgent->name) ? $oldAgent->name : "-";
                            $newValue = !empty($newAgent->name) ? $newAgent->name : "-";
                            $attr = 'Agent';
                        } elseif ($attr == 'billing_party') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $newBillingParty = $modelClient->getClientData($request->$attr);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                        }elseif ($attr == 'consignee_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Consignee';
                        } elseif ($attr == 'shipper_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Shipper';
                        } else if ($attr == 'hawb_hbl_no') {
                            $modelHawbFiles = new CcpackMaster;
                            $oldN = explode(',', $oldValue);
                            $newN = explode(',', $request->$attr);
                            $countOldNum = count($oldN);
                            $countNewNum = count($newN);

                            if ($countNewNum == $countOldNum) {
                                $dataModified = 0;
                            } else {
                                $diff = array_diff($newN, $oldN);
                                $oldValue = $modelHawbFiles->getNumberFromCcpackMasterFile($oldN);
                                $newValue = $modelHawbFiles->getNumberFromCcpackMasterFile($newN);
                            }
                        }else
                            $newValue = $request->$attr;
                    } else if ($flag == 'cargo' || $flag == 'houseFile') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue))
                            $oldValue = '""';

                        if ($attr == 'hawb_scan_status') {
                            if (!empty($oldValue) && $oldValue != '""') {
                                $oldValue = isset(Config::get('app.ups_new_scan_status')[$oldValue]) ? Config::get('app.ups_new_scan_status')[$oldValue] : '';
                                $newValue = isset(Config::get('app.ups_new_scan_status')[$request->$attr]) ? Config::get('app.ups_new_scan_status')[$request->$attr] : '';
                                $attr = 'File Status';
                            }
                        } elseif ($attr == 'cargo_master_scan_status') {
                            if (!empty($oldValue) && $oldValue != '""') {
                                $oldValue = isset(Config::get('app.ups_new_scan_status')[$oldValue]) ? Config::get('app.ups_new_scan_status')[$oldValue] : '';
                                $newValue = isset(Config::get('app.ups_new_scan_status')[$request->$attr]) ? Config::get('app.ups_new_scan_status')[$request->$attr] : '';
                            }
                            $attr = 'File Status';
                        }
                        else if ($attr == 'opening_date' || $attr == 'arrival_date') {
                            if ($oldValue == '""')
                                $oldValue = '""';
                            else
                                $oldValue = date('d-m-Y', strtotime($oldValue));

                            if (!empty($request->$attr))
                                $newValue = date('d-m-Y', strtotime($request->$attr));
                            else
                                $newValue = '""';

                            if ($oldValue == '""' && $newValue == '""')
                                $dataModified = 0;
                        } else if ($attr == 'consolidate_flag') {
                            $oldValue = ($oldValue == 1) ? 'Consolidated' : 'Non Consolidated';
                            $newValue = ($request->$attr == 1) ? 'Consolidated' : 'Non Consolidated';
                        } else if ($attr == 'flag_package_container') {
                            $oldValue = ($oldValue == 1) ? 'Package' : 'Container';
                            $newValue = ($request->$attr == 1) ? 'Package' : 'Container';
                        } elseif ($attr == 'agent_id') {
                            $modelUser = new User;
                            $oldAgent = $modelUser->getUserName($oldValue);
                            $newAgent = $modelUser->getUserName($request->$attr);
                            $oldValue = !empty($oldAgent->name) ? $oldAgent->name : "-";
                            $newValue = !empty($newAgent->name) ? $newAgent->name : "-";
                            $attr = 'Agent';
                        } elseif ($attr == 'billing_party') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $newBillingParty = $modelClient->getClientData($request->$attr);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                        } elseif ($attr == 'warehouse') {
                            $modelWarehouse = new Warehouse;
                            $oldWarehouse = $modelWarehouse->getData($oldValue);
                            $newWarehouse = $modelWarehouse->getData($request->$attr);
                            $oldValue = !empty($oldWarehouse->name) ? $oldWarehouse->name : "-";
                            $newValue = !empty($newWarehouse->name) ? $newWarehouse->name : "-";
                        } elseif ($attr == 'consignee_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Consignee';
                        } elseif ($attr == 'shipper_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Shipper';
                        } else if ($attr == 'hawb_hbl_no') {
                            $modelHawbFiles = new HawbFiles;
                            $oldN = explode(',', $oldValue);
                            $newN = explode(',', $request->$attr);
                            $countOldNum = count($oldN);
                            $countNewNum = count($newN);

                            if ($countNewNum == $countOldNum) {
                                $dataModified = 0;
                            } else {
                                $diff = array_diff($newN, $oldN);
                                $oldValue = $modelHawbFiles->getNumberFromCargoFile($request->cargo_operation_type, $oldN);
                                $newValue = $modelHawbFiles->getNumberFromCargoFile($request->cargo_operation_type, $newN);
                            }
                        } else if ($attr == 'rental') {
                            $oldValue = ($oldValue == 1) ? 'Rental' : 'Non Rental';
                            $newValue = ($request->$attr == 1) ? 'Rental' : 'Non Rental';
                            $attr = 'Rental Flag';
                        } else if ($request->rental == '1' && $attr == 'rental_cost') {
                            $oldValue = $oldValue == '""' ? '0.00' : number_format($oldValue, 2);
                            $newValue = number_format($request->$attr, 2);
                        } else if ($request->rental == '0' && $attr == 'rental_cost') {
                            $dataModified = 0;
                        } else if ($request->rental == '1' && $attr == 'contract_months') {
                            $oldValue = $oldValue == '""' ? '0' : $oldValue;
                            $newValue = $request->$attr;
                        } else if ($request->rental == '0' && $attr == 'contract_months') {
                            $dataModified = 0;
                        } else if ($request->rental == '1' && $attr == 'rental_paid_status') {
                            $dataModified = 0;
                        } else if ($request->rental == '0' && $attr == 'rental_paid_status') {
                            $dataModified = 0;
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'aeropost') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';
                        if ($attr == 'consignee') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Consignee';
                        } else if ($attr == 'flight_date_time') {
                            $oldValue = date('Y-m-d H:i:s', strtotime($oldValue));
                            $newValue = date('Y-m-d H:i:s', strtotime($request->$attr));
                        } else if ($attr == 'date') {
                            if ($oldValue == '""')
                                $oldValue = '""';
                            else
                                $oldValue = date('d-m-Y', strtotime($oldValue));

                            if (!empty($request->$attr))
                                $newValue = date('d-m-Y', strtotime($request->$attr));
                            else
                                $newValue = '""';

                            if ($oldValue == '""' && $newValue == '""')
                                $dataModified = 0;
                        } elseif ($attr == 'billing_party') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $newBillingParty = $modelClient->getClientData($request->$attr);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                        } elseif ($attr == 'aeropost_scan_status') {
                            if (!empty($oldValue) && $oldValue != '""') {
                                $oldValue = isset(Config::get('app.ups_new_scan_status')[$oldValue]) ? Config::get('app.ups_new_scan_status')[$oldValue] : '';
                                $newValue = isset(Config::get('app.ups_new_scan_status')[$request->$attr]) ? Config::get('app.ups_new_scan_status')[$request->$attr] : '';
                                $attr = 'File Status';
                            }
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'ccpack') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';
                        if ($attr == 'consignee') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Consignee';
                        } elseif ($attr == 'shipper_name') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newBillingParty = $modelClient->getClientDataByCompanyName($request->$attr);
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                            if ($oldValue == $newValue)
                                $dataModified = 0;

                            $attr = 'Shipper';
                        } else if ($attr == 'arrival_date') {
                            if ($oldValue == '""')
                                $oldValue = '""';
                            else
                                $oldValue = date('d-m-Y', strtotime($oldValue));

                            if (!empty($request->$attr))
                                $newValue = date('d-m-Y', strtotime($request->$attr));
                            else
                                $newValue = '""';

                            if ($oldValue == '""' && $newValue == '""')
                                $dataModified = 0;
                        } elseif ($attr == 'billing_party') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $newBillingParty = $modelClient->getClientData($request->$attr);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                        } elseif ($attr == 'ccpack_scan_status') {
                            if (!empty($oldValue) && $oldValue != '""') {
                                $oldValue = isset(Config::get('app.ups_new_scan_status')[$oldValue]) ? Config::get('app.ups_new_scan_status')[$oldValue] : '';
                                $newValue = isset(Config::get('app.ups_new_scan_status')[$request->$attr]) ? Config::get('app.ups_new_scan_status')[$request->$attr] : '';
                                $attr = 'File Status';
                            }
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'expenserequestchangestatus') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue))
                            $oldValue = '""';
                        $newValue = $request->$attr;
                        $attr = 'Expense Request Status';
                    } else if ($flag == 'invoicepaymentstatus') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue))
                            $oldValue = '""';
                        $newValue = $request->$attr;
                        $attr = 'Invoice Payment Status';
                    } else if ($flag == 'cargoexpense') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'exp_date') {
                            $oldValue = date('d-m-Y', strtotime($oldValue));
                            $newValue = date('d-m-Y', strtotime($request->$attr));;
                        } else if ($attr == 'admin_managers') {
                            $oldValue = User::getMultipleNames($oldValue);
                            $newValue = User::getMultipleNames(implode(',', $request->$attr));
                        } else if ($attr == 'admin_manager_role') {
                            $old = CashCreditDetailType::getData($oldValue);
                            $new = CashCreditDetailType::getData($request->$attr);
                            $oldValue = !empty($old->name) ? $old->name : "-";
                            $newValue = !empty($new->name) ? $new->name : "-";
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'upsexpense') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'exp_date') {
                            $oldValue = date('d-m-Y', strtotime($oldValue));
                            $newValue = date('d-m-Y', strtotime($request->$attr));;
                        } else if ($attr == 'admin_managers') {
                            $oldValue = User::getMultipleNames($oldValue);
                            $newValue = User::getMultipleNames(implode(',', $request->$attr));
                        } else if ($attr == 'admin_manager_role') {
                            $old = CashCreditDetailType::getData($oldValue);
                            $new = CashCreditDetailType::getData($request->$attr);
                            $oldValue = !empty($old->name) ? $old->name : "-";
                            $newValue = !empty($new->name) ? $new->name : "-";
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'upsMasterExpense' || $flag == 'aeropostMasterExpense' || $flag == 'ccpackMasterExpense') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'exp_date') {
                            $oldValue = date('d-m-Y', strtotime($oldValue));
                            $newValue = date('d-m-Y', strtotime($request->$attr));;
                        } else if ($attr == 'admin_managers') {
                            $oldValue = User::getMultipleNames($oldValue);
                            $newValue = User::getMultipleNames(implode(',', $request->$attr));
                        } else if ($attr == 'admin_manager_role') {
                            $old = CashCreditDetailType::getData($oldValue);
                            $new = CashCreditDetailType::getData($request->$attr);
                            $oldValue = !empty($old->name) ? $old->name : "-";
                            $newValue = !empty($new->name) ? $new->name : "-";
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'houseFileExpense') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'exp_date') {
                            $oldValue = date('d-m-Y', strtotime($oldValue));
                            $newValue = date('d-m-Y', strtotime($request->$attr));;
                        } else if ($attr == 'admin_managers') {
                            $oldValue = User::getMultipleNames($oldValue);
                            $newValue = User::getMultipleNames(implode(',', $request->$attr));
                        } else if ($attr == 'admin_manager_role') {
                            $old = CashCreditDetailType::getData($oldValue);
                            $new = CashCreditDetailType::getData($request->$attr);
                            $oldValue = !empty($old->name) ? $old->name : "-";
                            $newValue = !empty($new->name) ? $new->name : "-";
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'administrationExpense') {
                        $oldValue = $request->getOriginal($attr);
                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';

                        if ($attr == 'exp_date') {
                            $oldValue = date('d-m-Y', strtotime($oldValue));
                            $newValue = date('d-m-Y', strtotime($request->$attr));;
                        } else if ($attr == 'admin_manager_role') {
                            $old = CashCreditDetailType::getData($oldValue);
                            $new = CashCreditDetailType::getData($request->$attr);
                            $oldValue = !empty($old->name) ? $old->name : "-";
                            $newValue = !empty($new->name) ? $new->name : "-";
                        } else
                            $newValue = $request->$attr;
                    } else if ($flag == 'cargoinvoice' || $flag == 'upsinvoice' || $flag == 'upsMasterInvoice' || $flag == 'aeropostMasterInvoice' || $flag == 'ccpackMasterInvoice' || $flag == 'housefileinvoice' || $flag == 'aeropostinvoice' || $flag == 'ccpackinvoice') {
                        $oldValue = $request->getOriginal($attr);

                        if (empty($oldValue) && $oldValue !== 0)
                            $oldValue = '""';



                        if ($attr == 'date') {
                            $oldValue = date('d-m-Y', strtotime($oldValue));
                            $newValue = date('d-m-Y', strtotime($request->$attr));;
                        } elseif ($attr == 'bill_to') {
                            $modelClient = new Clients;
                            $oldBillingParty = $modelClient->getClientData($oldValue);
                            $newBillingParty = $modelClient->getClientData($request->$attr);
                            $oldValue = !empty($oldBillingParty->company_name) ? $oldBillingParty->company_name : "-";
                            $newValue = !empty($newBillingParty->company_name) ? $newBillingParty->company_name : "-";
                        } elseif ($attr == 'currency') {
                            $oldCurrency = Currency::getData($oldValue);
                            $newCurrency = Currency::getData($request->$attr);
                            $oldValue = $oldCurrency->code;
                            $newValue = $newCurrency->code;
                        } elseif ($flag == 'housefileinvoice' && $attr == 'hawb_hbl_no') {

                            $oldHouseFile = HawbFiles::getHouseFileData($oldValue);
                            $newHouseFile = HawbFiles::getHouseFileData($request->$attr);
                            if ($oldHouseFile->cargo_operation_type == '1') {
                                $oldValue = $oldHouseFile->hawb_hbl_no;
                                $newValue = $newHouseFile->hawb_hbl_no;
                            } else {
                                $oldValue = $oldHouseFile->export_hawb_hbl_no;
                                $newValue = $newHouseFile->export_hawb_hbl_no;
                            }
                        } elseif ($attr == 'cargo_id' || $attr == 'ups_id' || $attr == 'aeropost_id' || $attr == 'ccpack_id') {
                            if ($attr == 'cargo_id') {
                                $modelCargo = new Cargo;
                                $oldCargoFileNumber = $modelCargo->getCargoData($oldValue);
                                $newCargoFileNumber = $modelCargo->getCargoData($request->$attr);
                            } else if ($attr == 'ups_id') {
                                $modelCargo = new Ups;
                                $oldCargoFileNumber = $modelCargo->getUpsData($oldValue);
                                $newCargoFileNumber = $modelCargo->getUpsData($request->$attr);
                            } else if ($attr == 'aeropost_id') {
                                $modelCargo = new Aeropost;
                                $oldCargoFileNumber = $modelCargo->getAeropostData($oldValue);
                                $newCargoFileNumber = $modelCargo->getAeropostData($request->$attr);
                            } else if ($attr == 'ccpack_id') {
                                $modelCargo = new ccpack;
                                $oldCargoFileNumber = $modelCargo->getccpackdetail($oldValue);
                                $newCargoFileNumber = $modelCargo->getccpackdetail($request->$attr);
                            }

                            $oldValue = !empty($oldCargoFileNumber->file_number) ? $oldCargoFileNumber->file_number : "-";
                            $newValue = !empty($newCargoFileNumber->file_number) ? $newCargoFileNumber->file_number : "-";
                            $attr = 'File Number';
                            $dataModified = 0;
                        } else
                            $newValue = $request->$attr;
                    } else {
                        $oldValue = $request->getOriginal($attr);
                        $newValue = $request->$attr;
                    }
                    $attr =  ucwords(str_replace('_', ' ', $attr));
                    if ($flag == 'user' && $attr == 'Password' || $flag == 'client' && $attr == 'Password') {
                        $modelActivity->description = "Password has been changed";
                    } else if ($flag == 'client' && $attr == 'Credit Limit Add') {
                        $modelActivity->description = "Credit limit added - " . number_format($newValue, 2);
                    } else {
                        /* if($oldValue == 'up'){
                            $oldValue = 'Pending';
                            $newValue = 'Paid';
                        }  *//*else {
                            $oldValue = 'Paid';
                            $newValue = 'Pending';
                        }*/
                        $modelActivity->description = "Updated $attr From <strong>{$oldValue}</strong> To <strong>{$newValue}</strong>";
                    }
                    $modelActivity->updated_on = gmdate("Y-m-d H:i:s");
                    if (($flag == 'ups' && $attr == 'Last Action Flag') || $dataModified == 0) {
                    } else {
                        $modelActivity->save();
                    }
                }
            }
        }
    }
}
