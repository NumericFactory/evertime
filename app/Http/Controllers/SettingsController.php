<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class SettingsController extends Controller {

    function getUpdateProfile(){
        $user = auth()->user();
        return view('settings.update_profile', compact('user'));
    }

    function postUpdateProfile(Request $request){
        $rules = [
            'name' => 'required',
            'locale' => 'required'
        ];
        $this->validate($request, $rules);

        $user = auth()->user();
        $user->name = trim($request->name);
        $user->locale = trim($request->locale);
        $user->save();
        auth()->setUser($user);
        return redirect()->back()->with("flash_message", "Profile updated successfully");
    }
}

