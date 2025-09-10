@extends('layouts.app')
@section('title', "Api Setting")
@section('pagetitle', "Api Setting")

@php
    $table = "yes";
@endphp

@php
    $search = "hide";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Api Token</h4>
                        <button type="button" class="btn btn-primary mt-3" onclick="addSetup()">Add New</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>IP</th>
                                        <th>Token</th>
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

            <div class="col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Callback URL</h4>
                    </div>
                    <div class="card-body">
                        <form id="callbackForm" action="{{route('profileUpdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="id" value="{{Auth::user()->id}}">
                            <input type="hidden" name="actiontype" value="callbackurl">
                            <div class="mb-3">
                                <label>Payin Callback* </label> 
                                <textarea name="callbackurl" class="form-control" cols="30" rows="3" required placeholder="Enter Callback Url">{{Auth::user()->callbackurl ?? ""}}</textarea>
                                <label>Payout Callback* </label> 
                                <textarea name="payout_callback" class="form-control" cols="30" rows="3" required placeholder="Enter Callback Url">{{Auth::user()->payout_callback ?? ""}}</textarea>
                            </div>
    
                            <button type="submit" class="btn btn-primary mt-3">Update Info</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="setupModal">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Generate Token</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                            </button>
                        </div>
                        <form id="setupManager" action="{{route('apitokenstore')}}" method="post">
                            <div class="modal-body">
                                <input type="hidden" name="id">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>IP</label>
                                    <input type="text" name="ip" class="form-control" placeholder="Enter your server ip" required="">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

<script type="text/javascript">
    $(document).ready(function() {
        var type = 'apitoken';
        $('#datatable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('/statement/list/fetch', ['type' => 'apitoken', 'id' => 0, 'returntype' => 'json']) }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "error": function(xhr, status, error) {
                    console.log("Error:", error);
                    console.log("XHR:", xhr);
                    console.log("Status:", status);
                },
            },
            "columns": [{
                    "data": "ip"
                },
                {
                    "data": "token"
                },
                {
                    "data": "status",
                    render: function(data, type, full, meta) {
                        var check = "<label class='label label-danger'>In-Active</label>";
                        if (full.status == "1") {
                            check = "<label class='label label-success'>Active</label>";
                        }

                        return check;
                    }
                }
            ]
        });
        var url = "{{url('statement/list/fetch')}}/apitoken/0}}";
        console.log('test url', url);
        @foreach($user_permissions as $user_permission)
        $(".{{$user_permission->permission_id}}").html(`<label class="label label-success">Active</label>`);
        @endforeach

        var onDraw = function() {};

        var options = [{
                "data": "ip"
            },
            {
                "data": "token"
            },
            {
                "data": "name",
                render: function(data, type, full, meta) {
                    var check = "<label class='label label-danger'>In-Active</label>";
                    if (full.status == "1") {
                        check = "<label class='label label-success'>Active</label>";
                    }

                    return check;
                }
            },
            {
                "data": "action",
                render: function(data, type, full, meta) {
                    return `<button type="button" class="btn bg-danger btn-raised legitRipple btn-xs" onclick="deleteToken(` + full.id + `)"> <i class="fa fa-trash"></i></button>`;
                }
            },
        ];
        // datatableSetup(url, options, onDraw);

        $("#setupManager").validate({
            rules: {
                ip: {
                    required: true,
                },
                domain: {
                    required: true,
                }
            },
            messages: {
                ip: {
                    required: "Please enter ip",
                },
                domain: {
                    required: "Please enter domain",
                }
            },
            errorElement: "p",
            errorPlacement: function(error, element) {
                if (element.prop("tagName").toLowerCase() === "select") {
                    error.insertAfter(element.closest(".form-group").find(".select2"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function() {
                var form = $('#setupManager');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType: 'json',
                    beforeSubmit: function() {
                        form.find('button[type="submit"]').button('loading');
                    },
                    success: function(data) {
                        console.log('in submit');
                        if (data.status == "success") {
                            if (id == "new") {
                                form[0].reset();
                                form.closest('.modal').modal('hide');
                            }
                            notify("Token Successfully Generated", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                            form.find('button[type="submit"]').button('reset');
                        } else {
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        toastr.error(errors, form);
                    }
                });
            }
        });

        $("#callbackForm").validate({
            rules: {
                callback: {
                    required: true,
                }
            },
            messages: {
                callback: {
                    required: "Please enter callback url",
                }
            },
            errorElement: "p",
            errorPlacement: function(error, element) {
                if (element.prop("tagName").toLowerCase() === "select") {
                    error.insertAfter(element.closest(".form-group").find(".select2"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function() {
                var form = $('#callbackForm');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType: 'json',
                    beforeSubmit: function() {
                        form.find('button[type="submit"]').button('loading');
                    },
                    success: function(data) {
                        if (data.status == "success") {
                            form.find('button[type="submit"]').button('reset');
                            notify("Callback Successfully Updated", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        } else {
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        notify(errors, form);
                    }
                });
            }
        });

        $("#profileForm").validate({
            rules: {
                merchant_id: {
                    required: true,
                },
                merchant_key: {
                    required: true,
                },
                merchant_upi: {
                    required: true,
                }
            },
            messages: {
                merchant_id: {
                    required: "Please enter value",
                },
                merchant_key: {
                    required: "Please enter value",
                },
                merchant_upi: {
                    required: "Please enter value",
                }
            },
            errorElement: "p",
            errorPlacement: function(error, element) {
                if (element.prop("tagName").toLowerCase().toLowerCase() === "select") {
                    error.insertAfter(element.closest(".form-group").find(".select2"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function() {
                var form = $('form#profileForm');
                form.find('span.text-danger').remove();
                $('form#profileForm').ajaxSubmit({
                    dataType: 'json',
                    beforeSubmit: function() {
                        form.find('button:submit').button('loading');
                    },
                    complete: function() {
                        form.find('button:submit').button('reset');
                    },
                    success: function(data) {
                        if (data.status == "success") {
                            notify("Details Successfully Updated", 'success');
                        } else {
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $("#qrtestForm").validate({
            rules: {
                merchant_id: {
                    required: true,
                },
                merchant_key: {
                    required: true,
                },
                merchant_upi: {
                    required: true,
                }
            },
            messages: {
                merchant_id: {
                    required: "Please enter value",
                },
                merchant_key: {
                    required: "Please enter value",
                },
                merchant_upi: {
                    required: "Please enter value",
                }
            },
            errorElement: "p",
            errorPlacement: function(error, element) {
                if (element.prop("tagName").toLowerCase().toLowerCase() === "select") {
                    error.insertAfter(element.closest(".form-group").find(".select2"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function() {
                var form = $('form#qrtestForm');
                form.find('span.text-danger').remove();
                $('form#qrtestForm').ajaxSubmit({
                    dataType: 'json',
                    beforeSubmit: function() {
                        form.find('button:submit').button('loading');
                    },
                    complete: function() {
                        form.find('button:submit').button('reset');
                    },
                    success: function(data) {
                        if (data.statuscode == "TXN") {
                            $("#qrshowModal").find("p.qrlink").text(data.upi_string);
                            jQuery("div.qrimage").qrcode({
                                width: 250,
                                height: 250,
                                text: data.upi_string
                            });
                            $("#qrshowModal").modal("show");
                        } else {
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });
    });

    function addSetup() {
        $('#setupModal').find('.msg').text("Add");
        $('#setupModal').find('input[name="id"]').val("new");
        $('#setupModal').modal('show');
    }

    function getAmount(element) {
        var type = $(element).val();

        if (type == "dynamic") {
            $(".extra").html(`
                <div class="form-group  col-md-6">
                    <label>Amount</label>
                    <input type="text" name="amount" class="form-control" required="" placeholder="Enter Value">
                </div>
            `);
        } else {
            $(".extra").html('');
        }
    }

    function deleteToken(id) {
        swal({
            title: 'Are you sure ?',
            text: "You want to delete token",
            type: 'warning',
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: 'Yes delete it!',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !swal.isLoading(),
            preConfirm: () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: "{{ route('tokenDelete') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        data: {
                            'id': id
                        },
                        success: function(result) {
                            resolve(result);
                        },
                        error: function(error) {
                            resolve(error);
                        }
                    });
                });
            },
        }).then((result) => {
            if (result.value.status == "1") {
                notify("Token Successfully Deleted", 'success');
                $('#datatable').dataTable().api().ajax.reload();
            } else {
                notify('Something went wrong, try again', 'Oops', 'error');
            }
        });
    }
</script>
@endpush
