<?php
/*
 * includes/header.php
 * Top part of every page. Before including it, a page can set:
 *   $pageTitle  - text for the browser tab
 *   $bodyClass  - extra CSS class for <body> (login/register use 'auth-page')
 */
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?> | PEMS</title>
    <?php /* ?v=... changes whenever the file changes, so the browser never shows an old cached copy */ ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
</head>
<body class="<?php echo e($bodyClass); ?>">
<?php if (is_logged_in()) { require __DIR__ . '/navbar.php'; } ?>
<main class="container">
<?php foreach (get_flash() as $flash): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
<?php endforeach; ?>
