<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Admin;
class quickBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $accessToken;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
        //pre("ddd",1);
        pre($this->accessToken,1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        file_put_contents('test.txt', print_r("Nirl123", true));
        //session_start();
        $accessTokens = unserialize(base64_decode($this->accessToken, true));
        // Create customer
        $customerData = DB::table('clients')
            //->where('deleted', '0')->where('qb_sync', 0)
            ->orderBy('id', 'desc')->where('client_flag', 'B')->limit(1)->get();
        foreach ($customerData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '11';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'client';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateClient';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteClient';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        //exit;
        /* $modelAdmin = new Admin;
        $modelAdmin->qbApiCall('invoice',$this->model); */
    }
}
