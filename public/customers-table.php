<section class="content-header">
    <h1>Customers List</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr/>
</section>
<!-- search form -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <?php if($permissions['customers']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Customers</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" 
						data-url="api-firebase/get-bootstrap-table-data.php?table=users"
						data-page-list="[5, 10, 20, 50, 100, 200]"
						data-show-refresh="true" data-show-columns="true"
						data-side-pagination="server" data-pagination="true"
						data-search="true" data-trim-on-search="false"
						data-filter-control="true" data-filter-show-clear="true"
						data-sort-name="id" data-sort-order="desc">
					<thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="email" data-sortable="true">Email</th>
                            <th data-field="mobile" data-sortable="true">Mobile No</th>
                            <th data-field="balance" data-sortable="true">Balance</th>
                            <th data-field="street" data-sortable="true">Street</th>
                            <th data-field="area" data-sortable="true" >Area</th>
                            <th data-field="city" data-sortable="true" data-filter-control="select">City</th>
                            <th data-field="status" data-sortable="true">Status</th>
                            <th data-field="created_at" data-sortable="true">Date & Time</th>
                        </tr>
					</thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view customers</div>
        <?php } ?>
            <!-- /.box -->
        </div>
    </div>
    <!-- /.row (main row) -->
</section>
<!-- /.content -->