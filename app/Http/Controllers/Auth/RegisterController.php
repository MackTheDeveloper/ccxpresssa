<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Mail\RegistrationMail;
use Illuminate\Support\Facades\Mail;
use Session;
use Response;
use App\Activities;
use App\Permissions;
use Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            //'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6|confirmed',
            'department' => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        if (!isset($data['warehouses']))
            $data['warehouses'] = array();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
            'department' => $data['department'],
            'password' => bcrypt($data['password']),
            'warehouses' => implode(',', $data['warehouses'])
        ]);

        // Set group permission to user
        $query = DB::table('permissions')
            ->select('*')
            ->where('type_flag', 2)
            ->where('related_id', $data['department'])
            ->get();
        foreach ($query as $k => $v) {
            $model = new Permissions();
            $model->type_flag = 1;
            $model->related_id = $user['id'];
            $model->slug =         $v->slug;
            $model->parent_module = $v->parent_module;
            $model->permission_flag = 1;
            $model->created_on = gmdate("Y-m-d H:i:s");
            $model->created_by = Auth::user()->id;
            $model->save();
        }

        Activities::log('create', 'user', $user);
        $user['plain_password'] = $data['password'];
        $user['flag'] = 'registration';
        //app('App\Component\MailComponent')->sendMail($data['email'],$user);
        //Mail::to($data['email'])->send(new RegistrationMail($user));
        return $user;
    }

    public function checkuniqueemail()
    {
        $value = $_POST['value'];
        $id = $_POST['id'];
        if (!empty($id)) {
            $data = DB::table('users')->where('deleted', '0')->where('email', $value)->where('id', '<>', $id)->count();
        } else {
            $data = DB::table('users')->where('deleted', '0')->where('email', $value)->count();
        }

        if ($data)
            return 1;
        else
            return 0;
    }

    /**
     * User Listing
     */
    protected function index()
    {
        $checkPermission = User::checkPermission(['listing_users'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $users = DB::table('users')->where('deleted', '0')->where('user_type', 'user')->orderBy('id', 'desc')->get();
        return view("users.index", ['users' => $users]);
    }

    public function showRegistrationForm(){
        $model = new User;
        $departments = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where('cashcredit_account_type.name', 'User')
            ->pluck('name', 'id');

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');
        return view('auth.register', ['model' => $model, 'departments' => $departments, 'warehouses' => $warehouses]);
    }

    /**
     * Edit User
     */
    public function edit(Request $request, $id)
    {
        $checkPermission = User::checkPermission(['update_users'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = User::find($id);
        $departments = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where('cashcredit_account_type.name', 'User')
            ->pluck('name', 'id');

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');

        if (!empty($model->warehouses))
            $model->warehouses = explode(',', $model->warehouses);
        return view('auth.register', ['model' => $model, 'departments' => $departments, 'warehouses' => $warehouses]);
    }

    /**
     * Update User Model
     */
    public function update(Request $request, $id)
    {
        $validater = $this->validate($request, [

            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $request->id . ',id,deleted_at,NULL|max:255',
            'department' => 'required',
        ]);
        $model = User::find($id);
        $model->name = $request->name;
        $model->email = $request->email;
        $model->status = $request->status;
        $model->department = $request->department;

        if (!isset($request->warehouses))
            $request->warehouses = array();

        $model->warehouses = implode(',', $request->warehouses);

        // Save activity logs
        Activities::log('update', 'user', $model);
        $model->save();

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('users');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, $id)
    {
        $model = User::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);

        DB::table('permissions')->where('related_id', $id)->where('type_flag', 1)->delete();
    }

    public function changeuserstatus()
    {
        $status = $_POST['status'];
        $changeStatus = ($status == '1') ? '0' : '1';
        $userId = $_POST['userId'];
        $model = User::find($userId);
        $data['status'] = $changeStatus;
        $model->fill($data);
        Activities::log('update', 'user', $model);
        $userData = DB::table('users')->where('id', $userId)->update(['status' => $changeStatus]);
        return true;
    }

    public function resetpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
        ]);
        $input = $request->all();
        if ($validator->passes()) {
            $encryptedPassword = bcrypt($input['password']);

            $model = User::find($input['userId']);
            $data['password'] = $encryptedPassword;
            $model->fill($data);
            Activities::log('update', 'user', $model);

            $dataUser = DB::table('users')->where('id', $input['userId'])->update(['password' => $encryptedPassword]);
            $user = DB::table('users')->where('id', $input['userId'])->first();
            $user = (array) $user;
            $user['plain_password'] = $input['password'];
            $user['flag'] = 'resetPassword';
            Mail::to($user['email'])->send(new RegistrationMail($user));
            return Response::json(['success' => '1']);
        }

        return Response::json(['errors' => $validator->errors()]);
    }

    public function viewuserdetail(Request $request, $id)
    {
        $model = DB::table('users')->where('id', $id)->first();
        return view('users.userdetail', ['model' => $model]);
    }

    public function viewuseractivities(Request $request, $id)
    {
        $model = DB::table('activities')->where('related_id', $id)->where('type', 'user')->orderBy('updated_on', 'desc')->get();
        return view('users.useractivity', ['model' => $model]);
    }
}
