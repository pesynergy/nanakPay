<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class Member extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (\Auth::check()) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $post)
    {
        $rules = [
            'name'      => 'sometimes|required',
            'mobile'    => 'sometimes|required|numeric|digits:10|unique:users,mobile'.($post->id != "new" ? ",".$post->id : ''),
            'email'     => 'sometimes|required|email|unique:users,email'.($post->id != "new" ? ",".$post->id : ''),
            'address'=> 'sometimes|required',
            'city' => 'sometimes|required',
            'state'  => 'sometimes|required',
            'pincode'  => 'sometimes|required',
            'pancard'  => 'sometimes|required|unique:users,pancard'.($post->id != "new" ? ",".$post->id : ''),
            'aadharcard'  => 'sometimes|required|numeric|digits:12|unique:users,aadharcard'.($post->id != "new" ? ",".$post->id : ''),
            'role_id'  => 'sometimes|required|numeric',
            'scheme_id'  => 'sometimes|required|numeric',
        ];

        if($post->file('aadharcardpicfronts')){
            $rules['aadharcardpicfronts'] = 'sometimes|required|mimes:pdf,jpg,JPG,jpeg|max:1024';
        }
        if($post->file('aadharcardpicbacks')){
            $rules['aadharcardpicbacks'] = 'sometimes|required|mimes:pdf,jpg,JPG,jpeg|max:1024';
        }
        if($post->file('passbooks')){
            $rules['passbooks'] = 'sometimes|required|mimes:pdf,jpg,JPG,jpeg|max:1024';
        }

        if($post->file('pancardpics')){
            $rules['pancardpics'] = 'sometimes|required|mimes:pdf,jpg,JPG,jpeg|max:1024';
        }

        if($post->file('profiles')){
            $rules['profiles'] = 'sometimes|required|mimes:jpg,JPG,jpeg,png|max:500';
        }

        if (\Myhelper::can('member_password_reset')) {
            $rules['password'] = "sometimes|required|min:8";
        }else{
            $rules['password'] = "sometimes|required|min:8|confirmed";
        }

        if($post->has('oldpassword')){
            $rules['password'] = $rules['password']."|different:oldpassword";
        }
        return $rules;
    }
}
