@extends('layouts.consumer-layout')
@section('content')
    <div class="row no-gutters">
        <div class="col-md-12 col-lg-8 mx-auto">
            <div class="col-lg-11 mx-auto px-md-3 px-lg-2">
                <h1 class="cstm-card-header text-center text-dark my-3 my-lg-4">

                </h1>
                <div class="step2 mb-3 mb-lg-4">
                    @include("admin/common/message")

                </div>
            </div>
            <form id="send-now-form" class="form-horizontal" action="" method="post">
                <div class="card border-0 rounded-0" style="background:none;">
                    <div class="card-body py-0 py-sm-4 text-center">
                        <?php $userType = session()->get('cart_user_type'); ?>
                        @if($userType == 'consumer')
                            <h2>Looks like you’ve saved a credit card on our system. Do you want to sign in to
                                access?</h2>
                        @elseif($userType == 'corporate')
                            <h2>Looks like you have a corporate account under this email address. Would you like to
                                sign-in?</h2>
                        @endif

                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="button-cstm-img">
                                    <div class="button-cstm-img">
                                        <button name="sign_in" class="outer-img-btn activity" type="submit" id="btn-signin" >
                                            <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                            <span>Yes: Sign-in</span>
                                        </button>
                                        <br>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="button-cstm-img">
                                    <div class="button-cstm-img">
                                        <button name="guest" class="outer-img-btn activity" type="submit" id="btn-guest" >
                                            <img src="{{ asset('images/default-btn-background.png') }}" class="img-fluid">
                                            <span>No: Continue as Guest</span>
                                        </button>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </div>
                </div>
            </form>

        </div>

    </div>
@endsection
@section('scripts')
    <script>
        var cartItems =  '{!!   json_encode($cartItems) !!}';
        
    </script>

@endsection
