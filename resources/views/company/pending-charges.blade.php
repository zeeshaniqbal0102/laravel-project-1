@extends("admin.layouts." . $view)
@section('styles')
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">
    <style>
        body {
            background-color: #fff;
        }
        .inner {
            margin-top: 20px;
        }
        .panel-body {
            padding: 0px;
        }
        .invoice-title h2, .invoice-title h3 {
            display: inline-block;
        }

        .table > tbody > tr > .no-line {
            border-top: none;
        }

        .table > thead > tr > .no-line {
            border-bottom: none;
        }

        .table > tbody > tr > .thick-line {
            border-top: 2px solid;
        }
        .box header .icons {
            display: inline-block;
            float: none;
            position: absolute;
            left: 0;
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

        .btn-keep-original {
            background-color: #66cccc;
            background: -webkit-gradient( linear, left top, left bottom, color-stop(0.05, #66cccc), color-stop(1, #66cced));
            border-color: #5fc1c5;
            color: #fff;
            font-weight: bold;
            font-family: arial;
            font-size: 12px;
            padding: 3px 10px;
            margin-bottom: 4px;
        }
        .resend_hint {
            font-size: 12px;
            width: 200px;
            position: relative;
            top: 24px;
            padding: 8px;
            background: #ddd;
            border-radius: 8px;
            left: 200px;
        }
        .resend_hint span {
            color: #66cccc;
            font-weight: bold;
        }

        .timeUpdatedHere {
            margin-top: 36px;
        }

    </style>
@endsection
@section("content")
    <div class="inner">
        <div class="row">
            @include("admin/common/message")
            <div class="col-md-12">
                <div class="page-title text-center">
                    <h2>Pending Charges and Credits</h2>
                    <div class="page-sub-title">
                        here are all of the pending charges for products you have purchased that have not been added to an invoice. this also shows any credit and discounts that you have received that haven't been invoiced.
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed table-bordered table-hover" id="dataTables">
                                <thead>
                                <tr>
                                    <td class="text-left"><strong>Date</strong></td>
                                    <td class="text-left"><strong>Description</strong></td>
                                    <td class="text-right"><strong>Amount</strong></td>
                                </tr>
                                </thead>
                                <tbody>
                                @if($uninvoiced_balance)
                                    <tr>
                                        <td></td>
                                        <td ><a href="{{ route('company/summary', ['id' => $company->id, 'request_from' => 'company']) }}">Uninvoiced eCards</a> Balance
                                        </td>
                                        <td class="text-right">{{ $uninvoiced_balance }}</td>
                                    </tr>
                                @endif
                                @if($adjustments->isNotEmpty())
                                    @foreach($adjustments as $adjustment)
                                        <tr>
                                            <td>{{ MyHelper::getContainedString(date('n/j/y', strtotime($adjustment->updated_at))) }}</td>
                                            <td >{{ $adjustment->description }}
                                            </td>
                                            <td class="text-right">$ {{ MyHelper::getMoneyFormatter($adjustment->amount) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                @if ($total > 0)
                                    <tr>
                                        <td></td>
                                        <td>Total Balance</td>
                                        <td class="text-right">$ {{ MyHelper::getMoneyFormatter($total) }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@stop