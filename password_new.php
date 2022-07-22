<?php
	include 'includes/session.php';

	if(!isset($_GET['code']) OR !isset($_GET['user'])){
		header('location: index.php');
	    exit(); 
	}

	$path = 'password_reset.php?code='.$_GET['code'].'&user='.$_GET['user'];

	if(isset($_POST['reset'])){
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];

		if(strlen($password) < 8){
			$_SESSION['error'] = "La clave debe tener al menos 8 caracteres";
			header('location: '.$path);
		   return false;
		}
		if(strlen($password) > 20){
			$_SESSION['error'] = "La clave no puede tener más de 20 caracteres";
			header('location: '.$path);
		   return false;
		}
		if (!preg_match('`[a-z]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos una letra minúscula";
			header('location: '.$path);
		   return false;
		}
		if (!preg_match('`[A-Z]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos una letra mayúscula";
			header('location: '.$path);
		   return false;
		}
		if (!preg_match('`[0-9]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos un caracter numérico";
			header('location: '.$path);
		    return false;
		}
		if (!preg_match('`[!"#$%&()*+,-./:;<=>?@]`',$password)){
			$_SESSION['error'] = "La clave debe tener al menos un simbolo especial";
			header('location: '.$path);
		    return false;
		}
		if($password != $repassword){
			$_SESSION['error'] = 'Las contraseñas no coinciden';
			header('location: '.$path);
		}
		else{
			$conn = $pdo->open();

			$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE reset_code=:code AND id=:id");
			$stmt->execute(['code'=>$_GET['code'], 'id'=>$_GET['user']]);
			$row = $stmt->fetch();

			if($row['numrows'] > 0){
				$password = password_hash($password, PASSWORD_DEFAULT);

				try{
					$stmt = $conn->prepare("UPDATE users SET password=:password WHERE id=:id");
					$stmt->execute(['password'=>$password, 'id'=>$row['id']]);

					$_SESSION['success'] = 'La contraseña se restableció correctamente';
					header('location: login.php');
				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
					header('location: '.$path);
				}
			}
			else{
				$_SESSION['error'] = 'El código no coincide con el usuario';
				header('location: '.$path);
			}

			$pdo->close();
		}

	}
	else{
		$_SESSION['error'] = 'Ingrese la nueva contraseña primero';
		header('location: '.$path);
	}

?>
