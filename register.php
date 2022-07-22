<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	//require 'PHPMailer/Exception.php';
	//require 'PHPMailer/PHPMailer.php';
	//require 'PHPMailer/SMTP.php';

	include 'includes/session.php';

	if(isset($_POST['signup'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];

		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;

		/*
		if(!isset($_SESSION['captcha'])){
			require('recaptcha/src/autoload.php');		
			$recaptcha = new \ReCaptcha\ReCaptcha('6LevO1IUAAAAAFCCiOHERRXjh3VrHa5oywciMKcw', new \ReCaptcha\RequestMethod\SocketPost());
			$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

			if (!$resp->isSuccess()){
		  		$_SESSION['error'] = 'Por favor conteste recaptcha correctamente';
		  		header('location: signup.php');	
		  		exit();	
		  	}	
		  	else{
		  		$_SESSION['captcha'] = time() + (10*60);
		  	}

		}
		*/
		if(strlen($password) < 8){
			$_SESSION['error'] = "La clave debe tener al menos 8 caracteres";
			header('location: signup.php');
		   return false;
		}
		if(strlen($password) > 20){
			$_SESSION['error'] = "La clave no puede tener más de 20 caracteres";
			header('location: signup.php');
		   return false;
		}
		if (!preg_match('`[a-z]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos una letra minúscula";
			header('location: signup.php');
		   return false;
		}
		if (!preg_match('`[A-Z]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos una letra mayúscula";
			header('location: signup.php');
		   return false;
		}
		if (!preg_match('`[0-9]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos un caracter numérico";
			header('location: signup.php');
		    return false;
		}
		if (!preg_match('`[!"#$%&()*+,-./:;<=>?@]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos un simbolo especial";
			header('location: signup.php');
		    return false;
		}
		if($password != $repassword){
			$_SESSION['error'] = 'Las contraseñas no coinciden';
			header('location: signup.php');
			return false;
		}
		else{
			$conn = $pdo->open();

			$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
			$stmt->execute(['email'=>$email]);
			$row = $stmt->fetch();
			if($row['numrows'] > 0){
				$_SESSION['error'] = 'Correo electrónico ya tomado';
				header('location: signup.php');
			}
			else{
				$now = date('Y-m-d');
				$password = password_hash($password, PASSWORD_DEFAULT);

				//generate code
				$set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$code=substr(str_shuffle($set), 0, 12);

				try{
					$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, activate_code, created_on) VALUES (:email, :password, :firstname, :lastname, :code, :now)");
					$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'code'=>$code, 'now'=>$now]);
					$userid = $conn->lastInsertId();

					$message = "
						<h2>Gracias por registrarte.</h2>
						<p>Su cuenta:</p>
						<p>Correo electrónico: ".$email."</p>
						<p>Contraseña: ".$_POST['password']."</p>
						<p>Haga clic en el enlace a continuación para activar su cuenta.</p>
						<a href='http://localhost/tienda_online/activate.php?code=".$code."&user=".$userid."'>Activar la cuenta</a>
					";

					//Load phpmailer
		    		require 'vendor/autoload.php';

		    		$mail = new PHPMailer(true);                             
				    try {
				        //Server settings
				        $mail->isSMTP();                                     
				        $mail->Host = 'smtp.gmail.com';                      
				        $mail->SMTPAuth = true;                               
				        $mail->Username = 'inc.jena@gmail.com';     
				        $mail->Password = 'tjquuxchkohjcbno';                    
				        $mail->SMTPOptions = array(
				            'ssl' => array(
				            'verify_peer' => false,
				            'verify_peer_name' => false,
				            'allow_self_signed' => true
				            )
				        );                         
				        $mail->SMTPSecure = 'ssl';                           
				        $mail->Port = 465;                                   

				        $mail->setFrom('inc.jena@gmail.com');
				        
				        //Recipients
				        $mail->addAddress($email);              
				        $mail->addReplyTo('inc.jena@gmail.com');
				       
				        //Content
				        $mail->isHTML(true);                                  
				        $mail->Subject = 'Verificacion de Acceso a la Cuenta';
				        $mail->Body    = $message;

				        $mail->send();

				        unset($_SESSION['firstname']);
				        unset($_SESSION['lastname']);
				        unset($_SESSION['email']);

				        $_SESSION['success'] = 'Cuenta creada. Revise su correo electrónico para activar.';
				        header('location: signup.php');

				    } 
				    catch (Exception $e) {
				        $_SESSION['error'] = 'El mensaje no pudo ser enviado. Error de correo: '.$mail->ErrorInfo;
				        header('location: signup.php');
				    }


				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
					header('location: register.php');
				}

				$pdo->close();

			}

		}

	}
	else{
		$_SESSION['error'] = 'Rellene el formulario de registro primero';
		header('location: signup.php');
	}

?>
