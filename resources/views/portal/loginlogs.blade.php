@extends('layouts.app')
@section('title', "Api Logs")
@section('pagetitle',  "Api Logs")

@php
    $table = "yes";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Session Logs</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Agent Id</th>
                                        <th>Ip</th>
                                        <th>Browser</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="viewFullDataModal" class="modal fade" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Transaction Details</h4>
            </div>
            <div class="modal-body p-0 agentData">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    var DT;

    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/loginsessions/0";
        var onDraw = function() {

            $('.apiData').click(function(event) {
                var data = DT.row($(this).parent().parent()).data();
                
                var agentdata = "";
                $.each(data, function(index, values) {
                    agentdata += `<div class="row">
                        <div class="col-md-1">`+index+`</div>
                        <div class="col-md-11"><p>`+values+`</p></div>
                    </div>`;
                });
                $(".agentData").html(agentdata);
                $('#viewFullDataModal').modal();
            });
        };
        var options = [
            { "data" : "id"},
            { "data" : "username"},
            { "data" : "ip_address"},
            { "data" : "user_agent"},
            { "data" : "created_at"}
        ];

        DT = datatableSetup(url, options, onDraw);

    });
</script>
@endpush