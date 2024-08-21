<?php

namespace App\Http\Controllers;

use App;
use App\Manifests;
use App\ManifestDetails;
use App\ManifestsFileDetails;
use App\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
use Aws\Textract\TextractClient;
use Aws\Exception\AwsException;
use PDF;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class ManifestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getmanifestports()
    {
        $port = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(port)'))
            ->whereNotNull('port')
            ->where('port', '!=', '')
            ->get();
        return json_encode($port);
    }

    public function getmanifestcarrier()
    {
        $carrier = DB::table('manifests')
            ->select(DB::raw('DISTINCT(carrier)'))
            ->whereNotNull('carrier')
            ->where('carrier', '!=', '')
            ->get();
        return json_encode($carrier);
    }

    public function getmanifestconsignee()
    {
        $consignee = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(consignee)'))
            ->whereNotNull('consignee')
            ->where('consignee', '!=', '')
            ->get();
        return json_encode($consignee);
    }

    public function getmanifestshipper()
    {
        $shipper = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(shipper)'))
            ->whereNotNull('shipper')
            ->where('shipper', '!=', '')
            ->get();
        return json_encode($shipper);
    }

    public function getmanifestcomodity()
    {
        $comodity = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(comodity)'))
            ->whereNotNull('comodity')
            ->where('comodity', '!=', '')
            ->get();
        return json_encode($comodity);
    }

    public function index()
    {
        $checkPermission = User::checkPermission(['listing_manifestes'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        /* $ships = DB::table('manifests')
            ->select(DB::raw('DISTINCT(bateau)'))
            ->whereNotNull('bateau')
            ->where('bateau', '!=', '')
            ->pluck('bateau', 'bateau');
        $ships = json_decode($ships, 1); */

        /* $carrier = DB::table('manifests')
            ->select(DB::raw('DISTINCT(carrier)'))
            ->whereNotNull('carrier')
            ->where('carrier', '!=', '')
            ->pluck('carrier', 'carrier');
        $carrier = json_decode($carrier, 1); */

        /* $port = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(port)'))
            ->whereNotNull('port')
            ->where('port', '!=', '')
            ->pluck('port', 'port');
        $port = json_decode($port, 1); */

        /* $consignee = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(consignee)'))
            ->whereNotNull('consignee')
            ->where('consignee', '!=', '')
            ->pluck('consignee', 'consignee');
        $consignee = json_decode($consignee, 1); */

        /* $shipper = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(shipper)'))
            ->whereNotNull('shipper')
            ->where('shipper', '!=', '')
            ->pluck('shipper', 'shipper');
        $shipper = json_decode($shipper, 1); */

        /* $comodity = DB::table('manifest_details')
            ->select(DB::raw('DISTINCT(comodity)'))
            ->whereNotNull('comodity')
            ->where('comodity', '!=', '')
            ->pluck('comodity', 'comodity');
        $comodity = json_decode($comodity, 1); */


        return view("manifests.index");
    }

    public function listbydatatableserverside(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $port = $req['port'];
        $carrier = $req['carrier'];
        $consignee = $req['consignee'];
        $shipper = $req['shipper'];
        $comodity = $req['comodity'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['id', 'cntrqty', 'shipper', 'consignee', 'port', 'weight', 'comodity', 'quantity', 'quantity_unit', 'bateau', 'no_voyage', 'date_voyage', 'carrier', 'created_on'];

        $total = ManifestDetails::selectRaw('count(*) as total');

        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('date_voyage', array($fromDate, $toDate));
        }
        if (!empty($port)) {
            $total = $total->where('port', $port);
        }
        if (!empty($carrier)) {
            $total = $total->where('carrier', $carrier);
        }
        if (!empty($consignee)) {
            $total = $total->where('consignee', $consignee);
        }
        if (!empty($shipper)) {
            $total = $total->where('shipper', $shipper);
        }
        if (!empty($comodity)) {
            $total = $total->where('comodity', $comodity);
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('manifest_details')
            ->selectRaw('manifest_details.*');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('date_voyage', array($fromDate, $toDate));
        }
        if (!empty($port)) {
            $query = $query->where('port', $port);
        }
        if (!empty($carrier)) {
            $query = $query->where('carrier', $carrier);
        }
        if (!empty($consignee)) {
            $query = $query->where('consignee', $consignee);
        }
        if (!empty($shipper)) {
            $query = $query->where('shipper', $shipper);
        }
        if (!empty($comodity)) {
            $query = $query->where('comodity', $comodity);
        }

        $filteredq = DB::table('manifest_details');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('date_voyage', array($fromDate, $toDate));
        }
        if (!empty($port)) {
            $filteredq = $filteredq->where('port', $port);
        }
        if (!empty($carrier)) {
            $filteredq = $filteredq->where('carrier', $carrier);
        }
        if (!empty($consignee)) {
            $filteredq = $filteredq->where('consignee', $consignee);
        }
        if (!empty($shipper)) {
            $filteredq = $filteredq->where('shipper', $shipper);
        }
        if (!empty($comodity)) {
            $filteredq = $filteredq->where('comodity', $comodity);
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('cntrqty', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('port', 'like', '%' . $search . '%')
                    ->orWhere('weight', 'like', '%' . $search . '%')
                    ->orWhere('comodity', 'like', '%' . $search . '%')
                    ->orWhere('quantity', 'like', '%' . $search . '%')
                    ->orWhere('quantity_unit', 'like', '%' . $search . '%')
                    ->orWhere('bateau', 'like', '%' . $search . '%')
                    ->orWhere('no_voyage', 'like', '%' . $search . '%')
                    ->orWhere('date_voyage', 'like', '%' . $search . '%')
                    ->orWhere('carrier', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('cntrqty', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('port', 'like', '%' . $search . '%')
                    ->orWhere('weight', 'like', '%' . $search . '%')
                    ->orWhere('comodity', 'like', '%' . $search . '%')
                    ->orWhere('quantity', 'like', '%' . $search . '%')
                    ->orWhere('quantity_unit', 'like', '%' . $search . '%')
                    ->orWhere('bateau', 'like', '%' . $search . '%')
                    ->orWhere('no_voyage', 'like', '%' . $search . '%')
                    ->orWhere('date_voyage', 'like', '%' . $search . '%')
                    ->orWhere('carrier', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        $data = [];
        ['id', 'cntrqty', 'shipper', 'consignee', 'port', 'weight', 'comodity', 'quantity', 'quantity_unit', 'bateau', 'no_voyage', 'date_voyage', 'carrier', 'created_on'];
        foreach ($query as $key => $items) {
            $data[] = [$items->id, $items->cntrqty, $items->shipper, $items->consignee, $items->port, $items->weight, $items->comodity, $items->quantity, $items->quantity_unit, $items->bateau, $items->no_voyage, date('d-m-Y', strtotime($items->date_voyage)), $items->carrier, date('d-m-Y h:i:s', strtotime($items->created_on))];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function printandexport($fromDate = null, $toDate = null, $port = null, $carrier = null, $consignee = null, $shipper = null, $comodity = null, $submitButtonName = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        $query = DB::table('manifest_details')
            ->selectRaw('manifest_details.*');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('date_voyage', array($fromDate, $toDate));
        }
        if (!empty($port)) {
            $query = $query->where('port', $port);
        }
        if (!empty($carrier)) {
            $query = $query->where('carrier', $carrier);
        }
        if (!empty($consignee)) {
            $query = $query->where('consignee', $consignee);
        }
        if (!empty($shipper)) {
            $query = $query->where('shipper', $shipper);
        }
        if (!empty($comodity)) {
            $query = $query->where('comodity', $comodity);
        }

        $query = $query->orderBy('id', 'desc')->get();
        if ($submitButtonName == 'clsPrint') {
            // dd($query);
            // $pdf = new Mpdf();
            // $html = view('manifests.print', ['query' => $query, 'fromDate' => $fromDate, 'toDate' => $toDate])->render();
            // $pdf = PDF::loadView('manifests.print', ['query' => $query, 'fromDate' => $fromDate, 'toDate' => $toDate]);
            $pdf = PDF::chunkLoadView('<!-- chunk -->','manifests.print', ['query' => $query, 'fromDate' => $fromDate, 'toDate' => $toDate]);
            $pdf_file = 'manifests.pdf';
            $pdf_path = 'public/manifestsAll/' . $pdf_file;
            $pdf->save($pdf_path);
            // $chunks = explode("<!-- chunk -->", $html);
            // foreach ($chunks as $key => $val) {
            //     $pdf->WriteHTML($val);
            // }
            // $pdf->Output($pdf_file, 'D');
            return url('/') . '/' . $pdf_path;
        } else {
            $fileName = 'manifests.csv';
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            $columns = array('CntrQty', 'Shipper', 'Consignee', 'Port', 'Weight', 'Comodity', 'Quantity', 'Unit', 'Ship', 'Trip Number', 'Trip Date', 'Carrier', 'Added On');
            $callback = function () use ($query, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($query as $task) {
                    $row['cntrqty']  = $task->cntrqty;
                    $row['shipper']    = $task->shipper;
                    $row['consignee']  = $task->consignee;
                    $row['port']  = $task->port;
                    $row['weight']  = $task->weight;
                    $row['comodity']  = $task->comodity;
                    $row['quantity']  = $task->quantity;
                    $row['quantity_unit']  = $task->quantity_unit;
                    $row['bateau']  = $task->bateau;
                    $row['no_voyage']  = $task->no_voyage;
                    $row['date_voyage']  = date('d-m-Y', strtotime($task->date_voyage));
                    $row['carrier']  = $task->carrier;
                    $row['created_on']  = date('d-m-Y h:i:s', strtotime($task->created_on));

                    fputcsv($file, array($row['cntrqty'], $row['shipper'], $row['consignee'], $row['port'], $row['weight'], $row['comodity'], $row['quantity'], $row['quantity_unit'], $row['bateau'], $row['no_voyage'], $row['date_voyage'], $row['carrier'], $row['created_on']));
                }

                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
    }

    public function import()
    {
        $checkPermission = User::checkPermission(['import_manifestes'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
        $model = new Manifests();

        $fileDetails = DB::table('manifests_file_details')->where('deleted', 0)->orderBy('id', 'desc')->get();

        return view('manifests.import', ['model' => $model, 'fileDetails' => $fileDetails]);
    }

    public function reloadfilestatus()
    {
        $fileDetails = DB::table('manifests_file_details')->where('deleted', 0)->orderBy('id', 'desc')->get();

        return view('manifests.reloadfilestatus', ['fileDetails' => $fileDetails]);
    }

    public function progressstatus()
    {
        $id = $_POST['id'];
        $fileDetails = DB::table('manifests_file_details')->where('id', $id)->first();
        if ($fileDetails->upload_status == 'Uploaded') {
            ManifestsFileDetails::where('id', $id)->update(['uploaded_percentage' => 100]);
        } else {
            if (empty($fileDetails->uploaded_seconds))
                $uploadedSeconds = 2;
            else
                $uploadedSeconds = 2 + $fileDetails->uploaded_seconds;
            $uploadedPercentage = $uploadedSeconds * 100 / 836;
            if($uploadedPercentage > 100)
                $uploadedPercentage = 100;
            ManifestsFileDetails::where('id', $id)->update(['uploaded_percentage' => $uploadedPercentage, 'uploaded_seconds' => $uploadedSeconds]);
        }
        $fileDetailsNew = DB::table('manifests_file_details')->where('id', $id)->first();
        $data = array();
        $data['uploadStatus'] = $fileDetailsNew->upload_status;
        $data['uploadPercentage'] = $fileDetailsNew->uploaded_percentage;
        return json_encode($data);
    }

    public function importdata(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        if ($request->hasFile('import_file')) {
            $file = $request->file('import_file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $pdf_path = 'public/manifestsAll/' . $fileName;

            $destinationPath = 'public/manifestsAll';
            $file->move($destinationPath, $fileName);

            $s3path = 'Files/Manifests/' . $fileName;
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path, $filecontent, 'public');

            $client = new TextractClient([
                'version' => 'latest',
                //'version' => '2018-06-27',
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => 'AKIAYRVCP4JKZPIAAYAH',
                    'secret' => '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',
                ]
            ]);

            try {
                $result = $client->startDocumentAnalysis([
                    'DocumentLocation' => [ // REQUIRED
                        'S3Object' => [
                            'Bucket' => 'cargo-live-site',
                            'Name' => $s3path
                        ],
                    ],
                    'FeatureTypes' => ['TABLES'],
                    "NotificationChannel" => [
                        "SNSTopicArn" => "arn:aws:sns:us-east-1:625865427081:Demo",
                        "RoleArn" => "arn:aws:iam::625865427081:role/TextractRole"
                    ],
                    "JobTag" => "Receipt"
                ]);

                $manifestFileDetailInput['file_name'] = $fileName;
                $manifestFileDetailInput['total_pages'] = null;
                $manifestFileDetailInput['created_on'] = gmdate("Y-m-d H:i:s");
                $manifestFileDetailInput['upload_status'] = 'In process';
                $dataManifestFileDetails = ManifestsFileDetails::create($manifestFileDetailInput);

                $fData['jobId'] = $result['JobId'];
                $fData['dataManifestFileDetailsID'] = $dataManifestFileDetails->id;
                $fData['fileName'] = $fileName;
                $fData['createdUserId'] = auth()->user()->id;
                $allData = base64_encode(serialize($fData));

                $urlAction = url('manifests/background?datas=' . $allData);
                $adminModel = new Admin;
                $adminModel->backgroundPostForManifest($urlAction);

                return $dataManifestFileDetails->id;

                /* Session::flash('flash_message', 'Record has been imported successfully');
                return redirect('manifests/listing'); */
            } catch (AwsException $e) {

                /* echo "<pre>";
                echo "<pre>Error: $e</pre>";
                echo "</pre>"; */
            }
        }
    }

    public function background($datas = null)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        $datasAll = unserialize(base64_decode($datas, true));
        $jobId = $datasAll['jobId'];
        $dataManifestFileDetailsID = $datasAll['dataManifestFileDetailsID'];
        $fileName = $datasAll['fileName'];
        $createdUserId = $datasAll['createdUserId'];
        file_put_contents('test.txt', print_r($jobId, true));
        $client = new TextractClient([
            'version' => 'latest',
            //'version' => '2018-06-27',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => 'AKIAYRVCP4JKZPIAAYAH',
                'secret' => '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',
            ]
        ]);
        file_put_contents('test.txt', print_r($client, true));

        /* $manifestFileDetailInput['file_name'] = $fileName;
        $manifestFileDetailInput['total_pages'] = null;
        $manifestFileDetailInput['created_on'] = gmdate("Y-m-d H:i:s");
        $manifestFileDetailInput['created_by'] = $createdUserId;
        $manifestFileDetailInput['upload_status'] = 'In process';
        $dataManifestFileDetails = ManifestsFileDetails::create($manifestFileDetailInput); */
        ManifestsFileDetails::where('id', $dataManifestFileDetailsID)->update(['created_by' => $createdUserId]);

        sleep(800);
        $result1 = $client->getDocumentAnalysis([
            'JobId' => $jobId, // REQUIRED                
            //'JobId' => '1e61e0203aab212d7c05f41cd0210a5ca69fbc7aa4c5706d99a3f117a2c5d0f2', // REQUIRED                
            //'NextToken' => 'tkJOXx9UZpUFjj64TonSwhLzF5COZIpA/o0dFzZ9aEoPhT8T1GV6DRVtq8c/WmGrA4UcEK28lwxWRGk7VrwMIDBEs0SGGmiVJH6HiNUeyvcJ64TljQ==',
            //'MaxResults' => 1000,
        ]);
        if ($result1['JobStatus'] == 'IN_PROGRESS') {
            sleep(50);
        }
        file_put_contents('test.txt', print_r($result1['JobStatus'], true));
        $totalPages = $result1['DocumentMetadata']['Pages'];

        ManifestsFileDetails::where('id', $dataManifestFileDetailsID)->update(['total_pages' => $totalPages]);

        $rows = array();
        $nextTokenArray = array();
        $ConfidenceArray = array();
        $allBlockArray = array();
        for ($pageCount = 1; $pageCount <= $totalPages; $pageCount++) {
            if ($pageCount == 1) {
                $allBlockArray[] = $result1['Blocks'];
                foreach ($result1['Blocks'] as $k => $v) {
                    if ($v['BlockType'] == 'CELL') {
                        $row_index = $v['RowIndex'];
                        $col_index = $v['ColumnIndex'];
                        $Confidence = (string) $v['Confidence'];
                        $ConfidenceArray[] = $Confidence;

                        if (!empty($v['Relationships'])) {
                            $wordIds = $v['Relationships'][0]['Ids'];
                            $rows[$Confidence][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $allBlockArray));
                            //$rows[$pageCount][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $result1['Blocks']));
                        } else {
                            $rows[$Confidence][$row_index][$col_index] = '';
                            //$rows[$pageCount][$row_index][$col_index] = '';
                        }
                    }
                }
                /* foreach ($rows as $tk1 => $vk1) {
                        foreach ($vk1 as $tk11 => $vk11) {
                            ksort($rows[$tk1][$tk11]);
                        }
                    } */
                //ksort($rows[$pageCount]);
            } else {
                if (!empty($result1['NextToken'])) {
                    $nextTokenArray[] = $result1['NextToken'];
                    $result1 = $client->getDocumentAnalysis([
                        'JobId' => $jobId, // REQUIRED                
                        //'JobId' => '1e61e0203aab212d7c05f41cd0210a5ca69fbc7aa4c5706d99a3f117a2c5d0f2', // REQUIRED                
                        'NextToken' => $result1['NextToken'],
                        //'MaxResults' => 1000,
                    ]);

                    $allBlockArray[] = $result1['Blocks'];
                    $allBlockArrayLastTwo = array_slice($allBlockArray, -2, 2, true);
                    foreach ($result1['Blocks'] as $k1 => $v1) {
                        if ($v1['BlockType'] == 'CELL') {
                            $row_index = $v1['RowIndex'];
                            $col_index = $v1['ColumnIndex'];
                            $Confidence = (string) $v1['Confidence'];
                            /* if (in_array($Confidence, $ConfidenceArray))
                                continue; */

                            if (!empty($v1['Relationships'])) {
                                $wordIds = $v1['Relationships'][0]['Ids'];
                                $rows[$Confidence][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $allBlockArrayLastTwo));
                                //$rows[$pageCount][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $result1['Blocks']));
                            } else {
                                $rows[$Confidence][$row_index][$col_index] = '';
                                //$rows[$pageCount][$row_index][$col_index] = '';
                            }
                            ksort($rows[$Confidence][$row_index]);
                            //ksort($rows[$pageCount][$row_index]);
                        }
                    }
                    /* foreach ($rows as $tk1 => $vk1) {
                        foreach ($vk1 as $tk11 => $vk11) {
                            ksort($rows[$tk1][$tk11]);
                        }
                    } */
                    //ksort($rows[$pageCount]);
                }
            }
        }

        $wholeArray = array();
        $i = 1;
        foreach ($rows as $keyF => $valueF) {
            foreach ($valueF as $keyF1 => $valueF1) {
                $wholeArray[$i] = $valueF[$keyF1];
                $i++;
            }
        }

        $seperatedArray = array();
        $k = 1;
        $l = 1;
        foreach ($wholeArray as $keyW => $valueW) {
            $newF = array();
            if ($valueW[1] == 'Bateau' && $l == 1) {
                $seperatedArray[$k]['shippingInfo'] = $valueW;
            } else if (count($valueW) == 7 && $valueW[1] != 'Bateau' && strpos($valueW[1], 'Bateau') === false) {
                if ($valueW[3] == '') {
                    $sevenToSevenArray = array();
                    if ($valueW[1] == 'CntrQty Shipper') {
                        $sevenToSevenArray = explode(' ', $valueW[1]);
                    } else {
                        $sevenToSevenArray = explode(' ', $valueW[1], 2);
                    }
                    $sevenToSevenArray[2] = $valueW[2];
                    unset($valueW[1]);
                    unset($valueW[2]);
                    unset($valueW[3]);
                    $newF = array_merge($sevenToSevenArray, $valueW);
                    $seperatedArray[$k]['data'][] = array_combine(range(1, count($newF)), array_values($newF));
                } else {
                    $seperatedArray[$k]['data'][] = $valueW;
                }
            } else if (count($valueW) == 8 && $valueW[1] != 'Bateau' && strpos($valueW[1], 'Bateau') === false) {
                foreach ($valueW as $kc => $kv) {
                    if (empty($kv))
                        $blankKey = $kc;
                }
                unset($valueW[$blankKey]);
                //$seperatedArray[$k]['data'][] = array_values($valueW);
                $seperatedArray[$k]['data'][] = array_combine(range(1, count($valueW)), array_values($valueW));
            } else if (count($valueW) == 6 && $valueW[1] != 'Bateau' && strpos($valueW[1], 'Bateau') === false) {
                $explodeToIdentifyCntrQty = explode(' ', $valueW[1]);
                $sixToSevenArray = array();
                if ($valueW[1] == 'CntrQty Shipper') {
                    $sixToSevenArray = explode(' ', $valueW[1]);
                    unset($valueW[1]);
                    $newF = array_merge($sixToSevenArray, $valueW);
                    $seperatedArray[$k]['data'][] = array_combine(range(1, count($newF)), array_values($newF));
                } else if (empty($valueW[1]) && $valueW[2] == 'Consignee') {
                    $newF[1] = 'CntrQty';
                    $newF[2] = 'Shipper';
                    $newF[3] = $valueW[2];
                    $newF[4] = $valueW[3];
                    $newF[5] = $valueW[4];
                    $newF[6] = $valueW[5];
                    $newF[7] = $valueW[6];
                    $seperatedArray[$k]['data'][] = $newF;
                } else if ((int) $explodeToIdentifyCntrQty[0] === 0 && $valueW[1] != 'CntrQty') {
                    $newF[1] = '0';
                    $newF[2] = $valueW[1];
                    $newF[3] = $valueW[2];
                    $newF[4] = $valueW[3];
                    $newF[5] = $valueW[4];
                    $newF[6] = $valueW[5];
                    $newF[7] = $valueW[6];
                    $seperatedArray[$k]['data'][] = $newF;
                } else if ($valueW[1] == 'CntrQty') {
                    $newF[1] = $valueW[1];
                    $newF[2] = $valueW[2];
                    $newF[3] = $valueW[3];
                    $newF[4] = $valueW[4];
                    $newF[5] = $valueW[5];
                    $newF[6] = $valueW[6];
                    $newF[7] = 'Quantity';
                    $seperatedArray[$k]['data'][] = $newF;
                } else if (is_numeric(trim($valueW[1])) && (int) trim($valueW[1]) > 0) {
                    $newF[1] = $valueW[1];
                    $newF[2] = $valueW[2];
                    $newF[3] = $valueW[3];
                    $newF[4] = $valueW[4];
                    $newF[5] = $valueW[5];
                    $newF[6] = $valueW[6];
                    $newF[7] = null;
                    $seperatedArray[$k]['data'][] = $newF;
                } else {
                    $sixToSevenArray = explode(' ', $valueW[1], 2);
                    unset($valueW[1]);
                    $newF = array_merge($sixToSevenArray, $valueW);
                    $seperatedArray[$k]['data'][] = array_combine(range(1, count($newF)), array_values($newF));
                }
            } else {
                if (count($valueW) > 5) {
                    $k++;
                    $seperatedArray[$k]['shippingInfo'] = $valueW;
                }
            }
            $l++;
        }

        $manifestInput = array();
        $manifestDetailInput = array();
        $implArray = array();
        if (!empty($seperatedArray)) {
            foreach ($seperatedArray as $sk1 => $vk1) {
                if (isset($vk1['shippingInfo']) && isset($vk1['data'])) {
                    array_push($vk1['shippingInfo'], 'TheEnd');
                    $manifestInput['bateau'] = $this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'Bateau', 'Date Voyage');
                    $manifestInput['date_voyage'] = date('Y-m-d', strtotime($this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'Date Voyage', 'No Voyage')));
                    $manifestInput['no_voyage'] = $this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'No Voyage', 'Carrier');
                    $manifestInput['carrier'] = $this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'Carrier', 'TheEnd');
                    //$implArray[] = implode(' ', $vk1['shippingInfo']);
                    $manifestInput['created_on'] = gmdate("Y-m-d H:i:s");
                    $manifestInput['all_details'] = implode('|', $vk1['shippingInfo']);
                    $data = Manifests::create($manifestInput);

                    foreach ($vk1['data'] as $a1 => $b1) {
                        if ($b1[1] == 'CntrQty')
                            continue;

                        if (count($b1) != 7)
                            continue;

                        $manifestDetailInput['manifest_id'] = $data->id;
                        $manifestDetailInput['bateau'] = $manifestInput['bateau'];
                        $manifestDetailInput['date_voyage'] = $manifestInput['date_voyage'];
                        $manifestDetailInput['no_voyage'] = $manifestInput['no_voyage'];
                        $manifestDetailInput['carrier'] = $manifestInput['carrier'];
                        $manifestDetailInput['cntrqty'] = (int) $b1[1];
                        $manifestDetailInput['shipper'] = $b1[2];
                        $manifestDetailInput['consignee'] = $b1[3];
                        $manifestDetailInput['port'] = $b1[4];
                        $manifestDetailInput['weight'] = $b1[5];
                        $manifestDetailInput['comodity'] = $b1[6];
                        $resultUnit = preg_replace("/[^a-zA-Z]+/", "", $b1[7]);
                        $resultQty = trim(str_replace($resultUnit, '', $b1[7]));
                        //$manifestDetailInput['quantity'] = $b1[7];
                        $manifestDetailInput['quantity'] = $resultQty;
                        $manifestDetailInput['quantity_unit'] = $resultUnit;
                        $manifestDetailInput['created_on'] = gmdate("Y-m-d H:i:s");
                        $dataManifestDetails = ManifestDetails::create($manifestDetailInput);
                        //pre($dataManifestDetails);
                    }
                }
            }
            ManifestsFileDetails::where('id', $dataManifestFileDetailsID)->update(['upload_status' => 'Uploaded']);
        } else {
            ManifestsFileDetails::where('id', $dataManifestFileDetailsID)->update(['upload_status' => 'Failed']);
        }
    }

    public function importdata_bk(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        if ($request->hasFile('import_file')) {
            $file = $request->file('import_file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $pdf_path = 'public/manifestsAll/' . $fileName;

            $destinationPath = 'public/manifestsAll';
            $file->move($destinationPath, $fileName);

            $s3path = 'Files/Manifests/' . $fileName;
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path, $filecontent, 'public');

            $client = new TextractClient([
                'version' => 'latest',
                //'version' => '2018-06-27',
                'region' => 'us-east-1',
                'credentials' => [
                    'key' => 'AKIAYRVCP4JKZPIAAYAH',
                    'secret' => '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',
                ]
            ]);

            try {
                $result = $client->startDocumentAnalysis([
                    'DocumentLocation' => [ // REQUIRED
                        'S3Object' => [
                            'Bucket' => 'cargo-live-site',
                            'Name' => $s3path
                        ],
                    ],
                    'FeatureTypes' => ['TABLES'],
                    "NotificationChannel" => [
                        "SNSTopicArn" => "arn:aws:sns:us-east-1:625865427081:Demo",
                        "RoleArn" => "arn:aws:iam::625865427081:role/TextractRole"
                    ],
                    "JobTag" => "Receipt"
                ]);

                //pre($result['JobId'],1);
                sleep(80);
                $result1 = $client->getDocumentAnalysis([
                    'JobId' => $result['JobId'], // REQUIRED                
                    //'JobId' => '1e61e0203aab212d7c05f41cd0210a5ca69fbc7aa4c5706d99a3f117a2c5d0f2', // REQUIRED                
                    //'NextToken' => 'tkJOXx9UZpUFjj64TonSwhLzF5COZIpA/o0dFzZ9aEoPhT8T1GV6DRVtq8c/WmGrA4UcEK28lwxWRGk7VrwMIDBEs0SGGmiVJH6HiNUeyvcJ64TljQ==',
                    //'MaxResults' => 1000,
                ]);
                if ($result1['JobStatus'] == 'IN_PROGRESS') {
                    sleep(20);
                }
                //pre($result1);
                //$retResult = $result1['JobStatus'];
                /* $result1->then(
                    function ($value) {
                        if($value->get('JobStatus') == 'SUCCEEDED')
                        {
                            pre("success");
                        }else{
                            pre("inprogress");
                        }
                    },
                    function ($reason) {
                        echo "The promise was rejected with {$reason}";
                    }
                );
                
                $result55 = $result1->wait();
                exit;
                pre($result55->get('JobStatus'));
                pre($result1); */
                $totalPages = $result1['DocumentMetadata']['Pages'];
                $rows = array();
                $nextTokenArray = array();
                $ConfidenceArray = array();
                $allBlockArray = array();
                for ($pageCount = 1; $pageCount <= $totalPages; $pageCount++) {
                    if ($pageCount == 1) {
                        $allBlockArray[] = $result1['Blocks'];
                        foreach ($result1['Blocks'] as $k => $v) {
                            if ($v['BlockType'] == 'CELL') {
                                $row_index = $v['RowIndex'];
                                $col_index = $v['ColumnIndex'];
                                $Confidence = (string) $v['Confidence'];
                                $ConfidenceArray[] = $Confidence;

                                if (!empty($v['Relationships'])) {
                                    $wordIds = $v['Relationships'][0]['Ids'];
                                    $rows[$Confidence][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $allBlockArray));
                                    //$rows[$pageCount][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $result1['Blocks']));
                                } else {
                                    $rows[$Confidence][$row_index][$col_index] = '';
                                    //$rows[$pageCount][$row_index][$col_index] = '';
                                }
                            }
                        }
                        /* foreach ($rows as $tk1 => $vk1) {
                        foreach ($vk1 as $tk11 => $vk11) {
                            ksort($rows[$tk1][$tk11]);
                        }
                    } */
                        //ksort($rows[$pageCount]);
                    } else {
                        if (!empty($result1['NextToken'])) {
                            $nextTokenArray[] = $result1['NextToken'];
                            $result1 = $client->getDocumentAnalysis([
                                'JobId' => $result['JobId'], // REQUIRED                
                                //'JobId' => '1e61e0203aab212d7c05f41cd0210a5ca69fbc7aa4c5706d99a3f117a2c5d0f2', // REQUIRED                
                                'NextToken' => $result1['NextToken'],
                                //'MaxResults' => 1000,
                            ]);

                            $allBlockArray[] = $result1['Blocks'];
                            $allBlockArrayLastTwo = array_slice($allBlockArray, -2, 2, true);
                            foreach ($result1['Blocks'] as $k1 => $v1) {
                                if ($v1['BlockType'] == 'CELL') {
                                    $row_index = $v1['RowIndex'];
                                    $col_index = $v1['ColumnIndex'];
                                    $Confidence = (string) $v1['Confidence'];
                                    /* if (in_array($Confidence, $ConfidenceArray))
                                continue; */

                                    if (!empty($v1['Relationships'])) {
                                        $wordIds = $v1['Relationships'][0]['Ids'];
                                        $rows[$Confidence][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $allBlockArrayLastTwo));
                                        //$rows[$pageCount][$row_index][$col_index] = implode(' ', $this->get_text($wordIds, $result1['Blocks']));
                                    } else {
                                        $rows[$Confidence][$row_index][$col_index] = '';
                                        //$rows[$pageCount][$row_index][$col_index] = '';
                                    }
                                    ksort($rows[$Confidence][$row_index]);
                                    //ksort($rows[$pageCount][$row_index]);
                                }
                            }
                            /* foreach ($rows as $tk1 => $vk1) {
                        foreach ($vk1 as $tk11 => $vk11) {
                            ksort($rows[$tk1][$tk11]);
                        }
                    } */
                            //ksort($rows[$pageCount]);
                        }
                    }
                }

                $wholeArray = array();
                $i = 1;
                foreach ($rows as $keyF => $valueF) {
                    foreach ($valueF as $keyF1 => $valueF1) {
                        $wholeArray[$i] = $valueF[$keyF1];
                        $i++;
                    }
                }
                //pre($wholeArray);
                $seperatedArray = array();
                $k = 1;
                $l = 1;
                foreach ($wholeArray as $keyW => $valueW) {
                    $newF = array();
                    if ($valueW[1] == 'Bateau' && $l == 1) {
                        $seperatedArray[$k]['shippingInfo'] = $valueW;
                    } else if (count($valueW) == 7 && $valueW[1] != 'Bateau' && strpos($valueW[1], 'Bateau') === false) {
                        if ($valueW[3] == '') {
                            $sevenToSevenArray = array();
                            if ($valueW[1] == 'CntrQty Shipper') {
                                $sevenToSevenArray = explode(' ', $valueW[1]);
                            } else {
                                $sevenToSevenArray = explode(' ', $valueW[1], 2);
                            }
                            $sevenToSevenArray[2] = $valueW[2];
                            unset($valueW[1]);
                            unset($valueW[2]);
                            unset($valueW[3]);
                            $newF = array_merge($sevenToSevenArray, $valueW);
                            $seperatedArray[$k]['data'][] = array_combine(range(1, count($newF)), array_values($newF));
                        } else {
                            $seperatedArray[$k]['data'][] = $valueW;
                        }
                    } else if (count($valueW) == 8 && $valueW[1] != 'Bateau' && strpos($valueW[1], 'Bateau') === false) {
                        foreach ($valueW as $kc => $kv) {
                            if (empty($kv))
                                $blankKey = $kc;
                        }
                        unset($valueW[$blankKey]);
                        //$seperatedArray[$k]['data'][] = array_values($valueW);
                        $seperatedArray[$k]['data'][] = array_combine(range(1, count($valueW)), array_values($valueW));
                    } else if (count($valueW) == 6 && $valueW[1] != 'Bateau' && strpos($valueW[1], 'Bateau') === false) {
                        $explodeToIdentifyCntrQty = explode(' ', $valueW[1]);
                        $sixToSevenArray = array();
                        if ($valueW[1] == 'CntrQty Shipper') {
                            $sixToSevenArray = explode(' ', $valueW[1]);
                            unset($valueW[1]);
                            $newF = array_merge($sixToSevenArray, $valueW);
                            $seperatedArray[$k]['data'][] = array_combine(range(1, count($newF)), array_values($newF));
                        } else if (empty($valueW[1]) && $valueW[2] == 'Consignee') {
                            $newF[1] = 'CntrQty';
                            $newF[2] = 'Shipper';
                            $newF[3] = $valueW[2];
                            $newF[4] = $valueW[3];
                            $newF[5] = $valueW[4];
                            $newF[6] = $valueW[5];
                            $newF[7] = $valueW[6];
                            $seperatedArray[$k]['data'][] = $newF;
                        } else if ((int) $explodeToIdentifyCntrQty[0] === 0 && $valueW[1] != 'CntrQty') {
                            $newF[1] = '0';
                            $newF[2] = $valueW[1];
                            $newF[3] = $valueW[2];
                            $newF[4] = $valueW[3];
                            $newF[5] = $valueW[4];
                            $newF[6] = $valueW[5];
                            $newF[7] = $valueW[6];
                            $seperatedArray[$k]['data'][] = $newF;
                        } else if ($valueW[1] == 'CntrQty') {
                            $newF[1] = $valueW[1];
                            $newF[2] = $valueW[2];
                            $newF[3] = $valueW[3];
                            $newF[4] = $valueW[4];
                            $newF[5] = $valueW[5];
                            $newF[6] = $valueW[6];
                            $newF[7] = 'Quantity';
                            $seperatedArray[$k]['data'][] = $newF;
                        } else if (is_numeric(trim($valueW[1])) && (int) trim($valueW[1]) > 0) {
                            $newF[1] = $valueW[1];
                            $newF[2] = $valueW[2];
                            $newF[3] = $valueW[3];
                            $newF[4] = $valueW[4];
                            $newF[5] = $valueW[5];
                            $newF[6] = $valueW[6];
                            $newF[7] = null;
                            $seperatedArray[$k]['data'][] = $newF;
                        } else {
                            $sixToSevenArray = explode(' ', $valueW[1], 2);
                            unset($valueW[1]);
                            $newF = array_merge($sixToSevenArray, $valueW);
                            $seperatedArray[$k]['data'][] = array_combine(range(1, count($newF)), array_values($newF));
                        }
                    } else {
                        if (count($valueW) > 5) {
                            $k++;
                            $seperatedArray[$k]['shippingInfo'] = $valueW;
                        }
                    }
                    $l++;
                } //pre($seperatedArray);
?>
                <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->

                <?php /* $i = 1;
                foreach ($rows as $keys2 => $rows2) { ?>
                    <h4 style="background: #ccc"><?php echo "Table : " . $i . "<br>"; ?></h4>
                    <?php if (count($rows[$keys2][1]) == 7) { ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">CntrQty</th>
                                    <th scope="col">Shipper</th>
                                    <th scope="col">Consignee</th>
                                    <th scope="col">Port</th>
                                    <th scope="col">weight Kg</th>
                                    <th scope="col">Comodity</th>
                                    <th scope="col">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rows2 as $k => $v) {
                                    if (count($v) == 7 && $v[1] != 'CntrQty') { ?>
                                        <tr>
                                            <?php foreach ($v as $k1 => $v1) { ?>


                                                <td><?php echo !empty($v1) ? $v1 : '-'; ?></td>


                                            <?php } ?>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    <?php } else if (count($rows[$keys2][1]) == 6) { ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Shipper</th>
                                    <th scope="col">Consignee</th>
                                    <th scope="col">Port</th>
                                    <th scope="col">weight Kg</th>
                                    <th scope="col">Comodity</th>
                                    <th scope="col">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rows2 as $k => $v) {
                                    if (count($v) == 6 && $v[1] != 'CntrQty') { ?>
                                        <tr>
                                            <?php foreach ($v as $k1 => $v1) { ?>


                                                <td><?php echo !empty($v1) ? $v1 : '-'; ?></td>


                                            <?php } ?>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">CntrQty</th>
                                    <th scope="col">Shipper</th>
                                    <th scope="col">Consignee</th>
                                    <th scope="col">Port</th>
                                    <th scope="col">-</th>
                                    <th scope="col">weight Kg</th>
                                    <th scope="col">Comodity</th>
                                    <th scope="col">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rows2 as $k => $v) {
                                    if (count($v) == 8 && $v[1] != 'CntrQty') { ?>
                                        <tr>
                                            <?php foreach ($v as $k1 => $v1) { ?>


                                                <td><?php echo !empty($v1) ? $v1 : '-'; ?></td>


                                            <?php } ?>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                <?php }
                    $i++;
                }
                exit; */

                ?>

<?php
                //pre($seperatedArray);
                $manifestInput = array();
                $manifestDetailInput = array();
                $implArray = array();
                foreach ($seperatedArray as $sk1 => $vk1) {
                    if (isset($vk1['shippingInfo']) && isset($vk1['data'])) {
                        array_push($vk1['shippingInfo'], 'TheEnd');
                        $manifestInput['bateau'] = $this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'Bateau', 'Date Voyage');
                        $manifestInput['date_voyage'] = date('Y-m-d', strtotime($this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'Date Voyage', 'No Voyage')));
                        $manifestInput['no_voyage'] = $this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'No Voyage', 'Carrier');
                        $manifestInput['carrier'] = $this->string_between_two_string(implode(' ', $vk1['shippingInfo']), 'Carrier', 'TheEnd');
                        //$implArray[] = implode(' ', $vk1['shippingInfo']);
                        $manifestInput['created_on'] = gmdate("Y-m-d H:i:s");
                        $manifestInput['all_details'] = implode('|', $vk1['shippingInfo']);
                        $data = Manifests::create($manifestInput);

                        foreach ($vk1['data'] as $a1 => $b1) {
                            if ($b1[1] == 'CntrQty')
                                continue;

                            if (count($b1) != 7)
                                continue;

                            $manifestDetailInput['manifest_id'] = $data->id;
                            $manifestDetailInput['bateau'] = $manifestInput['bateau'];
                            $manifestDetailInput['date_voyage'] = $manifestInput['date_voyage'];
                            $manifestDetailInput['no_voyage'] = $manifestInput['no_voyage'];
                            $manifestDetailInput['carrier'] = $manifestInput['carrier'];
                            $manifestDetailInput['cntrqty'] = (int) $b1[1];
                            $manifestDetailInput['shipper'] = $b1[2];
                            $manifestDetailInput['consignee'] = $b1[3];
                            $manifestDetailInput['port'] = $b1[4];
                            $manifestDetailInput['weight'] = $b1[5];
                            $manifestDetailInput['comodity'] = $b1[6];
                            $resultUnit = preg_replace("/[^a-zA-Z]+/", "", $b1[7]);
                            $resultQty = trim(str_replace($resultUnit, '', $b1[7]));
                            //$manifestDetailInput['quantity'] = $b1[7];
                            $manifestDetailInput['quantity'] = $resultQty;
                            $manifestDetailInput['quantity_unit'] = $resultUnit;
                            $manifestDetailInput['created_on'] = gmdate("Y-m-d H:i:s");
                            $dataManifestDetails = ManifestDetails::create($manifestDetailInput);
                            //pre($dataManifestDetails);
                        }
                    }
                }
                Session::flash('flash_message', 'Record has been imported successfully');
                return redirect('manifests/listing');
            } catch (AwsException $e) {
                echo "<pre>";
                echo "<pre>Error: $e</pre>";
                echo "</pre>";
            }
        }
    }

    public function get_text($wordIds, $blockData)
    {
        $allIdsOfWord = array();
        $allWords = array();
        $wholeWord = array();
        foreach ($blockData as $k1 => $v1) {
            foreach ($v1 as $k => $v) {
                if ($v['BlockType'] == 'WORD') {
                    $allIdsOfWord[] = $v['Id'];
                    $allWords[] = $v['Text'];
                    if (in_array($v['Id'], $wordIds)) {
                        $wholeWord[] = $v['Text'];
                    }
                }
            }
        }
        /* echo "<pre>";
        print_r($allWords); exit; */
        /* print_r($allIdsOfWord);
        print_r($wholeWord); exit;
        */
        return $wholeWord;
    }

    public function string_between_two_string($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        //Adding the strating index of the strating word to  
        //its length would give its ending index 
        $subtring_start += strlen($starting_word);
        //Length of our required sub string 
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
        // Return the substring from the index substring_start of length size  
        return trim(str_replace(':', '', substr($str, $subtring_start, $size)));
    }

    public function checkuniquefile(Request $request)
    {
        $value = $_POST['value'];
        //pre($value,1);
        $dataF = DB::table('manifests_file_details')->where('deleted', '0')->get();
        $data = 0;
        foreach($dataF as $k => $v)
        {
            $remaining = '';
            $exploaded = explode('_',$v->file_name);
            unset($exploaded[0]);
            $remaining = implode('', $exploaded);
            /* var_dump($value);
            var_dump($remaining); */
            if($remaining == $value)
            {
                $data = 1;
            }
        }
        if ($data == 1)
            return 1;
        else
            return 0;
        
    }
}
