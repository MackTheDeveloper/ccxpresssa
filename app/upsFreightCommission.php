<?php

namespace App;

use Config;
use DB;
use Illuminate\Database\Eloquent\Model;

class upsFreightCommission extends Model
{
    protected $table = 'ups_freight_commission';
    public $timestamps = false;
    public $fillable = ['ups_file_id', 'freight', 'commission', 'pending_commission', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted', 'deleted_by', 'delete_at'];

    public function freightCommission($fileType, $freight, $billing, $pkgType, $pkgCount = null)
    {
        if ($fileType == 'export') {
            $exportCollect = Config::get('app.exportCollect');
            $exportPrepaid = Config::get('app.exportPrepaid');
            if ($billing == 'fc') {
                $billingTerm = 1;
            } else if ($billing == 'fd') {
                $billingTerm = 2;
            } else {
                $billingTerm = 3;
            }

            if ($pkgType == 'ltr') {
                $package = 'LTR';
            } else if ($pkgType == 'doc') {
                $package = 'DOC';
            } else {
                $package = 'PKG';
            }

            $commissionDetail = DB::table('ups_import_export_commission')->where('deleted', '0')->where('billing_term', $billingTerm)->where('courier_type', $package)->where('file_type', 'e')->orderBy('id', 'DESC')->first();
            $commission = 0;
            if (($billing == 'pp' && $pkgType == 'ltr') || ($billing == 'pp' && $pkgType == 'doc')) {
                $commission = ($freight * $commissionDetail->commission) / 100;
            } else if ($billing == 'pp' && $pkgType == 'pkg') {
                $commission = ($freight * $commissionDetail->commission) / 100;
            } else {
            }

            if ($billing == 'fc' && $pkgType == 'ltr') {
                $commission = $commissionDetail->commission;
            } else if ($billing == 'fc' && $pkgType == 'doc') {
                $commission = $commissionDetail->commission;
            } else if ($billing == 'fc' && $pkgType == 'pkg') {
                if ($pkgCount != null) {
                    if ($pkgCount > 1) {
                        $remainingCount = $pkgCount - 1;
                        $commission = $commissionDetail->commission + ($remainingCount * $exportCollect['mulPkg']);
                    } else {
                        $commission = $commissionDetail->commission;
                    }
                } else {
                }
            } else {
            }
            return $commission;
        } else {

            $importCollect = Config::get('app.importCollect');
            $importPrepaid = Config::get('app.importPrepaid');
            if ($billing == 'fc') {
                $billingTerm = 1;
            } else if ($billing == 'fd') {
                $billingTerm = 2;
            } else {
                $billingTerm = 3;
            }

            if ($pkgType == 'ltr') {
                $package = 'LTR';
            } else if ($pkgType == 'doc') {
                $package = 'DOC';
            } else {
                $package = 'PKG';
            }

            $commissionDetail = DB::table('ups_import_export_commission')->where('deleted', '0')->where('billing_term', $billingTerm)->where('courier_type', $package)->where('file_type', 'i')->orderBy('id', 'DESC')->first();
            $commission = 0;
            if (($billing == 'pp' && $pkgType == 'ltr')) {
                $commission = $commissionDetail->commission;
            } else if ($billing == 'pp' && $pkgType == 'doc') {
                $commission = $commissionDetail->commission;
            } else if ($billing == 'pp' && $pkgType == 'pkg') {

                if ($pkgCount != null) {
                    if ($pkgCount > 1) {
                        $remainingCount = $pkgCount - 1;
                        $commission = $commissionDetail->commission + ($remainingCount * $importCollect['multiPkg']);
                    } else {
                        $commission = $commissionDetail->commission;
                    }
                }
            } else {
            }
            
            if ($billing == 'fc' && $pkgType == 'ltr') {
                $commission = $commissionDetail->commission;
            } else if ($billing == 'fc' && $pkgType == 'doc') {
                $commission = $commissionDetail->commission;
            } else if ($billing == 'fc' && $pkgType == 'pkg') {
                if ($pkgCount != null) {
                    if ($pkgCount > 1) {
                        $remainingCount = $pkgCount - 1;
                        $commission = $commissionDetail->commission + ($remainingCount * $importCollect['multiPkg']);
                    } else {
                        $commission = $commissionDetail->commission;
                    }
                } else {
                }
            } else {
            }
        }
        return $commission;
    }
}
