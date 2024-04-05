<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the phone number from the POST request
    $phoneNumber = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';
    $withMessage = !isset($_POST['with_message']) || $_POST['with_message'] == 'true';
    $instantMode = isset($_POST['instant_mode']) && $_POST['instant_mode'] == 'true';
    $driverName = 'ShopeeFood Driver';
        $message = isset($_POST['message']) ? $_POST['message'] : "Apakah alamat yang tertera di aplikasi sudah benar?";
    if ($instantMode) {
        $driverName = 'Shopee Instant Driver';
        $receiverName = isset($_POST['receiver_name']) && $_POST['receiver_name'] != '' ? 'atas nama ' . ucwords($_POST['receiver_name']) : 'Shopee Instant';
        $message = "\nPesanan $receiverName sedang dalam perjalanan menuju lokasi Anda.".
            "\n\nApabila pembeli sedang tidak berada di lokasi, mohon untuk konfirmasi peletakan paket melalui chat ini.".
            "\nMohon ditunggu.";
    }
    if ($withMessage) {
        $templateMessage = "Halo, saya $driverName." .
            "\nSaya sedang mengantar pesanan Anda." .
            "\n$message" .
            "\n\nTerima kasih.";
        $message = urlencode($templateMessage);
    } else {
        $message = '';
    }

    if ($phoneNumber == '') {
        failResponse('Nomor telepon tidak boleh kosong');
    }

    // Menghapus karakter non-digit (spasi, tanda tambah)
    $phoneNumberCleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

    if (strlen($phoneNumberCleaned) < 7) {
        failResponse('Nomor telepon tidak valid');
    } else {
        // Memeriksa apakah nomor telepon dimulai dengan '+'
        if (strpos($phoneNumberCleaned, '+') === 0) {
            // Menghapus tanda '+' jika ada
            $phoneNumberCleaned = substr($phoneNumberCleaned, 1);
        }

        // Jika nomor telepon diawali dengan '08', ganti menjadi '628'
        if (strpos($phoneNumberCleaned, '0') === 0) {
            $phoneNumberCleaned = '62' . substr($phoneNumberCleaned, 1);
        } elseif (strpos($phoneNumberCleaned, '8') === 0) {
            $phoneNumberCleaned = "62$phoneNumberCleaned";
        }

        $result = [
            'send_wa' => "https://wa.me/$phoneNumberCleaned?text=$message",
            'phone_wa' => "https://wa.me/$phoneNumberCleaned",
            'with_message' => $withMessage
        ];
        successResponse($result);
    }
} else {
    // If the request method is not POST, return an error message
    http_response_code(404);
    errorResponse('Metode request tidak diizinkan');
}

function successResponse($data)
{

    // Set the response headers to indicate JSON content
    header('Content-Type: application/json');

    $response = [
        'status' => 'success',
        'data' => $data
    ];

    // Return the response as JSON
    echo json_encode($response);
}

function failResponse($message)
{
    // Set the response headers to indicate JSON content
    header('Content-Type: application/json');

    $response = [
        'status' => 'fail',
        'message' => $message
    ];

    // Return the response as JSON
    echo json_encode($response);
}

function errorResponse($message)
{
    // Set the response headers to indicate JSON content
    header('Content-Type: application/json');

    $response = [
        'status' => 'error',
        'message' => $message
    ];

    // Return the response as JSON
    echo json_encode($response);
}
