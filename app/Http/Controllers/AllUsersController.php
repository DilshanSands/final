<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Gym;
use App\Models\City;

class AllUsersController extends Controller
{
    #=======================================================================================#
    #			                           List Function                                	#
    #=======================================================================================#
    public function list()
    {
        $usersFromDB =  User::role('user')->withoutBanned()->get();
        if (count($usersFromDB) <= 0) { //for empty statement
            return view('empty');
        }
        return view("allUsers.list", ['users' => $usersFromDB]);
    }

    #=======================================================================================#
    #			                           Show Function                                	#
    #=======================================================================================#
    public function show($id)
    {
        $singleUser = User::findorfail($id);
        return view("allUsers.show", ['singleUser' => $singleUser]);
    }
    #=======================================================================================#
    #			                           Delete Function                                	#
    #=======================================================================================#
    public function deleteUser($id)
    {
        $singleUser = User::findorfail($id);
        $singleUser->delete();
        return response()->json(['success' => 'Record deleted successfully!']);
    }

       #=======================================================================================#
    #			                        create Function                                     #
    #=======================================================================================#
    public function create()
    {
        $coaches = User::all();
        $cities = City::all();
        return view('allUsers.create', [
            'users' => $coaches,
            'cities' => $cities,
        ]);
    }
    #=======================================================================================#
    #			                        store Function                                      #
    #=======================================================================================#
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required'],
            'profile_image' => ['nullable', 'mimes:jpg,jpeg'],
            'city_id' => ['required'],
        ]);

        if ($request->hasFile('profile_image') == null) {
            $imageName = 'imgs/defaultImg.jpg';
        } else {
            $image = $request->file('profile_image');
            $name = time() . \Str::random(30) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/imgs');
            $image->move($destinationPath, $name);
            $imageName = 'imgs/' . $name;
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->city_id = $request->city_id;
        $user->profile_image = $imageName;
        $user->assignRole('user');
        $user->save();
        return redirect()->route('allUsers.list');
    }


    #=======================================================================================#
    #			                        Assign Gym To User                              	#
    #=======================================================================================#
    public function addGym($id)
    {
        $singleUser = User::findorfail($id);
        return view('allUsers.addGym', [
            'user' => User::find($id),
            'gyms' => Gym::all(),
        ]);
    }

    public function submitGym(Request $request, $user_id)
    {
        $user = User::find($user_id);
        $request->validate([
            'gym_id' => 'required',
        ]);
        $user->gym_id = $request->gym_id;
        $user->update(['gym_id' => $request->gym_id]);
        $usersFromDB =  User::role('user')->withoutBanned()->get();
        return view("allUsers.list", ['users' => $usersFromDB]);
    }
}
