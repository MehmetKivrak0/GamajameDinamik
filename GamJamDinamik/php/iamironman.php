<?php
session_start();
if($_SERVER["REQUEST_METHOD"]=="POST"){
   
try {
    $bağlanti=mysqli_connect("localhost","root","","gamjam");
    if(!$bağlanti){
        die("Bağlantı hatası: " . mysqli_connect_error());
    }

    $emaıl=filter_var($_POST["email"],FILTER_SANITIZE_EMAIL);
    $sifre=trim($_POST["sifre"]);

    if(!filter_var($emaıl,FILTER_VALIDATE_EMAIL)){
        throw new Exception("Geçersiz e-posta adresi");
    }
    // E-posta adresinin rakamla başlamamasını kontrol et
    if (!preg_match('/^[a-zA-Z]/', $emaıl)) {
            throw new Exception("E-posta adresi bir harf ile başlamalıdır!");
    }

    $sql="SELECT id, username, emaıl, password, rol FROM users WHERE emaıl= ?";
    $stmt=mysqli_prepare($bağlanti,$sql);
    if(!$stmt){
        throw new Exception("Sorgu hazırlama hatası: " . mysqli_error($bağlanti));
    }
    mysqli_stmt_bind_param($stmt, "s", $emaıl);
    if(!mysqli_stmt_execute($stmt)){
        throw new Exception("Sorgu çalıştırma hatası: " . mysqli_stmt_error($stmt));
    }
    $sonuc=mysqli_stmt_get_result($stmt);

    if($kullanici=mysqli_fetch_assoc($sonuc)){
        if(!is_null($kullanici['password']) && password_verify($sifre, $kullanici['password'])){
            $_SESSION["kullanici_emaıl"] = $kullanici["emaıl"];
            $_SESSION["sifre"] = $kullanici["password"];
            $_SESSION["user_id"] = $kullanici["id"];
            $_SESSION["username"] = $kullanici["username"];
            error_log("Login - User ID: " . $kullanici["id"] . ", Username: " . $kullanici["username"]);

            switch($kullanici['rol']){
                case 'Admin':
                    header("Location: ../rolweb/admin.php");
                    break;
                case 'Kullanıcı':
                    header("Location: ../rolweb/user.html");
                    break;
                default:
                    throw new Exception("Tanımlanmamış kullanıcı rolü!");
            }
            exit();
        } else {
            throw new Exception("Hatalı şifre! Lütfen tekrar deneyiniz.");
        }
    } else {
        throw new Exception("Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı!");
    }     

    mysqli_stmt_close($stmt);
    mysqli_close($bağlanti);

} catch (Exception $e) {
    $hata_mesaji = $e->getMessage();
}
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTGET - Giriş Yap</title>
    <link rel="stylesheet" href="../WebSayfası/styles/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="../WebSayfası/resim/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: 'Montserrat', sans-serif;
        }

        .login-wrapper {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('../WebSayfası/resim/rg6.png') center/cover no-repeat;
            position: relative;
        }

        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.6) 100%);
        }

        .login-container {
            position: relative;
            width: 850px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            overflow: hidden;
        }

        .login-image {
            flex: 1;
            background: url('../WebSayfası/resim/logo.png') center/contain no-repeat;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            color: #fff;
            text-align: center;
        }

        .login-form-container {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            color: #c9d011;
            font-family: 'Press Start 2P', system-ui;
            font-size: 1.5rem;
            margin-bottom: 40px;
            text-align: center;
            text-shadow: 0 0 10px rgba(201, 208, 17, 0.3);
        }

        .form-group {
            position: relative;
            margin-bottom: 30px;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid rgba(201, 208, 17, 0.3);
            border-radius: 10px;
            color: #c9d011;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.8);
            border-color: #c9d011;
            box-shadow: 0 0 20px rgba(201, 208, 17, 0.2);
            outline: none;
        }

        .form-group {
            position: relative;
            margin-bottom: 30px;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c9d011;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .form-group:focus-within i {
            color: #ffff00;
        }

        .form-control::placeholder {
            color: rgba(201, 208, 17, 0.7);
            font-weight: 400;
        }

        .form-control:hover {
            border-color: rgba(201, 208, 17, 0.7);
        }

        .btn-login {
            background: linear-gradient(45deg, #c9d011, #dde226);
            border: none;
            padding: 15px;
            border-radius: 10px;
            color: #000;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(201, 208, 17, 0.4);
        }

        .error-message {
            background: rgba(220, 53, 69, 0.2);
            border-left: 4px solid #dc3545;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            color: #dc3545;
            font-size: 20px;
        }

        .form-footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-footer a {
            color: #c9d011;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-footer a:hover {
            color: #ffff00;
            text-shadow: 0 0 10px rgba(201, 208, 17, 0.5);
        }

        @media (max-width: 992px) {
            .login-container {
                width: 90%;
                max-width: 500px;
                height: auto;
                flex-direction: column;
            }

            .login-image {
                height: 200px;
                padding: 20px;
            }

            .login-form-container {
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .login-container {
                width: 95%;
            }

            .login-title {
                font-size: 1.2rem;
            }

            .form-control {
                padding: 12px 12px 12px 40px;
            }
        }

        .animated-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(201, 208, 17, 0.1) 0%, 
                rgba(0, 0, 0, 0) 50%, 
                rgba(201, 208, 17, 0.1) 100%);
            background-size: 200% 200%;
            animation: gradient 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="animated-background"></div>
            <div class="login-image"></div>
            <div class="login-form-container">
                <h1 class="login-title">OTGET Giriş</h1>
                
                <?php if(isset($hata_mesaji)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($hata_mesaji); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="E-posta Adresi" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="sifre" class="form-control" placeholder="Şifre" required>
                    </div>
                    <button type="submit" class="btn btn-login">
                        Giriş Yap <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <div class="form-footer">
                    Hesabınız yok mu? <a href="kayıtsayfa.php">Hemen Kayıt Ol</a>
                </div>
                <a href="../WebSayfası/index.html" class="btn-home">
                    <i class="fas fa-home"></i> Anasayfaya Dön
                </a>
            </div>
        </div>
    </div>

    <style>
        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid rgba(201, 208, 17, 0.3);
            color: #c9d011;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: rgba(0, 0, 0, 0.8);
            border-color: #c9d011;
            color: #ffff00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(201, 208, 17, 0.2);
        }

        .btn-home i {
            font-size: 18px;
        }

        @media (max-width: 576px) {
            .btn-home {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>

    <!-- jQuery önce yüklenmeli -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Sonra Popper.js (Bootstrap için gerekli) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <!-- En son Bootstrap -->
    <script src="../WebSayfası/assets/bootstrap.min.js"></script>
</body>
</html>