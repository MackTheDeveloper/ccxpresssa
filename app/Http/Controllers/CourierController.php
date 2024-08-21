<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Courier;
use Session;
use DB;
use Excel;
use stdClass;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['create_couriers', 'import_couriers', 'update_couriers', 'delete_couriers'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $couriers = DB::table('courier_detail')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("couriers.index", ['couriers' => $couriers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['create_couriers'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Courier;
        return view('couriers._form', ['model' => $model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validater = $this->validate($request, [
            'consignee_name' => 'required|string',
            'no_manifeste' => 'required|string',
            'awe_tracking' => 'required|string',
            'origin_country_code' => 'required|string|max:2',
            'origin_city' => 'string',
            'nbr_pcs' => 'nullable|numeric',
            'weight' => 'required|numeric',
            'declared_value' => 'required|numeric',
            'freight' => 'required|numeric',
            'freight_certificate' => 'nullable|numeric|max:1',
            'trucking' => 'nullable|numeric|max:1',
            'insurance' => 'nullable|numeric|max:1',
            'value_custom_purpose' => 'nullable|string',
            'charges_in_usd' => 'nullable|numeric',
            'charges_in_haiti' => 'nullable|string',
            'freight_collect' => 'nullable|numeric|max:1',
            'free_domicile' => 'nullable|numeric|max:1',
            'freight_prepaid' => 'nullable|numeric|max:1',
            'file_reference' => 'nullable|string',
            'credit' => 'nullable|string',
            'invest_in_htg' => 'nullable|numeric',
            'invest_in_usd' => 'nullable|numeric',
        ]);
        $input = $request->all();
        $input['freight_certificate'] = !empty($input['Freight Certificate']) ? $input['Freight Certificate'] : '0';
        $input['trucking'] = !empty($input['Trucking']) ? $input['Trucking'] : '0';
        $input['insurance'] = !empty($input['Insurance']) ? $input['Insurance'] : '0';
        $input['charges_in_usd'] = !empty($input['Charges in USD']) ? $input['Charges in USD'] : '0';
        $input['freight_collect'] = !empty($input['Freight Collect']) ? $input['Freight Collect'] : '0';
        $input['free_domicile'] = !empty($input['Free domicile']) ? $input['Free domicile'] : '0';
        $input['freight_prepaid'] = !empty($input['Freight Prepaid']) ? $input['Freight Prepaid'] : '0';
        $model = Courier::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('couriers');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $checkPermission = User::checkPermission(['update_couriers'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = Courier::find($id);
        return view('couriers._form', ['model' => $model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validater = $this->validate($request, [
            'consignee_name' => 'required|string',
            'no_manifeste' => 'required|string',
            'awe_tracking' => 'required|string',
            'origin_country_code' => 'required|string|max:2',
            'origin_city' => 'string',
            'nbr_pcs' => 'nullable|numeric',
            'weight' => 'required|numeric',
            'declared_value' => 'required|numeric',
            'freight' => 'required|numeric',
            'freight_certificate' => 'nullable|numeric|max:1',
            'trucking' => 'nullable|numeric|max:1',
            'insurance' => 'nullable|numeric|max:1',
            'value_custom_purpose' => 'nullable|string',
            'charges_in_usd' => 'nullable|numeric',
            'charges_in_haiti' => 'nullable|string',
            'freight_collect' => 'nullable|numeric|max:1',
            'free_domicile' => 'nullable|numeric|max:1',
            'freight_prepaid' => 'nullable|numeric|max:1',
            'file_reference' => 'nullable|string',
            'credit' => 'nullable|string',
            'invest_in_htg' => 'nullable|numeric',
            'invest_in_usd' => 'nullable|numeric',
        ]);
        $model = Courier::find($id);
        $input = $request->all();
        $input['freight_certificate'] = !empty($input['Freight Certificate']) ? $input['Freight Certificate'] : '0';
        $input['trucking'] = !empty($input['Trucking']) ? $input['Trucking'] : '0';
        $input['insurance'] = !empty($input['Insurance']) ? $input['Insurance'] : '0';
        $input['charges_in_usd'] = !empty($input['Charges in USD']) ? $input['Charges in USD'] : '0';
        $input['freight_collect'] = !empty($input['Freight Collect']) ? $input['Freight Collect'] : '0';
        $input['free_domicile'] = !empty($input['Free domicile']) ? $input['Free domicile'] : '0';
        $input['freight_prepaid'] = !empty($input['Freight Prepaid']) ? $input['Freight Prepaid'] : '0';
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('couriers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = Courier::where('id', $id)->update(['deleted' => 1]);
    }

    public function import()
    {
        $checkPermission = User::checkPermission(['import_couriers'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Courier;
        return view('couriers.import', ['model' => $model]);
    }

    public function importdata(Request $request)
    {
        $validater = $this->validate($request, [
            'import_file' => 'required',
        ]);
        if ($request->hasFile('import_file')) {
            $theArray = Excel::toArray(new stdClass(), $request->file('import_file'));
            $theArray = $theArray[0];
            $headercolumnArr = $theArray[0];
            // $headercolumnArr = Excel::load($request->file('import_file'))->get()->first()->keys()->toArray();
            if (
                in_array("Consignee", $headercolumnArr) && in_array("No manifeste", $headercolumnArr)
                && in_array("AWB", $headercolumnArr) && in_array("Origin", $headercolumnArr)
                && in_array("Origin city", $headercolumnArr) && in_array("NBR PCS", $headercolumnArr)
                && in_array("Weight (kg)", $headercolumnArr) && in_array("Declared Value", $headercolumnArr)
                && in_array("Freight", $headercolumnArr) && in_array("Freight Certificate", $headercolumnArr)
                && in_array("Trucking", $headercolumnArr) && in_array("Insurance", $headercolumnArr)
                && in_array("Value for customs purpose", $headercolumnArr) && in_array("Charges in USD", $headercolumnArr)
                && in_array("Charges in Haitian currency", $headercolumnArr) && in_array("Freight Collect", $headercolumnArr)
                && in_array("Free domicile", $headercolumnArr) && in_array("Freight Prepaid", $headercolumnArr)
                && in_array("File ref", $headercolumnArr) && in_array("Credit", $headercolumnArr)
                && in_array("Inv # in HTG", $headercolumnArr) && in_array("Inv # in USD", $headercolumnArr)
            ) {
                $dData = $theArray;
                unset($dData[0]);
                $dData = array_values($dData);
                foreach ($dData as $key => $row) {
                    //pre($row);
                    $data['consignee_name'] = $row['Consignee'];
                    $data['no_manifeste'] = $row['No manifeste'];
                    $data['awe_tracking'] = $row['AWB'];
                    $data['origin_country_code'] = $row['Origin'];
                    $data['origin_city'] = $row['Origin city'];
                    $data['nbr_pcs'] = $row['NBR PCS'];
                    $data['weight'] = !empty($row['Weight (kg)']) ? $row['Weight (kg)'] : '0.00';
                    $data['declared_value'] = !empty($row['Declared Value']) ? $row['Declared Value'] : '0.00';
                    $data['freight'] = !empty($row['Freight']) ? $row['Freight'] : '0.00';
                    $data['freight_certificate'] = !empty($row['Freight Certificate']) ? $row['Freight Certificate'] : '0';
                    $data['trucking'] = !empty($row['Trucking']) ? $row['Trucking'] : '0';
                    $data['insurance'] = !empty($row['Insurance']) ? $row['Insurance'] : '0';
                    $data['value_custom_purpose'] = $row['Value for customs purpose'];
                    $data['charges_in_usd'] = !empty($row['Charges in USD']) ? $row['Charges in USD'] : '0';
                    $data['charges_in_haiti'] = $row['Charges in Haitian currency'];
                    $data['freight_collect'] = !empty($row['Freight Collect']) ? $row['Freight Collect'] : '0';
                    $data['free_domicile'] = !empty($row['Free domicile']) ? $row['Free domicile'] : '0';
                    $data['freight_prepaid'] = !empty($row['Freight Prepaid']) ? $row['Freight Prepaid'] : '0';
                    $data['file_reference'] = $row['File ref'];
                    $data['credit'] = $row['Credit'];
                    $data['invest_in_htg'] = $row['Inv # in HTG'];
                    $data['invest_in_usd'] = $row['Inv # in USD'];

                    DB::table('courier_detail')->insert($data);
                }
                // Excel::load($request->file('import_file')->getRealPath(), function ($reader) {
                //     //pre($reader->toArray());
                //     foreach ($reader->toArray() as $key => $row) {
                //         //pre($row);
                //         $data['consignee_name'] = $row['Consignee'];
                //         $data['no_manifeste'] = $row['No manifeste'];
                //         $data['awe_tracking'] = $row['AWB'];
                //         $data['origin_country_code'] = $row['Origin'];
                //         $data['origin_city'] = $row['Origin city'];
                //         $data['nbr_pcs'] = $row['NBR PCS'];
                //         $data['weight'] = !empty($row['Weight (kg)']) ? $row['Weight (kg)'] : '0.00';
                //         $data['declared_value'] = !empty($row['Declared Value']) ? $row['Declared Value'] : '0.00';
                //         $data['freight'] = !empty($row['Freight']) ? $row['Freight'] : '0.00';
                //         $data['freight_certificate'] = !empty($row['Freight Certificate']) ? $row['Freight Certificate'] : '0';
                //         $data['trucking'] = !empty($row['Trucking']) ? $row['Trucking'] : '0';
                //         $data['insurance'] = !empty($row['Insurance']) ? $row['Insurance'] : '0';
                //         $data['value_custom_purpose'] = $row['Value for customs purpose'];
                //         $data['charges_in_usd'] = !empty($row['Charges in USD']) ? $row['Charges in USD'] : '0';
                //         $data['charges_in_haiti'] = $row['Charges in Haitian currency'];
                //         $data['freight_collect'] = !empty($row['Freight Collect']) ? $row['Freight Collect'] : '0';
                //         $data['free_domicile'] = !empty($row['Free domicile']) ? $row['Free domicile'] : '0';
                //         $data['freight_prepaid'] = !empty($row['Freight Prepaid']) ? $row['Freight Prepaid'] : '0';
                //         $data['file_reference'] = $row['File ref'];
                //         $data['credit'] = $row['Credit'];
                //         $data['invest_in_htg'] = $row['Inv # in HTG'];
                //         $data['invest_in_usd'] = $row['Inv # in USD'];

                //         DB::table('courier_detail')->insert($data);
                //     }
                // });
                Session::flash('flash_message', 'Records has been imported successfully');
            } else {
                Session::flash('flash_message_error', 'Invalid sheet format');
            }
        }

        return redirect('couriers');
    }
}
