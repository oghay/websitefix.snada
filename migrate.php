<?php
  /**
   * Database Migration Script
   * Run this once to update existing database schema.
   * DELETE this file after running it.
   */

  if (!file_exists(__DIR__ . '/config.php')) {
      die('Config not found. Please run install.php first.');
  }

  require_once __DIR__ . '/includes/db.php';
  require_once __DIR__ . '/includes/functions.php';

  $results = [];

  // Migration 1: Add price column to orders
  try {
      $check = fetch("SHOW COLUMNS FROM orders LIKE 'price'");
      if (!$check) {
          getDB()->exec("ALTER TABLE orders ADD COLUMN price BIGINT DEFAULT 0 AFTER notes");
          $results[] = ['type' => 'ok', 'msg' => 'Kolom price berhasil ditambahkan ke tabel orders.'];
      } else {
          $results[] = ['type' => 'skip', 'msg' => 'Kolom price sudah ada di tabel orders.'];
      }
  } catch (Exception $e) {
      $results[] = ['type' => 'error', 'msg' => 'Gagal menambahkan kolom price: ' . $e->getMessage()];
  }

  // Migration 2: Add category column to blog_posts
  try {
      $check = fetch("SHOW COLUMNS FROM blog_posts LIKE 'category'");
      if (!$check) {
          getDB()->exec("ALTER TABLE blog_posts ADD COLUMN category VARCHAR(50) DEFAULT '' AFTER status");
          $results[] = ['type' => 'ok', 'msg' => 'Kolom category berhasil ditambahkan ke tabel blog_posts.'];
      } else {
          $results[] = ['type' => 'skip', 'msg' => 'Kolom category sudah ada di tabel blog_posts.'];
      }
  } catch (Exception $e) {
      $results[] = ['type' => 'error', 'msg' => 'Gagal menambahkan kolom category: ' . $e->getMessage()];
  }

  ?>
  <!DOCTYPE html>
  <html lang="id">
  <head>
      <meta charset="UTF-8">
      <title>Database Migration</title>
      <style>
          body { font-family: sans-serif; max-width: 700px; margin: 60px auto; padding: 0 20px; }
          h1 { color: #1a365d; }
          .ok    { color: #059669; }
          .skip  { color: #6b7280; }
          .error { color: #dc2626; font-weight: bold; }
          .warn  { background: #fef9c3; border: 1px solid #fbbf24; padding: 12px 16px; border-radius: 8px; margin-top: 24px; }
      </style>
  </head>
  <body>
      <h1>OJS Developer &mdash; Database Migration</h1>
      <ul>
          <?php foreach ($results as $r): ?>
          <li class="<?= htmlspecialchars($r['type']) ?>"><?= htmlspecialchars($r['msg']) ?></li>
          <?php endforeach; ?>
      </ul>
      <div class="warn">
          <strong>Penting:</strong> Setelah migrasi berhasil, hapus file <code>migrate.php</code> dari server untuk keamanan.
      </div>
  </body>
  </html>
  