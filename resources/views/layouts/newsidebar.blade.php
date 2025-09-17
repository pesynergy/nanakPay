<div class="deznav">
	<div class="deznav-scroll">
		<ul class="metismenu" id="menu">
			<li><a class="ai-icon" href="{{route('home')}}" aria-expanded="false">
					<i class="flaticon-381-networking"></i>
					<span class="nav-text">Dashboard</span>
				</a>
			</li>
			@if (Myhelper::can(['company_manager', 'change_company_profile', 'scheme_manager']))
    			<li>
    				<a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
    					<i class="flaticon-381-controls-3"></i>
    					<span class="nav-text">Resources</span>
    				</a>
    				<ul aria-expanded="false">
    				    @if (Myhelper::can('company_manager'))
    					    <li><a href="{{route('resource', ['type' => 'company'])}}">Company Manager</a></li>
    					@endif
                        @if (Myhelper::can('change_company_profile'))
    					    <li><a href="{{route('resource', ['type' => 'companyprofile'])}}">Company Profile</a></li>
    					@endif
                        @if (Myhelper::can('scheme_manager'))
    					    <li><a href="{{route('resource', ['type' => 'scheme'])}}">Scheme Manager</a></li>
    					@endif
    				</ul>
    			</li>
    		@endif
    		@if (Myhelper::can(['view_apiuser', 'view_mis', 'view_web', 'view_admin']))
    			<li>
    				<a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
    					<i class="fa-regular fa-user fw-bold"></i>
    					<span class="nav-text">Member</span>
    				</a>
    				<ul aria-expanded="false">
    				    @if (Myhelper::can(['view_subamdin']))
        					<li><a href="{{route('member', ['type' => 'subadmin'])}}">Admin</a></li>
        				@endif
        				@if (Myhelper::can(['view_apiuser']))
        				    <li><a href="{{route('member', ['type' => 'apiuser'])}}">API User</a></li>
        				@endif
        				<!--@if (Myhelper::can(['view_mis']))-->
        				<!--    <li><a href="{{route('member', ['type' => 'mis'])}}">MIS User</a></li>-->
        				<!--@endif-->
    				</ul>
    			</li>
			@endif
            @if (Myhelper::can(['setup_apitoken', 'complaint']))
            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false" style="font-size: 14px;">
                    <i class="fa-solid flaticon-381-list"></i>
                    <span class="nav-text">Pending Approvals </span>
                </a>
                <ul aria-expanded="false">
                    @if (Myhelper::can('complaint'))
                    <li><a href="{{route('complaintlist')}}">Complaints</a></li>
                    @endif
                    @if (Myhelper::can('setup_apitoken'))
                    <li><a href="{{route('setup', ['type' => 'apitoken'])}}">Api Token</a></li>
                    @endif
                </ul>
            </li>
            @endif
            @if (Myhelper::can(['view_apiuser', 'view_mis', 'view_web', 'view_admin']))
            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-list-1"></i>
                    <span class="nav-text">KYC Manager </span>
                </a>
                <ul aria-expanded="false">
                    @if (Myhelper::can(['view_kycpending']))
                    <li><a href="{{route('member', ['type' => 'kycpending'])}}">Pending Kyc</a>
                    </li>
                    @endif

                    @if (Myhelper::can(['view_kycsubmitted']))
                    <li><a href="{{route('member', ['type' => 'kycsubmitted'])}}">Submitted Kyc</a>
                    </li>
                    @endif

                    @if (Myhelper::can(['view_kycrejected']))
                    <li><a href="{{route('member', ['type' => 'kycrejected'])}}">Rejected Kyc</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif
            @if (Myhelper::can(['fund_transfer', 'fund_return', 'fund_request_view', 'fund_report', 'fund_request']))
            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="icon-wallet"></i>
                    <span class="nav-text">Capital</span>
                </a>
                <ul aria-expanded="false">
                    @if (Myhelper::can(['fund_requestview']))
                    <li><a href="{{route('fund', ['type' => 'requestview'])}}">Request</a></li>
                    <li><a href="{{route('fund', ['type' => 'payoutview'])}}">Payout Request</a></li>
                    @endif
                    @if (Myhelper::hasNotRole('admin') && Myhelper::can('fund_request'))
                    <li><a href="{{route('fund', ['type' => 'request'])}}">Top-up Wallet</a></li>
                    @endif
                    @if (Myhelper::hasNotRole('admin') && Myhelper::can('qr_request'))
                    <li><a href="{{route('fund', ['type' => 'upiload'])}}">QR Intent</a></li>
                    <li><a href="{{route('fund', ['type' => 'payinload'])}}">UPI Intent</a></li>
                    @endif
                    @if (Myhelper::hasNotRole('admin') && Myhelper::can('payout_request'))
                    <li><a href="{{route('fund', ['type' => 'payout'])}}">Payout</a></li>

                    @endif
                    @if (Myhelper::can(['fund_report']))
                    <li><a href="{{route('fund', ['type' => 'requestviewall'])}}">Request Report</a></li>
                    <li><a href="{{route('fund', ['type' => 'allfund'])}}">Fund Transfer Report</a></li>
                    @endif
                </ul>
            </li>
            @endif
			<li><a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
					<i class="flaticon-381-network"></i>
					<span class="nav-text">Reports</span>
				</a>
				<ul aria-expanded="false">
				    @if (Myhelper::can('collection_statement'))
					    <li><a href="{{route('reports', ['type' => 'payin'])}}">Collection</a></li>
				        <li><a href="{{route('reports', ['type' => 'chargeback'])}}">Chargeback</a></li>
					@endif
					@if (Myhelper::can('payout_statement'))
					    <li><a href="{{route('reports', ['type' => 'payout'])}}">Payout</a></li>
					@endif
					@if (Myhelper::can('qr_statement'))
					    <li><a href="{{route('reports', ['type' => 'upiintent'])}}">Intent</a></li>
					@endif
				</ul>
			</li>
			<li><a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
					<i class="flaticon-381-layer-1"></i>
					<span class="nav-text">Account Ledger</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{route('reports', ['type' => 'mainwallet'])}}">Collection Wallet</a></li>
					<li><a href="{{route('reports', ['type' => 'payoutwallet'])}}">Payout Wallet</a></li>
				</ul>
			</li>
			@if (Myhelper::hasRole('apiuser'))
			    <li><a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
    					<i class="flaticon-381-layer-1"></i>
    					<span class="nav-text">Developer Tools</span>
    				</a>
    				<ul aria-expanded="false">
    					<li><a href="{{route('apisetup', ['type' => 'setting'])}}">API Settings</a></li>
    					<li><a href="#" target="_blank">Api Documents</a></li>
    				</ul>
    			</li>
    		@endif
    		@if (Myhelper::can(['setup_bank', 'api_manager', 'setup_operator']))
    			<li>
    				<a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
    					<i class="fa-regular fa-gear fw-bold"></i>
    					<span class="nav-text">Setup Tools</span>
    				</a>
    				<ul aria-expanded="false">
    				    @if (Myhelper::can('api_manager'))
    					    <li><a href="{{route('setup', ['type' => 'api'])}}">API Manager</a></li>
    					@endif
        				@if (Myhelper::can('api_manager'))
        					<li><a href="{{route('setup', ['type' => 'bank'])}}">Bank Account</a></li>
        				@endif
        				@if (Myhelper::can('api_manager'))
        				    <li><a href="{{route('setup', ['type' => 'operator'])}}">Operator Manager</a></li>
        				@endif
        				@if (Myhelper::can('api_manager'))
        				    <li><a href="{{route('setup', ['type' => 'portalsetting'])}}">Portal Setting</a></li>
        				@endif
    				</ul>
    			</li>
    		@endif

			@if (Myhelper::can(['view_apiuser', 'view_mis', 'view_web', 'view_admin']))
    			<li>
    				<a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
    					<i class="fa-regular fa-user fw-bold"></i>
    					<span class="nav-text">payment</span>
    				</a>
    				<ul aria-expanded="false">
    				    @if (Myhelper::can(['view_subamdin']))
        					<li><a href="{{ route('pay.form') }}">payIn</a></li>
        				@endif
        				@if (Myhelper::can(['view_apiuser']))
        				    <li><a href="#">payOut</a></li>
        				@endif
    				</ul>
    			</li>
					<li>
    				<a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
    					<i class="fa-regular fa-user fw-bold"></i>
    					<span class="nav-text">Api-Specification</span>
    				</a>
    				<ul aria-expanded="false">
    				  <li><a href="{{ route('apiSpecification') }}">ApiSpecification</a></li>
					  <li><a href="https://documenter.getpostman.com/view/44508955/2sB3HqJeHu" target="_blank">API Handbook</a></li>
    				</ul>
    			</li>
			@endif
		</ul>
	</div>
</div>