<?php

if (!function_exists('clean')) {
    function clean($data) {
        return htmlspecialchars(strip_tags(trim($data ?? '')));
    }
}

if (!function_exists('h')) {
    function h($data) {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₹' . number_format((float)$amount, 2);
    }
}

if (!function_exists('sendUnifiedTemplate')) {
    function sendUnifiedTemplate($pdo, $email, $phone, $template, $data, $category) {
        error_log("PORTAL FALLBACK: sendUnifiedTemplate to $email | Template: $template | Data: " . json_encode($data));
        return true;
    }
}

if (!function_exists('creditReferralCommission')) {
    function creditReferralCommission($pdo, $userId, $type, $amount, $refId = null) {
        error_log("PORTAL FALLBACK: creditReferralCommission for user $userId | Type: $type | Amount: $amount");
        return true;
    }
}

if (!function_exists('autoGenerateCertificate')) {
    function autoGenerateCertificate($pdo, $internId, $flag, $reqId) {
        $certNo = 'CERT-' . date('Y') . strtoupper(bin2hex(random_bytes(3)));
        error_log("PORTAL FALLBACK: autoGenerateCertificate for intern $internId | Cert No: $certNo");
        return $certNo;
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($success, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success], $data));
        exit;
    }
}
