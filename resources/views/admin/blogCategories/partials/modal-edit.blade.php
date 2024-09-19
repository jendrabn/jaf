<div class="modal fade"
     id="modal-edit"
     tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLabel">Edit Blog Category</h5>
                <button aria-label="Close"
                        class="close"
                        data-dismiss="modal"
                        type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="required"
                               for="name">Name</label>
                        <input class="form-control"
                               name="name"
                               required
                               type="text">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary"
                            data-dismiss="modal"
                            type="button">Close</button>
                    <button class="btn btn-primary"
                            type="submit">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
