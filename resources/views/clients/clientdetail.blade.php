<div class="detail-container1">
    <h4 style="float: left;width: 98%;background-color: #ececec;padding: 7px;margin-bottom: 25px;">Basic Detail</h4>
    <div class="labeldata">Name</div><div class="resultdata"><?php echo $model->name; ?></div>
    <div class="labeldata">Company Name</div><div class="resultdata"><?php echo $model->company_name; ?></div>
    <div class="labeldata">Phone Number</div><div class="resultdata"><?php echo $model->phone_number; ?></div>
    <div class="labeldata">Address</div><div class="resultdata"><?php echo $model->company_address; ?></div>

    <div class="labeldata">Email</div><div class="resultdata"><?php echo $model->email; ?></div>
    <div class="labeldata">Status</div><div class="resultdata"><?php echo $model->status == 1 ? 'Active' : 'Inactive'; ?></div>
</div>