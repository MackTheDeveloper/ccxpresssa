<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Response;
use Session;
use DB;
use Config;
use App\upsUploadedFiles;
use App\aeropostUploadedFiles;
use App\ccpackUploadedFiles;
use App\cargoUploadedFiles;

class fileManagerController extends Controller
{
    public function addmoreFiles(Request $request)
    {
        $count = $request->get('count');
        $file_number = $request->get('file_number');
        return view('Files.addmorefile', ['count' => $count, 'file_number' => $file_number]);
    }

    public function create($flag = null, $id = null)
    {
        if ($flag == 'ups') {
            $actionUrl = url('file/uploadUps');
            $fileData = DB::table('ups_details')->where('id', $id)->first();
        } else if ($flag == 'ups-master') {
            $actionUrl = url('file/uploadUpsMaster');
            $fileData = DB::table('aeropost')->where('id', $id)->first();
        } else if ($flag == 'aeropost') {
            $actionUrl = url('file/uploadAeropost');
            $fileData = DB::table('aeropost')->where('id', $id)->first();
        } else if ($flag == 'aeropost-master') {
            $actionUrl = url('file/uploadAeropostMaster');
            $fileData = DB::table('aeropost')->where('id', $id)->first();
        } else if ($flag == 'ccpack') {
            $actionUrl = url('file/uploadCCpack');
            $fileData = DB::table('ccpack')->where('id', $id)->first();
        } else if ($flag == 'ccpack-master') {
            $actionUrl = url('file/uploadCcpackMaster');
            $fileData = DB::table('ccpack')->where('id', $id)->first();
        } else if ($flag == 'cargo') {
            $actionUrl = url('file/uploadCargo');
            $fileData = DB::table('cargo')->where('id', $id)->first();
        } else if ($flag == 'houseFile') {
            $actionUrl = url('file/uploadHouseFile');
            $fileData = DB::table('hawb_files')->where('id', $id)->first();
        }

        return view('Files.uploadFiles', ['flag' => $flag, 'id' => $id, 'actionUrl' => $actionUrl, 'file_number' => $fileData->file_number]);
    }

    public function uploadUps(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        // $input['filevalue'] = array_values($_FILES['files']['tmp_name']['filevalue']);
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        //pre($input['filename'][0]);
        //pre($files);

        $count = $input['count'];
        //pre($_FILES['files']);
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new upsUploadedFiles();
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('ups_details')->where('id', $id)->first();
            if ($fileData->courier_operation_type == 1) {
                $dir = "Files/Courier/Ups/Import/" . $fileData->file_number;
            } else {
                $dir = 'Files/Courier/Ups/Export/' . $fileData->file_number;
            }
            $filePath = $dir . '/' . $name;
            //pre($filePath);
            $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            //pre($success,1);
            if ($success) {
                $data = DB::table('ups_uploaded_files')->where('file_name', $name)->where('deleted', '0')->first();
                if ($data) {
                    DB::table('ups_uploaded_files')->where('file_name', $name)->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        // 	mkdir('public/files/1/'.$dir, true);
        // } else {
        // 	pre('No');
        // }

        return back();
    }

    public function uploadUpsMaster(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        // $input['filevalue'] = array_values($_FILES['files']['tmp_name']['filevalue']);
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        //pre($input['filename'][0]);
        //pre($files);

        $count = $input['count'];
        //pre($_FILES['files']);
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new upsUploadedFiles();
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->master_file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('ups_details')->where('id', $id)->first();
            if ($fileData->courier_operation_type == 1) {
                $dir = "Files/Courier/Ups-Master/Import/" . $fileData->file_number;
            } else {
                $dir = 'Files/Courier/Ups-Master/Export/' . $fileData->file_number;
            }
            $filePath = $dir . '/' . $name;
            //pre($filePath);
            $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            //pre($success,1);
            if ($success) {
                $data = DB::table('ups_uploaded_files')->where('file_name', $name)->where('deleted', '0')->first();
                if ($data) {
                    DB::table('ups_uploaded_files')->where('file_name', $name)->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        // 	mkdir('public/files/1/'.$dir, true);
        // } else {
        // 	pre('No');
        // }

        return back();
    }

    public function uploadAeropost(Request $request)
    {
        $input = $request->all();
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        $id = $input['id'];
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        //pre($input['filename'][0]);
        //pre($files);

        $count = $input['count'];
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new aeropostUploadedFiles();
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('aeropost')->where('id', $id)->first();
            $dir = "Files/Courier/Aeropost/" . $fileData->file_number;

            $filePath = $dir . '/' . $name;
            //pre($filePath);
            $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            //pre($success,1);
            if ($success) {
                $data = DB::table('aeropost_uploaded_files')->where('file_name', $name)->where('deleted', '0')->first();
                if ($data) {
                    DB::table('aeropost_uploaded_files')->where('file_name', $name)->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        //     mkdir('public/files/1/'.$dir, true);
        // } else {
        //     pre('No');
        // }

        return back();
    }

    public function uploadAeropostMaster(Request $request)
    {
        $input = $request->all();
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        $id = $input['id'];
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        //pre($input['filename'][0]);
        //pre($files);

        $count = $input['count'];
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new aeropostUploadedFiles();
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->master_file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('aeropost_master')->where('id', $id)->first();
            $dir = "Files/Courier/Aeropost-Master/" . $fileData->file_number;

            $filePath = $dir . '/' . $name;
            //pre($filePath);
            $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            //pre($success,1);
            if ($success) {
                $data = DB::table('aeropost_uploaded_files')->where('file_name', $name)->where('deleted', '0')->first();
                if ($data) {
                    DB::table('aeropost_uploaded_files')->where('file_name', $name)->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        //     mkdir('public/files/1/'.$dir, true);
        // } else {
        //     pre('No');
        // }

        return back();
    }

    public function uploadCCpack(Request $request)
    {
        $input = $request->all();
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        $count = $input['count'];
        $id = $input['id'];
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        //pre($input['filename'][0]);
        //pre($files);
        //pre($_FILES['files']);
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new ccpackUploadedFiles();
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('ccpack')->where('id', $id)->first();
            if ($fileData->ccpack_operation_type == 1) {
                $dir = "Files/Courier/CCpack/Import/" . $fileData->file_number;
            } else {
                $dir = 'Files/Courier/CCpack/Export/' . $fileData->file_number;
            }
            $filePath = $dir . '/' . $name;
            //pre($filePath);
            $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            //pre($success,1);
            if ($success) {
                $data = DB::table('ccpack_uploaded_files')->where('file_name', $name)->where('deleted', '0')->first();
                if ($data) {
                    DB::table('ccpack_uploaded_files')->where('file_name', $name)->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong! Please choose another file.');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        //     mkdir('public/files/1/'.$dir, true);
        // } else {
        //     pre('No');
        // }

        return back();
    }

    public function uploadCcpackMaster(Request $request)
    {
        $input = $request->all();
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        $id = $input['id'];
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        //pre($input['filename'][0]);
        //pre($files);

        $count = $input['count'];
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new ccpackUploadedFiles();
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->master_file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('ccpack_master')->where('id', $id)->first();
            $dir = "Files/Courier/Ccpack-Master/" . $fileData->file_number;

            $filePath = $dir . '/' . $name;
            //pre($filePath);
            $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            //pre($success,1);
            if ($success) {
                $data = DB::table('ccpack_uploaded_files')->where('file_name', $name)->where('deleted', '0')->first();
                if ($data) {
                    DB::table('ccpack_uploaded_files')->where('file_name', $name)->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        //     mkdir('public/files/1/'.$dir, true);
        // } else {
        //     pre('No');
        // }

        return back();
    }

    public function uploadCargo(Request $request)
    {
        $input = $request->all();
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        $fileArr = $_FILES['files'];
        //pre($fileArr);

        //pre($input['filename'][0]);
        //pre($files);
        $id = $input['id'];
        $count = $input['count'];
        //pre($_FILES['files']);
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new cargoUploadedFiles();
            $fileObj->flag_module = 'cargo';
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('cargo')->where('id', $id)->first();
            if ($fileData->cargo_operation_type == 1) {
                $dir = 'Files/Cargo/Import/' . $fileData->file_number;
            } else if ($fileData->cargo_operation_type == 2) {
                $dir = 'Files/Cargo/Export/' . $fileData->file_number;
            } else {
                $dir = 'Files/Cargo/Local/' . $fileData->file_number;
            }
            $filePath = $dir . '/' . $name;
            //pre($filePath);
            if ($fileArr['size'][$i] > 0) {
                $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            } else {
                $success = 0;
            }
            //pre($success,1);
            if ($success) {
                $data = DB::table('cargo_uploaded_files')->where('file_name', $name)->where('flag_module', 'cargo')->where('deleted', '0')->first();
                if ($data) {
                    DB::table('cargo_uploaded_files')->where('file_name', $name)->where('flag_module', 'cargo')->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }

        // if(!is_dir("public/files/1/".$dir)){
        //     mkdir('public/files/1/'.$dir, true);
        // } else {
        //     pre('No');
        // }

        return back();
    }


    public function uploadHouseFile(Request $request)
    {
        $input = $request->all();
        $input['file_type'] = array_values($input['files']['filetype']);
        $input['filename'] = array_values($input['files']['filename']);
        if ($request->file('files'))
            $files = array_values($request->file('files'));
        else
            $files = null;
        $fileArr = $_FILES['files'];
        //pre($fileArr);

        //pre($input['filename'][0]);
        //pre($files);
        $id = $input['id'];
        $count = $input['count'];
        //pre($_FILES['files']);
        for ($i = 0; $i <= $count; $i++) {
            //print_r($file);die;
            $fileObj = new cargoUploadedFiles();
            $fileObj->flag_module = 'houseFile';
            $fileObj->file_type = $input['file_type'][$i];
            $fileObj->file_id = $id;
            $name = $input['filename'][$i];
            $fileObj->file_name = $name;
            $fileObj->uploaded_at = date('Y-m-d H:i:s');
            $fileObj->uploaded_by = auth()->user()->id;
            $fileData = DB::table('hawb_files')->where('id', $id)->first();
            if ($fileData->cargo_operation_type == 1) {
                $dir = 'Files/houseFile/Import/' . $fileData->file_number;
            } else if ($fileData->cargo_operation_type == 2) {
                $dir = 'Files/houseFile/Export/' . $fileData->file_number;
            } 
            $filePath = $dir . '/' . $name;
            //pre($filePath);
            if ($fileArr['size'][$i] > 0) {
                $success = Storage::disk('s3')->put($filePath, file_get_contents($files[$i]), 'public');
            } else {
                $success = 0;
            }
            //pre($success,1);
            if ($success) {
                $data = DB::table('cargo_uploaded_files')->where('file_name', $name)->where('flag_module', 'houseFile')->where('deleted', '0')->first();
                if ($data) {
                    DB::table('cargo_uploaded_files')->where('file_name', $name)->where('flag_module', 'houseFile')->where('deleted', '0')->update(['file_type' => $fileObj->file_type, 'file_name' => $name, 'uploaded_by' => auth()->user()->id, 'uploaded_at' => date('Y-m-d H:i:s')]);
                } else {
                    $fileObj->save();
                }
                Session::flash('flash_message_fm', 'Files has been uploaded successfully!');
            } else {
                Session::flash('flash_message_error', 'Something went wrong!');
            }
        }
        return back();
    }


    public function awsdemo()
    {
        $AWS = Config::get('filesystems.disks.s3');
        $url = 'https://s3.' . $AWS['region'] . '.amazonaws.com/' . $AWS['bucket'] . '/';
        $images = [];
        $files = Storage::disk('s3')->files('Files/Courier/Ups/Export/E1220');
        print_r($files);
        die;
        foreach ($files as $file) {
            $images[] = [
                'name' => $file,
                'src' => $url . $file
            ];
        }
    }

    public function destroy($flag = null, $id = null, $filename = null)
    {
        $filename = unserialize($filename);
        if ($flag == 'ups') {
            $filedata = DB::table('ups_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ups_details')->where('id', $id)->first();
            $filePath = 'Files/Courier/Ups/';
            if ($data->courier_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filename;
            } else {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filename;
            }
            DB::table('ups_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            // pre($filePath);
            //$delete = Storage::disk('s3')->delete($filePath);
            //pre($delete);
            /* if ($delete) {
                DB::table('ups_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            } else {
            } */
        }
        else if ($flag == 'ups-master') {
            $filedata = DB::table('ups_uploaded_files')->where('file_name', $filename)->where('master_file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ups_master')->where('id', $id)->first();
            $filePath = 'Files/Courier/Ups-Master/';
            if ($data->ups_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filename;
            } else {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filename;
            }
            DB::table('ups_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            // pre($filePath);
            //$delete = Storage::disk('s3')->delete($filePath);
            //pre($delete);
            /* if ($delete) {
                DB::table('ups_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            } else {
            } */
        }
        else if ($flag == 'aeropost') {
            $filedata = DB::table('aeropost_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('aeropost')->where('id', $id)->first();
            $filePath = 'Files/Courier/Aeropost/' . $data->file_number . '/' . $filename;
            DB::table('aeropost_uploaded_files')->where('id', $id)->update(['deleted' => '1']);
            /* $delete = Storage::disk('s3')->delete($filePath);
            if ($delete) {
                DB::table('aeropost_uploaded_files')->where('id', $id)->update(['deleted' => '1']);
            } else {
            } */
        } else if ($flag == 'aeropost-master') {
            $filedata = DB::table('aeropost_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('aeropost_master')->where('id', $id)->first();
            $filePath = 'Files/Courier/Aeropost-Master/' . $data->file_number . '/' . $filename;
            DB::table('aeropost_uploaded_files')->where('id', $id)->update(['deleted' => '1']);
            /* $delete = Storage::disk('s3')->delete($filePath);
            if ($delete) {
                DB::table('aeropost_uploaded_files')->where('id', $id)->update(['deleted' => '1']);
            } else {
            } */
        } else if ($flag == 'ccpack') {
            $filedata = DB::table('ccpack_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ccpack')->where('id', $filedata->file_id)->first();
            $filePath = 'Files/Courier/CCpack/';
            if ($data->ccpack_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filename;
            } else {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filename;
            }
            DB::table('ccpack_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            /* $delete = Storage::disk('s3')->delete($filePath);
            if ($delete) {
                DB::table('ccpack_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            } else {
            } */
        } else if ($flag == 'ccpack-master') {
            $filedata = DB::table('ccpack_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ccpack_master')->where('id', $id)->first();
            $filePath = 'Files/Courier/Ccpack-Master/' . $data->file_number . '/' . $filename;
            DB::table('ccpack_uploaded_files')->where('id', $id)->update(['deleted' => '1']);
            /* $delete = Storage::disk('s3')->delete($filePath);
            if ($delete) {
                DB::table('aeropost_uploaded_files')->where('id', $id)->update(['deleted' => '1']);
            } else {
            } */
        } else if ($flag == 'cargo') {
            $filedata = DB::table('cargo_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('flag_module', 'cargo')->where('deleted', '0')->first();

            $data = DB::table('cargo')->where('id', $filedata->file_id)->first();
            $filePath = 'Files/Cargo/';
            if ($data->cargo_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filedata->file_name;
            } else if ($data->cargo_operation_type == 2) {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filedata->file_name;
            } else {
                $filePath = $filePath . 'Local/' . $data->file_number . '/' . $filedata->file_name;
            }
            DB::table('cargo_uploaded_files')->where('id', $filedata->id)->where('flag_module', 'cargo')->update(['deleted' => '1']);
            /* $delete = Storage::disk('s3')->delete($filePath);
            if ($delete) {
                DB::table('cargo_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            } else {
            } */
        } else if ($flag == 'houseFile') {
            $filedata = DB::table('cargo_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('flag_module', 'houseFile')->where('deleted', '0')->first();

            $data = DB::table('hawb_files')->where('id', $filedata->file_id)->first();
            $filePath = 'Files/houseFile/';
            if ($data->cargo_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filedata->file_name;
            } else if ($data->cargo_operation_type == 2) {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filedata->file_name;
            }
            DB::table('cargo_uploaded_files')->where('id', $filedata->id)->where('flag_module', 'houseFile')->update(['deleted' => '1']);
            /* $delete = Storage::disk('s3')->delete($filePath);
            if ($delete) {
                DB::table('cargo_uploaded_files')->where('id', $filedata->id)->update(['deleted' => '1']);
            } else {
            } */
        }
    }

    public function download($flag = null, $id = null, $filename = null)
    {
        $filename = unserialize($filename);
        if ($flag == 'ups') {
            $filedata = DB::table('ups_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ups_details')->where('id', $id)->first();
            $filePath = 'Files/Courier/Ups/';
            if ($data->courier_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filename;
            } else {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filename;
            }

            if ($filedata) {
                DB::table('ups_uploaded_files')->where('id', $filedata->id)->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
            }
        }
        else if ($flag == 'ups-master') {
            $filedata = DB::table('ups_uploaded_files')->where('file_name', $filename)->where('master_file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ups_master')->where('id', $id)->first();
            $filePath = 'Files/Courier/Ups-Master/';
            if ($data->ups_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filename;
            } else {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filename;
            }

            if ($filedata) {
                DB::table('ups_uploaded_files')->where('id', $filedata->id)->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
            }
        } else if ($flag == 'aeropost') {
            $filedata = DB::table('aeropost_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('aeropost')->where('id', $id)->first();
            $filePath = 'Files/Courier/Aeropost/' . $data->file_number . '/' . $filename;
            if ($filedata) {
                DB::table('aeropost_uploaded_files')->where('id', $filedata->id)->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
            }
        } else if ($flag == 'aeropost-master') {
            $filedata = DB::table('aeropost_uploaded_files')->where('file_name', $filename)->where('master_file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('aeropost_master')->where('id', $id)->first();
            $filePath = 'Files/Courier/Aeropost-Master/' . $data->file_number . '/' . $filename;
            if ($filedata) {
                DB::table('aeropost_uploaded_files')->where('id', $filedata->id)->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
            }
        } else if ($flag == 'ccpack') {
            $filedata = DB::table('ccpack_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ccpack')->where('id', $id)->first();
            $filePath = 'Files/Courier/CCpack/';
            if ($data->ccpack_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filename;
            } else {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filename;
            }
            if ($filedata) {
                DB::table('ccpack_uploaded_files')->where('id', $filedata->id)->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
            }
        } else if ($flag == 'ccpack-master') {
            $filedata = DB::table('ccpack_uploaded_files')->where('file_name', $filename)->where('master_file_id', $id)->where('deleted', '0')->first();
            $data = DB::table('ccpack_master')->where('id', $id)->first();
            $filePath = 'Files/Courier/Ccpack-Master/' . $data->file_number . '/' . $filename;
            if ($filedata) {
                DB::table('ccpack_uploaded_files')->where('id', $filedata->id)->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
            }
        } else if ($flag == 'cargo') {
            $filedata = DB::table('cargo_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('flag_module', 'cargo')->where('deleted', '0')->first();

            $data = DB::table('cargo')->where('id', $filedata->file_id)->first();
            $filePath = 'Files/Cargo/';
            if ($data->cargo_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filedata->file_name;
            } else if ($data->cargo_operation_type == 2) {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filedata->file_name;
            } else {
                $filePath = $filePath . 'Local/' . $data->file_number . '/' . $filedata->file_name;
            }

            DB::table('cargo_uploaded_files')->where('id', $filedata->id)->where('flag_module', 'cargo')->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
        } else if ($flag == 'houseFile') {
            $filedata = DB::table('cargo_uploaded_files')->where('file_name', $filename)->where('file_id', $id)->where('flag_module', 'houseFile')->where('deleted', '0')->first();

            $data = DB::table('hawb_files')->where('id', $filedata->file_id)->first();
            $filePath = 'Files/houseFile/';
            if ($data->cargo_operation_type == 1) {
                $filePath = $filePath . 'Import/' . $data->file_number . '/' . $filedata->file_name;
            } else if ($data->cargo_operation_type == 2) {
                $filePath = $filePath . 'Export/' . $data->file_number . '/' . $filedata->file_name;
            }
            DB::table('cargo_uploaded_files')->where('id', $filedata->id)->where('flag_module', 'houseFile')->update(['downloaded_at' => date('Y-m-d H:i:s'), 'downloaded_by' => auth()->user()->id]);
        }

        $assetPath = Storage::disk('s3')->url($filePath);
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($assetPath));
        return readfile($assetPath);
    }

    public function show()
    {
        if (isset(auth()->user()->id)) {
            return view('Files.filemanager');
        } else {
            return redirect(route('home'));
        }
    }

    public function makeDirectory($path)
    {
        $success = Storage::disk('s3')->makeDirectory(base64_decode($path), '', 'public');
        if ($success) {
            file_put_contents('test.txt', 'Created');
        } else {
            file_put_contents('test.txt', 'Fail');
        }
    }
}
