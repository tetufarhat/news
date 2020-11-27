"use strict"
$(document).ready(function () {

    $(document).ajaxStart(function () {
        Pace.restart();
    });

    $('.timepicker').datetimepicker({
        format: 'HH:mm:ss'
    });
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss'
    });

    //tooltip
    $('.shortkeys > span').tooltip({ trigger: 'hover', placement: 'top', title: $('.shortkeys').attr('title') });
    //Clipboard
    new Clipboard('.shortkeys > span', {
        text: function (trigger) {
            return trigger.innerText
        }
    }).on('success', function (e) {
        e.clearSelection();
        $(e.trigger).tooltip('hide').attr('data-original-title', 'Copied!').tooltip('show');
        setTimeout(function () {
            $(e.trigger).tooltip('hide').attr('data-original-title', $('.shortkeys').attr('title'));
        }, 1000);
    });

    //notifications seen
    $('.seen').bind('click', function () {
        var dis = $(this);
        if (dis.hasClass('seen')) {
            dis.removeClass('seen');
            var unseen = parseInt($(".unseen-notification").html());
            if (unseen == 1) {
                $(".unseen-notification").remove();
            } else {
                $(".unseen-notification").html(unseen - 1);
            }
        }
    });

    //for tab change
    $('button[type="button"]').click(function () {
        $('#' + $(this).data('tab') + '-tab').tab('show');
    });
    //auto selected select option value!
    autoselected();
    // delete confirmation
    $(document).on('click', '.btn-remove', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1bcfb4',
            cancelButtonColor: '#fe7c96',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.value) {
                var link = $(this).attr('href');
                if (typeof link !== typeof undefined && link !== false) {
                    window.location.href = link;
                } else {
                    $(this).closest('form').submit();
                }
            }
        })
    });
    //Ajax Delete
    $(document).on("submit", ".ajax-delete", function () {
        var dis = this;
        var link = $(dis).attr("action");
        $.ajax({
            method: "POST",
            url: link,
            data: new FormData(dis),
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#preloader").css("display", "block");
            },
            success: function (data) {
                $("#preloader").css("display", "none");
                var json = JSON.parse(data);
                if (json['result'] == 'success') {
                    $(dis).parent().parent().remove();
                    toast('success', json['message']);
                } else {
                    toast('error', json['message']);
                }
            }
        });
        return false;
    });
    //load plugins
    //dropify
    $('.dropify').dropify();
    /*Summernote editor*/
    summernote();
    //select2
    $("select.select2").select2();
    // datepicker
    $(document).on('focus', '.datepicker', function () {
        $(this).datepicker({
            format: 'yyyy-mm-dd'
        }).on('changeDate', function () {
            $(this).datepicker('hide');
            $("#main_modal").css("overflow-y", "auto");
        });
    });

    //monthpicker
    $(document).on('focus', '.monthpicker', function () {
        $(this).datepicker({
            format: "mm/yyyy",
            viewMode: "months",
            minViewMode: "months"
        }).on('changeDate', function () {
            $(this).datepicker('hide');
        });
    });
    //datetimepicker
    $(document).on('focus', '.datetimepicker', function () {
        $(this).datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            pickerPosition: "top-right"
        }).on('changeDate', function () {
            $(this).datetimepicker('hide');
            $("#main_modal").css("overflow-y", "auto");
        });
    });

    //Print Command
    $(document).on('click', '.print', function () {
        $("#preloader").css("display", "block");
        var div = "#" + $(this).data("print");
        $(div).print({
            timeout: 1000,
        });
    });
    //Form validation
    validate();
    // required
    $("input:required, select:required, textarea:required").prev().append('<span class="required"> *</span>');
    $(".dropify:required").parent().prev().append('<span class="required"> *</span>');
    $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
    });

    $("#main_modal").on('show.bs.modal', function () {
        $('#main_modal').css("overflow-y", "hidden");
    });

    $("#main_modal").on('shown.bs.modal', function () {
        setTimeout(function () {
            $('#main_modal').css("overflow-y", "auto");
        }, 1000);
    });
    //Ajax Modal Function
    $(document).on("click", ".ajax-modal", function () {
        var link = $(this).attr("href");
        var title = $(this).data("title");
        $.ajax({
            url: link,
            beforeSend: function () {
                $("#preloader").css("display", "block");
            },
            success: function (data) {
                $("#preloader").css("display", "none");

                $('#main_modal .modal-title').html(title);
                $('#main_modal .modal-body').html(data);
                $('#main_modal .alert-success').css("display", "none");
                $('#main_modal .alert-danger').css("display", "none");
                $('#main_modal').modal('show');
                //init Essention jQuery Library
                /*Summernote editor*/
                summernote();
                autoselected();
                $('#main_modal .select2').select2();
                $('#main_modal .ajax-submit').validate();
                $('#main_modal .dropify').dropify();
                $('#main_modal input:required, #main_modal select:required, #main_modal textarea:required').prev().append('<span class="required"> *</span>');
                $('#main_modal .dropify:required').parent().prev().append('<span class="required"> *</span>');
            }
        });
        return false;
    });

    $(document).on("submit", ".ajax-submit", function () {
        var link = $(this).attr("action");
        $.ajax({
            method: "POST",
            url: link,
            data: new FormData(this),
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#preloader").css("display", "block");
                $("#main_modal .alert").css("display", "none");
            },
            success: function (data) {
                $("#preloader").css("display", "none");
                var json = JSON.parse(data);

                if (json['result'] == "success") {
                    if (typeof json['redirect'] != 'undefined' && json['redirect'] != '') {
                        window.setTimeout(function () { window.location.replace(json['redirect']) }, 1000);
                        toast('success', json['message']);
                        return true;
                    }

                    $("#main_modal .alert-danger").css("display", "none");
                    if (json['action'] == "update") {
                        $('#row_' + json['data']['id']).find('td').each(function () {
                            if (typeof $(this).attr("class") != "undefined") {
                                $(this).html(json['data'][$(this).attr("class").replace(/\s/g, '')]);
                            }
                        });
                    } else if (json['action'] == "store") {
                        $('.ajax-submit')[0].reset();
                        //store = true;
                        var new_row = $("table").find('tr:eq(1)').clone().last();
                        $(new_row).attr("id", "row_" + json['data']['id']);
                        $(new_row).find('td').each(function () {
                            if ($(this).attr("class") == "dataTables_empty") {
                                window.location.reload();
                            }
                            if (typeof $(this).attr("class") != "undefined") {
                                $(this).html(json['data'][$(this).attr("class").replace(/\s/g, '')]);
                            }
                        });

                        var url = window.location.href;
                        $(new_row).find('form').attr("action", url + "/" + json['data']['id']);
                        $(new_row).find('.btn-warning').attr("href", url + "/" + json['data']['id'] + "/edit");
                        $(new_row).find('.btn-info').attr("href", url + "/" + json['data']['id']);
                        $("table").prepend(new_row);
                        //window.setTimeout(function(){window.location.reload()}, 2000);
                    }
                    toast('success', json['message']);
                    $('#main_modal').modal('hide');
                } else {
                    jQuery.each(json['message'], function (i, val) {
                        $("#main_modal .alert-danger").html("<p>" + val + "</p>");
                    });
                    $("#main_modal .alert-success").css("display", "none");
                    $("#main_modal .alert-danger").css("display", "block");
                }
            }
        });
        return false;
    });

    //Ajax Non Modal Submit
    $(".ajax-submit2").each(function () {
        $(this).validate({
            ignore: [],
            submitHandler: function (form) {
                var link = $(form).attr("action");
                $.ajax({
                    method: "POST",
                    url: link,
                    data: new FormData(form),
                    mimeType: "multipart/form-data",
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        $("#preloader").css("display", "block");
                    },
                    success: function (data) {
                        $("#preloader").css("display", "none");
                        var json = JSON.parse(data);
                        if (json['result'] == "success") {
                            if (typeof json['redirect'] != 'undefined' && json['redirect'] != '') {
                                setTimeout(function () {
                                    window.location.replace(json['redirect']);
                                }, 1000);
                            }
                            toast('success', json['message']);
                        } else {
                            jQuery.each(json['message'], function (i, val) {
                                toast('error', val);
                            });
                        }
                    }
                });
                return false;
            },
            invalidHandler: function (form, validator) { },
            errorPlacement: function (error, element) { }
        });
    });

    $(".ajax-submit-tab").validate({
        ignore: [],
        submitHandler: function (form) {
            var link = $(form).attr("action");
            $.ajax({
                method: "POST",
                url: link,
                data: new FormData(form),
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $("#preloader").css("display", "block");
                },
                success: function (data) {
                    $("#preloader").css("display", "none");
                    var json = JSON.parse(data);
                    if (json['result'] == "success") {
                        setTimeout(function () {
                            window.location.replace(json['redirect']);
                        }, 1000);
                        toast('success', json['message']);
                    } else {
                        jQuery.each(json['message'], function (i, val) {
                            toast('error', val);
                        });
                    }
                }
            });
            return false;
        },
        invalidHandler: function (form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var element = validator.errorList[0].element;
                element.focus();
                var tab_id = $(element).closest('.tab-pane').attr('id')
                $('#' + tab_id + '-tab').tab('show');
            }
        },
        errorPlacement: function (error, element) { }
    });
});


function toast(result, message) {
    $.toast({
        heading: result,
        text: message,
        showHideTransition: 'slide',
        icon: result,
        position: 'bottom-left'
    });
}

function validate() {
    //Validation Form
    $(".validate").validate({
        submitHandler: function (form) {
            form.submit();
        },
        invalidHandler: function (form, validator) { },
        errorPlacement: function (error, element) { }
    });
}

function summernote() {
    //summernote editor
    if ($(".summernote").length > 0) {
        // tinymce.init({
        //     selector: "textarea.summernote",
        //     theme: "modern",
        //     height: 250,
        //     plugins: [
        //         "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
        //         "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
        //         "save table contextmenu directionality emoticons template paste textcolor"
        //     ],
        //     toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
        //     style_formats: [
        //         { title: 'Bold text', inline: 'b' },
        //         { title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
        //         { title: 'Red header', block: 'h1', styles: { color: '#ff0000' } },
        //         { title: 'Example 1', inline: 'span', classes: 'example1' },
        //         { title: 'Example 2', inline: 'span', classes: 'example2' },
        //         { title: 'Table styles' },
        //         { title: 'Table row 1', selector: 'tr', classes: 'tablerow1' }
        //     ]
        // });
        
        $('#summernote,.summernote').summernote({
            height: 200,
            popover: {
                image: [],
                link: [],
                air: []
            },
            // toolbar: [
            //     // ['style', ['style']],
            //     // ['font', ['bold', 'italic', 'underline', 'clear']],
            //     // ['fontname', ['fontname']],
            //     // ['color', ['color']],
            //     // ['para', ['ul', 'ol', 'paragraph']],
            //     // ['height', ['height']],
            //     // ['table', ['table']],
            //     // ['view', ['fullscreen', 'codeview']],
            //     // ['help', ['help']]
            // ],
            dialogsInBody: true
        });
    }
}

function autoselected() {
    $(document).ready(function () {
        $('select').each(function (index) {
            var $value = $(this).data('value');
            if (typeof $value != 'undefined') {
                $(this).val($value).trigger('change');
            }
        });
    });
}