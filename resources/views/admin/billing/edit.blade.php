@extends("admin.layouts.layoutwithsugarwishdesignforadmin")

@section('title')
    {{{ 'Update Company' }}} :: @parent
@stop

@section("content")



    <div class="inner">
        <div class="row">
            <div class="col-lg-12">
                <h1> Manage Companies
                    <span class="pull-right">
                        <a href="{{  URL::route(MyHelper::returnRoute($scope, 'list'))  }}">
                            <button type="button" class="btn btn-success btn-circle btn-lg"><i class="icon-list"></i>
                            </button>
                        </a>
                    </span>
                </h1>
            </div>
        </div>
        <hr/>
        <div class="row">

            @include("admin/common/message")

            <div class="col-lg-12">
                <div class="box">
                    <header>
                        <div class="icons"><i class="icon-th-large"></i></div>
                        <h5>Update Company</h5>

                        <div class="toolbar">
                            <ul class="nav">
                                <li>
                                    <div class="btn-group">
                                        <a class="accordion-toggle btn btn-xs minimize-box" data-toggle="collapse"
                                           href="#collapseOne">
                                            <i class="icon-chevron-up"></i>
                                        </a>
                                        <button class="btn btn-xs btn-danger close-box">
                                            <i class="icon-remove"></i>
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>

                    </header>
                    <div id="collapseOne" class="accordion-body collapse in body">


                        <?php
                        echo Form::open([
                                "url" => URL::route(MyHelper::returnRoute($scope, 'edit'), ['id' => $company->id]),
                                "autocomplete" => "off",
                                "id" => "resource-validate",
                                "class" => "form-horizontal",
                                'files' => true
                        ]);

                        ?>

                        <?php

                        echo Form::field([
                                "name" => "name",
                                "label" => "Company Name",
                                "form" => $form,
                                "placeholder" => "Company Name",
                                "id" => "name",
                                "value" => $company->name
                        ]);

                        echo Form::field([
                                "name" => "company_id",
                                "type" => "text",
                                "label" => "Company ID",
                                "form" => $form,
                                "placeholder" => "Company ID",
                                "id" => "company_id",
                                "value" => $company->company_id
                        ]);

                        echo Form::field([
                                "name" => "phone",
                                "label" => "Phone Number",
                                "form" => $form,
                                "placeholder" => "Phone Number",
                                "id" => "phone",
                                "value" => $company->phone
                        ]);
                        ?>

                        <div class="form-group">
                            <label for="name" class="control-label col-lg-4">Country</label>
                            <div class="col-lg-4">
                                <select name="country" class="crs-country" data-whitelist="US" data-region-id="state" data-default-value="<?php echo $company->country; ?>" placeholder="Country" data-value="shortcode"></select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="state" class="control-label col-lg-4">State</label>
                            <div class="col-lg-4">
                                <select id="state" name="state" data-value="shortcode" data-default-value="<?php echo $company->state; ?>">
                                </select>
                            </div>
                        </div>

                        <?php
                        echo Form::field([
                                "name" => "street",
                                "label" => "Street",
                                "form" => $form,
                                "placeholder" => "street",
                                "id" => "street",
                                "value" => $company->street
                        ]);

                        echo Form::field([
                                "name" => "city",
                                "label" => "City",
                                "form" => $form,
                                "placeholder" => "city",
                                "id" => "city",
                                "value" => $company->city
                        ]);



                        echo Form::field([
                                "type" => "number",
                                "name" => "zip",
                                "label" => "Zip",
                                "form" => $form,
                                "placeholder" => "Zip",
                                "id" => "zip",
                                "value" => $company->zip
                        ]);

                        echo Form::field([
                                "type" => 'file',
                                "name" => "image",
                                "label" => "Logo",
                                "form" => $form,
                                "placeholder" => "logo",
                                "id" => "image"
                        ]);

                        echo Form::field([
                                "name" => "discount_percent",
                                "label" => "Discount Percent",
                                "form" => $form,
                                "placeholder" => "eg: 10",
                                "id" => "discount_percent",
                                "value" => $company->discount_percent
                        ]);

                        $redeem1 = ($company->redeem_only == 1) ? 'true' : '';
                        $redeem2 = ($company->redeem_only == 0) ? 'true' : '';

                        $c1 = ($company->secure == 1) ? 'true' : '';
                        $c2 = ($company->secure == 0) ? 'true' : '';

                        echo Form::field([
                                "type" => "radio",
                                "name" => "redeem_only",
                                "label" => "Redeem Only",
                                "form" => $form,
                                "id" => "redeem_only",
                                "radios" => [
                                        "1" => [
                                                "value" => "1",
                                                "label" => "Yes",
                                                "checked" => $redeem1
                                        ],
                                        "2" => [
                                                "value" => "0",
                                                "label" => "No",
                                                "checked" => $redeem2
                                        ]
                                ],
                        ]);

                        echo Form::field([
                                "type" => "radio",
                                "name" => "secure",
                                "label" => "Secure",
                                "form" => $form,
                                "id" => "secure",
                                "radios" => [
                                        "1" => [
                                                "value" => "1",
                                                "label" => "Yes",
                                                "checked" => $c1
                                        ],
                                        "2" => [
                                                "value" => "0",
                                                "label" => "No",
                                                "checked" => $c2
                                        ]
                                ],
                        ]);
                        ?>

                        <div class="form-group">
                            <label for="image" class="control-label col-lg-4">Existing Image: </label>
                            <div class="col-lg-4">
                                <img src="{{ ($company->logo) ? asset('/company/' . $company->logo) : asset('/company/default.jpg') }}" class="img-rounded" alt="Logo" width="150" height="150" style="height: 150px; width: 150px; object-fit: contain;"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image" class="control-label col-lg-4">Created By: </label>
                            <div class="col-lg-4">
                                <p> {{ MyHelper::getContainedString($username) }} </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image" class="control-label col-lg-4">Created On: </label>
                            <div class="col-lg-4">
                                <p> {{ MyHelper::getContainedString(date('jS \o\f F, Y', strtotime($company->created_at ))) }} </p>
                            </div>
                        </div>

                        <div class="form-actions no-margin-bottom" style="text-align:center;">
                            <input type="submit" value="Update" class="btn btn-primary btn-lg "/>
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

    </div>


@stop
@section("footer")
    @parent
            <!--<script src="//polyfill.io"></script>-->
@stop


@section('scripts')

    <!-- PAGE LEVEL SCRIPTS -->

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <script src="{{ asset('admin/plugins/validationengine/js/jquery.validationEngine.js') }}"></script>
    <script src="{{ asset('admin/plugins/validationengine/js/languages/jquery.validationEngine-en.js') }}"></script>
    <script src="{{ asset('admin/plugins/jquery-validation-1.11.1/dist/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('admin/js/validationInit.js') }}"></script>
    <script>
        $(function () {
            formValidation();
        });
    </script>
    <!--END PAGE LEVEL SCRIPTS -->

    <script src="{{ asset('admin/js/js_scripts.js') }}"></script>
    <script src="{{ asset('admin/js/crs.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('input[type="radio"]').click(function () {
                if ($(this).attr("value") == "1") {
                    $("#assign_role_wrapper").show();
                }
                if ($(this).attr("value") !== "1") {
                    $("#assign_role_wrapper").hide();
                }
            });
        });
    </script>


@stop