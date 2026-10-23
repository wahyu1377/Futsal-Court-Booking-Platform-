<?php /* variabel opsional: $pageTitle, $useTailwind */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Arena Sport') ?> - Arena Sport</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link href="../../assets/css/app.css" rel="stylesheet">
  <?php if (!empty($useTailwind)): ?>
  <!-- Tailwind CSS untuk utility class. Preflight dimatikan agar tidak menimpa style Bootstrap. -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      corePlugins: { preflight: false },
      theme: {
        extend: {
          colors: {
            primary: '#006a4a', 'primary-dark': '#005239', mint: '#8cf7c4',
            ink: '#0d1b2e', muted: '#5f6b7a', field: '#eef2ff',
            bg: '#f6f8ff', line: '#e3e8f5'
          }
        }
      }
    };
  </script>
  <?php endif; ?>
</head>
<body>