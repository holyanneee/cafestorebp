<?php 
    require __DIR__ . '/../config.php';
    require __DIR__ .'/../lib/PHPMailer/src/Exception.php';
    require __DIR__ .'/../lib/PHPMailer/src/PHPMailer.php';
    require __DIR__ .'/../lib/PHPMailer/src/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    

    function markUserAsVerified($email){
        global $conn;

        $update = $conn->prepare("UPDATE `users` SET email_verified_at = now() WHERE email = ?");
        $update->execute([$email]);
    }

    function sendVerificationEmail($email, $verification_code){
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = "yanadoms01@gmail.com";
            $mail->Password   = "thnvvhzprrzyxgsy";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom("yanadoms01@gmail.com", 'Kape Milagrosa');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - Kape Milagrosa';
            $mail->Body    = "Please click the link below to verify your email address:<br>
                              <a href='http://localhost/cafestorebp/verify.php?email=$email&code=$verification_code'>Verify Email</a>";

            $mail->send();
            return [
                'status' => 'success',
                'message' => 'Verification email sent.'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ];
        }
    }




?>