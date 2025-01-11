<?php
// Database connection
$db = mysqli_connect("localhost", "root", "", "e_com");

// Function to get user IP address
function getUserIp(){
    switch (true) {
        case (!empty($_SERVER['HTTP_X_REAL_IP'])): return $_SERVER['HTTP_X_REAL_IP'];
        case (!empty($_SERVER['HTTP_CLIENT_IP'])): return $_SERVER['HTTP_CLIENT_IP'];
        case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])): return $_SERVER['HTTP_X_FORWARDED_FOR'];
        default: return $_SERVER['REMOTE_ADDR'];
    }
}

// Function to add product to cart
function addCart() {
    global $db;
    if (isset($_GET['add_cart'])) {
        $ip_add = getUserIp(); // Get the user's IP address
        $p_id = $_GET['add_cart']; // Get the product ID
        $product_qty = $_POST['product_qty']; // Get the product quantity
        $product_size = $_POST['product_size']; // Get the product size

        // Check if the product is already in the cart for the user
        $check_product = "SELECT * FROM cart WHERE ip_add = ? AND p_id = ?";
        $stmt = $db->prepare($check_product);
        $stmt->bind_param("si", $ip_add, $p_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('This product is already added in your cart')</script>";
            echo "<script>window.open('details.php?pro_id=$p_id', '_self')</script>";
        } else {
            // Add the product to the cart
            $query = "INSERT INTO cart (p_id, ip_add, qty, size) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bind_param("isis", $p_id, $ip_add, $product_qty, $product_size);
            $stmt->execute();
            echo "<script>window.open('details.php?pro_id=$p_id', '_self')</script>";
        }
    }
}

// Function to count the items in the cart
function item() {
    global $db;
    $ip_add = getUserIp(); // Get the user's IP address
    $get_items = "SELECT * FROM cart WHERE ip_add = ?";
    $stmt = $db->prepare($get_items);
    $stmt->bind_param("s", $ip_add);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = mysqli_num_rows($result);
    echo $count;
}

// Function to calculate the total price of items in the cart
function totalPrice() {
    global $db;
    $ip_add = getUserIp(); // Get the user's IP address
    $total = 0;

    $select_cart = "SELECT * FROM cart WHERE ip_add = ?";
    $stmt = $db->prepare($select_cart);
    $stmt->bind_param("s", $ip_add);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($record = mysqli_fetch_array($result)) {
        $pro_id = $record['p_id'];
        $pro_qty = $record['qty'];

        // Get the price of the product
        $get_price = "SELECT * FROM products WHERE product_id = ?";
        $stmt_price = $db->prepare($get_price);
        $stmt_price->bind_param("i", $pro_id);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();

        while ($row = mysqli_fetch_array($result_price)) {
            $sub_total = $row['product_price'] * $pro_qty;
            $total += $sub_total;
        }
    }

    echo $total;
}

// Function to get the latest products
function getPro() {
    global $db;
    $get_product = "SELECT * FROM products ORDER BY 1 DESC LIMIT 0,6";
    $run_products = mysqli_query($db, $get_product);
    while ($row_product = mysqli_fetch_array($run_products)) {
        $pro_id = $row_product['product_id'];
        $pro_title = $row_product['product_title'];
        $pro_price = $row_product['product_price'];
        $pro_img1 = $row_product['product_img1'];

        echo "<div class='col-md-4 col-sm-6 single'>
                <div class='product'>
                    <a href='details.php?pro_id=$pro_id'>
                        <img src='admin_area/product_images/$pro_img1' class='img-responsive' width='300' height='300'>
                    </a>
                    <h3><a href='details.php?pro_id=$pro_id'><span>$pro_title </span></a></h3>
                    <p class='price'>INR $pro_price</p>
                    <p class='buttons'>
                        <a href='details.php?pro_id=$pro_id' class='btn btn-default'>View Details</a>
                        <a href='details.php?pro_id=$pro_id' class='btn btn-primary'><i class='fa fa-shopping-cart'></i> Add to Cart</a>
                    </p>
                </div>
            </div>";
    }
}

// Function to get product categories
function getPCats() {
    global $db;
    $get_p_cats = "SELECT * FROM product_category";
    $run_p_cats = mysqli_query($db, $get_p_cats);
    while ($row_p_cats = mysqli_fetch_array($run_p_cats)) {
        $p_cat_id = $row_p_cats['p_cat_id'];
        $p_cat_title = $row_p_cats['p_cat_title'];
        echo "<li><a href='trimer.php?p_cat=$p_cat_id'>$p_cat_title</a></li>";
    }
}

// Function to get categories
function getCat() {
    global $db;
    $get_cat = "SELECT * FROM categories";
    $run_cat = mysqli_query($db, $get_cat);
    while ($row_cat = mysqli_fetch_array($run_cat)) {
        $cat_id = $row_cat['cat_id'];
        $cat_title = $row_cat['cat_title'];
        echo "<li><a href='trimer.php?cat_id=$cat_id'>$cat_title</a></li>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce - Cart</title>
    <link rel="stylesheet" href="path_to_your_styles.css">
</head>
<body>

    <!-- Your page content here -->

    <div class="container">
        <h2>Shopping Cart</h2>
        <p>Items in your cart: <?php item(); ?> </p>
        <p>Total Price: ₹<?php totalPrice(); ?></p>
        <a href="index.php" class="btn btn-default">Continue Shopping</a>
    </div>

    <!-- Optionally, display products -->
    <div class="products">
        <?php getPro(); ?>
    </div>

    <!-- Include your footer, scripts, etc. -->
    
</body>
</html>
