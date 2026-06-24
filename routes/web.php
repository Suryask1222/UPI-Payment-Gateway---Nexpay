<?php

$router = new Router();

$router->add('GET', '/', 'AuthController', 'loginView');

$router->add('GET', '/pay', 'PaymentController', 'pay');
$router->add('GET', '/pay/success', 'PaymentController', 'success');
$router->add('GET', '/pay/status', 'PaymentController', 'status');
$router->add('POST', '/api/submit-utr', 'PaymentController', 'submitUtr');
$router->add('POST', '/pay/direct-initiate', 'PaymentController', 'initiateDirectPay');

$router->add('GET', '/admin/login', 'AuthController', 'loginView');
$router->add('POST', '/admin/login', 'AuthController', 'login');
$router->add('GET', '/admin/logout', 'AuthController', 'logout');

$router->add('GET', '/admin/dashboard', 'DashboardController', 'index');
$router->add('GET', '/admin/timeline', 'DashboardController', 'timeline');
$router->add('GET', '/admin/analytics', 'DashboardController', 'analytics');

$router->add('GET', '/admin/transactions', 'TransactionController', 'index');
$router->add('POST', '/admin/transactions/action', 'TransactionController', 'takeAction');

$router->add('GET', '/admin/payment-links', 'PaymentLinkController', 'index');
$router->add('POST', '/admin/payment-links/generate', 'PaymentLinkController', 'generate');
$router->add('POST', '/admin/payment-links/delete', 'PaymentLinkController', 'delete');

$router->add('GET', '/admin/upi', 'UpiController', 'index');
$router->add('POST', '/admin/upi/add', 'UpiController', 'add');
$router->add('POST', '/admin/upi/edit', 'UpiController', 'edit');
$router->add('POST', '/admin/upi/toggle', 'UpiController', 'toggle');
$router->add('POST', '/admin/upi/delete', 'UpiController', 'delete');
$router->add('POST', '/admin/upi/default', 'UpiController', 'setDefault');

return $router;
