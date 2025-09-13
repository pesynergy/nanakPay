@extends('layouts.app')
@section('title', 'Scheme Manager')
@section('pagetitle',  'Scheme Manager')
@php
    $table = "yes";
    $agentfilter = "hide";

    $status['type'] = "Scheme";
    $status['data'] = [
        "1" => "Active",
        "0" => "De-active"
    ];
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Scheme Manager</h4>
                        <div class="heading-elements">
                            <button type="submit" class="btn btn-info btn-xs btn-labeled legitRipple" onclick="addSetup()"q data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="flaticon-381-add-1" style="padding-right:5px;"></i></b> Add New</button></div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table-bordered nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Status</th>
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

<div id="setupModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Scheme</h6>
            </div>
            <form id="setupManager" action="{{route('resourceupdate')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="scheme">
                        {{ csrf_field() }}
                        <div class="form-group col-md-12">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Bank Name" required="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                   <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div> 
    </div> 
</div> 

@foreach($charge as $key => $value)
    <div id="{{$key}}Modal" class="modal fade" role="dialog" data-backdrop="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">{{$key}} Charge</h4>
                </div>
                <form class="commissionForm" method="post" action="{{ route('resourceupdate') }}">
                    <div class="modal-body p-0" style="margin-bottom:20px">
                        {!! csrf_field() !!}
                        <input type="hidden" name="actiontype" value="commission">
                        <input type="hidden" name="scheme_id" value="">                
                        <table class="table table-bordered m-0">
                            <thead>
                                <th>Operator</th>
                                @if (Myhelper::hasRole('admin'))
                                    <th>Charge Type</th>
                                @endif
                                <th>Value</th>
                            </thead>
                            <tbody>
                                @foreach ($value as $element)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="slab[]" value="{{$element->id}}">
                                            {{$element->name}}
                                        </td>
                                        @if (Myhelper::hasRole('admin'))     
                                            <td class="p-t-0 p-b-0">
                                                <select class="form-control" name="type[]" required="">
                                                    <option value="">Select Type</option>
                                                    <option value="percent">Percent (%)</option>
                                                    <option value="flat" selected="selected">Flat (Rs)</option>
                                                </select>
                                            </td>
                                        @endif
                                        <td class="p-t-0 p-b-0">
                                            <input type="number" step="any" name="apiuser[]" placeholder="Enter Value" class="form-control" >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 
@endforeach

<div id="commissionModal" class="modal fade" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Scheme <span class="schemename"></span> Commission/Charge</h4>
            </div>

            <div class="modal-body no-padding commissioData">
            </div>
            <div class="modal-footer">
           <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div><!-- /.modal -->
@endsection

@push('script')
    <script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/resource{{$type}}/0";
        
        $('input[name="whitelable[]"]').val('0');
        $('input[name="md[]"]').val('0');
        $('input[name="distributor[]"]').val('0');
        $('input[name="retailer[]"]').val('0');
        
        var onDraw = function() {
            $('input#schemeStatus').on('click', function(evt){
                evt.stopPropagation();
                var ele = $(this);
                var id = $(this).val();
                var status = "0";
                if($(this).prop('checked')){
                    status = "1";
                }
                
                $.ajax({
                    url: '{{ route('resourceupdate') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data: {'id':id, 'status':status, "actiontype":"scheme"}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        notify("Scheme Updated", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        if(status == "1"){
                            ele.prop('checked', false);
                        }else{
                            ele.prop('checked', true);
                        }
                        notify("Something went wrong, Try again." ,'warning');
                    }
                })
                .fail(function(errors) {
                    if(status == "1"){
                        ele.prop('checked', false);
                    }else{
                        ele.prop('checked', true);
                    }
                    showError(errors, "withoutform");
                });
            });
        };

        var options = [
            { "data" : "id"},
            { "data" : "name"},
            { "data" : "status",
                render:function(data, type, full, meta){
                    var check = "";
                    if(full.status == "1"){
                        check = "checked='checked'";
                    }

                    return `<label class="switch">
                                <input type="checkbox" id="schemeStatus" `+check+` value="`+full.id+`" actionType="`+type+`">
                                <span class="slider round"></span>
                            </label>`;
                }
            },
            { "data" : "id",
                render:function(data, type, full, meta){
                    var menu = ``;
                        menu += `<li class="dropdown-header">Charge</li>`;

                        @foreach($charge as $key => $value)
                        menu += `<li style="padding-left:15px;padding-bottom:10px;text-transform:capitalize;"><a href="javascript:void(0)" onclick="commission(`+full.id+`, '{{$key}}','{{$key}}Modal')"><i class="fa fa-inr"></i> {{$key}} Charge</a></li>`;
                        @endforeach

                    var out =  `<button type="button" class="btn btn-info btn-raised legitRipple btn-xs" onclick="editSetup(this)"><i class="fas fa-pencil-alt" style="padding-right:5px;"></i>Edit</button>
                                <button type="button" class="btn btn-info btn-raised legitRipple btn-xs" onclick="viewCommission(`+full.id+`, '`+full.name+`')"><i class="flaticon-381-list" style="padding-right:5px;"></i> View Commission</button>
                                <div class="btn-group btn-group-fade">
                                    <button type="button" class="btn btn-info btn-raised legitRipple btn-xs" data-toggle="dropdown" aria-expanded="false"><i class="flaticon-381-list-1" style="padding-right:5px;"></i>Commission/Charge <span class="caret"></span></button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        `+menu+`
                                    </ul>
                                </div>`;

                    return out;
                }
            },
        ];
        datatableSetup(url, options, onDraw);

        $( "#setupManager" ).validate({
            rules: {
                name: {
                    required: true,
                }
            },
            messages: {
                name: {
                    required: "Please enter bank name",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
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

        $('form.commissionForm').submit(function(){
            var form= $(this);
            form.closest('.modal').find('tbody').find('span.pull-right').remove();
            $(this).ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    form.find('button[type="submit"]').button('loading');
                },
                complete: function(){
                    form.find('button[type="submit"]').button('reset');
                },
                success:function(data){
                    $.each(data.status, function(index, values) {
                        if(values.id){
                            form.find('input[value="'+index+'"]').closest('tr').find('td').eq(0).append('<span class="pull-right text-success"><i class="fa fa-check"></i></span>');
                        }else{
                            form.find('input[value="'+index+'"]').closest('tr').find('td').eq(0).append('<span class="pull-right text-danger"><i class="fa fa-times"></i></span>');
                            if(values != 0){
                                form.find('input[value="'+index+'"]').closest('tr').find('input[name="apiuser[]"]').closest('td').append('<span class="text-danger pull-right"><i class="fa fa-times"></i> '+values+'</span>');
                            }
                        }
                    });
    
                    setTimeout(function () {
                        form.find('span.pull-right').remove();
                    }, 10000);
                },
                error: function(errors) {
                    showError(errors, form);
                }
            });
            return false;
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

    function editSetup(ele){
        var id = $(ele).closest('tr').find('td').eq(0).text();
        var name = $(ele).closest('tr').find('td').eq(1).text();

        $('#setupModal').find('.msg').text("Edit");
        $('#setupModal').find('input[name="id"]').val(id);
        $('#setupModal').find('input[name="name"]').val(name);
        $('#setupModal').modal('show');
    }
    
    function commission(id, type, modal) {
        $.ajax({
            url: '{{ url('resources/get') }}/'+type+"/commission",
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType:'json',
            data:{'scheme_id':id}
        })
        .done(function(data) {
            if(data.length > 0){
                $.each(data, function(index, values) {
                    if(type != "gst" && type != "itr"){
                        @if (Myhelper::hasRole('admin'))
                            $('#'+modal).find('input[value="'+values.slab+'"]').closest('tr').find('select[name="type[]"]').val(values.type);
                        @endif
                    }
                    $('#'+modal).find('input[value="'+values.slab+'"]').closest('tr').find('input[name="apiuser[]"]').val(values.apiuser);
                });
            }
        })
        .fail(function(errors) {
            notify('Oops', errors.status+'! '+errors.statusText, 'warning');
        });
    
        $('#'+modal).find('input[name="scheme_id"]').val(id);
        $('#'+modal).modal('show');
    }

    function viewCommission(id, name) {
        if (id != '') {
            // Show loader
            $('#loader').show();
    
            $.ajax({
                url: '{{route("getMemberCommission")}}',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { "scheme_id": id },
                beforeSend: function () {
                    // Optionally, you can disable buttons or inputs here.
                    console.log('Fetching commission details...');
                },
                success: function (data) {
                    $('#loader').hide(); // Hide loader
                    $('#commissionModal').find('.schemename').text(name);
                    $('#commissionModal').find('.commissioData').html(data);
                    $('#commissionModal').modal('show');
                },
                error: function (xhr, status, error) {
                    $('#loader').hide(); // Hide loader
                    console.error('Error:', error);
                    notify('Something went wrong while fetching commission details', 'warning');
                }
            });
        } else {
            notify('Invalid scheme ID provided', 'warning');
        }
    }


    function SETTYPE(ele){
        var type = $(ele).val();
        $('[name="type[]"]').select2().val(type).trigger('change');
    }

    function SETVALUE(ele, type){
        var value = $(ele).val();
        $('[name="'+type+'[]"]').val(value);
    }
</script>
@endpush