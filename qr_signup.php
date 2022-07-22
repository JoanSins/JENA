<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
<div class="login-box">
  	<div class="login-box-body">
	    <h3 class="login-box-msg"><center><b>¡Escanea!</b></center></h3>
    	<form action="" method="">
			<?php
			require 'phpqrcode/qrlib.php';
			
			$dir = 'temp/';
			
			if(!file_exists($dir))
			mkdir($dir);
			
			$filename = $dir.'test.png';
			$tamanio = 5;
			$level = 'H';
			$frameSize = 1;
			$contenido = 'https://uptex-vsc2.com/jena/signup.php';
			
			QRcode::png($contenido, $filename, $level, $tamanio, $frameSize);
			
			echo '<center><img src="'.$filename.'" /></center';
			?>
    	</form>
      <br>
      <center>
        <a href="signup.php"><i class="fa fa-ban"></i> Cancelar</a><br>
        <a href="index.php"><i class="fa fa-home"></i> Inicio</a>
      </center>
  	</div>
</div>
	
<?php include 'includes/scripts.php' ?>
</body>
</html>