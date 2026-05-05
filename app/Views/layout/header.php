<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumière PMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --gold: #C9A84C;
            --deep: #0A0A0F;
            --panel: #12121A;
            --border: #2A2A3A;
            --text: #E8E4DC;
            --muted: #7A7A8E;
        }
        body {
            background: #0f0f17;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
        }
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: var(--panel);
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0; left: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .brand {
            font-family: 'Cormorant Garamond', serif;
            color: var(--gold);
            font-size: 22px;
            letter-spacing: 4px;
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
        }
        .nav-link {
            color: var(--muted) !important;
            padding: 10px 20px;
            font-size: 13px;
            letter-spacing: 1px;
            transition: all .2s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--gold) !important;
            background: rgba(201,168,76,.06);
        }
        .nav-link i {
            margin-right: 10px;
            width: 16px;
        }
        .nav-section {
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--border);
            padding: 16px 20px 6px;
        }
        .topbar {
            background: var(--panel);
            border-bottom: 1px solid var(--border);
            padding: 14px 30px;
            margin-left: 250px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 4px;
        }
        .card-title {
            font-family: 'Cormorant Garamond', serif;
            color: var(--gold);
            font-size: 18px;
        }
        .badge-gold {
            background: rgba(201,168,76,.15);
            color: var(--gold);
            border: 1px solid rgba(201,168,76,.3);
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 2px;
        }
    </style>
</head>
<body>