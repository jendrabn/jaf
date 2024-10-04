<div class="modal fade"
     id="modal-filter"
     tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Filter Order
                </h5>
                <button aria-label="Close"
                        class="close"
                        data-dismiss="modal"
                        type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-row"
                      id="form-filter">
                    <div class="form-group col-md-6">
                        <label for="date">Date</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-calendar"></i>
                                </span>
                            </div>
                            <input class="form-control date-range"
                                   name="daterange"
                                   type="text">
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="payment_method">Payment Method</label>
                        <select class="custom-select"
                                id="payment_method"
                                name="payment_method">
                            <option value="">All</option>
                            <option value="bank">Bank</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary"
                        data-dismiss="modal"
                        type="button">
                    <i class="fa-solid fa-xmark"></i> Close
                </button>
                <button class="btn btn-default"
                        id="btn-reset-filter"
                        type="button">
                    <i class="fa-solid fa-rotate"></i> Reset
                </button>
                <button class="btn btn-primary"
                        id="btn-filter"
                        type="button">
                    <i class="fa-solid fa-filter"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
