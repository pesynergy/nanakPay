$(document).ready(function () {
    var url = baseUrl + "/statement/list/fetch/resource" + type + "/0";

    var onDraw = function () {
        $('.toggleStatusBtn').off('click').on('click', function () {
            let btn = $(this);
            let id = btn.data('id');
            let currentStatus = btn.data('status');
            let newStatus = (currentStatus == "1") ? "0" : "1";

            $.ajax({
                url: resourceUpdateUrl,
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                data: { id: id, status: newStatus, actiontype: "company" }
            })
                .done(function (data) {
                    if (data.status == "success") {
                        let newText = (newStatus == "1") ? "Active" : "Inactive";
                        btn.text(newText);
                        btn.data('status', newStatus);

                        notify("Company Updated", 'success');
                    } else {
                        notify("Something went wrong, Try again.", 'warning');
                    }
                })
                .fail(function (errors) {
                    showError(errors, "withoutform");
                });
        });
    };

    var options = [
        { "data": "id" },
        { "data": "companyname" },
        { "data": "website" },
        {
            "data": "status",
            render: function (data, type, full, meta) {
                let btnText = (full.status == "1") ? "Active" : "Inactive";

                return `
            <button type="button" 
                class="btn btn-sm btn-dark toggleStatusBtn" 
                data-id="${full.id}" 
                data-status="${full.status}">
                ${btnText}
            </button>
        `;
            }
        },
        {
            "data": null,
            render: function (data, type, full, meta) {
                return `
            <button class="btn btn-sm btn-dark" 
                onclick="editSetup('${full.id}', '${full.companyname}', '${full.website}', '${full.senderid}', '${full.smsuser}', '${full.smspwd}')">
                <i class="fas fa-pencil-alt"></i> Edit
            </button>
        `;
            }
        }
    ];
    datatableSetup(url, options, onDraw);

    $("#setupManager").validate({
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
        errorPlacement: function (error, element) {
            if (element.prop("tagName").toLowerCase() === "select") {
                error.insertAfter(element.closest(".form-group").find(".select2"));
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function () {
            var form = $('#setupManager');
            var id = form.find('[name="id"]').val();
            form.ajaxSubmit({
                dataType: 'json',
                beforeSubmit: function () {
                    form.find('button[type="submit"]').button('loading');
                },
                success: function (data) {
                    if (data.status == "success") {
                        if (id == "new") {
                            form[0].reset();
                        }
                        form.find('button[type="submit"]').button('reset');
                        notify("Task Successfully Completed", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    } else {
                        notify(data.status, 'warning');
                    }
                },
                error: function (errors) {
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

// global functions
function addSetup() {
    $('#setupModal').find('.msg').text("Add");
    $('#setupModal').find('input[name="id"]').val("new");
    $('#setupModal').modal('show');
}

function editSetup(id, companyname, website, senderid, smsuser, smspwd) {
    $('#setupModal').find('.msg').text("Edit");
    $('#setupModal').find('input[name="id"]').val(id);
    $('#setupModal').find('input[name="companyname"]').val(companyname);
    $('#setupModal').find('input[name="website"]').val(website);
    $('#setupModal').find('input[name="senderid"]').val(senderid);
    $('#setupModal').find('input[name="smsuser"]').val(smsuser);
    $('#setupModal').find('input[name="smspwd"]').val(smspwd);
    $('#setupModal').modal('show');
}
