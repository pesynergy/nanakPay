<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Billpay extends FormRequest
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
    public function rules()
    {
        return [
            'provider_id'      => 'required|numeric',
            'number'      => 'required|min:8|max:15',
            'mobile'      => 'sometimes|required|numeric',
            'amount'      => 'sometimes|required|numeric|min:10',
            'biller'      => 'sometimes|required',
            'duedate'     => 'sometimes|required',
            'type'        => 'required'
        ];
    }
}
