<?php
// Veritabanı bağlantısı ve diğer konfigürasyonlar burada...

// Site URL yapılandırması
function getSiteURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $baseDir = substr($scriptName, 0, strpos($scriptName, '/php'));
    return $protocol . $host . $baseDir;
}

// Sayfa yönlendirme fonksiyonu
function redirect($path) {
    $url = getSiteURL() . '/' . ltrim($path, '/');
    header("Location: $url");
    exit();
}

// Sabit tanımlamaları
define('SITE_URL', getSiteURL());

define('DB_HOST', 'localhost');
define('DB_NAME', 'gamjam');
define('DB_USER', 'root');
define('DB_PASS', '');

// PDO ayarları
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Hata modunu istisna yap
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ,// FETCH_ASSOC =>Veriyi associative array olarak döner (anahtar sütun adı)
  // Veri çekmede diziyi tercih et
    PDO::ATTR_EMULATE_PREPARES => false, // Gerçek prepared statement kullan
];

try {
    // PDO bağlantısını oluştur
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    error_log(
        "Veritabanı bağlantı hatası: " . $e->getMessage() .
        " (Dosya: " . $e->getFile() . ", Satır: " . $e->getLine() . ")",
        0
    );
    die("Sitemizde şu anda teknik bir sorun yaşanmaktadır. Lütfen daha sonra tekrar deneyin veya yöneticilerle iletişime geçin.");
}
?>