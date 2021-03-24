<script>
    $(document).ready(function () {
        var date = new Date();
        var currentMonth = date.getMonth() - 10;
        var currentDate = date.getDate();
        var currentYear = date.getFullYear() - 10;

        $('#from').datepicker({
            minDate: new Date(currentYear, currentMonth, currentDate),
            dateFormat: 'yy-mm-dd',

        });
    });
</script>
<script>
    $(document).ready(function () {
        var date = new Date();
        var currentMonth = date.getMonth() - 10;
        var currentDate = date.getDate();
        var currentYear = date.getFullYear() - 10;

        $('#to').datepicker({
            minDate: new Date(currentYear, currentMonth, currentDate),
            dateFormat: 'yy-mm-dd',

        });
    });
</script>
<script language="javascript">
    function printpage()
    {
        window.print();
    }
</script>
<?php
// include('includes/functions.php');
$function=new functions();
?>
<!-- Main row -->

<div class="row">
    <!-- Left col -->
    <div class="col-xs-12">
        <?php if($permissions['reports']['read']==0){?>
            <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view reports</div>
        <?php exit(); } ?>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Invoice Report</h3>
                <div class="box-tools">
                    <form method="post" action="invoices.php" name="form1">
                        <div class="input-group" style="width: 400px;">
                            <input type="text" id="from" name="start_date" placeholder="YYYY/MM/DD" required/>
                            To
                            <input type="text" id="to" name="end_date" placeholder="YYYY/MM/DD" required/> &nbsp; <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>


                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_POST) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $function = new functions;
    $month = $_POST['start_date'];
    $daysago = $_POST['end_date'];
    // create array variable to store data from database
    $data = array();

    if (isset($_GET['keyword'])) {
        // check value of keyword variable
        $keyword = $_GET['keyword'];
    } else {
        $keyword = "";
    }

    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date< '" . $daysago . "' and invoice_date >'" . $month . "'
				ORDER BY id DESC";
    } else {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date< '" . $daysago . "' and invoice_date >'" . $month . "'
				AND name LIKE '%".$keyword."%' 
				ORDER BY id DESC";
    }
         // Execute query
         $db->sql($sql_query);
         // store result 
         $res=$db->getResult();
        // get total records
        $total_records = $db->numRows();
    // check page parameter
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    // number of data that will be display per page
    $offset = 20;

    //lets calculate the LIMIT for SQL, and save it $from
    if ($page) {
        $from = ($page * $offset) - $offset;
    } else {
        //if nothing was given in page request, lets load the first page
        $from = 0;
    }
    $month = $_POST['start_date'];
    $daysago = $_POST['end_date'];
    $sql_daily = "SELECT SUM(payment) as num FROM invoice  WHERE invoice_date< '" . $daysago . "' and invoice_date >'" . $month . "'";
    $db->sql($sql_daily);
    $total_daily = $db->getResult();
    $total_daily = $total_daily[0]['num'];
    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date < '" . $daysago . "' and invoice_date >'" . $month . "'
				ORDER BY id DESC 
				LIMIT ".$from.",".$offset."";
    } else {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
                FROM invoice WHERE invoice_date < '" . $daysago . "' and invoice_date >'" . $month . "'
				AND  name LIKE '%".$keyword."%' 
				ORDER BY id DESC 
				LIMIT ".$from.",".$offset."";
    }
    
       
        // Execute query
        $db->sql($sql_query);
        $res=$db->getResult();
        // print_r($res);
        // for paging purpose
        $total_records_paging = $total_records;
    // if no data on database show "Tidak Ada Pemesanan"
    if ($total_records_paging == 0) {
        ?>
        <section class="content-header">
            <h1>There is No Records for this date</h1>
            <ol class="breadcrumb">
                <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
            </ol>
            <hr />
        </section>
        <?php
        // otherwise, show data
    } else {
        $row_number = $from + 1;
        ?>
        <section class="content-header">
            <h1>Records List</h1>
            <ol class="breadcrumb">
                <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
            </ol>
            <hr/>
        </section>

        <!-- search form -->
        <section class="content">
            <!-- Main row -->

            <div class="row">
                <!-- Left col -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Records /</h3>
                            <h3 class="box-title">Total Sale Rs: <?php echo $total_daily; ?></h3>
                            <div class="box-tools">
                                <form  method="get">
                                    <div class="input-group" style="width: 150px;">

                                        <input type="text" name="keyword" class="form-control input-sm pull-right" placeholder="Search">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body table-responsive">
                            <table class="table table-hover">
                                <tr>
                                    <th>Invoice No.</th>
                                    <th>Invoice_Date</th>
                                    <th>Order_ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Order Date</th>
                                    <th>Phone</th>
                                    <th>Order List</th>
                                    <th>Email</th>
                                    <th>Discount</th>
                                  
                                    <th>Total Amount</th>
                                      <th>Shipping Charge</th>
                                    <th>Paid Amount</th>

                                </tr>
                                <?php
                                // get all data using while loop
                                $count = 1;
                                // print_r($res);
                                foreach($res as $row){?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['invoice_date']; ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['address']; ?> </td>
                                        <td><?php echo $row['order_date']; ?></td>
                                        <td><?php echo $row['phone_number']; ?></td>
                                        <td><?php
                                            // echo $data['items'];
                                            $items = json_decode($row['order_list']);
                                            foreach ($items as $item) {
                                                echo "<b>Product Code : </b>" . $item[0];
                                                echo " <b>Name : </b>" . $item[1];
                                                echo " <b>Quantity : </b>" . $item[2];
                                                echo " <b>Sub Total : </b>" . $item[3] . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['discount'] ?></td>
                                         <td><?php echo $row['total_sale']; ?></td>
                                        <td><?php echo $row['shipping_charge']; ?></td>
                                        <td><?php echo $row['payment']; ?></td>
                                    </tr>
                                    <?php
                                    $count++;
                               } 
                            }
                            ?>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-sx-12">
                <h4>
                    <?php
                    // for pagination purpose
                    $function->doPages($offset, 'sales-report.php', '', $total_records, $keyword);
                    ?>
                </h4>
            </div>
            <div class="separator"> </div>
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
        </div><!-- /.row (main row) -->

    </section><!-- /.content --> 
    <?php
    $db->disconnect();
} else {
    $function = new functions;
    // create array variable to store data from database

    if (isset($_GET['keyword'])) {
        // check value of keyword variable
        $keyword = $_GET['keyword'];
    } else {
        $keyword = "";
    }

    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date > DATE_SUB(NOW(), INTERVAL 1 MONTH) 
				ORDER BY id DESC";
    } else {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date > DATE_SUB(NOW(), INTERVAL 1 MONTH) 
				AND name LIKE '%".$keyword."%' 
				ORDER BY id DESC";
    }
        // Execute query
        $db->sql($sql_query);
        // store result 
        $res=$db->getResult();

        // get total records
        $total_records = $db->numRows();
    // check page parameter
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    // number of data that will be display per page
    $offset = 20;

    //lets calculate the LIMIT for SQL, and save it $from
    if ($page) {
        $from = ($page * $offset) - $offset;
    } else {
        //if nothing was given in page request, lets load the first page
        $from = 0;
    }
    $sql_daily = "SELECT SUM(payment) as num FROM invoice WHERE invoice_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $db->sql($sql_daily);
    $total_daily = $db->getResult();
    $total_daily = $total_daily[0]['num'];
    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)
				ORDER BY id DESC 
				LIMIT ".$from.", ".$offset."";
    } else {
        $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
                FROM invoice WHERE invoice_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)
				AND  name LIKE '%".$keyword."%' 
				ORDER BY id DESC 
				LIMIT ".$from.", ".$offset."";
    }       
         // Execute query
         $db->sql($sql_query);
         // store result 
         $res=$db->getResult();
        // for paging purpose
        $total_records_paging = $total_records;
    // if no data on database show "Tidak Ada Pemesanan"
    if ($total_records_paging == 0) {
        ?>
        <section class="content-header">
            <h1>There is No Records</h1>
            <ol class="breadcrumb">
                <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
            </ol>
            <hr />
        </section>
        <?php
        // otherwise, show data
    } else {
        $row_number = $from + 1;
        ?>
        <section class="content-header">
            <h1>Records List</h1>
            <ol class="breadcrumb">
                <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
            </ol>
            <hr/>
        </section>

        <!-- search form -->
        <section class="content">
            <!-- Main row -->

            <div class="row">
                <!-- Left col -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Records /</h3>
                            <h3 class="box-title">Total Sale Rs: <?php echo $total_daily; ?></h3>
                            <div class="box-tools">
                                <form  method="get">
                                    <div class="input-group" style="width: 150px;">

                                        <input type="text" name="keyword" class="form-control input-sm pull-right" placeholder="Search">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body table-responsive">
                            <table class="table table-hover">
                                <tr>
                                    <th>Invoice No.</th>
                                    <th>Invoice_Date</th>
                                    <th>Order_ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Order Date</th>
                                    <th>Phone</th>
                                    <th>Order List</th>
                                    <th>Email</th>
                                    <th>Discount</th>
                                    <th>Total Amount</th>
                                    <th>Shipping Charge</th>
                                    <th>Paid Amount</th>
                                    <th>Action</th>
                                </tr>
                                <?php
                                // get all data using while loop
                                $count = 1;
                                foreach ($res as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['invoice_date']; ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['address']; ?> </td>
                                        <td><?php echo $row['order_date']; ?></td>
                                        <td><?php echo $row['phone_number']; ?></td>
                                        <td><?php
                                            // echo $data['items'];
                                            $items = json_decode($row['order_list']);
                                            foreach ($items as $item) {
                                                echo "<b>Product Code : </b>" . $item[0];
                                                echo " <b>Name : </b>" . $item[1];
                                                echo " <b>Quantity : </b>" . $item[2];
                                                echo " <b>Sub Total : </b>" . $item[3] . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['discount']; ?></td>
                                        <td><?php echo $row['total_sale']; ?></td>
                                        <td><?php echo $row['shipping_charge']; ?></td>
                                        <td><?php echo $row['payment']; ?></td>
                                        <td style=""><a href="order-detail.php?id=<?php echo $row['order_id']; ?>"><i class="fa fa-eye"></i>View Order</a>
				                        <br><a href="invoice.php?id=<?php echo $row['order_id']; ?>"><i class="fa fa-eye"></i>View Invoice</a></td>
                                    </tr>
                                    <?php
                                    $count++;
                                }
                            }
                            ?>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-sx-12">
                <h4>
                    <?php
                    // for pagination purpose
                    $function->doPages($offset, 'sales-report.php', '', $total_records, $keyword);
                    ?>
                </h4>
            </div>
            <div class="separator"> </div>
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
        </div><!-- /.row (main row) -->

    </section><!-- /.content --> 
    <?php
    $db->disconnect();
}
?>
