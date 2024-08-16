@extends("admin.layouts.dashboard-layout")
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">
@endsection
@section("content")

    <style>
        body {
            background-color: #fff;
        }
        .inner {
            margin-top:20px;
        }
        .new-theme-btn .btn {
            background-color: #5fc4c6;
            border-color: #5fc4c6;
            border-radius: 9px;
            padding: 10px 40px;
            color: #fff;
            font-weight: bold;
            font-size: 18px;
        }
        .page-title h2 {
            display: inline-block;
            text-transform: none;
            vertical-align: middle;
            margin-bottom: 0px;
            margin-top: 0px;
        }
        .page-title img {
            display: inline-block;
        }

        .page-title h2 span {
            font-size: 14px;
        }
        .btn-keep-original {
            background: -webkit-gradient( linear, left top, left bottom, color-stop(0.05, #66cccc), color-stop(1, #66cced));
            border-color: #5fc1c5;
            color: #fff;
            font-weight: bold;
            font-family: arial;
            font-size: 12px;
            padding: 3px 10px;
        }
    </style>
    <div class="inner">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title text-center">
                    <img src="{{ asset('images/creditcard-optimized.png') }}" alt="">
                    <h2>Payment Info<span style="position:relative; top:20px; left: -175px; display: inline-block">(the boring stuff)</span></h2>
                </div>
                
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 text-right">
                @if(!Session::has('company_id'))
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="" class="col-sm-7 control-label">Credit card on file</label>
                            <div class="col-sm-5">
                                <select class="form-control" id="cc_on_file" name="cc_on_file" onchange="ccOnFile(this)" data-company_id=" {{ $company_id }}">
                                    <option {{ $company->cc_on_file == 1 ? 'selected' : '' }} value="1">Yes</option>
                                    <option {{ $company->cc_on_file == 0 ? 'selected' : '' }} value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-md-8 text-right new-theme-btn">
                <a class="btn btn-success btn-md" href="{{ URL::route(MyHelper::returnRoute($scope, 'add'), ['id' => $company_id]) }}">
                    Add new card
                </a>
                @if(!Session::has('company_id'))
                    <a class="btn btn-success btn-md" href="{{ URL::route(MyHelper::returnRoute('transactions', 'list'), ['company' => $company->company_id]) }}">
                        Go To Transactions
                    </a>
                @endif
            </div>
        </div>
        <div class="row">
            @include("admin/common/message")
            <div class="col-lg-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed table-bordered table-hover" id="dataTables-example">
                                <thead>
                                <tr>
                                    <th width="20%">Billing Full Name</th>
                                    <th width="20%">Billing Email</th>
                                    <th width="10%">Card</th>
                                    <th width="10%">Status</th>
                                    <th width="20%">Expiration Date</th>
                                    <th width="25%">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if (count($items))
                                    @foreach ($items as $item)
                                        <tr class="{{ $item->active == 0 ? 'danger' : '' }}">
                                            <td>{{ ucfirst($item->full_name) }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->card_type_name . ' - ' . $item->last_four }}</td>
                                            <td>{{ $item->default == 1 ?  'Default' : ' ' }}
                                            </td>
                                            <td>{{ $item->expiration_date }}</td>
                                            <td>
                                                <a href="{{ route('billing/default', ['cid' => $item->company_id, 'pid' => $item->id]) }}" class="btn btn-keep-original" style="margin-bottom: 5px;" {{ $item->default == 1 ?  'disabled' : ' ' }}>{{ $item->default == 1 ?  'Default' : ' Make Default ' }}</a>
                                            <a data-url="{{ route('billing.edit.detail', ['billing_id' => urlencode($item->id)]) }}" data-billing_name="{{ $item->full_name }}" data-billing_email="{{ $item->email }}" href="#" class="btn btn-keep-original edit-billing" data-toggle="modal" style="margin-bottom: 5px;" data-target="#edit-billing-modal">Edit</a>
                                                <a href="{{ URL::route(MyHelper::returnRoute($scope, 'delete'), ['id' => $item->id]) }}" class="confirm" data-confirm="Are you sure you want to delete this payment method?">
                                                    <button class="btn btn-keep-original" style="margin-bottom: 5px;"><i class="icon-remove icon-white"></i> Delete
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Pay Now</h4>
                </div>
                <form id="paymentform" method="POST" >
                    <div class="modal-body">
                        <label name="Amount">Amount</label>
                        <input type="text" name="amount" id="amount" />
                        <br />
                        <label name="description">Description</label>
                        <textarea name="description"> Description </textarea>
                        <br />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" name="submit" value="Pay" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('admin.billing.error_modal')
    @include('admin.billing.edit_billing_modal_new')
@stop

{{-- Scripts --}}
@section('scripts')

    <script type="text/javascript">

        $(document).ready(function () {
            $(".confirm").on("click", function () {
                return confirm($(this).data("confirm"));
            });

            var payUrl = "/admin/billing/pay/";

            $(".paynow").click(function(){
                var payUrlFinal = payUrl + ($(this).attr('data-id'));
                $("#paymentform").attr('action', encodeURI(payUrlFinal));
            })

            @if(Session::get('error'))
                $('#error-modal').modal('show');
            @endif
        });

    </script>

    <!-- PAGE LEVEL SCRIPTS -->
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            var otable = $('#dataTables-example').DataTable({
                "order": [[ 0, "asc" ]],
                "oLanguage": {
                    "sEmptyTable": "There are no credit cards added yet."
                }
            });

            $('.edit-billing').on('click', function() {
                var url = $(this).data('url');
                var billing_name = $(this).data('billing_name');
                var billing_email= $(this).data('billing_email');
                $('#billing_name').val(billing_name);
                $('#billing_email').val(billing_email);
                $('#edit-billing-form').attr('action', url);
                $(".requesStatusCustomCard").html('').hide();
            });

            $('#btn-edit-billing').on('click', function(e) {
                e.preventDefault();
                var billing_name = $('#billing_name').val();
                var billing_email= $('#billing_email').val();
                var token = $('input[name=_token]').val();
                var url = $('#edit-billing-form').attr('action');
                $(".requesStatusCustomCard").html('Sending Request..').show();
                axios.post(url, {
                    _token: token,
                    full_name: billing_name,
                    email: billing_email
                })
                .then(function (response) {
                    $(".requesStatusCustomCard").removeClass('text-danger').addClass('text-success').html(response.data.message).show();
                    setTimeout(() => {
                        $('#edit-billing-modal').modal('hide');
                        location.reload();
                    }, 800);
                })
                .catch(function (error) {
                    $(".requesStatusCustomCard").removeClass('text-success').addClass('text-danger').html('Something went wrong, try again!').show();
                });
            });
        });

        function ccOnFile(file){
            var file = file;
            var cc_on_file = file.value;
            var company_id = file.getAttribute("data-company_id").trim();

//            console.log(company_id);
            $.ajax({
                method: 'get',
                url: '{{ url('admin/company/updatecardonfilestatus') }}' + '/' + company_id,
                data: { 'cc_on_file' : cc_on_file },
                dataType: 'json',
                error: function (request, status, error) {
                    console.log(request.responseText);
                },
                success: function (data) {

                    if(data.event == 'success'){
                        console.log('Success');
                    }

                    if(data.event == 'failed'){
                        console.log('Failed');
                    }
                }
            });
        }
    </script>
    <!-- END PAGE LEVEL SCRIPTS -->





@stop
