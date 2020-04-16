<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Illuminate\Support\Facades\Auth; 
use Hash;
use Validator;


class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','show']]);
         $this->middleware('permission:user-create', ['only' => ['create','store']]);
         $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function login(){ 
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            return response()->json([
                'response_code' => 0,
                'response_status' => false,
                'message' => 'Success',
                'result' => [
                    'info_users' => $user,
                    'key'=>$success
                    ]
            ], $this-> successStatus); 
        } 
        else{ 
            return response()->json([
                'response_status' => true,
                'error'=>'Unauthorized'], 401); 
        } 
    }

    public function logout(Request $request)
    { 
        if (Auth::check()) {
            Auth::user()->token()->delete();
            $response = response()->json([
                'response_code' => 0,
                'response_status'=>false,
                'message'=>'Success logout'],200);
        }else{
            $response = response()->json([
                'response_code' => 0,
                'response_status'=>true,
                'message'=>'Something is wrong'],400); 
        }
        return $response;
        // $result = $request->user()->token()->delete();                  
        //     if($result){    
        //             $response = response()->json(['response_status'=>false,'message'=>'Success'],200);
        //       }else{
        //             $response = response()->json(['response_status'=>true,'message'=>'Something is wrong'],400);            
        //       }   
        //     return response()->json([$result]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::orderBy('id','DESC')->paginate(5);
        return view('users.index',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }
    public function getAllUser(Request $request)
    {
        $data = User::orderBy('id','DESC')->paginate(5);
        if($data){
            return response()->json([
                'response_code'=>0,
                    'response_status'=>false,
                    'message'=>'Success',
                    'result' => $data
                ], $this-> successStatus);
        }else{
            return response()->json([
                'response_code'=>001,
                    'response_status'=>true,
                    'message'=>'Something went wrong',
                    'result' => $data
                ], $this-> successStatus); 
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('users.create',compact('roles'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);


        $input = $request->all();
        $input['password'] = Hash::make($input['password']);


        $user = User::create($input);
        $user->assignRole($request->input('roles'));


        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }

    public function storeUser(Request $request) 
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json([
                'response_code'=>001,
                'response_status'=>true,
                'message'=>'Errors',
                'result'=>$validator->errors()], 401);            
        }else{
            $validator = $request->all(); 
            $validator['password'] = bcrypt($validator['password']); 
            $user = User::create($validator); 
            $user->assignRole($request->input('roles'));
            return response()->json([
                'response_code'=>0,
                'response_status'=>false,
                'message'=>'Success',
                'result'=>$user], 
                $this-> successStatus); 
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }
    public function showUserById($id)
    {
        $user = User::find($id);
        return response()->json(['result' => $user]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();


        return view('users.edit',compact('user','roles','userRole'));
    }
    public function editUser($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        return response()->json([
                'response_code'=>0,
                'response_status'=>false,
                'message'=>'Success',
                'result'=>[
                    'users' => $user,
                    'roles' => $roles,
                    'users_roles' => $userRole,
                ]
            ], $this-> successStatus);
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
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);


        $input = $request->all();
        if(!empty($input['password'])){ 
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));    
        }


        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();


        $user->assignRole($request->input('roles'));


        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }

    public function updateUser(Request $request, $id) 
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);
        
        if ($validator->fails()) { 
            return response()->json([
                'response_code'=>001,
                'response_status'=>true,
                'message'=>'Errors',
                'result'=>$validator->errors()], 401);            
        }else{
            $validator = $request->all();
            if(!empty($validator['password'])){ 
                $validator['password'] = Hash::make($validator['password']);
            }else{
                $validator = array_except($validator,array('password'));    
            }
            $user = User::find($id);
            $user->update($validator);
            DB::table('model_has_roles')->where('model_id',$id)->delete();
            return response()->json([
                'response_code'=>0,
                'response_status'=>false,
                'message'=>'Success',
                'result'=>$user], 
                $this-> successStatus); 
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }
    public function destroyUser($id)
    {
        $user = User::find($id);
        if (!empty($user)) { 
            $user->delete();
            return response()->json([
                'response_code'=>0,
                'response_status'=>false,
                'message'=>'Success',
                'result'=>$user], 
                $this-> successStatus); 
                       
        }else{
            return response()->json([
                'response_code'=>001,
                'response_status'=>true,
                'message'=>'Errors',
                'result'=>'Data not found'], $this-> successStatus); 
        }
    }


    /**
     * Detail the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function details() 
    { 
        $user = Auth::user(); 
        if($user){
            return response()->json([
                'response_code'=>0,
                'response_status'=>false,
                'message'=>'Success logout',
                'result' => $user
            ], $this-> successStatus); 
        }else{
            return response()->json([
                'response_code'=>001,
                'response_status'=>true,
                'message'=>'Something went wrong',
                'result' => $user
            ], $this-> successStatus);         
        }
    } 
}