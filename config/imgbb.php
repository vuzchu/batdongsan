<?php
/**
 * Upload anh len ImgBB (khong luu file cuc bo). API key cau hinh qua bien moi truong
 * IMGBB_API_KEY (xem .htaccess.example) - khong hard-code key that vao file nay vi day
 * la file duoc dua vao git.
 */

function uploadImageToImgbb(string $imageFilePath)
{
    $apiKey = getenv('IMGBB_API_KEY');
    if (!$apiKey) {
        return false;
    }

    $imageData = base64_encode(file_get_contents($imageFilePath));

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.imgbb.com/1/upload?key=' . $apiKey,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => ['image' => $imageData],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);
    if (!empty($result['success'])) {
        return $result['data']['url'];
    }
    return false;
}
