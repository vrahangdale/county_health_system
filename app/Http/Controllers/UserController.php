<?php

namespace App\Http\Controllers;


use App\Nurse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\User;
use App\Role;
use App\Hospital;
use Auth;
use Session;
use Input;
use DB;
use Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->users = User::all();
        $this->list_role = Role::lists('display_name', 'id');
        $this->list_hospital = Hospital::lists('hospital_name', 'id');
        $this->heading = "Users";

        $this->viewData = ['user' => $this->user, 'users' => $this->users, 'list_role' => $this->list_role, 'list_hospital' => $this->list_hospital, 'heading' => $this->heading];
    }

    public function index()
    {
        Log::info('UsersController.index: ');
        $users = User::all();
        $this->viewData['users'] = $users;

        return view('users.index', $this->viewData);
    }

    public function show(User $users)
    {
        $object = $users;
        Log::info('UsersController.show: '.$object->id.'|'.$object->name);
        $this->viewData['user'] = $object;
        $this->viewData['heading'] = "View User: ".$object->name;

        return view('users.show', $this->viewData);
    }

    public function create()
    {
        Log::info('UsersController.create: ');
        $this->viewData['heading'] = "New User";

        return view('users.create', $this->viewData);
    }

    public function store(UserRequest $request)
    {
        Log::info('UsersController.store - Start: ');

        $roleId = $request->input('rolelist');
        $roleType = Role::where('id', $roleId)->value('name');
        if($roleType == 'nurse') {
            $this->validate($request, [
                'hospitallist' => 'required',
            ]);
        }


        $input = $request->all();
        $this->populateCreateFields($input);
        $input['password'] = bcrypt($request['password']);

        $object = User::create($input);
        $this->syncRoles($object, $request->input('rolelist'));


        if($roleType == 'nurse') {
            $hospitalId = $request->input('hospitallist');
            $nurse = new Nurse();
            $nurse->name = $request['name'];
            $nurse->hospital_id = $hospitalId;
            $nurse->user_id = $object->id;
            $nurse->save();
        }

        Session::flash('flash_message', 'User successfully added!');
        Log::info('UsersController.store - End: '.$object->id.'|'.$object->name);
        return redirect('users');
    }

    public function edit(User $users)
    {
        $object = $users;
        Log::info('UsersController.edit: '.$object->id.'|'.$object->name);
        $this->viewData['user'] = $object;
        $this->viewData['heading'] = "Edit User: ".$object->name;

        return view('users.edit', $this->viewData);
    }

    public function update(User $users, UserRequest $request)
    {
        $object = $users;
        Log::info('UsersController.update - Start: '.$object->id.'|'.$object->name);
//        $this->authorize($object);
        $this->populateUpdateFields($request);
        $request['active'] = $request['active'] == '' ? false : true;

        $object->update($request->all());
        $this->syncRoles($object, $request->input('rolelist'));
        Session::flash('flash_message', 'User successfully updated!');
        Log::info('UsersController.update - End: '.$object->id.'|'.$object->name);
        return redirect('users');
    }

    /**
     * Destroy the given user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return Response
     */
    public function destroy(Request $request, User $users)
    {
        $object = $users;
        Log::info('UsersController.destroy: Start: '.$object->id.'|'.$object->name);

            $object->delete();

        Log::info('UsersController.destroy: End: ');
        return redirect('/users');
    }

    /**
     * Sync up the list of roles for the given user record.
     *
     * @param  User  $user
     * @param  array  $roles (id)
     */
    private function syncRoles(User $user, array $roles)
    {
        Log::info('UsersController.syncRoles: Start: '.$user->name);
        // ToDo: At somepoint need to update the timestamps and created_by/updated_by fields on the pivot table
        $user->roles()->sync($roles);
//        $user->roles()->sync([$roles => ['created_by' => Auth::user()->name, 'updated_by' => Auth::user()->name]]);
    }

}