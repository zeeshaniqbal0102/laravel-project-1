<a href="#" data-toggle="modal" data-target="#request_custom_card">
    <div class="inner request_custom_card_section">
        <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="page-title">
                                <h2>want a custom eCard created? click here</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</a>
<div id="request_custom_card" class="modal fade" tabindex="1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Request custom card designs</h4>
            </div>
            <form id="request_custom_card_form" action="" method="post">
                <div class="modal-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input name="name" type="text" class="form-control" value="{{ auth()->user() ? auth()->user()->name : '' }}" id="name" placeholder="Name">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input name="email" type="email" value="{{ auth()->user() ? auth()->user()->email : '' }}" class="form-control" id="email" placeholder="Email">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input name="phone" type="text" class="form-control" id="phone" placeholder="Phone">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea name="comment" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <span class="requesStatusCustomCard"> Sending request... </span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-md">Request</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->