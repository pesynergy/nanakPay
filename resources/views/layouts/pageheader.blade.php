<div class="content-body" style="margin-top:73px;">
    <div class="container-fluid">
        @if (!isset($header))
            <div class="page-header mb-10">
                <div class="form-head mb-4">
                    <div class="page-title">
                        <div class="row">
                            <h2 class="text-black font-w600 mb-0"><span class="text-semibold">NanakPay</span> - @yield('pagetitle')</h2>
                            @if ($mydata['news'] != '' && $mydata['news'] != null)
                                <h4 class="col-md-9 text-danger"><marquee style="height: 25px" onmouseover="this.stop();" onmouseout="this.start();">{{$mydata['news']}}</marquee></h4>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        @if (!isset($search))
		<div class="row">
            <div class="col-12">
                <div class="card">
                    <form id="searchForm">
                        <div class="card-header">
                            <h4 class="card-title">Search</h4>
                            <div class="heading-elements">
                                <button type="submit" class="btn btn-info btn-xs btn-labeled legitRipple" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="flaticon-381-search-2" style="padding-right:5px;"></i></b> Search</button>
                                <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Refreshing"><b><i class="flaticon-381-repeat-1" style="padding-right:5px;"></i></b> Refresh</button>
                                <button type="button" class="btn btn-primary btn-xs btn-labeled legitRipple {{ isset($export) ? '' : 'hide' }}" product="{{ $export ?? '' }}" id="reportExport"><b><i class="flaticon-381-download" style="padding-right:5px;"></i></b> Export</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(isset($mystatus))
                                    <div class="form-group col-md-2 m-b-10">
                                        <input type="hidden" name="status" value="{{$mystatus}}">
                                    </div>
                                @endif
                                <div class="form-group col-md-2 m-b-10">
                                    <input type="text" name="from_date" class="form-control mydate" placeholder="From Date">
                                </div>
                                <div class="form-group col-md-2 m-b-10">
                                    <input type="text" name="to_date" class="form-control mydate" placeholder="To Date">
                                </div>
                                <div class="form-group col-md-2 m-b-10">
                                    <input type="text" name="searchtext" class="form-control" placeholder="Search Value">
                                </div>
                                 @if (Myhelper::hasRole(['admin', 'subadmin']))
                                    <div class="form-group col-md-2 m-b-10 {{ isset($agentfilter) ? $agentfilter : ''}}">
                                        <input type="text" name="agent" class="form-control" placeholder="Agent Id / Parent Id">
                                    </div>
                                @endif
                                @if(isset($status))
                                <div class="form-group col-md-2">
                                    <select name="status" class="form-control select">
                                        <option value="">Select {{$status['type'] ?? ''}} Status</option>
                                        @if (isset($status['data']) && sizeOf($status['data']) > 0)
                                            @foreach ($status['data'] as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif
            
                                @if(isset($product))
                                <div class="form-group col-md-2">
                                    <select name="product" class="form-control select">
                                        <option value="">Select {{$product['type'] ?? ''}}</option>
                                        @if (isset($product['data']) && sizeOf($product['data']) > 0)
                                            @foreach ($product['data'] as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>