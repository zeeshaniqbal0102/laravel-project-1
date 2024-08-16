@extends('layouts.consumer-layout')
@section('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" />
@endsection
@section('styles')
    <style>
        .was-validated .form-control:valid ~ .invalid-feedback,
        .was-validated .form-control:valid ~ .invalid-tooltip,
        .form-control.is-valid ~ .invalid-feedback,
        .form-control.is-valid ~ .invalid-tooltip,
        .was-validated .custom-select:valid ~ .invalid-feedback,
        .was-validated .custom-select:valid ~ .invalid-tooltip,
        .custom-select.is-valid ~ .invalid-feedback,
        .custom-select.is-valid ~ .invalid-tooltip {
            display: none;
        }

        .was-validated .form-control:valid ~ .valid-feedback,
        .was-validated .form-control:valid ~ .valid-tooltip,
        .form-control.is-valid ~ .valid-feedback,
        .form-control.is-valid ~ .valid-tooltip,
        .was-validated .custom-select:valid ~ .valid-feedback,
        .was-validated .custom-select:valid ~ .valid-tooltip,
        .custom-select.is-valid ~ .valid-feedback,
        .custom-select.is-valid ~ .valid-tooltip {
            display: block;
        }
        @media only screen and (max-width: 600px) {
            .button-cstm-img span {
                font-size: 18px;
            }
        }
        .btn-default-card {
            background-color: #5fc4c6;
            color:#fff;
        }
        .payment-opt {
            font-size: 12px;
            color: #428bca;
        }

    </style>
@endsection
@section('content')
    <div class="row no-gutters">
        <div class="col-md-12 col-lg-8 mx-auto">
            <div class="col-lg-11 mx-auto px-md-3 px-lg-2">
                <h1 class="cstm-card-header text-center text-dark my-3 my-lg-4">
                    <span>when &amp; where</span> should we send this eCard?
                </h1>
                <div class="step2 mb-3 mb-lg-4">
                    @include("admin/common/message")
                    <form id="send-now-form" autocomplete="off" class="form-horizontal" action="{{ $url }}"
                          method="post">
                        <input id="billing_id" type="hidden" name="billing_id" value="">
                        <div class="card border-0 rounded-0">
                            <div class="card-body py-0 py-sm-4">
                                <div class="form-group row">
                                    <label for="delivery_method" class="col-sm-4 col-form-label text-sm-right">Delivery Method <span class="text-danger">*</span> </label>
                                    <div class="col-sm-6">
                                        <select name="delivery_method" class="form-control rounded-0" id="delivery_method" placeholder="">
                                            <option value="email"  >eCard sent via Email </option>
                                            <option value="message" selected="selected">eCard via Messaging (eg Text, Slack, Facebook, Snapchat)</option>
                                            <option value="print">eCard printed at Home/Office</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="mail_from" class="col-sm-4 col-form-label text-sm-right">From Name <span class="text-danger">*</span> </label>
                                    <div class="col-sm-6">
                                        <input type="text" name="mail_from" class="form-control rounded-0" id="mail_from" placeholder="">
                                    </div>
                                </div>
                                @if(!auth()->user())
                                    <div class="form-group row">
                                        <label for="mail_from_email" class="col-sm-4 col-form-label text-sm-right">From Email <span class="text-danger">*</span> </label>
                                        <div class="col-sm-6">
                                            <input type="text" name="mail_from_email" class="form-control rounded-0" id="mail_from_email" placeholder="">
                                        </div>
                                    </div>
                                @elseif(auth()->user() && auth()->user()->isAdmin())
                                    <div class="form-group row">
                                        <label for="mail_from_email" class="col-sm-4 col-form-label text-sm-right">From Email <span class="text-danger">*</span> </label>
                                        <div class="col-sm-6">
                                            <input type="text" value="{{ auth()->user()->email }}" name="mail_from_email"
                                                   class="form-control rounded-0" id="mail_from_email" placeholder="">
                                        </div>
                                    </div>
                                @else
                                    <input id="mail_from_email" type="hidden" name="mail_from_email" value="{{ auth()->user()->email }}">
                                @endif
                                <div class="form-group row">
                                    <label for="size" class="col-sm-4 col-form-label text-sm-right">Size <span class="text-danger">*</span></label>
                                    <div class="col-sm-6">
                                        <select id="choose-candy" name="product_id" class="form-control">
                                            <option value="" data-price="0.00" data-fee-to-can="0.00">-- Please Select --</option>
                                            @if($products)
                                                @foreach($products as $prd)                        
                                                    <option 
                                                        value="{{ $prd->id }}" 
                                                        data-size="{{ $prd->sku }}" 
                                                        data-product-id="{{ $prd->id }}" 
                                                        data-fee-to-can="{{ $prd->formatted_fee_to_can }}" 
                                                        data-price="{{ $prd->getApplicablePrice($company) }}" 
                                                        {{ ($prd->sku == $size)?" 
                                                        selected":"" }}
                                                    >
                                                        {{ $prd->formatted_label }}
                                                    </option>     
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div id="candy-price" class="col-sm-2"><span>$0.00</span></div>
                                </div>
                                @if($insertsPrice > 0)
                                    <div class="form-group row">
                                        <div class="col-sm-4 col-form-label text-sm-right"></div>
                                        <div class="col-sm8">
                                            Custom box card fee of ${{ $insertsPrice }} added
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="m-2 p-5" >
                                <textarea id="add-list-receiver" name="excel_data" rows="5" class="form-control" ></textarea>
                            </div>
                            <div id="excel_table" class="m-2 p-2">

                            </div>
                        </div>

                        
                        
                        <input id="message" type="hidden" name="message" value="">
                        <input id="custom-image" type="hidden" name="image_path" value="">
                        <input id="keep-image" type="hidden" name="keep" value="">
                        <div class="payback-section mt-3">

                            <div class="row no-gutters align-items-center justify-content-center">
                                <div class="col-3 col-md-2 d-none d-md-block">
                                    <h2 class="title mb-0 text-right">All set</h2>
                                </div>
                                <div class="col-2 col-md-2 d-none d-md-block text-md-right">
                                    <img src="{{ asset('images/img_arrow.png') }}" class="img-fluid" alt="">
                                </div>
                                <div class="col col-md-6 pl-0">
                                    <div class="button-cstm-img">
                                        <?php $add_cart_button = false; ?>
                                        @if(auth()->user() && (auth()->user()->isCompanyUser() || auth()->user()->isAdmin()))
                                            <?php $add_cart_button = true; ?>

                                            @if ($company && $company->charge_month_end == 1)
                                                <button class="outer-img-btn activity" id="btn-send-now" type="submit">
                                                    <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                                    <span class="custom-send-sugarwish-btn" style="font-size: 16px;">Send now with company account</span>
                                                </button>
                                            @else                                                
                                                <button id="btn-charge-card" class="outer-img-btn activity btn-charge-card" type="submit">
                                                    <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                                    <span class="custom-send-sugarwish-btn" style="font-size: 16px;">Charge card on file</span>
                                                </button>
                                    
                                            @endif
                                        @elseif(auth()->user() && auth()->user()->isConsumer() && auth()->user()->cc_on_file == 1 && $billing)
                                            <?php $add_cart_button = true; ?>
                                            @if (auth()->user()->status == 1)
                                            <button id="btn-charge-card" class="outer-img-btn activity btn-charge-card" type="submit">
                                                <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                                <span class="custom-send-sugarwish-btn" style="font-size: 16px;">Charge card on file</span>
                                            </button>
                                            @else
                                            <button class="outer-img-btn activity" id="btn-send-now" type="submit">
                                                <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                                <span class="custom-send-sugarwish-btn" style="font-size: 16px;">Send Now (Card ending in {{ $billing->last_four }})</span>
                                            </button>
                                            @endif
                                        @else
                                            <button class="outer-img-btn activity" id="btn-add-to-cart" type="submit">
                                                <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                                <span id="add-to-cart" class="custom-send-sugarwish-btn">add to cart</span>
                                            </button>
                                        @endif
                                        <input type="hidden" name="sku" value="{{ $sku }}">
                                        <input id="f_size" type="hidden" name="size" value="{{ $size }}">
                                        
                                        {{ csrf_field() }}
                                    </div>
                                </div>
                            </div>
                            @if($add_cart_button)
                                <div class="row no-gutters align-items-center justify-content-center">
                                    <div class="col-3 col-md-2 d-none d-md-block">
                                    </div>
                                    <div class="col-2 col-md-2 text-md-right d-none d-md-block">
                                    </div>
                                    <div class="col col-md-6 pl-0 text-center">
                                        <div class="button-cstm-img">
                                            <button id="add-to-cart-btn" class="btn btn-link" type="submit">or add to cart  </button>
                                        </div>
                                        <span class="payment-opt">(other payment options available)</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    @include('consumer.modals._add_receiver_from_list_modal')
    @include('consumer.modals._preview_card')
    @includeWhen((auth()->user() && auth()->user()->isAdmin()),'consumer.modals.proceed')
    @includeWhen((auth()->user() && auth()->user()->isCompanyUser() && $ccOnFile == 1), 'consumer.modals._card_not_available_modal')
  
    @include('consumer.modals.add-card')
    @include('consumer.modals.choose-card')
    @include('consumer.modals._add_promo_code')
@endsection
@section('scripts')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

    <script type="text/javascript">
        Stripe.setPublishableKey("{{ \Illuminate\Support\Facades\Config::get('services.stripe.key') }}");
    </script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="{{ asset('admin/js/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/jquery-validation/jquery.validate.js') }}"></script>
    <script src="{{ asset('admin/plugins/jquery-validation/additional-methods.js') }}"></script>    
    <script src="{{ asset('js/axios/axios.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery.creditCardValidator.js') }}"></script>
    <script src="{{ asset('admin/js/inputmask/jquery.inputmask.js') }}"></script>

    <script>

        $(document).ready(function(){
            function generateTable() {
                // Get the data
                var excelData = document.getElementById('add-list-receiver').value;
                
                // split into rows
                excelRow = excelData.split(String.fromCharCode(10));

                // split rows into columns
                for (i=0; i<excelRow.length; i++) {
                    excelRow[i] = excelRow[i].split(String.fromCharCode(9));

                }
                
                // start to create the HTML table
                var myTable = document.createElement("table");
                myTable.classList.add("table");
                var myTbody = document.createElement("tbody");
                
                // Loop over the rows
                for (i=0; i<excelRow.length - 1; i++) {

                    // create a row in the HTML table
                    var myRow = document.createElement("tr");
                    
                    // Loop over the columns and add TD to the TR
                    for (j=0; j<excelRow[i].length; j++) {
                        // Loop over the row columns
                        if (excelRow[i][j].length != 0) {
                                var myCell = document.createElement("td");
                                myCell.innerHTML = excelRow[i][j];
                        }			
                        myRow.appendChild(myCell);			
                    }
                    myTbody.appendChild(myRow);	
                }
                    myTable.appendChild(myTbody);

                // document.body.appendChild(myTable);
                $('#excel_table').html(myTable);
                // console.log(myTable)





                // var data = $('#add-list-receiver').val();
                // var rows = data.split("\n");

                // var table = $('<table class="table" />');

                // for(var y in rows) {
                //     var cells = rows[y].split("\t");
                //     var row = $('<tr />');
                //     for(var x in cells) {
                //         row.append('<td>'+cells[x]+'</td>');
                //     }
                //     table.append(row);
                // }
                // $('#excel_table').html(table);
            }

            $(document).ready(function() {
                $('#add-list-receiver').on('paste', function(event) {
                $('#add-list-receiver').on('input', function() {
                    generateTable(); 
                    $('#add-list-receiver').off('input');
                })
            })
            })

        });

        var Config = {
            base_url: "{{ url('/') }}"
        };
        var checkInvoiceByUserUrl = "{{ route('consumer.is-company-invoice-by-user') }}";
        var total_amount = 0.00;
        var no_charge_url = '{{ $noChargeUrl }}';
        function validateForm() {
            $('#send-now-form').validate({
                rules: {
                    delivery_method: "required",
                    mail_from: {
                        required: true,
                        maxlength: 40
                    },
                    mail_from_email: {
                        required: {
                            depends:function(){
                                $(this).val($.trim($(this).val()));
                                return true;
                            }
                        },
                        validate_email:true
                    },
                    product_id: {
                        required: true
                    },
                    "mail_to[]": {
                        required: true
                    },
                    "mail_to_email[]": {
                        required: {
                            depends:function(){
                                $(this).val($.trim($(this).val()));
                                return true;
                            }
                        },
                        validate_email: true
                    },
                    "delivery_date[]": {
                        required: true
                    }
                },
                errorClass: 'invalid-feedback',
                errorElement: 'div',
                highlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-valid').addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                focusInvalid: false,
                invalidHandler: function(form, validator) { 
                    if (!validator.numberOfInvalids())
                        return;
                    $(validator.errorList[0].element).focus();                    
                }

            });
        }
        function validateDate(date) {
            var regex = /^[0-9]{1,4}[\/\-][0-9]{1,2}[\/\-][0-9]{1,4}$/;
            return regex.test(date);
        }
        var size = "{{ $size }}";
        var sku = "{{ $sku }}";
        var discount = "{{ $discount }}";
        var price = 0.00;
        var fee_to_can = 0.00 ;
        var discount_percent = {{ ($company && $company->discount_percent) ? $company->discount_percent: 0 }};
        var insertPrice = {{ $insertsPrice }};
        var consumer_order = JSON.parse(localStorage.getItem(sku));
        var addToCartLink = "{{ $addToCartLink }}";
        var corporateDiscount = {{ $corporateDiscount }};
        var billingCount = {{ count($billings) }};
        var availableCredit = {{ $availableCredit }};
        var user_id = '{{ $user_id }}';
        var company_id = '{{ $company_id }}';

        var referal_credit = {{ $referalAmount }};
        
        var couponData = @if($couponData) {!! json_encode($couponData) !!} @else '' @endif;
        if (consumer_order && consumer_order.message.length > 0) {
            $('input[name=message]#message').val(consumer_order.message);
            $('#gift-card-preview .message-text').text(consumer_order.message);
        }
        if (consumer_order && consumer_order.image.length > 0) {
            $('input[name=image_path]#custom-image').val(consumer_order.image);
        }
        if (consumer_order && consumer_order.keep_image.length > 0) {
            $('input[name=keep]#keep-image').val(consumer_order.keep_image);
        }

        function calculateCouponDiscount(couponData, price, receiver_count)
        {
            if (couponData == '')
                return 0.00;

            var couponDiscount = 0.00;
            if (couponData.shipping_start_date && couponData.shipping_end_date) {
                $("input[name='delivery_date[]']").each(function() {
                    var _date = $(this).val();
                   if(_date){
                        var datetocheck = moment(_date).format('YYYY-MM-DD');
                        if (couponData.shipping_start_date <= datetocheck && couponData.shipping_end_date >= datetocheck){
                            if (couponData.discount_type == 'percentage') {
                                couponDiscount = parseFloat(couponDiscount) +  parseFloat((couponData.discount * price * 0.01).toFixed(2));
                            } else if (couponData.discount_type == 'amount') {
                                couponDiscount = parseFloat(couponDiscount) + parseFloat((couponData.discount > price) ? price : couponData.discount);
                            }
                        }
                    }
                });    
            }else{
                if (couponData.discount_type == 'percentage') {
                    couponDiscount = (couponData.discount * price * 0.01).toFixed(2);
                } else if (couponData.discount_type == 'amount') {
                    couponDiscount = (couponData.discount > price) 
                                        ? price 
                                        : couponData.discount ;
                }
            }
            return couponDiscount;
        }

        $(document).ready(function() {
            displayProductPrice();
            calculate();
            jQuery.validator.addMethod("validate_email", function(value, element) {
                if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value)) {
                    return true;
                } else {
                    return false;
                }
            }, "Please make sure the email address is valid. Hint: You might see this error if you are missing some letters in \“@email.com\”");
            validateForm();
            $("#delivery_method").val("email").change();

            receiver_template = receiver_email = '@include("consumer.partials._receiver_fields")';
            receiver_message = '@include("consumer.partials._receiver_fields_message")';
            receiver_print = '@include("consumer.partials._receiver_fields_print")';

            $(document).on('change', '#delivery_method', function(e) {
                _delivery_method_val = $(this).val();
                if(_delivery_method_val == 'message'){
                    $('#btn-add-recipient-list').hide();
                    receiver_template = receiver_message;
                    $('#recipients-wrap').html(receiver_template);
                }else if(_delivery_method_val == 'print'){
                    $('#btn-add-recipient-list').hide();
                    receiver_template = receiver_print;
                    $('#recipients-wrap').html(receiver_template);
                }else{
                    $('#btn-add-recipient-list').show();
                    receiver_template = receiver_email;
                    $('#recipients-wrap').html(receiver_template);
                    $('.datepicker').datepicker({
                        format: "mm/dd/yyyy",
                        startDate: moment().format('MM/DD/YYYY'),
                        todayHighlight: true,
                        autoclose:true
                    });
                }
            });
            $('#choose-candy').on('change', function() {
                displayProductPrice();
                $('#f_size').val($('#choose-candy option:selected').data('size'));
                calculate();
            });
            $("#recipients-wrap button.remove_field").hide();

            $(document).on('click', '#btn-add-recipient-list', function(e) {
                e.preventDefault();
            });

            $(document).on('click', '#btn-promo-code', function(e) {
                e.preventDefault();
            });

            $('.datepicker').datepicker({
                format: "mm/dd/yyyy",
                startDate: moment().format('MM/DD/YYYY'),
                todayHighlight: true,
                autoclose:true
            });
            $(document).on('change', "input[name='delivery_date[]']", function(e) {
                calculate();
            });

            $(document).on('click', '#btn-add-recipient', function(e) {
                e.preventDefault();
                $('#recipients-wrap').append(receiver_template);
                $("#recipients-wrap button.remove_field").show();
                calculate();
                $('.datepicker').datepicker({
                    format: "mm/dd/yyyy",
                    startDate: moment().format('MM/DD/YYYY'),
                    todayHighlight: true,
                    autoclose:true
                });
            });
            $(document).on('click', '.remove_field', function() {
                $(this).closest('.receiver').remove();
                var receiver_count = $('.receiver').length;
                if (receiver_count <= 1) {
                    $("#recipients-wrap button.remove_field").hide();
                }
                calculate();
            });

            $(document).on('click', '#btn-add-recipients-to-page', function(e) {
                var receiver_list_raw = $('#receiver-list').val().trim();
                if (receiver_list_raw.length === 0) {
                    $('#receiver-message').text('Please add receivers');
                    return;
                }
                var receiver_list = receiver_list_raw.split("\n");
                if(receiver_list.length > 1000) {
                    $('#receiver-message').text("Whoa - hold your horses. We're a little slow on the uptake and can only accept orders of 1000 cards max. Please place multiple orders. Sorry for the trouble.");
                    return;
                }
                if (receiver_list.length > 0) {
                    var receivers_data =  [];
                    for (var rec in receiver_list) {
                        var receiver_raw = receiver_list[rec].trim();
                        if(receiver_raw.length > 0) {
                            var receiver = null;
                            if (receiver_raw.indexOf('\t') > -1) {
                                receiver = receiver_raw.split('\t')
                            } else if(receiver_raw.indexOf(',') > -1) {
                                receiver = receiver_raw.split(",");
                            } else {
                                $('#receiver-message').text('Please check the receiver list. You might have formatted the list incorrectly or might have typos');
                                return false;
                            }
                            if (receiver.length === 3) {
                                if (validateDate(receiver[2])) {
                                    if (!moment(receiver[2]).isValid()) {
                                        $('#receiver-message').text('Please check the receiver list. Some delivery dates might be invalid');
                                        return false;
                                    } else {
                                        if(moment(receiver[2]).year() < moment().year()) {
                                            $('#receiver-message').text("Are you trying to time travel? We can't send cards with send dates in the past");
                                            return false;
                                        } else if (moment(receiver[2]).isBefore(moment(), 'day')) {
                                            $('#receiver-message').text("Are you trying to time travel? We can't send cards with send dates in the past");
                                            return false;
                                        } else {
                                            var rec = {mail_to: receiver[0], mail_to_email: receiver[1], mail_delivery_date: moment(receiver[2]).format('MM/DD/YYYY')};
                                            receivers_data.push(rec);
                                        }
                                    }
                                } else {
                                    $('#receiver-message').text('Please check the receiver list. Some delivery dates might be invalid');
                                    return false;
                                }
                            } else if (receiver.length === 2) {
                                var rec = {mail_to: receiver[0], mail_to_email: receiver[1], mail_delivery_date: 'today'};
                                receivers_data.push(rec);
                            } else {
                                $('#receiver-message').text('Please check the receiver list. You might have formatted the list incorrectly or might have typos');
                                return;
                            }
                        }
                    }
                    if (receivers_data.length > 0) {
                        if ($('#recipients-wrap .receiver:first').find('input[name="mail_to[]"]').val().trim() == '') {
                            $('#recipients-wrap .receiver:first').remove();
                            calculate();
                        }
                        for (var recd in receivers_data) {
                            $('#recipients-wrap').append('@include("consumer.partials._receiver_fields")');
                            $('#recipients-wrap .receiver:last-child').find('input[name="mail_to[]"]').val(receivers_data[recd].mail_to);
                            $('#recipients-wrap .receiver:last-child').find('input[name="mail_to_email[]"]').val(receivers_data[recd].mail_to_email);
                            if (receivers_data[recd].mail_delivery_date === 'today') {
                                $('#recipients-wrap .receiver:last-child').find('.delivery-date').html('<div class="row"><div class="col-xs-1 hidden-md hidden-lg"></div><div class="col-xs-10 col-md-12">Today<a href="javascript:void(0)" class="change-link">change</a><input type="hidden" name="delivery_date[]" value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}"></div></div>');
                            } else {
                                $('#recipients-wrap .receiver:last-child').find('.delivery-date').html('<input type="text" name="delivery_date[]" placeholder="mm/dd/yyyy" class="datepicker form-control" value="'+ receivers_data[recd].mail_delivery_date+'" required>');

                            }
                            calculate();
                        }
                        $('.datepicker').datepicker({
                            format: "mm/dd/yyyy",
                            startDate: moment().format('MM/DD/YYYY'),
                            todayHighlight: true,
                            autoclose:true
                        });
                    }
                }
                $('#add-receiver-modal').modal('hide');
            });

            var old_sender = '{name}';
            $('#preview-giftcard').on('click', function() {
                var sender = $('#mail_from').val().trim();
                var message = $('#message').val();
                var substring = "{NAME}";
                var substring2 = "{Name}";
                var substring3 = "{name}";
                $('.sender-span').text();
                var customCards = [
                    'UDC-sugarwishinsert',
                    'UDPOPCORN-sugarwishinsert',
                    'UDCOOKIES-sugarwishinsert'
                ];
                if (customCards.includes(sku)) {
                    $('#hidden-custom-image').attr('src', consumer_order.image).show();
                }
                if(message.indexOf(substring) !== -1) {
                    var receiver = $('input[name="mail_to\[\]"]:first').val();
                    message = message.replace("{NAME}", receiver);
                    $('#gift-card-preview .message-text').text(message);
                }
                if(message.indexOf(substring2) !== -1) {
                    var receiver = $('input[name="mail_to\[\]"]:first').val();
                    message = message.replace("{Name}", receiver);
                    $('#gift-card-preview .message-text').text(message);
                }
                if(message.indexOf(substring3) !== -1) {
                    var receiver = $('input[name="mail_to\[\]"]:first').val();
                    message = message.replace("{name}", receiver);
                    $('#gift-card-preview .message-text').text(message);
                }
                if (sender.length <= 0)
                    sender = '{name}';

                var senderfinal = '<span class="sender-span">'+sender+'</span>';
                var email_body = $('#ecard-body').html();
                var ecard_body = replaceAll(email_body, old_sender, senderfinal);
                $('#ecard-body').html(ecard_body);
                old_sender = senderfinal;
            });

            $(document).on('click', '.is_canadian', function(event) {
                if($(this).prop("checked") === true) {
                    $( event.target ).siblings('input.canadian').val('1');
                } else {
                    $( event.target ).siblings('input.canadian').val('0');
                }
                calculate()
            });

            $('#add-to-cart-btn').on('click', function(e) {
                e.preventDefault();
                if($('#send-now-form').valid()) {
                    $('#add-to-cart-btn').attr('disabled', true);
                    $('#send-now-form').attr('action', addToCartLink);
                    $('#send-now-form').submit();
                }
            });
            @if(auth()->user() && auth()->user()->isAdmin())

            $('#btn-send-now').on('click', function(e) {
                e.preventDefault();
                if($('#send-now-form').valid()) {
                    $('#btn-send-now').attr('disabled', true);
                    var token = $('input[name=_token]').val();
                    $.ajax({
                        'url' : checkInvoiceByUserUrl,
                        'method': 'post',
                        'data': {
                            _token: token,
                            email: $('#mail_from_email').val(),
                            company_id: {{ $company->id }}
                        },
                        'success': function(data) {
                            if (data.invoice_by_user == 'yes') {
                                $('#proceed-further-modal').modal('show')
                                return false;
                            } else {
                                $('#send-now-form').submit();
                            }
                        },
                        'error': function() {

                        }
                    });
                    return false;
                }
                $('#btn-send-now').attr('disabled', true);
                $('#send-now-form').submit();
            });

            $('#btn-cancel').on('click', function () {
                $('#proceed-further-modal').modal('hide');
                return true;
            });
            $('#btn-proceed').on('click', function () {
                $('#proceed-further-modal').modal('hide');
                $('#send-now-form').submit();
            });
            @elseif(auth()->user() && auth()->user()->isCompanyUser() && $ccOnFile == 1)
            $('#btn-send-now').on('click', function(e) {
                e.preventDefault();
                $('#btn-send-now').attr('disabled', true)
                if($('#send-now-form').valid()) {
                    $('#card-not-available-modal').modal('show');
                }
            });
            $('#use-alternate-payment').on('click', function() {
                $('#send-now-form').attr('action', addToCartLink);
                $('#send-now-form').submit();
            })
            $('#m-add-to-cart-btn').on('click', function() {
                $('#send-now-form').attr('action', addToCartLink+'?redirect=add-payment');
                $('#send-now-form').submit();
            });
            @else 
            $('#btn-send-now').on('click', function(e) {
                e.preventDefault();                
                if($('#send-now-form').valid()) {
                    $('#btn-send-now').attr('disabled', true);
                    $('#send-now-form').submit();
                }
            });
            @endif

        });

        function replaceAll(str, find, replace) {
            return str.replace(new RegExp(find, 'g'), replace);
        }

        function calculate() {
            var shipping_cost = parseFloat($('#choose-candy option:selected').data('fee-to-can'));
            var product = parseFloat($('#choose-candy option:selected').val());
            var price = parseFloat($('#choose-candy option:selected').data('price'));
            var canadian_count = $('input[type="checkbox"][name="is_canadian[]"]:checked').length;
            var receiver_count = $('.receiver').length;
            if (product)
                price = price + insertPrice;
            var sub_total = parseFloat(price * receiver_count);
            var couponDiscount = parseFloat(calculateCouponDiscount(couponData, price,receiver_count));
            var shipping = parseFloat(canadian_count * shipping_cost);
            var corporateDiscount = discount_amount = parseFloat(sub_total * discount * 0.01);
            if (couponDiscount > 0 && couponDiscount >= corporateDiscount) {
                corporateDiscount = discount_amount = 0.00;
            } else {
                couponDiscount = 0.00;
            }

            if (couponDiscount > sub_total) {
                couponDiscount = sub_total;
            }
            total_amount = sub_total + shipping - discount_amount - referal_credit - couponDiscount;
            total_amount = total_amount > 0.00 ? total_amount : 0.00;
            var perOrderCredit = 0.00;
            var companyCredit = 0.00;

            if (availableCredit > 0.00 && total_amount >= availableCredit) {
                perOrderCredit = availableCredit / receiver_count;
                companyCredit = perOrderCredit * receiver_count;
                discount_amount = discount_amount + companyCredit;
                total_amount = total_amount - availableCredit;
            } else if (availableCredit > 0.00 && total_amount < availableCredit) {
                perOrderCredit = total_amount / receiver_count;
                companyCredit = perOrderCredit * receiver_count;
                discount_amount = discount_amount + companyCredit;
                total_amount = 0.00;
            }
            console.log('coupon discount', couponDiscount);
            console.log('corporate discount', corporateDiscount);
            if (shipping > 0.00) {
                $('.shipping').show();
            } else {
                $('.shipping').hide();
            }
            if (discount_amount == 0.00 && shipping == 0.00 && referal_credit <= 0.00 && corporateDiscount <=0.00)
                $('.subtotal').hide();
            else
                $('.subtotal').show();
            if (companyCredit == 0.00) {
                $('.credit').hide();
            } else {
                $('.credit').show();
            }
            if (total_amount == 0) {
                $('#btn-charge-card').removeClass('btn-charge-card').addClass('btn-send-now');
                $('#btn-charge-card span.custom-send-sugarwish-btn').text('send now');
            } else {
                $('#btn-charge-card').removeClass('btn-send-now').addClass('btn-charge-card');
                $('#btn-charge-card span.custom-send-sugarwish-btn').text('Charge card on file');
            }
            if (referal_credit > 0) {
                $('#total-container').find('#referal_credit').text('$'+ parseFloat(referal_credit).toFixed(2));
            }
            
            if (couponDiscount > 0.0) {
                $('.subtotal').show();
                $('#total-container').find('.discount').hide();
                $('#total-container').find('.promo-discount').show();
                $('#total-container').find('#promo-discount').text('$'+ parseFloat(couponDiscount).toFixed(2));
            } else if (corporateDiscount > 0.00) {
                $('.subtotal').show();
                $('#total-container').find('.discount').show();
                $('#total-container').find('.promo-discount').hide();
                $('#total-container').find('#discount').text('$'+ parseFloat(corporateDiscount).toFixed(2));
            }
            $('#total-container').find('#subtotal').text('$'+ sub_total.toFixed(2));
            $('#total-container').find('input[name=sub_total]').val(sub_total.toFixed(2));
            $('#total-container').find('#shipping').text('$'+ shipping.toFixed(2));
            $('#total-container').find('input[name=shipping]').val(shipping.toFixed(2));
            $('#total-container').find('#discount').text('$'+ parseFloat(corporateDiscount).toFixed(2));
            $('#total-container').find('#promo-discount').text('$'+ parseFloat(couponDiscount).toFixed(2));
            $('#total-container').find('#credit').text('$'+ parseFloat(companyCredit).toFixed(2));
            $('#total-container').find('input[name=discount]').val(discount_amount);
            $('#total-container').find('#total').text('$'+ total_amount.toFixed(2));
            $('#total-container').find('input[name=total]').val(total_amount);
            
        }

        function displayProductPrice() {
            $('#candy-price > span')
                .text('$' + parseFloat($('#choose-candy option:selected')
                .data('price'))
                .toFixed(2));
        }
    </script>
    

    <script>
        function validateCardForm() {
            $('#add-new-card-form').validate({
                rules: {
                    ccn: "required",
                    cvc: "required",
                    zip_code: "required",
                    month: "required",
                    year: "required",
                    
                },
                errorClass: 'invalid-feedback',
                errorElement: 'div',
                highlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-valid').addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },

            });
        }
        $(document).ready(function() {
            
                  
            var delivery_method = localStorage.getItem("consumer_delivery_method");
            var mail_from = localStorage.getItem("consumer_mail_from");

            var mail_from_email = localStorage.getItem("consumer_mail_from_email");
            var choose_candy = localStorage.getItem("consumer_choose_candy");
            
            var mailtos = localStorage.getItem("consumer_mail_to");
            var deliveryDates = localStorage.getItem("consumer_delivery_dates");
            var mailToEmails = localStorage.getItem("consumer_mailtoemails");
           
            if (delivery_method !== null) {
                $('#delivery_method').val(delivery_method);
                $('#delivery_method').change();
            } 
            if (mail_from !== null) $('#mail_from').val(mail_from);
            if (mail_from_email !== null) $('#mail_from_email').val(mail_from_email);
            if (choose_candy !== null) {
                $('#choose-candy').val(choose_candy);
                $('#choose-candy').change();
            }
            if (mailtos !== null) {
                var mailToSplitted = mailtos.split(',');
                var deliveryDatesSplitted = deliveryDates.split(',');
                var mailToEmailsSplitted = mailToEmails.split(',');
                var deliveryMethod = $('#delivery_method').val();
                for (i = 0; i < mailToSplitted.length; i++) {
                    if (i > 0 && deliveryMethod == 'email')
                        $('#recipients-wrap').append('@include("consumer.partials._receiver_fields")');
                    else if (i > 0 && deliveryMethod == 'message')
                        $('#recipients-wrap').append('@include("consumer.partials._receiver_fields_message")');
                    else if (i > 0 && deliveryMethod == 'print')
                        $('#recipients-wrap').append('@include("consumer.partials._receiver_fields_print")');
                    $('#recipients-wrap .receiver:last-child').find('input[name="mail_to[]"]').val(mailToSplitted[i]);
                    $('#recipients-wrap .receiver:last-child').find('input[name="mail_to_email[]"]').val(mailToEmailsSplitted[i]);
                    if (deliveryMethod == 'email') {
                        if (deliveryDatesSplitted[i] === 'today') {
                            $('#recipients-wrap .receiver:last-child').find('.delivery-date').html('<div class="row"><div class="col-xs-1 hidden-md hidden-lg"></div><div class="col-xs-10 col-md-12">Today<a href="javascript:void(0)" class="change-link">change</a><input type="hidden" name="delivery_date[]" value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}"></div></div>');
                        } else {
                            $('#recipients-wrap .receiver:last-child').find('.delivery-date').html('<input type="text" name="delivery_date[]" placeholder="mm/dd/yyyy" class="datepicker form-control" value="' + deliveryDatesSplitted[i] + '" required>');
                        }
                    }
                    calculate();
                    $('.datepicker').datepicker({
                        format: "mm/dd/yyyy",
                        startDate: moment().format('MM/DD/YYYY'),
                        todayHighlight: true,
                        autoclose:true
                    });
                }
            }
            localStorage.removeItem("consumer_delivery_method");
            localStorage.removeItem("consumer_mail_from");
            localStorage.removeItem("consumer_mail_from_email");
            localStorage.removeItem("consumer_choose_candy");
            localStorage.removeItem("consumer_mail_to")
            localStorage.removeItem("consumer_mailtoemails")
            localStorage.removeItem("consumer_delivery_dates")
            validateCardForm();
            /**
             * Reveals credit card type and inserts it into the credit card input
             */
            $("#card").keyup( function(){
                if( $("#card").val() !== ''){
                    $('#card').validateCreditCard(function(result)
                    {
                        switch(result.card_type.name)
                        {
                            case 'visa':
                                $("#card").css({
                                    "background":"white url('"+Config.base_url+"/admin/img/visa.png') no-repeat scroll 5px 5px",
                                    "padding-left": "50px"
                                });
                                $("#card").inputmask("mask", {"mask": "9999 9999 9999 9999"});
                                break;
                            case 'amex':
                                $("#card").css({
                                    "background":"white url('"+Config.base_url+"/admin/img/amex.png') no-repeat scroll 7px 7px",
                                    "padding-left": "50px"
                                });
                                $("#card").inputmask("mask", {"mask": "9999 999999 99999"});
                                break;
                            case 'mastercard':
                                $("#card").css({
                                    "background":"white url('"+Config.base_url+"/assets/img/mastercard.png') no-repeat scroll 7px 7px",
                                    "padding-left": "50px"
                                });
                                $("#card").inputmask("mask", {"mask": "9999 9999 9999 9999"});
                                break;
                            default:
                                $("#card").css({
                                    "background":"white"
                                });
                                $("#card").inputmask("mask", {"mask": "9999 9999 9999 9999"});
                                break;
                        }
                    });
                }
            });
            /**
             * Takes the card information and sends it to stripe, expects a token
             */
            $('#btn-add-new-card').click(function(event) {
                //console.log("Submitting form");
                validateCardForm();

                var $form = $("#add-new-card-form");
                if($form.valid()) {
                    // Disable the submit button to prevent repeated clicks
                    $form.find('button').prop('disabled', true);
                    if(!Stripe.card.validateCardNumber(jQuery('#card').val())){
                        $form.find('#errors').text("Not a valid card");
                        $form.find('button').prop('disabled', false);
                        $("#errors").show();
                        return false;
                    }

                    if(!Stripe.card.validateCVC(jQuery('#cvc').val())){
                        $form.find('#errors').text("Not a valid cvc number");
                        $form.find('button').prop('disabled', false);
                        $("#errors").show();
                        return false;
                    }

                    if(!Stripe.card.validateExpiry(jQuery('#exp_month').val(), jQuery('#exp_year').val())){
                        $form.find('#errors').text("Not a valid date");
                        $form.find('button').prop('disabled', false);
                        $("#errors").show();
                        return false;
                    }

                    //do the stripe stuff
                    Stripe.card.createToken($form, stripeResponseHandler);
                    // Prevent the form from submitting with the default action
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return false;
                }
            });

            function stripeResponseHandler(status, response) {
                var $form = $('#add-new-card-form');

                if (response.error) {
                    // Show the errors on the form
                    $form.find('#errors').text(response.error.message);
                    $("#errors").show();
                    $(".loading").remove();
                    $form.find('button').prop('disabled', false);
                } else {
                    // response contains id and card, which contains additional card details
                    var token = response.id;
                    // Insert the token into the form so it gets submitted to the server
                    $form.append($('<input type="hidden" name="stripeToken" id="stripe_token" />').val(token));
                    // and submit
                    $form.find('#errors').text('');
                    axios.post('/admin/billing/add-new-card', {  
                        stripeToken: token,                  
                        zip_code: $('#zip').val(),
                        month: $('#exp_month').val(),
                        year: $('#exp_year').val(),
                        _token: '{{ csrf_token() }}',
                        user_id,
                        company_id,
                    })
                    .then(function (response) {
                        $form.find('#errors').text('');
                        if (response.data.status == 'success') {
                            $('#success-msg').text(response.data.message).css('color', 'green').show();
                            setTimeout(function() {
                                $('#add-new-card-modal').modal('hide');
                                localStorage.setItem("consumer_delivery_method", $('#delivery_method').val());
                                localStorage.setItem("consumer_mail_from", $('#mail_from').val());
                                localStorage.setItem("consumer_mail_from_email", $('#mail_from_email').val());
                                localStorage.setItem("consumer_choose_candy", $('#choose-candy').val());
                                var mailtos = $("input[name='mail_to[]']").map(function(){return $(this).val();}).get();
                                var mailtoemails = $("input[name='mail_to_email[]']").map(function(){return $(this).val();}).get();
                                var delivery_dates = $("input[name='delivery_date[]']").map(function(){return $(this).val();}).get();
                                
                                localStorage.setItem("consumer_mail_to", mailtos)
                                localStorage.setItem("consumer_mailtoemails", mailtoemails)
                                localStorage.setItem("consumer_delivery_dates", delivery_dates)
                                location.reload(true);
                            }, 2500);
                        } else {
                            $('#success-msg').text(response.data.message).css('color', 'red').show();
                            $form.find('button').prop('disabled', false);
                        }
                        
                        return false;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                    
                }
            }

            $(document).on('click','#btn-charge-card.btn-charge-card', function(e) {
                e.preventDefault();
                if ($('#send-now-form').valid()) {
                    if (billingCount > 0) {
                        $('#choose-card-modal').modal('show');
                    } else {
                        $('#add-new-card-modal').modal('show');
                    }
                }
            });
            $(document).on('click', '#btn-charge-card.btn-send-now', function(e) {
                e.preventDefault();
                if ($('#send-now-form').valid()) {
                    if (total_amount <= 0.00) {
                        $('#send-now-form').attr('action', no_charge_url);
                    }
                    $('#send-now-form').submit();
                }
            });
            
            $('#btn-add-to-cart').on('click', function(e) {
                e.preventDefault();
                if ($('#send-now-form').valid()) {
                    $('#btn-add-to-cart').attr('disabled', true);
                    $('#send-now-form').submit();
                }
            })
            $('.btn-pay-with-cc').on('click', function() {
                $('.btn-pay-with-cc').attr('disabled', true);
                $('#billing_id').val($(this).data('billing_id'));
                $('#send-now-form').submit();
            })

            $(document).on('click', '#btn-check-validity',function() {
                var code = $('#code').val();
                if (code == '') {
                    return false;
                }

                sending_dates = $("input[name='delivery_date[]']").map(function(){return $(this).val();}).get();

                $.ajax({
                    url: "{{ route('consumer.check-coupon-validity') }}",
                    method: 'POST',
                    data: {
                        code: code,
                        product_id: $('#choose-candy').val(),
                        page: 'receiver',
                        _token: '{{ csrf_token() }}',
                        dates: sending_dates
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.status == false) {
                            $('.coupon-error').text(data.message);
                            $('.coupon-body').show();
                            $('.apply-body').hide();
                        } else {
                            $('.coupon-error').text('');
                            $('#coupon-desc').html(data.data.message);
                            $('.coupon-body').hide();
                            $('.apply-body').show();

                        }
                    }
                });
            });
            $(document).on('click', '#btn-apply-code', function() {
                localStorage.setItem("consumer_delivery_method", $('#delivery_method').val());
                localStorage.setItem("consumer_mail_from", $('#mail_from').val());
                localStorage.setItem("consumer_mail_from_email", $('#mail_from_email').val());
                localStorage.setItem("consumer_choose_candy", $('#choose-candy').val());
                var mailtos = $("input[name='mail_to[]']").map(function(){return $(this).val();}).get();
                var mailtoemails = $("input[name='mail_to_email[]']").map(function(){return $(this).val();}).get();
                var delivery_dates = $("input[name='delivery_date[]']").map(function(){return $(this).val();}).get();
                
                localStorage.setItem("consumer_mail_to", mailtos)
                localStorage.setItem("consumer_mailtoemails", mailtoemails)
                localStorage.setItem("consumer_delivery_dates", delivery_dates)
                location.reload(true);
            });
            
        });       
    </script>
    @include('consumer.partials._redirect_js')
@endsection
