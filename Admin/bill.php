<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng</title>
    <link rel="stylesheet" href="./CSS/style.css?v=4.6">
</head>

<body>

    <?php
    require_once './root/checkLogin.php';
    ?>


    <?php
    if (!isset($connect)) {
        require_once './root/connect.php';
    }

    require_once './root/alert.php';

    if (isset($_GET['approve'])) {
        $id = $_GET['approve'];
        $status_query = "SELECT status FROM bill WHERE id = '$id'";
        $status_result = mysqli_query($connect,$status_query);
        $status_result_value = mysqli_fetch_array($status_result);
        if(mysqli_num_rows($status_result) == 0){
            header('location:./not_found.php');
        }else{
            if($status_result_value['status'] == 2 || $status_result_value['status'] ==3){
                phpAlert('Anh bạn à');
            }else{
                $update = "UPDATE bill SET status = 2 WHERE id = '$id'";
                mysqli_query($connect, $update);
                header("Refresh:0, location:bill.php");
            }
        }
    }
    if (isset($_GET['cancel'])) {
        $id = $_GET['cancel'];
        $bill_detail = "SELECT * FROM bill_detail WHERE bill_id = '$id'";
        $result_bill = mysqli_query($connect, $bill_detail);

        $status_query = "SELECT status FROM bill WHERE id = '$id'";
        $status_result = mysqli_query($connect,$status_query);
        $status_result_value = mysqli_fetch_array($status_result);

        if(mysqli_num_rows($status_result) == 0){
            header('location:./not_found.php');
        }else{
            if($status_result_value['status'] == 2 || $status_result_value['status'] ==3){
                phpAlert('Anh bạn à');
            }else{
                foreach ($result_bill as $key) {
                    $product_id = $key['product_id'];
                    $quantity = (int)$key['quantity'];
                    $get_product = "SELECT * FROM product WHERE id = '$product_id'";
                    $get_product_result = mysqli_query($connect, $get_product);
                    $get_product_array = mysqli_fetch_array($get_product_result);
                    $quantity_update = (int)$get_product_array['quantity'] + $quantity;
                    $sold_update = (int)$get_product_array['sold'] - $quantity;
                    $update_quantity = "UPDATE product SET quantity = $quantity_update , sold = $sold_update WHERE id = '$product_id'";
                    mysqli_query($connect, $update_quantity);
                    // echo $update_quantity;
                }
                $update = "UPDATE bill SET status = 3 WHERE id = '$id'";
                mysqli_query($connect, $update);
                header("Refresh:0,location:bill.php");
            }
        }
    }

    $page = 1;
    $search = "";

    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }

    $number_of_post_query = "SELECT count(*) FROM bill WHERE (customer_id = '$search') OR (id = '$search') OR (recipient_name like '%$search%')";
    if (isset($_GET['hide'])) {
        if ($_GET['hide'] == true) {
            $number_of_post_query = "SELECT count(*) FROM bill WHERE ((customer_id = '$search') OR (id = '$search') OR (recipient_name like '%$search%')) AND  ((status != 2) AND (status != 3)) ORDER BY id ";
        }
    }
    $post_array = mysqli_query($connect, $number_of_post_query);
    $result_array = mysqli_fetch_array($post_array);
    $number_of_post = $result_array['count(*)'];
    $number_post_per_page = 10;
    $number_of_page = ceil($number_of_post / $number_post_per_page);
    $number_of_skip_page = $number_post_per_page * ($page - 1);

    $querry = "SELECT * FROM bill WHERE (customer_id = '$search') OR (id = '$search') OR (recipient_name like '%$search%') ORDER BY id DESC LIMIT $number_post_per_page OFFSET $number_of_skip_page ";

    if (isset($_GET['hide'])) {
        if ($_GET['hide'] == true) {
            $querry = "SELECT * FROM bill WHERE ((customer_id = '$search') OR (id = '$search') OR (recipient_name like '%$search%')) AND  ((status != 2) AND (status != 3)) ORDER BY id DESC LIMIT $number_post_per_page OFFSET $number_of_skip_page ";
        }
    }
    $result = mysqli_query($connect, $querry);

    ?>

    <div class="grid-container">
        <div class="container-header">
            <?php require_once "./root/navbar.php"; ?>
        </div>
        <div class="container-menu">
            <?php require_once "./root/sidebar.php"; ?>
        </div>
        <div class="container-main">
            <h1 class="main-title">Danh sách đơn hàng</h1>

            <div class="add-new-item">

                <a class="link-button" href="?hide=true"><i class="fas fa-calendar-times"></i>Ẩn tất cả đơn đã hoàn thành</a>
                <a class="link-button exel" href="./root/export_exel.php?bill=true"><i class="fas fa-file-excel"></i>Xuất file exel</a>

                <table class="styled-table">
                    <thead>
                        <tr>

                            <th>ID</th>
                            <th>Tên người nhận</th>
                            <th>Sđt người nhận</th>
                            <th>Địa chỉ</th>
                            <th>Tình trạng đơn</th>
                            <th>Quản lí</th>

                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($result as $bill) { ?>
                            <tr>
                                <td><a href="./view-infomation/bill_detail.php?bill_detail=<?php echo $bill['id'] ?>"><?php echo $bill['id'] ?></a></td>
                                <td><a href="./view-infomation/bill_detail.php?bill_detail=<?php echo $bill['id'] ?>"><?php echo $bill['recipient_name'] ?></a></td>
                                <td><a href="./view-infomation/bill_detail.php?bill_detail=<?php echo $bill['id'] ?>"><?php echo $bill['customer_phone'] ?></a></td>
                                <td><a href="./view-infomation/bill_detail.php?bill_detail=<?php echo $bill['id'] ?>"><?php echo $bill['customer_address'] ?></a></td>
                                <td><a href="./view-infomation/bill_detail.php?bill_detail=<?php echo $bill['id'] ?>">
                                        <?php
                                        if ($bill['status'] == 1) {
                                            echo "Chờ duyệt đơn";
                                        } else if ($bill['status'] == 2) {
                                            echo "Đã duyệt đơn";
                                        } else if ($bill['status'] == 3) {
                                            echo "Đã hủy đơn";
                                        }
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    if ($bill['status'] == 1) {
                                        echo '<a class="link-button" href="?approve=' . $bill["id"] . '"><i class="fas fa-check"></i>Duyệt</a>';
                                        echo '<a class="link-button" href="?cancel=' . $bill["id"] . '"><i class="fas fa-times"></i></i>Hủy</a>';
                                    } else if ($bill['status'] == 2) {
                                        echo 'Đã duyệt';
                                    } else if ($bill['status'] == 3) {
                                        echo 'Đã hủy đơn';
                                    }
                                    ?>

                                </td>
                            </tr>


                        <?php } ?>
                        <?php
                        if (mysqli_num_rows($result) == 0) {
                            echo '<tr ><td colspan="6" style="text-align:center">Không có kết quả nào</td> </tr>';
                        }
                        ?>
                    </tbody>
                </table>

                <div class="page_number_list">
                    <?php for ($i = 1; $i <= $number_of_page; $i++) { ?>
                        <div class="page_number">
                            <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                                <?php echo $i; ?>
                            </a>
                        </div>

                    <?php } ?>
                </div>

            </div>

        </div>
        <div class="container-footer">
            <?php require_once "./root/footer.php"; ?>
        </div>
    </div>

</body>
<script src="./JS/validateform.js?v=2.3"></script>
<script src="./JS/selectOption.js?v=2"></script>
<script src="https://kit.fontawesome.com/cb1ae4cd96.js" crossorigin="anonymous"></script>

</html>