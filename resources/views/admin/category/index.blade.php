@extends("admin.layouts.layoutwithsugarwishdesignforadmin")

@section('title')
    {{{ 'Category List' }}} :: @parent
@stop

@section("content")
<style>
    .btn-sm {
        padding: 5px 8px;
    }
    .calendar.left {
        clear: left;
         margin-right: 34px !important;
    }

    .daterangepicker {
        position: fixed !important;
        top:50% !important;
        left:50% !important;
        transform: translate(-50%, -50%);
    }
</style>
    <script type="text/javascript">
        $('body').on('hidden.bs.modal', '.modal', function () {
            $(this).removeData('bs.modal');
        });
    </script>


    <!--script type="text/javascript" src="//cdn.jsdelivr.net/jquery/1/jquery.min.js"></script-->
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <!--link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap/latest/css/bootstrap.css" /-->

    <!-- Include Date Range Picker -->
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">



    <div class="inner">
        <div class="row">
            <div class="col-lg-12">
                <div class="col-lg-6">
                    <h3>Category List</h3>

                </div>
                <div class="col-lg-6">
                    <span class="pull-right">
                    <a href="{{ url('admin/category/add') }}">
                        <button type="button" class="btn btn-success btn-circle btn-lg"><i class="fa fa-plus"></i>
                        </button>
                    </a>
                </span>
                </div>

            </div>
        </div>

        <hr/>

        <div class="row">

            @include("admin/common/message")

            <div class="col-lg-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-condensed" id="{{ count($categories) > 0 ? 'dataTables-example': '' }}">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Healthy</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($categories))
                                        @foreach ($categories as $category)
                                            <tr>
                                                <td>{{ $category->name }}</td>
                                                <td>{{ !empty($category->description) ? str_limit($category->description, 30) : 'N/A' }}</td>
                                                <td><img src="{{ ($category->category_image) ? asset('/category/' . $category->category_image) : asset('/category/default.jpg') }}" width="100" height="100"/></td>
                                                <td>{{  MyHelper::getContainedString(date('m/d/Y', strtotime($category->start_date ))) }}</td>
                                                <td>{{  MyHelper::getContainedString(date('m/d/Y', strtotime($category->end_date ))) }}</td>
                                                <td>{{ $category->healthy == 1 ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    <a href="{{ URL::route($scope. '.edit', ['id' => $category->id]) }}"><button class="btn btn-sm btn-primary"> Edit </button></a>
                                                    <a href="{{ URL::route($scope. '.delete', ['id' => $category->id]) }}"><button class="btn btn-sm btn-danger"> Delete </button></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5">There are no Categories added.</td>
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



@stop

{{-- Scripts --}}
@section('scripts')

    <script type="text/javascript">
        $(document).ready(function () {
            $(".confirm").on("click", function () {
                return confirm($(this).data("confirm"));
            });
        });
    </script>

    <!-- PAGE LEVEL SCRIPTS -->
    <script src="{{asset('admin/plugins/dataTables/jquery.dataTables.js')}}"></script>
    <script src="{{asset('admin/plugins/dataTables/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('admin/js/typeahead/typeahead.bundle.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').DataTable({
//                "order": [[ 0, "asc" ]]
            });

            // typeahead
            // Instantiate the Bloodhound suggestion engine
            var company = new Bloodhound({
                datumTokenizer: function (datum) {
                    return Bloodhound.tokenizers.whitespace(datum.value);
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{{ url('admin/company/ajaxforcompanycontroller/%QUERY') }}',
                    wildcard: '%QUERY',
                    filter: function (company) {
                        // Map the remote source JSON array to a JavaScript object array
                        return $.map(company, function (company) {
                            return {
                                id: company.id,
                                value: company.name
                            };
                        });
                    }
                }
            });

            // Initialize the Bloodhound suggestion engine
            company.initialize();

            // Instantiate the Typeahead UI
            $('.typeahead').typeahead(null, {
                displayKey: 'value',
                source: company
            });

            // typeahead ends

            // Company Search Table Redraw
//            $('#search-form-company_submit').on('submit', function(e) {
//                oTable.draw();
//                e.preventDefault();
//            });
        });
    </script>
    <!-- END PAGE LEVEL SCRIPTS -->



@stop