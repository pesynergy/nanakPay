<!--**********************************
    Nav header start
***********************************-->
<div class="nav-header">
	<a href="#" class="brand-logo">
		<img class="logo-abbr" src="{{asset('')}}new_assests/images/logo.png" alt="">
		<img class="brand-title" src="{{asset('')}}new_assests/images/logo.png" alt="" width="100%">
	</a>

	<div class="nav-control">
		<div class="hamburger">
			<span class="line"></span><span class="line"></span><span class="line"></span>
		</div>
	</div>
</div>
<!--**********************************
    Nav header end
***********************************-->

<!--**********************************
    Header start
***********************************-->
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    
                </div>
                <ul class="navbar-nav header-right">
                    <button type="button" class="btn btn-rounded btn-primary me-5" data-toggle="modal" data-target="#walletLoadModal"><span
								class="btn-icon-start text-primary"><i class="fa fa-wallet"></i>
							</span>LOAD WALLET</button>
					<li class="nav-item dropdown notification_dropdown">
						  <a class="nav-link bell dz-theme-mode" href="javascript:void(0);">
							<i id="icon-light" class="fas fa-sun"></i>
							 <i id="icon-dark" class="fas fa-moon"></i>
						  </a>
					</li>
					<li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
							<div class="header-info">
								<span class="text-black">Hello, <strong>{{ explode(' ',ucwords(Auth::user()->name))[0] }}</strong></span>
								@if(Myhelper::hasRole(["admin"]))
								    <p class="fs-12 mb-0">Super Admin</p>
								@endif
								@if(Myhelper::hasRole(["subadmin"]))
								    <p class="fs-12 mb-0">Admin</p>
								@endif
								@if(Myhelper::hasRole(["apiuser"]))
								    <p class="fs-12 mb-0">Merchant</p>
								@endif
							</div>
                            <img src="{{asset('')}}new_assests/images/profile/17.jpg" width="20" alt="">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!--<a href="#" class="dropdown-item ai-icon">-->
                            <!--    <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>-->
                            <!--    <span class="ms-2">Profile </span>-->
                            <!--</a>-->
                            <a href="{{route('logout')}}" class="dropdown-item ai-icon">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span class="ms-2">Logout </span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<!--**********************************
    Header end ti-comment-alt
***********************************-->

@include('layouts.newsidebar')