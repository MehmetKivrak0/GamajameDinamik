<?php
session_start();

// Sayfa yenilendiğinde hataları ve hata mesajlarını temizle
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($hatalar);
    unset($error_message);
}

function validEmailDomain($email) {
    $invalidDomains = [
        [
    // Gmail Varyasyonları
    'gmail.com',        // Doğru Gmail
    'gmil.com',
    'gmial.com',
    'gmai.com',
    'gmal.com',
    'gmailcom',         // Eksik nokta
    'www.gmail.com',    // Gereksiz www
    'mail.gmail.com',   // Alt alan adı (eğer kabul etmek istemiyorsanız çıkarılabilir)
    'googlemail.com',   // Eski Google Mail varyasyonu
    'google.com',       // Hatalı genel Google alanı (kullanıcılar bazen karıştırır)

    // Hotmail Varyasyonları
    'hotmail.com',      // Doğru Hotmail
    'hotnail.com',      // n yerine m
    'hotmal.com',       // i eksik
    'hotmail.co.uk',    // İngiltere uzantısı (diğer ülke uzantıları da eklenebilir)
    'hotmail.fr',       // Fransa uzantısı
    'hotmail.de',       // Almanya uzantısı
    'hotmail.it',       // İtalya uzantısı
    'hotmail.es',       // İspanya uzantısı
    'hotmail.ca',       // Kanada uzantısı
    'hotmail.com.tr',   // Türkiye uzantısı (varsa ve kabul ediyorsanız)
    'hotrmail.com',     // Fazla r
    'htomail.com',      // Harf yer değişimi
    'hotmaill.com',     // Fazla l
    'homtail.com',      // Harf yer değişimi
    'hotmailcom',       // Eksik nokta
    'www.hotmail.com',  // Gereksiz www

    // Outlook Varyasyonları (Hotmail ile genellikle birlikte kullanılır)
    'outlook.com',      // Doğru Outlook
    'outlok.com',
    'outloook.com',
    'outlook.co.uk',
    'outlook.com.tr',
    'www.outlook.com',
    'live.com',         // Eski Live Mail
    'msn.com',          // Eski MSN Mail

    // Yahoo Varyasyonları
    'yahoo.com',        // Doğru Yahoo
    'yaho.com',
    'yhoo.com',
    'yahoomail.com',
    'yahoo.co.uk',
    'yahoo.com.tr',
    'www.yahoo.com',
    'mail.yahoo.com',

    // Diğer Popüler E-posta Sağlayıcıları (İsteğe bağlı olarak eklenebilir)
    'aol.com',          // AOL Mail
    'protonmail.com',   // Proton Mail
    'icloud.com',       // iCloud Mail
    'mail.com',         // Mail.com
    'yandex.com',       // Yandex Mail
    'web.de',           // Almanya'da popüler
    'gmx.de'            // Almanya'da popüler
    ]
    ];

    $validExtensions = [
        '.com',
        '.net',
        '.org',
        '.edu',
        '.gov',
        '.mil',
        '.edu.tr',
        '.com.tr',
        '.org.tr',
        '.net.tr'
    ];

    $parts = explode('@', $email);
    if (count($parts) != 2) return false;

    $domainPart = strtolower($parts[1]); // Alan adı kısmını al ve küçük harfe çevir

    // Geçersiz alan adlarını kontrol et
    if (in_array($domainPart, $invalidDomains)) {
        return false;
    }

    // Geçerli uzantıları kontrol et
    $domainExtension = substr($domainPart, strrpos($domainPart, '.'));
    return in_array($domainExtension, $validExtensions);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Veritabanı bağlantısı
    try {
        $bağlanti = mysqli_connect("localhost", "root", "", "gamjam");
        if (!$bağlanti) {
            throw new Exception("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
        }
        mysqli_set_charset($bağlanti, "utf8mb4");
    } catch (Exception $e) {
        $error_message = "Sistem hatası: Lütfen daha sonra tekrar deneyin.";
        error_log("Veritabanı Hatası: " . $e->getMessage());
        // Hata durumunda kodun devamının çalışmasını engelle
        goto form_display;
    }
    
    $hatalar = [];

    // Form verilerini al ve temizle
    //Form F5 ile yenilendiğinde form verileri temizleniyor
    $kullanici_adi = trim(htmlspecialchars($_POST['kullanici_adi'] ?? ''));
    $ad = trim(htmlspecialchars($_POST['ad'] ?? ''));
    $soyad = trim(htmlspecialchars($_POST['soyad'] ?? ''));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $sifre = $_POST['sifre'] ?? '';

    // Kullanıcı adı kontrolü
    if (strlen($kullanici_adi) < 3 || strlen($kullanici_adi) > 30 || !preg_match("/^[a-zA-Z0-9_]+$/", $kullanici_adi)) {
        $hatalar[] = "Kullanıcı adı en az 3, en fazla 30 karakter olmalı ve sadece harf, rakam ve alt çizgi içermelidir.";
    }
    if (preg_match('/\s/', $kullanici_adi)) {
        $hatalar[] = "Kullanıcı adı boşluk içermemelidir.";
    }
   if (preg_match('/^[0-9]/', $kullanici_adi)) {
    $hatalar[] = "Kullanıcı adı rakamla başlayamaz.";
}

    // Ad-Soyad kontrolü
    if (strlen($ad) < 2 || strlen($ad) > 30 || !preg_match("/^[a-zA-ZğüşıöçĞÜŞİÖÇ]+$/u", $ad)) {
        $hatalar[] = "Ad en az 2, en fazla 30 karakter olmalı ve sadece harf içermelidir.";
    }
    if (strlen($soyad) < 2 || strlen($soyad) > 30 || !preg_match("/^[a-zA-ZğüşıöçĞÜŞİÖÇ]+$/u", $soyad)) {
        $hatalar[] = "Soyad en az 2, en fazla 30 karakter olmalı ve sadece harf içermelidir.";
    }

    // Email kontrolü
    $sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) 
    {
        $hatalar[] = "Geçerli bir e-posta adresi girin.";
    } 
    else 
    {
        $at_pozisyonu = strpos($email, '@');
        if ($at_pozisyonu !== false) {
            $username = substr($email, 0, $at_pozisyonu);
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9.]*$/', $username)) {
                $hatalar[] = "E-posta adresi harf ile başlamalı ve sadece harf, rakam ve nokta içerebilir.";
            } elseif (strlen($username) < 3) {
                $hatalar[] = "E-posta adresinizin '@' işaretinden önceki kısmı en az 3 karakter olmalıdır.";
            } elseif (!validEmailDomain($email)) {
                $hatalar[] = "Geçersiz e-posta uzantısı. Sadece .com, .net vb. uzantılar kabul edilir.";
            }
        }
    }

    // Şifre kontrolü
    if (strlen($sifre) < 8 || strlen($sifre) > 20) {
        $hatalar[] = "Şifre en az 8, en fazla 20 karakter olmalıdır.";
    }
    if (!preg_match('/[A-Z]/', $sifre)) {
        $hatalar[] = "Şifre en az bir büyük harf içermelidir.";
    }
    if (!preg_match('/[a-z]/', $sifre)) {
        $hatalar[] = "Şifre en az bir küçük harf içermelidir.";
    }
    if (!preg_match('/[0-9]/', $sifre)) {
        $hatalar[] = "Şifre en az bir rakam içermelidir.";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $sifre)) {
        $hatalar[] = "Şifre en az bir özel karakter (. * + ? ^ $ [] () | \ {}) içermelidir.";
    }

    // Email format ve domain kontrolü
    if (!validEmailDomain($email)) {
        $hatalar[] = "Geçersiz e-posta adresi veya domain. Lütfen geçerli bir e-posta adresi girin.";
    }

    // Email ve kullanıcı adı benzersizlik kontrolü
    try {
        $kontrol_sql = "SELECT username, emaıl FROM users WHERE emaıl = ? OR username = ? LIMIT 1";
        $kontrol_stmt = mysqli_prepare($bağlanti, $kontrol_sql);
        
        if (!$kontrol_stmt) {
            throw new Exception("Sorgu hazırlama hatası: " . mysqli_error($bağlanti));
        }

        if (!mysqli_stmt_bind_param($kontrol_stmt, "ss", $email, $kullanici_adi)) {
            throw new Exception("Parametre bağlama hatası: " . mysqli_stmt_error($kontrol_stmt));
        }

        if (!mysqli_stmt_execute($kontrol_stmt)) {
            throw new Exception("Sorgu çalıştırma hatası: " . mysqli_stmt_error($kontrol_stmt));
        }

        $kontrol_sonuc = mysqli_stmt_get_result($kontrol_stmt);

        if ($kontrol_sonuc === false) {
            throw new Exception("Sonuç alma hatası: " . mysqli_error($bağlanti));
        }

        if ($row = mysqli_fetch_assoc($kontrol_sonuc)) {
            if (strcasecmp($row['emaıl'], $email) === 0) {
                $hatalar[] = "Bu e-posta adresi zaten kayıtlı.";
            }
            if (strcasecmp($row['username'], $kullanici_adi) === 0) {
                $hatalar[] = "Bu kullanıcı adı zaten kullanılıyor.";
            }
        }

    } catch (Exception $e) {
        error_log("Kullanıcı kontrolü hatası: " . $e->getMessage());
        $hatalar[] = "Sistem hatası oluştu. Lütfen daha sonra tekrar deneyin.";
    } finally {
        if (isset($kontrol_stmt)) {
            mysqli_stmt_close($kontrol_stmt);
        }
    }

    // Hata yoksa kayıt işlemini gerçekleştir
    if (empty($hatalar)) {
        $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, name, surname, emaıl, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($bağlanti, $sql);

        if (!$stmt) {
            $error_message = "Sistem hatası oluştu. Lütfen daha sonra tekrar deneyin.";
            error_log("SQL Hatası: Sorgu hazırlanamadı - " . mysqli_error($bağlanti));
        } else {
            mysqli_stmt_bind_param($stmt, "sssss", $kullanici_adi, $ad, $soyad, $email, $sifre_hash);

            if (mysqli_stmt_execute($stmt)) {
                $last_id = mysqli_insert_id($bağlanti);

                $_SESSION['user_id'] = $last_id;
                $_SESSION['username'] = $kullanici_adi;
                $_SESSION['name'] = $ad;
                $_SESSION['surname'] = $soyad;
                $_SESSION['email'] = $email;

                $success_message = "Kayıt başarıyla oluşturuldu! Yönlendiriliyorsunuz...";
                $_SESSION['redirect'] = true;
                ?>
                <script>
                    setTimeout(function() {
                        window.location.href = 'iamironman.php';
                    }, 2000);
                </script>
                <?php
                exit();
            } else {
                $error_message = "Kayıt işlemi başarısız oldu. Lütfen daha sonra tekrar deneyin.";
                error_log("SQL Hatası: Kayıt işlemi başarısız - " . mysqli_error($bağlanti));
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "error";
    }

    if (isset($bağlanti)) {
        mysqli_close($bağlanti);
    }
}

form_display:
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTGET GAME JAM 2025 - Kayıt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../WebSayfası/resim/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Genel Stil */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Arka Plan */
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-size: cover;
            background-position: center;
            filter: brightness(0.7);
            z-index: -1;
            opacity: 0.9;
        }

        /* Form Container */
        .form-container {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            margin: 2rem;
            border: 5px solid #c9d011;
        }

        /* Başlık */
        .form-container h2 {
            text-align: center;
            color: #c9d011;
            font-family: 'Press Start 2P', cursive;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            text-shadow: 0 0 15px rgba(201, 208, 17, 0.7);
        }

        /* Form Grupları */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #c9d011;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(201, 208, 17, 0.3);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: #fff;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #c9d011;
            outline: none;
            background: rgba(255, 255, 255, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Şifre Toggle */
        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #c9d011;
        }

        /* Butonlar */
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-kayit, .btn-geri {
            padding: 0.75rem 1.5rem;
            border: 2px solid #c9d011;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            flex: 1;
            min-width: 120px;
            background: rgba(0, 0, 0, 0.7);
            color: #c9d011;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.8rem;
        }

        .btn-kayit:hover, .btn-geri:hover {
            background: #c9d011;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 0 15px rgba(201, 208, 17, 0.7);
        }

        /* Alert Mesajları */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid #c9d011;
            color: #c9d011;
            animation: fadeIn 0.5s ease-in-out;
        }

        .alert-success, .alert-danger {
            text-shadow: 0 0 10px rgba(201, 208, 17, 0.5);
            padding: 15px;
            border: 2px solid #c9d011;
            box-shadow: 0 0 15px rgba(201, 208, 17, 0.3);
        }

        .alert .error-text {
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1rem;
            color: #c9d011;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .alert .error-item {
            font-size: 0.95rem;
            margin: 0.5rem 0;
            padding: 8px;
            background: rgba(201, 208, 17, 0.1);
            border-radius: 5px;
            color: #fff;
            animation: slideIn 0.3s ease-in-out;
            display: flex;
            align-items: center;
        }

        .alert .error-item::before {
            content: "•";
            color: #c9d011;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* İletişim Bilgisi */
        .contact-info {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-info a {
            color: #c9d011;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
            text-shadow: 0 0 10px rgba(201, 208, 17, 0.7);
        }

        /* Animasyonlar */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive Tasarım */
        @media (max-width: 480px) {
            .form-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .form-container h2 {
                font-size: 1.2rem;
            }

            .button-group {
                flex-direction: column;
            }

            .redirect-message {
                color: #c9d011;
                font-size: 0.9rem;
                margin-top: 0.5rem;
                font-style: italic;
                animation: pulse 1.5s infinite;
            }

            @keyframes pulse {
                0% {
                    opacity: 0.6;
                }
                50% {
                    opacity: 1;
                }
                100% {
                    opacity: 0.6;
                }
            }

            .btn-kayit, .btn-geri {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="background-image" style="background-image: url('../WebSayfası/resim/rg6.png');"></div>
        
        <div class="form-container">
            <h2>KAYIT OL</h2>
            <form method="POST">
                <?php if(!empty($hatalar)): ?>
                    <div class="alert alert-danger">
                        <div class="error-text">Lütfen aşağıdaki hataları düzeltin:</div>
                        <?php
                        foreach ($hatalar as $hata) {
                            echo '<div class="error-item">• ' . htmlspecialchars($hata) . '</div>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars("Kayıt başarıyla oluşturuldu!"); ?>
                        <div class="redirect-message">Ana sayfaya yönlendiriliyorsunuz...</div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" placeholder="Kullanıcı adınızı girin" required>
                </div>
                
                <div class="form-group">
                    <label for="ad" class="form-label">Ad</label>
                    <input type="text" class="form-control" id="ad" name="ad" placeholder="Adınızı girin" required>
                </div>
                
                <div class="form-group">
                    <label for="soyad" class="form-label">Soyad</label>
                    <input type="text" class="form-control" id="soyad" name="soyad" placeholder="Soyadınızı girin" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">E-posta</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="E-posta adresinizi girin" required>
                </div>
                
                <div class="form-group">
                    <label for="sifre" class="form-label">Şifre</label>
                    <div class="password-toggle">
                        <input type="password" class="form-control" id="sifre" name="sifre" placeholder="Şifrenizi girin" required>
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-kayit">KAYIT OL</button>
                    <a href="../WebSayfası/index.html" class="btn-geri" type="button">GERI DÖN</a>
                </div>

                <div class="contact-info">
                    <p>Sorun yaşarsanız bize ulaşın: <a href="mailto:info@otget.mcbu.edu.tr">info@otget.mcbu.edu.tr</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const sifreInput = document.getElementById('sifre');
            const type = sifreInput.getAttribute('type') === 'password' ? 'text' : 'password';
            sifreInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
