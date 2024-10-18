<?php
require __DIR__. '/../include/vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;

// MESSAGE & EMAIL CONFIGURATION FOR SCRIPT
class message{
    private $conn;
    public function send_mail($email, $message, $subject){

        $mail = new PHPMailer();
        //SMTP Settings
        //$mail->isSMTP();
        $mail->isMail();
        $mail->Host = "mail.ukrgb.online"; // Change Email Host
        $mail->SMTPAuth = true;
        $mail->Username = "info@ukrgb.online"; // Change Email Address
        $mail->Password = '@SECukrgb1'; // Change Email Password
        $mail->Port = 587; //587
        $mail->SMTPSecure = "ssl"; //tls

        //Email Settings
        $mail->isHTML(true);
        $mail->setFrom('info@ukrgb.online','Ukraine Gas Bank'); // Change
        $mail->addAddress($email);
        $mail->AddReplyTo("info@ukrgb.online", "Ukraine Gas Bank"); // Change
        $mail->Subject = $subject;
        $mail->MsgHTML($message);
        $mail->Send();


    }

}
