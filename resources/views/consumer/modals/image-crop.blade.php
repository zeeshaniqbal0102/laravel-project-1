<div id="image-preview-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="crop-image-modal-title" class="text-center">Crop &amp; save your image</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img id="preview-image" src="" alt="" style="width:100%; height: 100%;">
                <div class="btn-group mt-1 ml-3">
                    <button id="zoom-in" type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="" data-original-title="$().cropper(&quot;zoom&quot;, 0.1)">
                            <span class="fa fa-search-plus"></span>
                        </span>
                    </button>
                    <button id="zoom-out" type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="" data-original-title="$().cropper(&quot;zoom&quot;, -0.1)">
                            <span class="fa fa-search-minus"></span>
                        </span>
                    </button>
                </div>
                <div class="btn-group pull-right mt-1 mr-3">
                    <button id="rotate-left" type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="" data-original-title="$().cropper(&quot;rotate&quot;, -45)">
                            <span class="fa fa-undo"></span>
                        </span>
                    </button>
                    <button id="rotate-right" type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
                        <span class="docs-tooltip" data-toggle="tooltip" data-animation="false" title="" data-original-title="$().cropper(&quot;rotate&quot;, 45)">
                            <span class="fa fa-repeat"></span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                <button type="button" id="btn-save-image" class="btn btn-primary">Use this image</button>
            </div>
        </div>
    </div>
</div>