<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = ['agentcode','name','email','mobile','alternate_mobile','password','remember_token','lockedamount','role_id','parent_id','company_id','scheme_id','status','address','district','shopname','website', 'rm','city','state','pincode','pancard','aadharcard','pancardpic','aadharcardpicfront','aadharcardpicback', 'passbook','gstpic','msme', 'otherdoc','kyc','callbackurl','payout_callback','remark','resetpwd','otpverify','otpresend','account', 'ifsc', 'bank', 'merchant_key', 'merchant_id', 'merchant_upi'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'otpverify'
    ];

    public $with = ['role', 'company'];
    protected $appends = ['parents'];

    public function role(){
        return $this->belongsTo('App\Model\Role');
    }
    
    public function company(){
        return $this->belongsTo('App\Model\Company');
    }

    public function getParentsAttribute() {
        $user = User::where('id', $this->parent_id)->first(['id', 'name', 'mobile', 'role_id']);
        if($user){
            return $user->name." (".$user->id.")<br>".$user->mobile."<br>".$user->role->name;
        }else{
            return "Not Found";
        }
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
    
    public function getMainwalletAttribute($value)
    {
        return round($value, 2);
    }
    
    public function getAepsbalanceAttribute($value)
    {
        return round($value, 2);
    }
    
    public function getMicroatmbalanceAttribute($value)
    {
        return round($value, 2);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
}
