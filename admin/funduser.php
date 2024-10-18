<?php
include_once("./layout/header.php");
//require_once("./include/adminloginFunction.php");
//include_once("../include/config.php");

if (isset($_POST['credit'])) {
    // Credit transaction
    $trans_type = "credit";
    $subject_prefix = "CREDIT";
} elseif (isset($_POST['debit'])) {
    // Debit transaction
    $trans_type = "debit";
    $subject_prefix = "DEBIT";
}

$user_id = $_POST['user_id'];
$sender_name = $_POST['sender_name'];
$amount = $_POST['amount'];
$description = $_POST['description'];
$created_at = $_POST['created_at'];
$time_created = $_POST['time_created'];

// Fetch user info
$sql = "SELECT * FROM users WHERE id = :user_id";
$checkUser = $conn->prepare($sql);
$checkUser->execute(['user_id' => $user_id]);
$result = $checkUser->fetch(PDO::FETCH_ASSOC);

$user_balance = $result['acct_balance'];
$available_balance = ($trans_type == "credit") ? $user_balance + $amount : $user_balance - $amount;

// Set currency symbol
$currency_symbols = [
    'USD' => '$', 'EUR' => '€', 'WON' => '₩', 'CNY' => '¥', 'JPY' => '¥',
    'MYR' => 'RM', 'GBP' => '£', 'CAD' => '$', 'NOK' => 'kr', 'UAH' => '₴'
];
$currency = isset($currency_symbols[$result['acct_currency']]) ? $currency_symbols[$result['acct_currency']] : '';

// Update user balance
$sql = "UPDATE users SET acct_balance = :available_balance WHERE id = :user_id";
$addUp = $conn->prepare($sql);
$addUp->execute([
    'available_balance' => $available_balance,
    'user_id' => $user_id
]);

// Insert into transactions
$trans_id = uniqid();
$sql = "INSERT INTO transactions (user_id, sender_name, amount, description, created_at, time_created, trans_type, refrence_id, trans_status) 
        VALUES (:user_id, :sender_name, :amount, :description, :created_at, :time_created, :trans_type, :refrence_id, :trans_status)";
$stmt = $conn->prepare($sql);
$stmt->execute([
    'user_id' => $user_id,
    'sender_name' => $sender_name,
    'amount' => $amount,
    'description' => $description,
    'created_at' => $created_at,
    'time_created' => $time_created,
    'trans_type' => $trans_type == 'credit' ? 1 : 2,
    'refrence_id' => $trans_id,
    'trans_status' => 1
]);

// Prepare email
$APP_NAME = $pageTitle;
$email = $result['acct_email'];
$fullName = $result['firstname'] . " " . $result['lastname'];

// Call email message method
$message = $sendMail->FundUsers($fullName, $currency, $sender_name, $amount, $available_balance, $description, $created_at, $trans_type, $APP_NAME);

// Set the subject dynamically for both credit and debit
$subject = "[$subject_prefix NOTIFICATION] - $APP_NAME";

// Send email
$email_message->send_mail($email, $message, $subject);
$email_message->send_mail(WEB_EMAIL, $message, $subject);

// Send success/failure alerts
toast_alert('success', ucfirst($trans_type) . ' transaction successful.', 'Approved');

?>
<!--  BEGIN CONTENT AREA  -->
<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">

            <div id="basic" class="col-lg-12 layout-spacing">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <div class="row">
                            <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                <h4>Credit Users</h4>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area">

                        <div class="row">
                            <div class="col-lg-10 col-12 mx-auto">
                                <form method="post">


                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="">Users</label>

                                                <select  name="user_id" class="form-control  basic" required>
                                                    <option selected="selected">Select User</option>

                                                    <?php
                                                    $sql="select * from users order by id ASC";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->execute();

                                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                                        $fullName = $row['firstname']. " ".$row['lastname']

                                                    ?>
                                                    <option value="<?=$row['id']?>"><?= ucwords($fullName)?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="">From</label>
                                                <input value="" type="text" name="sender_name" class="form-control" id="" placeholder="From" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="">Amount <span class="fs-2">User Remaining Balance: <span class="text-black"><?php echo $user_balance; ?></span> </span> </label>
                                                <input value="" type="number" name="amount" class="form-control" id="" placeholder="Amount" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="">Decription</label>
                                                <textarea name="description" class="form-control" placeholder="Decription" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="">Date</label>
                                                <input value="" type="date" name="created_at" class="form-control" id="" placeholder="date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="">Time</label>
                                                <input type="time" name="time_created" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <div class="col-md-6 text-center">
                                            <button name="credit"  type="submit" class="btn btn-primary mt-3 col-md-12">Credit User</button>

                                        </div>
                                        <div class="col-md-6 text-center">
                                            <button name="debit"  type="submit" class="btn btn-danger mt-3 col-md-12">Debit User</button>

                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>


        <?php
include_once("./layout/footer.php");
?>
