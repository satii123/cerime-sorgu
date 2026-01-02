<?php
// --- ARKA PLAN (PHP) MOTORU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fin_sorgu'])) {
    header('Content-Type: application/json');
    $fin = strtoupper(trim($_POST['fin_sorgu']));
    
    // AsanPay API adresi
    $url = "https://www.asanpay.az/api/v1/public/check-penalty"; 
    $postData = json_encode(['fin' => $fin]);

    // Sunucu cURL kapalıysa alternatif yöntem (Stream Context)
    $options = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/json\r\n" .
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
                        "Referer: https://www.asanpay.az/\r\n" .
                        "Origin: https://www.asanpay.az\r\n",
            "content" => $postData,
            "ignore_errors" => true,
            "timeout" => 10
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === FALSE) {
        // Eğer bu da çalışmazsa sunucu dış bağlantılara tamamen kapalıdır
        echo json_encode(['error' => 'Sunucu xətası: Xarici bağlantı izni yoxdur.']);
    } else {
        echo $response;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cərimə Sorgulama | ASAN Pay</title>
    <style>
        /* --- MODERN DİZAYN (CSS) --- */
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { 
            background: #f0f2f5; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
        }
        .container { 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }
        h2 { color: #1c1e21; margin-bottom: 8px; }
        p { color: #606770; font-size: 14px; margin-bottom: 25px; }
        input { 
            width: 100%; 
            padding: 14px; 
            margin-bottom: 20px; 
            border: 1px solid #dddfe2; 
            border-radius: 8px; 
            font-size: 16px; 
            text-transform: uppercase; 
            text-align: center;
            outline: none;
        }
        input:focus { border-color: #1877f2; box-shadow: 0 0 0 2px #e7f3ff; }
        button { 
            width: 100%; 
            padding: 14px; 
            background: #1877f2; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 16px; 
            transition: 0.2s; 
        }
        button:hover { background: #166fe5; }
        button:disabled { background: #e4e6eb; color: #bcc0c4; cursor: not-allowed; }
        #res { 
            margin-top: 20px; 
            padding: 15px; 
            border-radius: 10px; 
            display: none; 
            font-size: 15px; 
            text-align: left;
            border: 1px solid #ddd;
        }
        .success { background: #e7f3ff; color: #1877f2; border-color: #1877f2 !important; }
        .error { background: #ffebe8; color: #f02849; border-color: #f02849 !important; }
    </style>
</head>
<body>

<div class="container">
    <h2>🚔 Cərimə Sorgula</h2>
    <p>FIN kodunuzu daxil edərək borcunuzu yoxlayın</p>
    
    <input type="text" id="finBox" placeholder="Məsələn: 7ABC123" maxlength="7">
    <button id="btnSorgula" onclick="sorgula()">YOXLA</button>

    <div id="res"></div>
</div>

<script>
/* --- İŞLEM (JAVASCRIPT) --- */
async function sorgula() {
    const fin = document.getElementById('finBox').value.trim();
    const btn = document.getElementById('btnSorgula');
    const resDiv = document.getElementById('res');

    if(fin.length !== 7) {
        alert("Zəhmət olmasa 7 simvollu FIN kodu daxil edin.");
        return;
    }

    // Ekranı hazırla
    btn.disabled = true;
    btn.innerText = "YOXLANILIR...";
    resDiv.style.display = "block";
    resDiv.className = "";
    resDiv.innerHTML = "Məlumat alınır, lütfən gözləyin...";

    const fd = new FormData();
    fd.append('fin_sorgu', fin);

    try {
        const response = await fetch('', { method: 'POST', body: fd });
        const data = await response.json();

        if(data.totalAmount !== undefined) {
            resDiv.classList.add("success");
            resDiv.innerHTML = `
                <b>Nəticə Tapıldı:</b><br>
                💰 Ümumi Borc: <b>${data.totalAmount} AZN</b><br>
                📑 Cərimə Sayı: <b>${data.count} ədəd</b>
            `;
        } else {
            resDiv.classList.add("error");
            resDiv.innerHTML = "❌ " + (data.error || "Məlumat tapılmadı. FIN kodu düzgün olmayabilir.");
        }
    } catch(e) {
        resDiv.classList.add("error");
        resDiv.innerHTML = "⚠️ Sunucu bağlantısı uğursuz oldu. Hosting firmanız xarici saytlara girişi bloklamış ola bilər.";
    } finally {
        btn.disabled = false;
        btn.innerText = "YOXLA";
    }
}
</script>

</body>
</html>