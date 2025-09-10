<?php

use Illuminate\Database\Seeder;

class Permission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Permission::create(['name' => 'Manage Whitelabel','slug' => 'view_whitelabel','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Master Distributor','slug' => 'view_md','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Distributor','slug' => 'view_distributor','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Retailer','slug' => 'view_retailer','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Api Partner','slug' => 'view_apiuser','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Other','slug' => 'view_other','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Kyc Pending User','slug' => 'view_kycpending','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Kyc Submitted User','slug' => 'view_kycsubmitted','type' => 'member']);
        App\Model\Permission::create(['name' => 'Manage Kyc Rejected User','slug' => 'view_kycrejected','type' => 'member']);
        App\Model\Permission::create(['name' => 'Create Whitelabel','slug' => 'create_whitelabel','type' => 'member']);
        App\Model\Permission::create(['name' => 'Create Master Distributor','slug' => 'create_md','type' => 'member']);
        App\Model\Permission::create(['name' => 'Create Distributor','slug' => 'create_distributor','type' => 'member']);
        App\Model\Permission::create(['name' => 'Create Retailer','slug' => 'create_retailer','type' => 'member']);
        App\Model\Permission::create(['name' => 'Create Api Partner','slug' => 'create_apiuser','type' => 'member']);
        App\Model\Permission::create(['name' => 'Create Other','slug' => 'create_other','type' => 'member']);
        App\Model\Permission::create(['name' => 'Member Permissions Change','slug' => 'member_permission_change','type' => 'member']);
        App\Model\Permission::create(['name' => 'Member Profile Edit','slug' => 'member_profile_edit','type' => 'member']);
        App\Model\Permission::create(['name' => 'Member Password Reset','slug' => 'member_password_reset','type' => 'member']);
        App\Model\Permission::create(['name' => 'Member Kyc Manager Update','slug' => 'member_kyc_update','type' => 'member']);
        App\Model\Permission::create(['name' => 'Member scheme change','slug' => 'member_scheme_change','type' => 'member']);

        App\Model\Permission::create(['name' => 'Fund Transfer Action','slug' => 'fund_transfer','type' => 'fund']);
        App\Model\Permission::create(['name' => 'Fund Return Action','slug' => 'fund_return','type' => 'fund']);
        App\Model\Permission::create(['name' => 'Make Fund Request','slug' => 'fund_request','type' => 'fund']);
        App\Model\Permission::create(['name' => 'Fund Request View','slug' => 'fund_request_view','type' => 'fund']);

        App\Model\Permission::create(['name' => 'Account Statement View','slug' => 'account_statement','type' => 'setting']);
        App\Model\Permission::create(['name' => 'Profile Edit','slug' => 'profile_edit','type' => 'setting']);
        App\Model\Permission::create(['name' => 'Password Manager','slug' => 'password_reset','type' => 'setting']);

        App\Model\Permission::create(['name' => 'Fund Report','slug' => 'fund_report','type' => 'report']);
        App\Model\Permission::create(['name' => 'Recharge Statement','slug' => 'recharge_statement','type' => 'report']);
        App\Model\Permission::create(['name' => 'Bill Payment Statement','slug' => 'billpayment_statement','type' => 'report']);
        App\Model\Permission::create(['name' => 'Uti Vle id Report','slug' => 'utiid_statement','type' => 'report']);
        App\Model\Permission::create(['name' => 'Uti Pancard Report','slug' => 'utipancard_statement','type' => 'report']);
        App\Model\Permission::create(['name' => 'Money Transfer Statement','slug' => 'money_statement','type' => 'report']);

        App\Model\Permission::create(['name' => 'Api Manager','slug' => 'setup_api','type' => 'setup']);
        App\Model\Permission::create(['name' => 'Bank Account Setup','slug' => 'setup_bank','type' => 'setup']);
        App\Model\Permission::create(['name' => 'Operator Manager','slug' => 'setup_operator','type' => 'setup']);
        
        App\Model\Permission::create(['name' => 'Recharge Service','slug' => 'recharge_service','type' => 'service']);
        App\Model\Permission::create(['name' => 'Billpayment Service','slug' => 'billpayment_service','type' => 'service']);
        App\Model\Permission::create(['name' => 'Uti Pancard Service','slug' => 'utipancard_service','type' => 'service']);
        App\Model\Permission::create(['name' => 'Money Transfer 1','slug' => 'dmt1_service','type' => 'service']);
        App\Model\Permission::create(['name' => 'Aeps Service','slug' => 'aeps_service','type' => 'service']);
        
        App\Model\Permission::create(['name' => 'Uti Vle id Report Editing','slug' => 'utiid_statement_edit','type' => 'edit report']);App\Model\Permission::create(['name' => 'Uti Pancard Report Editing','slug' => 'Utipancard_statement_edit','type' => 'edit report']);
        App\Model\Permission::create(['name' => 'Billpay Report Editing','slug' => 'billpay_statement_edit','type' => 'edit report']);
        App\Model\Permission::create(['name' => 'Recharge Report Editing','slug' => 'recharge_statement_edit','type' => 'edit report']);
        App\Model\Permission::create(['name' => 'Money Transfer Report Editing','slug' => 'money_statement_edit','type' => 'edit report']);

        App\Model\Permission::create(['name' => 'Company Manager','slug' => 'company_manager','type' => 'resource']);
        App\Model\Permission::create(['name' => 'Scheme Manager','slug' => 'scheme_manager','type' => 'resource']);
        App\Model\Permission::create(['name' => 'Change Company Profile','slug' => 'change_company_profile','type' => 'resource']);
        
        App\Model\Permission::create(['name' => 'Recharge Status','slug' => 'recharge_status','type' => 'report status']);
        App\Model\Permission::create(['name' => 'Money Transfer Status Check','slug' => 'dmt_status','type' => 'report status']);
        App\Model\Permission::create(['name' => 'Money Transfer Report Status','slug' => 'money_status','type' => 'report status']);
        
        App\Model\Permission::create(['name' => 'Complaint Subject Manager','slug' => 'complaint_subject','type' => 'complaint']);
        App\Model\Permission::create(['name' => 'Complaint Manager','slug' => 'complaint_edit','type' => 'complaint']);
        App\Model\Permission::create(['name' => 'Complaint Submission','slug' => 'complaint','type' => 'complaint']);

        App\Model\Permission::create(['name' => 'Api User Account Manager','slug' => 'apiuser_acc_manager','type' => 'apiuser']);

        App\Model\Permission::create(['name' => 'Member BIllpament Report','slug' => 'member_billpayment_statement_view','type' => 'member report']);
        App\Model\Permission::create(['name' => 'Member Recharge Report','slug' => 'member_recharge_statement_view','type' => 'member report']);
        App\Model\Permission::create(['name' => 'Member Money Transfer Report','slug' => 'member_money_statement_view','type' => 'member report']);
        App\Model\Permission::create(['name' => 'Member Utipancard Report','slug' => 'member_utipancard_statement_view','type' => 'member report']);
        App\Model\Permission::create(['name' => 'Member Utiid Report','slug' => 'member_utiid_statement_view','type' => 'member report']);
        App\Model\Permission::create(['name' => 'Member Account Statement','slug' => 'member_account_statement_view','type' => 'member report']);

        App\Model\Permission::create(['name' => 'Uti id request','slug' => 'utiid_request','type' => 'portal']);
        App\Model\Permission::create(['name' => 'Uti Vle id','slug' => 'uti_vle_creation','type' => 'portalidself']);
    }
}
