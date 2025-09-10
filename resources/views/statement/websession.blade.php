@extends('layouts.app')
@section('title', "Web Session")
@section('pagetitle',  "Web Session")

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
                        <h4 class="card-title">Web Session</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Details</th>
                                        <th>User Agent</th>
                                        <th>Ip Address</th>
                                        <th>Action</th>
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
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/websession/0";
        var onDraw = function() {
        };
        var options = [
            { "data" : "id"},
            { "data" : "username"},
            { "data" : "user_agent"},
            { "data" : "ip_address"},
            { "data" : "status",
                render:function(data, type, full, meta){
                    return `<a href="javascript:void(0)" class="btn btn-xs btn-danger" onclick="mydelete('`+full.tid+`')"><i class="icon-x"></i> Delete</a>`;
                }
            }
        ];

        datatableSetup(url, options, onDraw);
    });

    function mydelete(id){
        $.ajax({
            url: `{{route('statementDelete')}}`,
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend:function(){
                swal({
                    title: 'Wait!',
                    text: 'Please wait, we are working in your request',
                    onOpen: () => {
                        swal.showLoading()
                    },
                    allowOutsideClick: () => !swal.isLoading()
                });
            },
            dataType:'json',
            data:{'id':id, 'type': "websession"}
        })
        .done(function(data) {
            swal.close();
            notify("Task Successfully Completed", 'success');
            $('#datatable').dataTable().api().ajax.reload();
        })
        .fail(function(errors) {
            swal.close();
            notify('Oops', errors.status+'! '+errors.statusText, 'warning');
        });
    }
</script>
@endpush