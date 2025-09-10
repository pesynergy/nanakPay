@extends('layouts.app')
@section('title', 'Operator List')
@section('pagetitle',  'Operator List')
@php
    $table = "yes";
    $agentfilter = "hide";
    $product['type'] = "Operator Type";
    $product['data'] = [
        "mobile" => "Mobile",
        "dth" => "Dth",
        "electricity" => "Electricity",
        "pancard" => "Pancard",
        "dmt" => "Dmt"
    ];
@endphp

@php
    $search = "hide";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Operator List</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>Provider Name</th>
                                        <th>Provider Code</th>
                                        <th>Type</th>
                                        <th>Status</th>
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

@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/setup{{$type}}/0";

        var onDraw = function() {
        };

        var options = [
            { "data" : "name"},
            { "data" : "id"},
            { "data" : "type"},
            { "data" : "action",
                render:function(data, type, full, meta){
                    if(full.status == '1'){
                        return `<span class="label label-success">Active</span>`;
                    }else{
                        return `<span class="label label-success">De-active</span>`;
                    }
                }
            },
        ];
        datatableSetup(url, options, onDraw, ele="#datatable", element={columnDefs: [{
            orderable: false,
            targets: [ 0 ]
        }]});

        $( "#setupManager" ).validate({
            rules: {
                name: {
                    required: true,
                },
                recharge1: {
                    required: true,
                },
                recharge2: {
                    required: true,
                },
                type: {
                    required: true,
                },
                api_id: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Please enter operator name",
                },
                recharge1: {
                    required: "Please enter value",
                },
                recharge2: {
                    required: "Please enter value",
                },
                type: {
                    required: "Please select operator type",
                },
                api_id: {
                    required: "Please select api",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('#setupManager');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            if(id == "new"){
                                form[0].reset();
                                $('[name="api_id"]').select2().val(null).trigger('change');
                            }
                            form.find('button[type="submit"]').button('reset');
                            notify("Task Successfully Completed", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

    	$("#setupModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('.msg').text("Add");
            $('#setupModal').find('form')[0].reset();
        });
    
    });

    function addSetup(){
    	$('#setupModal').find('.msg').text("Add");
    	$('#setupModal').find('input[name="id"]').val("new");
    	$('#setupModal').modal('show');
	}

	function editSetup(id, name, recharge1, recharge2, type, api_id){
		$('#setupModal').find('.msg').text("Edit");
    	$('#setupModal').find('input[name="id"]').val(id);
    	$('#setupModal').find('input[name="name"]').val(name);
        $('#setupModal').find('input[name="recharge1"]').val(recharge1);
        $('#setupModal').find('input[name="recharge2"]').val(recharge2);
        $('#setupModal').find('[name="type"]').select2().val(type).trigger('change');
        $('#setupModal').find('[name="api_id"]').select2().val(api_id).trigger('change');
    	$('#setupModal').modal('show');
    }
    
    function apiUpdate(ele, id){
        var api_id = $(ele).val();
        if(api_id != ""){
            $.ajax({
                url: '{{ route('setupupdate') }}',
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                data: {'id':id, 'api_id':api_id, "actiontype":"operator"}
            })
            .done(function(data) {
                if(data.status == "success"){
                    notify("Operator Updated", 'success');
                }else{
                    notify("Something went wrong, Try again." ,'warning');
                }
                $('#datatable').dataTable().api().ajax.reload();
            })
            .fail(function(errors) {
                showError(errors, "withoutform");
            });
        }
    }
</script>
@endpush