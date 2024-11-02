<?php
session_start();

// Engellenen ülkeler
$blockedCountries = ["RU", "TR"]; // ISO 3166-1 alpha-2 formatında ülke kodları

// Kullanıcının IP adresini api.ipify.org ile al
$userIP = null;
$ipApiURL = "https://api.ipify.org?format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ipApiURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 saniye zaman aşımı

$response = curl_exec($ch);
$ipData = json_decode($response);
$userIP = $ipData->ip ?? null; // IP adresini ayarla

curl_close($ch);

// Ülke bilgisini almak için ipinfo.io API'sini kullan
$countryCode = null;
if ($userIP) {
    $countryApiURL = "https://ipinfo.io/{$userIP}/json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $countryApiURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $countryData = json_decode($response);
    $countryCode = $countryData->country ?? null; // Ülke kodunu ayarla

    curl_close($ch);
}

// Eğer ülke engellenen ülkeler listesinde ise erişimi durdur
if (in_array($countryCode, $blockedCountries)) {
    header("HTTP/1.1 403 Forbidden");
    echo "Erişiminiz engellenmiştir.";
    exit();
}

// İstek Hızını Kontrol Etme ve Diğer Güvenlik Kontrolleri (örneğin hız sınırlaması)
$timeWindow = 60; // 60 saniye
$rateLimit = 10; // 60 saniyede 10 istek

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

$_SESSION['requests'] = array_filter($_SESSION['requests'], function($timestamp) use ($timeWindow) {
    return (time() - $timestamp) < $timeWindow;
});

if (count($_SESSION['requests']) >= $rateLimit) {
    header("HTTP/1.1 429 Too Many Requests");
    echo "Çok fazla istek gönderildi. Lütfen bir süre sonra tekrar deneyin.";
    exit();
}

$_SESSION['requests'][] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doğrulama Sayfası</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Güvenlik Doğrulaması</h1>
    <p>Bot olmadığınızı kanıtlamak için doğrulama işlemini tamamlayın.</p>
    <p>Ülke Kodu: <?php echo htmlspecialchars($countryCode); ?></p>
    <p>IP Adresiniz: <?php echo htmlspecialchars($userIP); ?></p> <!-- IP adresini burada gösteriyoruz -->
    <div id="loadingMessage">Doğrulama başlıyor...</div>
    <div id="captchaContainer" style="display: none;">
    </div>
  </div>
</body>
</html>
