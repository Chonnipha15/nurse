<?php
// เริ่มต้น Session เพื่อรองรับการจัดการสิทธิ์หรือตะกร้าสินค้า
session_start();

// ตั้งค่า Environment และ Configuration
$supabaseUrl = getenv('SUPABASE_URL') ?: 'https://rpbyapwseypgzcuesnoi.supabase.co';
$supabaseAnonKey = getenv('SUPABASE_ANON_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJwYnlhcHdzZXlwZ3pjdWVzbm9pIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODkyODE0MjgsImV4cCI6MjEwNDg1NzQyOH0.nFcP1PYQSSwM8ZEoMJFiNEQC2JhmLBImOgk6i_rvlkI';

// ฟังก์ชันแปลงเงินบาทฝั่ง PHP
function formatMoney($amount) {
    return number_format($amount, 0, '.', ',') . ' บาท';
}

// 1. รายการสินค้า (Data Catalog) จัดการผ่าน Server-side
$catalog = [
    [
        'group' => 'รายชิ้น',
        'desc'  => 'ซื้อทีละเล่ม เลือกเฉพาะเรื่องที่ต้องการ',
        'items' => [
            ['id' => 'ekg', 'name' => 'การอ่าน EKG เบื้องต้น', 'meta' => 'คอร์สวิดีโอ', 'price' => 150, 'was' => null],
            [
                'id' => 'patho1',
                'name' => 'Pathology โรคที่พบบ่อยใน ER เล่ม 1',
                'meta' => 'เอกสารประกอบการเรียน · PDF',
                'price' => 250,
                'was' => null,
                'pdf_url' => '/files/BIOLOGY.pdf'
            ],
            ['id' => 'patho2', 'name' => 'Pathology โรค เล่ม 2', 'meta' => 'เอกสารประกอบการเรียน · PDF', 'price' => 250, 'was' => null],
            ['id' => 'ca', 'name' => 'Pathology CA', 'meta' => 'เอกสารประกอบการเรียน · PDF', 'price' => 50, 'was' => null],
            ['id' => 'ob', 'name' => 'Pathology ผดุงครรภ์', 'meta' => 'เอกสารประกอบการเรียน · PDF', 'price' => 50, 'was' => null],
            ['id' => 'pedia-newborn', 'name' => 'Pathology Pediatric & Newborn', 'meta' => 'เอกสารประกอบการเรียน · PDF', 'price' => 350, 'was' => null],
            ['id' => 'case47', 'name' => '47 Case Study', 'meta' => 'โรคสำคัญใน Medical ICU และตึกอายุรกรรม', 'price' => 490, 'was' => null],
            ['id' => 'surgical', 'name' => 'Surgical & ICU Surgical', 'meta' => 'พยาธิสภาพและการพยาบาลกรณีศึกษา', 'price' => 450, 'was' => null],
        ]
    ],
    [
        'group' => 'ชุดคอมโบ คุ้มกว่าซื้อแยก',
        'desc'  => 'รวมหลายเล่มในราคาพิเศษ',
        'items' => [
            ['id' => 'b-ekg-patho1', 'name' => 'EKG + Pathology เล่ม 1', 'meta' => 'รวม 2 เรื่อง', 'price' => 350, 'was' => 400],
            ['id' => 'b-patho12', 'name' => 'Pathology เล่ม 1 + เล่ม 2', 'meta' => 'รวม 2 เรื่อง', 'price' => 450, 'was' => 500],
            ['id' => 'b-patho2-ekg', 'name' => 'Pathology เล่ม 2 + EKG', 'meta' => 'รวม 2 เรื่อง', 'price' => 350, 'was' => 400],
            ['id' => 'b-triple', 'name' => 'Pathology เล่ม 1 + เล่ม 2 + EKG', 'meta' => 'รวม 3 เรื่อง', 'price' => 550, 'was' => 650],
            ['id' => 'b-all5', 'name' => 'ครบชุด Pathology + EKG', 'meta' => 'EKG, Patho เล่ม 1-2, CA, ผดุงครรภ์ (5 เรื่อง)', 'price' => 650, 'was' => 750],
            ['id' => 'b-addpatho2', 'name' => 'เพิ่ม Pathology เล่ม 2', 'meta' => 'ราคาพิเศษสำหรับผู้ที่มี EKG + Pathology เล่ม 1 แล้ว', 'price' => 200, 'was' => null],
            ['id' => 'icu-med-surgical', 'name' => 'ICU Med & ICU Surgical', 'meta' => 'พยาธิสภาพและการพยาบาลผู้ป่วยวิกฤต', 'price' => 650, 'was' => null],
        ]
    ],
    [
        'group' => 'แพ็กใหญ่ ครบจบทุกเรื่อง',
        'desc'  => '',
        'items' => [
            ['id' => 'b-5-case', 'name' => 'ครบชุด Pathology + EKG + 47 Case Study', 'meta' => 'รวม 6 เรื่อง', 'price' => 850, 'was' => 1140],
            ['id' => 'b-5-surgical', 'name' => 'ครบชุด Pathology + EKG + Surgical & ICU Surgical', 'meta' => 'รวม 6 เรื่อง', 'price' => 850, 'was' => 1100],
            ['id' => 'b-all8', 'name' => 'ครบทุกไฟล์ (8 ไฟล์)', 'meta' => 'ทุกเล่ม + Pediatric & Newborn + 47 Case Study + Surgical & ICU Surgical', 'price' => 1400, 'was' => 2040],
        ]
    ]
];

// 2. ตัวอย่างไฟล์ก่อนซื้อ (Sample/Preview files) พร้อมลิงก์ไฟล์จริง
$previewSamples = [
    [
        'name' => 'Pathology โรคที่พบบ่อยใน ER เล่ม 1',
        'meta' => 'ตัวอย่าง 3 หน้าแรก · PDF',
        'icon' => '📄',
        'url'  => '/files/BIOLOGY.pdf'
    ],
    [
        'name' => 'Pathology โรค เล่ม 2',
        'meta' => 'ตัวอย่าง 3 หน้าแรก · PDF',
        'icon' => '📄',
        'url'  => ''
    ],
    [
        'name' => 'การอ่าน EKG เบื้องต้น',
        'meta' => 'ตัวอย่างคลิปสั้น · วิดีโอ',
        'icon' => '🎬',
        'url'  => ''
    ],
    [
        'name' => 'Pathology ผดุงครรภ์',
        'meta' => 'ตัวอย่าง 3 หน้าแรก · PDF',
        'icon' => '📄',
        'url'  => ''
    ],
    [
        'name' => 'Pathology Pediatric & Newborn',
        'meta' => 'ตัวอย่าง 3 หน้าแรก · PDF',
        'icon' => '📄',
        'url'  => ''
    ],
    [
        'name' => 'Pathology CA',
        'meta' => 'ตัวอย่าง 3 หน้าแรก · PDF',
        'icon' => '📄',
        'url'  => ''
    ],
    [
        'name' => '47 Case Study',
        'meta' => 'ตัวอย่าง 1 เคส · Medical & ICU · PDF',
        'icon' => '📄',
        'url'  => ''
    ],
    [
        'name' => 'Surgical & ICU Surgical',
        'meta' => 'ตัวอย่าง 1 เคส · PDF',
        'icon' => '📄',
        'url'  => ''
    ],
];

// 3. รีวิวเริ่มต้น
$initialReviews = [
    ['name' => 'พย.กมลชนก', 'role' => 'พยาบาล ER', 'rating' => 5, 'text' => 'เอกสาร Pathology อ่านเข้าใจง่าย ใช้ทบทวนก่อนขึ้นเวรได้จริง'],
    ['name' => 'พย.สุพัตรา', 'role' => 'นักศึกษาพยาบาลปี 4', 'rating' => 5, 'text' => 'คอร์ส EKG อธิบายละเอียด ดูซ้ำได้เรื่อย ๆ คุ้มราคามาก'],
    ['name' => 'พย.ณัฐวดี', 'role' => 'พยาบาลวิชาชีพ', 'rating' => 4, 'text' => '47 Case Study ช่วยเตรียมสอบได้เยอะ อยากให้มีเคสเพิ่มอีกในอนาคต'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nurse ER — เลือกสินค้า</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mali:wght@400;500;600;700&family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<!-- นำเข้า Supabase JS Client สำหรับเชื่อมต่อฐานข้อมูล -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<style>
  :root {
    --cream: #FBF8F1;
    --panel: #FFFFFF;
    --ink: #2B2B2B;
    --ink-soft: #6B6560;
    --line: #E7E1D2;
    --red: #C9524A;
    --red-soft: #F7E3E1;
    --green-soft: #5B7A52;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    background: var(--cream);
    color: var(--ink);
    font-family: 'Sarabun', sans-serif;
    line-height: 1.6;
  }

  h1, h2, h3, .brand { font-family: 'Mali', sans-serif; font-weight: 600; margin: 0; }

  .wrap { max-width: 1040px; margin: 0 auto; padding: 32px 20px 90px; }

  header.site {
    display: flex; align-items: center; justify-content: space-between;
    padding-bottom: 20px; border-bottom: 2px solid var(--line); margin-bottom: 28px;
    flex-wrap: wrap; gap: 14px;
  }

  .brand-group { display: flex; align-items: center; gap: 10px; }
  .brand { font-size: 22px; letter-spacing: 0.01em; }
  .brand span { color: var(--red); }
  .brand small { display: block; font-family: 'Sarabun', sans-serif; font-size: 12px; font-weight: 400; color: var(--ink-soft); margin-top: 2px; }

  .stepper { display: flex; gap: 6px; font-size: 13px; color: var(--ink-soft); flex-wrap: wrap; font-family: 'Mali', sans-serif; }
  .stepper .step { display: flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; }
  .stepper .step.active { background: var(--red-soft); color: var(--red); font-weight: 600; }
  .stepper .divider { color: var(--line); padding-top: 5px; }

  .cart-fab {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px; border: 2px solid var(--ink); border-radius: 999px;
    cursor: pointer; font-size: 14px; background: var(--panel); font-family: 'Mali', sans-serif; font-weight: 600;
  }
  .cart-fab .count {
    background: var(--red); color: #fff; border-radius: 999px;
    min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;
    font-size: 12px; padding: 0 5px;
  }

  h1.page-title { font-size: 30px; margin-bottom: 4px; }
  .subtitle { color: var(--ink-soft); font-size: 14px; margin-bottom: 24px; }

  .layout { display: grid; grid-template-columns: 1.6fr 1fr; gap: 28px; align-items: start; }
  @media (max-width: 720px) { .layout { grid-template-columns: 1fr; } }

  .panel { background: var(--panel); border: 2px solid var(--line); border-radius: 18px; }

  /* Sample/preview files */
  .preview-section { margin-bottom: 36px; padding-bottom: 28px; border-bottom: 2px dashed var(--line); }
  .preview-section h2 { font-size: 19px; margin-bottom: 4px; }
  .preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 14px; }
  @media (max-width: 860px) { .preview-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px) { .preview-grid { grid-template-columns: 1fr; } }

  .preview-card {
    border: 2px solid var(--line); border-radius: 16px; padding: 16px;
    background: var(--panel); display: flex; flex-direction: column; gap: 8px;
  }
  .preview-icon { font-size: 22px; }
  .preview-name { font-size: 14.5px; font-weight: 600; font-family: 'Mali', sans-serif; }
  .preview-meta { font-size: 12px; color: var(--ink-soft); }
  .preview-btn {
    margin-top: 4px; padding: 8px 12px; border-radius: 999px; font-size: 13px;
    font-family: 'Mali', sans-serif; font-weight: 600; cursor: pointer; border: 2px solid var(--ink);
    background: transparent; color: var(--ink); text-align: center; text-decoration: none; display: inline-block;
  }
  .preview-btn:hover { background: var(--ink); color: #fff; }

  /* Catalog */
  .cat-group { margin-bottom: 32px; }
  .cat-group h2 { font-size: 19px; margin-bottom: 4px; }
  .cat-group .cat-desc { font-size: 13px; color: var(--ink-soft); margin-bottom: 14px; }

  .product-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
  @media (max-width: 640px) { .product-grid { grid-template-columns: 1fr; } }

  .product-card {
    border: 2px solid var(--line); border-radius: 16px; padding: 16px;
    background: var(--panel); display: flex; flex-direction: column; gap: 8px;
  }

  .product-name { font-size: 15px; font-weight: 600; font-family: 'Mali', sans-serif; }
  .product-meta { font-size: 12.5px; color: var(--ink-soft); }

  .price-row { display: flex; align-items: baseline; gap: 8px; margin-top: 2px; flex-wrap: wrap; }
  .price-now { font-size: 17px; font-weight: 700; color: var(--red); font-family: 'Mali', sans-serif; }
  .price-was { font-size: 13px; color: var(--ink-soft); text-decoration: line-through; }
  .save-badge {
    font-size: 11.5px; color: var(--green-soft); background: #EAF0E6;
    padding: 2px 8px; border-radius: 999px; margin-left: auto;
  }

  .add-btn {
    margin-top: 4px; padding: 9px 12px; border-radius: 999px; font-size: 14px;
    font-family: 'Mali', sans-serif; font-weight: 600; cursor: pointer; border: 2px solid var(--ink);
    background: transparent; color: var(--ink);
  }
  .add-btn.in-cart { background: var(--red); border-color: var(--red); color: #fff; }

  /* Cart items */
  .item-row { display: flex; align-items: center; gap: 16px; padding: 18px 20px; border-bottom: 1px dashed var(--line); }
  .item-row:last-child { border-bottom: none; }
  .item-name { font-size: 16px; font-weight: 600; font-family: 'Mali', sans-serif; }
  .item-meta { font-size: 13px; color: var(--ink-soft); margin-top: 2px; }
  .item-price { margin-left: auto; font-weight: 700; color: var(--red); white-space: nowrap; font-family: 'Mali', sans-serif; }
  .remove-btn { background: none; border: none; color: var(--ink-soft); font-size: 13px; text-decoration: underline; cursor: pointer; padding: 0; }
  .remove-btn:hover { color: var(--red); }
  .empty-cart { padding: 50px 20px 60px; text-align: center; color: var(--ink-soft); }

  /* Summary card */
  .summary { padding: 24px; position: sticky; top: 20px; }
  .summary h3 { font-size: 18px; margin-bottom: 18px; }
  .summary-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; color: var(--ink-soft); }
  .summary-row.total { font-size: 19px; color: var(--ink); font-weight: 700; padding-top: 14px; margin-top: 6px; border-top: 2px dashed var(--line); font-family: 'Mali', sans-serif; }
  .summary-row.total span:last-child { color: var(--red); }

  .btn-primary {
    width: 100%; padding: 14px; background: var(--red); color: #fff; border: none;
    border-radius: 999px; font-family: 'Mali', sans-serif; font-size: 16px; font-weight: 700;
    cursor: pointer; margin-top: 8px;
  }
  .btn-primary:hover { background: #B23F38; }
  .btn-primary:disabled { background: var(--line); color: var(--ink-soft); cursor: not-allowed; }

  .btn-outline {
    padding: 10px 18px; border: 2px solid var(--ink); background: transparent; color: var(--ink);
    border-radius: 999px; font-family: 'Mali', sans-serif; font-weight: 600; font-size: 14px; cursor: pointer;
    text-decoration: none; display: inline-block;
  }

  .back-link { display: inline-block; color: var(--ink-soft); font-size: 14px; text-decoration: none; margin-bottom: 16px; cursor: pointer; }
  .back-link:hover { color: var(--red); }

  /* Checkout form */
  .form-panel { padding: 24px; }
  .field { margin-bottom: 18px; }
  .field label { display: block; font-size: 13px; color: var(--ink-soft); margin-bottom: 6px; }
  .field input {
    width: 100%; padding: 11px 14px; border: 2px solid var(--line); border-radius: 12px;
    font-family: 'Sarabun', sans-serif; font-size: 15px; background: var(--cream);
  }
  .field input:focus { outline: none; border-color: var(--red); }
  .field-error { font-size: 12px; color: var(--red); margin-top: 5px; display: none; }
  .field-note { font-size: 12.5px; color: var(--ink-soft); margin-top: 6px; background: var(--red-soft); padding: 8px 10px; border-radius: 8px; }
  .field.invalid input { border-color: var(--red); }
  .field.invalid .field-error { display: block; }

  .mini-item { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; }
  .mini-item .name { color: var(--ink); }
  .mini-item .price { color: var(--ink-soft); }

  /* Confirmation */
  .confirm-panel { padding: 44px 32px; text-align: center; }
  .order-id {
    display: inline-block; font-weight: 700; letter-spacing: 0.03em; background: var(--cream);
    border: 2px dashed var(--line); padding: 8px 18px; border-radius: 999px; margin: 14px 0 24px;
    font-family: 'Mali', sans-serif; color: var(--red);
  }
  .confirm-detail { text-align: left; max-width: 360px; margin: 0 auto 28px; padding-top: 20px; border-top: 2px dashed var(--line); }
  .status-note { font-size: 13px; color: var(--ink-soft); background: var(--red-soft); padding: 10px 14px; border-radius: 999px; margin-bottom: 24px; display: inline-block; }

  /* Reviews */
  .reviews-section { margin-top: 44px; padding-top: 32px; border-top: 2px dashed var(--line); }
  .reviews-section h2 { font-size: 20px; margin-bottom: 4px; }
  .reviews-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 16px; }
  @media (max-width: 860px) { .reviews-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px) { .reviews-grid { grid-template-columns: 1fr; } }

  .review-card {
    border: 2px solid var(--line); border-radius: 16px; padding: 18px;
    background: var(--panel); display: flex; flex-direction: column; gap: 8px;
  }

  .review-stars { color: var(--red); font-size: 14px; letter-spacing: 2px; }
  .review-text { font-size: 13.5px; color: var(--ink); }
  .review-who { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
  .review-avatar {
    width: 30px; height: 30px; border-radius: 50%; background: var(--red-soft);
    display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;
  }
  .review-name { font-size: 13px; font-weight: 600; font-family: 'Mali', sans-serif; }
  .review-role { font-size: 11.5px; color: var(--ink-soft); }

  .review-form {
    margin-top: 28px; padding: 22px; border: 2px solid var(--line); border-radius: 18px;
    background: var(--panel); max-width: 480px;
  }
  .review-form h3 { font-size: 17px; margin-bottom: 14px; }
  .rating-picker { font-size: 26px; color: var(--line); margin-bottom: 16px; cursor: pointer; }
  .rating-picker .star { transition: color 0.1s; }
  .rating-picker .star.active { color: var(--red); }
  .review-form textarea {
    width: 100%; padding: 11px 14px; border: 2px solid var(--line); border-radius: 12px;
    font-family: 'Sarabun', sans-serif; font-size: 14px; background: var(--cream); resize: vertical;
  }
  .review-form textarea:focus { outline: none; border-color: var(--red); }
  .review-submit { width: auto; padding: 11px 22px; }
  .review-thanks { margin-top: 12px; font-size: 13.5px; color: var(--green-soft); }

  .hidden { display: none; }

  /* Payment */
  .pay-section-title { font-size: 15px; font-weight: 600; font-family: 'Mali', sans-serif; margin: 28px 0 14px; }
  .pay-panel { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; }
  .bank-details {
    border: 2px solid var(--line); border-radius: 16px; padding: 16px 20px; width: 100%;
  }
  .bank-row { display: flex; justify-content: space-between; font-size: 14px; padding: 8px 0; border-bottom: 1px dashed var(--line); color: var(--ink-soft); }
  .bank-row:last-child { border-bottom: none; }
  .bank-row strong { color: var(--ink); font-family: 'Mali', sans-serif; }

  .slip-upload { margin: 8px 0 20px; }
  .slip-label { display: inline-block; }
  .slip-filename { margin-top: 10px; font-size: 13px; color: var(--ink-soft); }

  /* Access */
  .access-list { margin-top: 24px; display: flex; flex-direction: column; gap: 12px; }
  .access-item {
    display: flex; align-items: center; gap: 14px; padding: 16px 18px;
    border: 2px solid var(--line); border-radius: 16px;
  }
  .access-icon { font-size: 26px; flex-shrink: 0; }
  .access-info { flex: 1; }
  .access-name { font-weight: 600; font-family: 'Mali', sans-serif; font-size: 15px; }
  .access-meta { font-size: 12.5px; color: var(--ink-soft); }
  .access-btn {
    padding: 9px 16px; border-radius: 999px; border: none; background: var(--red); color: #fff;
    font-family: 'Mali', sans-serif; font-weight: 600; font-size: 13.5px; cursor: pointer; white-space: nowrap;
    text-decoration: none; display: inline-block;
  }
</style>
</head>
<body>

<div class="wrap">

  <header class="site">
    <div class="brand-group">
      <div class="brand">Nurse<span>ER</span><small>Private Learning Platform สำหรับพยาบาล</small></div>
    </div>
    <div class="stepper" id="stepper">
      <div class="step active" data-step="catalog">เลือกสินค้า</div>
      <span class="divider">–</span>
      <div class="step" data-step="cart">ตะกร้า</div>
      <span class="divider">–</span>
      <div class="step" data-step="payment">ชำระเงิน</div>
      <span class="divider">–</span>
      <div class="step" data-step="access">เข้าถึงไฟล์</div>
    </div>
    <div class="cart-fab" onclick="goTo('cart')">
      🛒 ตะกร้า <span class="count" id="cart-count">0</span>
    </div>
    <button class="btn-outline" onclick="goTo('member')">🔑 เข้าถึงไฟล์ที่ซื้อแล้ว</button>
  </header>

  <!-- CATALOG VIEW -->
  <section id="view-catalog">
    <div class="preview-section">
      <h2>ตัวอย่างไฟล์ก่อนตัดสินใจซื้อ</h2>
      <div class="cat-desc">ดูตัวอย่างเนื้อหาบางส่วนก่อนเลือกซื้อ</div>
      <div id="preview-grid" class="preview-grid">
        <?php foreach ($previewSamples as $sample): ?>
          <div class="preview-card">
            <div class="preview-icon"><?= htmlspecialchars($sample['icon']) ?></div>
            <div class="preview-name"><?= htmlspecialchars($sample['name']) ?></div>
            <div class="preview-meta"><?= htmlspecialchars($sample['meta']) ?></div>
            
            <?php if (!empty($sample['url'])): ?>
              <a class="preview-btn" href="<?= htmlspecialchars($sample['url']) ?>" target="_blank" rel="noopener noreferrer">
                ดูตัวอย่าง
              </a>
            <?php else: ?>
              <a class="preview-btn" href="#" onclick="openPreview('<?= htmlspecialchars(addslashes($sample['name'])) ?>', '<?= htmlspecialchars(addslashes($sample['meta'])) ?>'); return false;">
                ดูตัวอย่าง
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <h1 class="page-title">เลือกสินค้า</h1>
    <p class="subtitle">เลือกซื้อรายชิ้น หรือเลือกชุดคอมโบเพื่อราคาที่คุ้มกว่า</p>
    <div id="catalog-groups">
      <?php foreach ($catalog as $group): ?>
        <div class="cat-group">
          <h2><?= htmlspecialchars($group['group']) ?></h2>
          <?php if (!empty($group['desc'])): ?>
            <div class="cat-desc"><?= htmlspecialchars($group['desc']) ?></div>
          <?php endif; ?>
          <div class="product-grid">
            <?php foreach ($group['items'] as $item): ?>
              <?php $save = (!empty($item['was'])) ? ($item['was'] - $item['price']) : 0; ?>
              <div class="product-card" id="card-<?= htmlspecialchars($item['id']) ?>">
                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                <?php if (!empty($item['meta'])): ?>
                  <div class="product-meta"><?= htmlspecialchars($item['meta']) ?></div>
                <?php endif; ?>
                <div class="price-row">
                  <span class="price-now"><?= formatMoney($item['price']) ?></span>
                  <?php if (!empty($item['was'])): ?>
                    <span class="price-was"><?= formatMoney($item['was']) ?></span>
                  <?php endif; ?>
                  <?php if ($save > 0): ?>
                    <span class="save-badge">ประหยัด <?= number_format($save, 0, '.', ',') ?> บาท</span>
                  <?php endif; ?>
                </div>
                <button class="add-btn" id="btn-<?= htmlspecialchars($item['id']) ?>" onclick="toggleCart('<?= htmlspecialchars($item['id']) ?>')">
                  เพิ่มลงตะกร้า
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="reviews-section">
      <h2>รีวิวจากผู้เรียน</h2>
      <div class="cat-desc">เสียงจากพยาบาลที่เคยใช้เอกสารและคอร์สของเรา</div>
      <div id="reviews-grid" class="reviews-grid">
        <?php foreach ($initialReviews as $r): ?>
          <div class="review-card">
            <div class="review-stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></div>
            <div class="review-text"><?= htmlspecialchars($r['text']) ?></div>
            <div class="review-who">
              <div class="review-avatar">🐶</div>
              <div>
                <div class="review-name"><?= htmlspecialchars($r['name']) ?></div>
                <div class="review-role"><?= htmlspecialchars($r['role']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="review-form">
        <h3>เขียนรีวิวของคุณ</h3>
        <div class="rating-picker" id="rating-picker">
          <span class="star" data-value="1">★</span>
          <span class="star" data-value="2">★</span>
          <span class="star" data-value="3">★</span>
          <span class="star" data-value="4">★</span>
          <span class="star" data-value="5">★</span>
        </div>
        <div class="field" id="field-review-name">
          <label for="review-name">ชื่อของคุณ</label>
          <input type="text" id="review-name" placeholder="เช่น พย.สมหญิง">
          <div class="field-error">กรุณากรอกชื่อ</div>
        </div>
        <div class="field" id="field-review-text">
          <label for="review-text">ความคิดเห็น</label>
          <textarea id="review-text" rows="3" placeholder="เล่าประสบการณ์การใช้งานของคุณ"></textarea>
          <div class="field-error">กรุณากรอกความคิดเห็น</div>
        </div>
        <button class="btn-primary review-submit" onclick="submitReview()">ส่งรีวิว</button>
        <div class="review-thanks hidden" id="review-thanks">ขอบคุณสำหรับรีวิวของคุณ 🐶</div>
      </div>
    </div>
  </section>

  <!-- CART VIEW -->
  <section id="view-cart" class="hidden">
    <span class="back-link" onclick="goTo('catalog')">‹ เลือกซื้อสินค้าเพิ่ม</span>
    <h1 class="page-title">ตะกร้าของฉัน</h1>
    <p class="subtitle">ตรวจสอบรายการก่อนดำเนินการชำระเงิน</p>
    <div class="layout">
      <div class="panel" id="cart-list"></div>
      <div class="panel summary">
        <h3>สรุปยอด</h3>
        <div class="summary-row total">
          <span>รวมทั้งหมด</span>
          <span id="cart-total">0 บาท</span>
        </div>
        <button class="btn-primary" id="cart-checkout-btn" onclick="goToPayment()">ดำเนินการชำระเงิน</button>
      </div>
    </div>
  </section>

  <!-- PAYMENT VIEW -->
  <section id="view-payment" class="hidden">
    <div class="panel confirm-panel" style="text-align:left; padding:32px;">
      <div style="text-align:center;">
        <h2>สร้างคำสั่งซื้อเรียบร้อยแล้ว</h2>
        <div class="order-id" id="order-id-display">NER-00000000-0000</div>
        <div style="margin-bottom:20px;"><span class="status-note" id="payment-status-note">🟡 รอชำระเงิน</span></div>
      </div>

      <div class="confirm-detail" style="max-width:none;">
        <div id="confirm-items"></div>
        <div class="summary-row total">
          <span>ยอดที่ต้องชำระ</span>
          <span id="confirm-total">0 บาท</span>
        </div>
      </div>

      <div class="field" id="field-payment-email" style="margin-top:20px; max-width:400px;">
        <label for="payment-email">อีเมลผู้สั่งซื้อ (ใช้เข้าถึงไฟล์ภายหลัง)</label>
        <input type="email" id="payment-email" placeholder="name@email.com">
        <div class="field-error">กรุณากรอกอีเมลให้ถูกต้อง ต้องใช้อีเมลนี้เพื่อเข้าถึงไฟล์ภายหลัง</div>
        <div class="field-note">⚠️ กรุณาใช้อีเมลนี้ทุกครั้งที่ต้องการเข้าถึงไฟล์ที่สั่งซื้อในภายหลัง หากใส่อีเมลอื่นจะไม่สามารถเข้าถึงไฟล์ได้</div>
      </div>

      <div class="pay-section-title">โอนเงินเข้าบัญชี</div>

      <div class="pay-panel">
        <div class="bank-details">
          <div class="bank-row"><span>ธนาคาร</span><strong>ไทยพาณิชย์ (SCB)</strong></div>
          <div class="bank-row"><span>เลขบัญชี</span><strong>538-2-81205-0</strong></div>
          <div class="bank-row"><span>ชื่อบัญชี</span><strong>วิชุดา แฝงเมืองคุก</strong></div>
        </div>
      </div>

      <div class="field" id="field-slip-account-name" style="max-width:400px;">
        <label for="slip-account-name">ชื่อบัญชีปลายทางที่ปรากฏในสลิปโอนเงิน</label>
        <input type="text" id="slip-account-name" placeholder="กรอกชื่อบัญชีตามที่ปรากฏในสลิป">
        <div class="field-error">ชื่อบัญชีไม่ตรงกับบัญชีผู้รับ (วิชุดา แฝงเมืองคุก) กรุณาตรวจสอบว่าโอนเงินถูกบัญชี</div>
        <div class="field-note">⚠️ ชื่อบัญชีที่กรอกต้องตรงกับ "วิชุดา แฝงเมืองคุก" เท่านั้น หากไม่ตรงระบบจะไม่อนุญาตให้เข้าถึงไฟล์</div>
      </div>

      <div class="field" id="field-slip-amount" style="max-width:400px;">
        <label for="slip-amount">ยอดเงินที่โอนตามสลิป (บาท)</label>
        <input type="number" id="slip-amount" min="0" placeholder="0">
        <div class="field-error">ยอดเงินไม่ตรงกับยอดที่ต้องชำระ กรุณาตรวจสอบสลิปอีกครั้ง</div>
        <div class="field-note">⚠️ ยอดเงินที่กรอกต้องตรงกับยอดที่ต้องชำระเป๊ะ ๆ หากไม่ตรงระบบจะไม่อนุญาตให้เข้าถึงไฟล์</div>
      </div>

      <div class="slip-upload" id="slip-upload-block">
        <label for="slip-file" class="btn-outline slip-label">📎 อัปโหลดหลักฐานการชำระเงิน (JPG, PNG, PDF)</label>
        <input type="file" id="slip-file" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="handleSlipUpload(event)">
        <div class="slip-filename" id="slip-filename"></div>
        <div class="field-error" id="slip-reuse-error" style="display:none;">สลิปนี้เคยถูกใช้ยืนยันการชำระเงินของคำสั่งซื้ออื่นไปแล้ว ไม่สามารถใช้ซ้ำได้ กรุณาแนบสลิปใหม่</div>
      </div>

      <div id="slip-confirmation" class="hidden">
        <div class="status-note">ส่งหลักฐานการชำระเงินเรียบร้อยแล้ว กรุณารอการตรวจสอบ</div>
        <p class="subtitle" style="margin-top:10px;">Admin จะตรวจสอบหลักฐานการชำระเงินของคุณ เมื่อยืนยันแล้วคุณจะเข้าถึงไฟล์ที่สั่งซื้อได้ทันที</p>
        <button class="btn-primary" onclick="simulateAdminConfirm()">จำลอง: Admin ยืนยันการชำระเงิน (ทดสอบระบบ)</button>
      </div>
    </div>
  </section>

  <!-- ACCESS VIEW -->
  <section id="view-access" class="hidden">
    <div class="panel confirm-panel" style="text-align:left; padding:32px;">
      <div style="text-align:center;">
        <h2>ยืนยันการชำระเงินเรียบร้อยแล้ว 🎉</h2>
        <p class="subtitle">คุณสามารถเข้าสู่ Nurse ER Member เพื่อเข้าถึงสินค้าที่คุณซื้อได้แล้ว</p>
      </div>
      <div id="access-list" class="access-list"></div>
      <div style="text-align:center; margin-top:24px;">
        <button class="btn-outline" onclick="resetFlow()">กลับไปเลือกสินค้าต่อ</button>
      </div>
    </div>
  </section>

  <!-- MEMBER FILE ACCESS VIEW -->
  <section id="view-member" class="hidden">
    <span class="back-link" onclick="goTo('catalog')">‹ กลับไปเลือกสินค้า</span>
    <h1 class="page-title">เข้าถึงไฟล์ที่ซื้อแล้ว</h1>
    <p class="subtitle">สำหรับลูกค้าที่เคยสั่งซื้อและชำระเงินแล้ว — กรอกอีเมลที่ใช้ตอนสั่งซื้อ</p>
    <div class="panel form-panel" style="max-width:440px;">
      <div class="field" id="field-member-email">
        <label for="member-email">อีเมลผู้สั่งซื้อ</label>
        <input type="email" id="member-email" placeholder="name@email.com">
        <div class="field-error">กรุณากรอกอีเมลให้ถูกต้อง</div>
      </div>
      <button class="btn-primary" style="width:auto; padding:11px 22px;" onclick="checkMemberAccess()">ตรวจสอบสิทธิ์</button>
    </div>
    <div id="member-result" style="margin-top:20px; max-width:640px;"></div>
  </section>

  <!-- FOOTER & ADMIN TOOLBAR -->
  <div style="text-align:center; margin-top:60px; padding-top:20px; border-top:1px dashed var(--line); display:flex; gap:16px; justify-content:center; flex-wrap:wrap; align-items:center;">
    <a href="/admin" style="font-size:14px; color:#C9524A; font-weight:700; text-decoration:none; padding:6px 14px; border:2px solid #C9524A; border-radius:999px; background:#fff;">⚙️ ไปหน้า Admin จัดการสินค้า</a>
  </div>

</div>

<script>
  // 1. ตั้งค่า Configuration
  const SUPABASE_URL = "https://rpbyapwseypgzcuesnoi.supabase.co";
  const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJwYnlhcHdzZXlwZ3pjdWVzbm9pIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODkyODE0MjgsImV4cCI6MjEwNDg1NzQyOH0.nFcP1PYQSSwM8ZEoMJFiNEQC2JhmLBImOgk6i_rvlkI";
window.supabaseClient = window.supabaseClient || ((window.supabase && SUPABASE_URL) ? window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null);
var supabase = window.supabaseClient;
  // 2. ข้อมูลสินค้า (Pure JavaScript ไม่ต้องผ่าน PHP)
  const catalog = [
    {
      group: 'รายชิ้น',
      desc: 'ซื้อทีละเล่ม เลือกเฉพาะเรื่องที่ต้องการ',
      items: [
        { id: 'ekg', name: 'การอ่าน EKG เบื้องต้น', meta: 'คอร์สวิดีโอ', price: 150, was: null },
        { id: 'patho1', name: 'Pathology โรคที่พบบ่อยใน ER เล่ม 1', meta: 'เอกสารประกอบการเรียน · PDF', price: 250, was: null, pdf_url: '/files/BIOLOGY.pdf' },
        { id: 'patho2', name: 'Pathology โรค เล่ม 2', meta: 'เอกสารประกอบการเรียน · PDF', price: 250, was: null },
        { id: 'ca', name: 'Pathology CA', meta: 'เอกสารประกอบการเรียน · PDF', price: 50, was: null },
        { id: 'ob', name: 'Pathology ผดุงครรภ์', meta: 'เอกสารประกอบการเรียน · PDF', price: 50, was: null },
        { id: 'pedia-newborn', name: 'Pathology Pediatric & Newborn', meta: 'เอกสารประกอบการเรียน · PDF', price: 350, was: null },
        { id: 'case47', name: '47 Case Study', meta: 'โรคสำคัญใน Medical ICU และตึกอายุรกรรม', price: 490, was: null },
        { id: 'surgical', name: 'Surgical & ICU Surgical', meta: 'พยาธิสภาพและการพยาบาลกรณีศึกษา', price: 450, was: null }
      ]
    },
    {
      group: 'ชุดคอมโบ คุ้มกว่าซื้อแยก',
      desc: 'รวมหลายเล่มในราคาพิเศษ',
      items: [
        { id: 'b-ekg-patho1', name: 'EKG + Pathology เล่ม 1', meta: 'รวม 2 เรื่อง', price: 350, was: 400 },
        { id: 'b-patho12', name: 'Pathology เล่ม 1 + เล่ม 2', meta: 'รวม 2 เรื่อง', price: 450, was: 500 },
        { id: 'b-patho2-ekg', name: 'Pathology เล่ม 2 + EKG', meta: 'รวม 2 เรื่อง', price: 350, was: 400 },
        { id: 'b-triple', name: 'Pathology เล่ม 1 + เล่ม 2 + EKG', meta: 'รวม 3 เรื่อง', price: 550, was: 650 },
        { id: 'b-all5', name: 'ครบชุด Pathology + EKG', meta: 'EKG, Patho เล่ม 1-2, CA, ผดุงครรภ์ (5 เรื่อง)', price: 650, was: 750 },
        { id: 'b-addpatho2', name: 'เพิ่ม Pathology เล่ม 2', meta: 'ราคาพิเศษสำหรับผู้ที่มี EKG + Pathology เล่ม 1 แล้ว', price: 200, was: null },
        { id: 'icu-med-surgical', name: 'ICU Med & ICU Surgical', meta: 'พยาธิสภาพและการพยาบาลผู้ป่วยวิกฤต', price: 650, was: null }
      ]
    },
    {
      group: 'แพ็กใหญ่ ครบจบทุกเรื่อง',
      desc: '',
      items: [
        { id: 'b-5-case', name: 'ครบชุด Pathology + EKG + 47 Case Study', meta: 'รวม 6 เรื่อง', price: 850, was: 1140 },
        { id: 'b-5-surgical', name: 'ครบชุด Pathology + EKG + Surgical & ICU Surgical', meta: 'รวม 6 เรื่อง', price: 850, was: 1100 },
        { id: 'b-all8', name: 'ครบทุกไฟล์ (8 ไฟล์)', meta: 'ทุกเล่ม + Pediatric & Newborn + 47 Case Study + Surgical & ICU Surgical', price: 1400, was: 2040 }
      ]
    }
  ];

  let productIndex = {};
  if (Array.isArray(catalog)) {
    catalog.forEach(function(g) {
      if (g && Array.isArray(g.items)) {
        g.items.forEach(function(p) {
          productIndex[p.id] = p;
        });
      }
    });
  }

  let cart = [];
  let orders = [];
  let currentOrderId = null;
  let usedSlipFingerprints = new Set();

  const money = (n) => Number(n || 0).toLocaleString('th-TH') + ' บาท';
  const subtotal = () => cart.reduce((sum, item) => sum + Number(item.price || 0), 0);

  function toggleCart(id) {
    let item = productIndex[id];

    // Fallback: หากหาในดัชนีไม่พบ ให้วนหาจาก catalog
    if (!item && Array.isArray(catalog)) {
      for (const g of catalog) {
        const found = g.items.find(x => x.id === id);
        if (found) { item = found; break; }
      }
    }

    if (!item) {
      console.error('ไม่พบสินค้า ID:', id);
      return;
    }

    const index = cart.findIndex(x => x.id === id);
    if (index > -1) {
      cart.splice(index, 1);
    } else {
      cart.push(item);
    }
    updateCartUI();
  }

  function removeItem(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartUI();
  }

  function updateCartUI() {
    const countEl = document.getElementById('cart-count');
    if (countEl) countEl.textContent = cart.length;

    // อัปเดตสถานะปุ่มบนหน้าเว็บ
    Object.keys(productIndex).forEach(id => {
      const btn = document.getElementById('btn-' + id);
      if (btn) {
        const inCart = cart.some(item => item.id === id);
        btn.classList.toggle('in-cart', inCart);
        btn.textContent = inCart ? '✓ อยู่ในตะกร้าแล้ว — นำออก' : 'เพิ่มลงตะกร้า';
      }
    });

    renderCart();
  }

  function renderCart() {
    const list = document.getElementById('cart-list');
    if (!list) return;

    if (cart.length === 0) {
      list.innerHTML = `
        <div class="empty-cart">
          <div style="font-size:36px; margin-bottom:12px;">🛒</div>
          <div>ตะกร้าของคุณยังว่างอยู่<br>เลือกคอร์สหรือเอกสารที่ต้องการเริ่มเรียนได้เลย</div>
        </div>`;
    } else {
      list.innerHTML = cart.map(item => `
        <div class="item-row">
          <div>
            <div class="item-name">${item.name}</div>
            <div class="item-meta">${item.meta || ''}</div>
            <button class="remove-btn" onclick="removeItem('${item.id}')">นำออกจากตะกร้า</button>
          </div>
          <div class="item-price">${money(item.price)}</div>
        </div>
      `).join('');
    }

    const totalEl = document.getElementById('cart-total');
    if (totalEl) totalEl.textContent = money(subtotal());

    const checkoutBtn = document.getElementById('cart-checkout-btn');
    if (checkoutBtn) checkoutBtn.disabled = cart.length === 0;
  }

  function setStep(step) {
    document.querySelectorAll('.stepper .step').forEach(el => {
      el.classList.toggle('active', el.dataset.step === step);
    });
  }

  function goTo(view) {
    ['catalog', 'cart', 'payment', 'access', 'member'].forEach(v => {
      const el = document.getElementById('view-' + v);
      if (el) el.classList.add('hidden');
    });
    const target = document.getElementById('view-' + view);
    if (target) target.classList.remove('hidden');
    setStep(view);
    if (view === 'cart') renderCart();
    window.scrollTo(0, 0);
  }

  function validateField(id, isValid) {
    const field = document.getElementById(id);
    if (field) field.classList.toggle('invalid', !isValid);
    return isValid;
  }

  function generateOrderId() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    const rand = String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
    return `NER-${y}${m}${d}-${rand}`;
  }

  function goToPayment() {
    if (cart.length === 0) return;

    currentOrderId = generateOrderId();
    document.getElementById('order-id-display').textContent = currentOrderId;
    document.getElementById('confirm-items').innerHTML = cart.map(item => `
      <div class="mini-item">
        <span class="name">${item.name}</span>
        <span class="price">${money(item.price)}</span>
      </div>
    `).join('');
    document.getElementById('confirm-total').textContent = money(subtotal());

    document.getElementById('payment-email').value = '';
    document.getElementById('field-payment-email').classList.remove('invalid');
    document.getElementById('slip-account-name').value = '';
    document.getElementById('field-slip-account-name').classList.remove('invalid');
    document.getElementById('slip-amount').value = '';
    document.getElementById('field-slip-amount').classList.remove('invalid');
    document.getElementById('slip-reuse-error').style.display = 'none';
    document.getElementById('slip-confirmation').classList.add('hidden');
    document.getElementById('slip-upload-block').classList.remove('hidden');
    document.getElementById('slip-filename').textContent = '';
    document.getElementById('slip-file').value = '';
    document.getElementById('payment-status-note').textContent = '🟡 รอชำระเงิน';

    goTo('payment');
  }

  const PAYEE_ACCOUNT_NAME = 'วิชุดา แฝงเมืองคุก';
  const normalizeName = (s) => (s || '').trim().replace(/\s+/g, ' ');

  function handleSlipUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const email = document.getElementById('payment-email').value.trim().toLowerCase();
    const emailOk = validateField('field-payment-email', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));

    const accountNameRaw = document.getElementById('slip-account-name').value;
    const accountNameOk = validateField('field-slip-account-name', normalizeName(accountNameRaw) === PAYEE_ACCOUNT_NAME);

    const slipAmountRaw = document.getElementById('slip-amount').value;
    const slipAmountOk = validateField('field-slip-amount', slipAmountRaw !== '' && Number(slipAmountRaw) === subtotal());

    const fingerprint = file.name + '|' + file.size + '|' + file.lastModified;
    const reuseErrorBox = document.getElementById('slip-reuse-error');
    const isReused = usedSlipFingerprints.has(fingerprint);
    reuseErrorBox.style.display = isReused ? 'block' : 'none';

    if (!(emailOk && accountNameOk && slipAmountOk) || isReused) {
      event.target.value = '';
      return;
    }

    usedSlipFingerprints.add(fingerprint);
    orders.push({ orderId: currentOrderId, email: email, items: [...cart], status: 'pending', slipFingerprint: fingerprint });

    document.getElementById('slip-filename').textContent = 'แนบไฟล์แล้ว: ' + file.name;
    document.getElementById('slip-upload-block').classList.add('hidden');
    document.getElementById('slip-confirmation').classList.remove('hidden');
    document.getElementById('payment-status-note').textContent = '🟡 รอตรวจสอบการชำระเงิน';
  }

  function openPreview(name, meta, url = '') {
    if (url) {
      window.open(url, '_blank');
      return;
    }

    const w = window.open('', '_blank');
    if (w) {
      w.document.write(`
        <!DOCTYPE html>
        <html lang="th">
        <head>
          <meta charset="UTF-8">
          <title>ตัวอย่าง: ${name}</title>
          <style>
            body { font-family: sans-serif; background: #FBF8F1; color: #2B2B2B; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .box { max-width: 440px; background: #fff; padding: 30px; border-radius: 18px; border: 2px solid #E7E1D2; text-align: center; }
            h2 { color: #C9524A; margin-top: 0; }
            p { color: #6B6560; font-size: 14px; }
          </style>
        </head>
        <body>
          <div class="box">
            <h2>ตัวอย่าง: ${name}</h2>
            <p>${meta}</p>
            <p style="margin-top:20px; color:#C9524A;">📌 กำลังจัดเตรียมไฟล์ตัวอย่างสำหรับรายการนี้</p>
          </div>
        </body>
        </html>
      `);
      w.document.close();
    }
  }

  function renderAccessItemsHtml(items) {
    return items.map(item => {
      const isVideo = (item.meta || '').includes('วิดีโอ');
      let actionHtml;

      if (item.pdf_url) {
        actionHtml = `<a class="access-btn" href="${item.pdf_url}" download target="_blank" rel="noopener">ดาวน์โหลด PDF</a>`;
      } else if (item.extraLink) {
        actionHtml = `<a class="access-btn" href="${item.extraLink}" target="_blank" rel="noopener">เข้าถึงไฟล์</a>`;
      } else if (isVideo) {
        actionHtml = `<button class="access-btn" onclick="openPreview('${item.name}', '${item.meta}')">ดูวิดีโอ</button>`;
      } else {
        actionHtml = `<button class="access-btn" onclick="openPreview('${item.name}', '${item.meta}')">เปิดอ่าน PDF</button>`;
      }

      return `
        <div class="access-item">
          <div class="access-icon">${isVideo ? '🎬' : '📄'}</div>
          <div class="access-info">
            <div class="access-name">${item.name}</div>
            <div class="access-meta">${item.meta || ''}</div>
          </div>
          ${actionHtml}
        </div>
      `;
    }).join('');
  }

  async function simulateAdminConfirm() {
    const order = orders.find(o => o.orderId === currentOrderId);
    if (order) order.status = 'confirmed';

    if (supabase) {
      try {
        await supabase.from('orders').insert([
          {
            order_code: currentOrderId,
            customer_email: order ? order.email : 'guest@nurseer.com',
            total_amount: subtotal(),
            status: 'confirmed'
          }
        ]);
      } catch (e) {
        console.warn('บันทึกคำสั่งซื้อลง Supabase ไม่สำเร็จ:', e.message);
      }
    }

    const email = order ? order.email : null;
    const accessibleItems = email ? getAccessibleItemsForEmail(email) : cart;

    document.getElementById('access-list').innerHTML = renderAccessItemsHtml(accessibleItems);
    goTo('access');
  }

  function getAccessibleItemsForEmail(email) {
    const matched = orders.filter(o => o.email === email && o.status === 'confirmed');
    const itemMap = {};
    matched.forEach(o => o.items.forEach(item => { itemMap[item.id] = item; }));
    return Object.values(itemMap);
  }

  function checkMemberAccess() {
    const email = document.getElementById('member-email').value.trim().toLowerCase();
    const emailOk = validateField('field-member-email', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));
    const resultBox = document.getElementById('member-result');
    if (!emailOk) {
      resultBox.innerHTML = '';
      return;
    }

    const items = getAccessibleItemsForEmail(email);

    if (items.length === 0) {
      resultBox.innerHTML = `<div class="status-note" style="background:var(--red-soft); color:var(--red);">ไม่พบไฟล์ที่เข้าถึงได้สำหรับอีเมลนี้ กรุณาตรวจสอบอีเมลที่ใช้ตอนสั่งซื้อ หรืออาจยังไม่ได้รับการยืนยันการชำระเงิน</div>`;
      return;
    }

    resultBox.innerHTML = `
      <p class="subtitle">พบสิทธิ์เข้าถึง ${items.length} รายการ สำหรับอีเมลนี้ (รวมทุกคำสั่งซื้อที่ยืนยันแล้ว)</p>
      <div class="access-list">${renderAccessItemsHtml(items)}</div>
    `;
  }

  function resetFlow() {
    cart = [];
    updateCartUI();
    goTo('catalog');
  }

  // ระบบ Review
  let selectedRating = 0;
  const stars = document.querySelectorAll('#rating-picker .star');
  stars.forEach(star => {
    star.addEventListener('click', () => {
      selectedRating = parseInt(star.dataset.value, 10);
      stars.forEach(s => {
        s.classList.toggle('active', parseInt(s.dataset.value, 10) <= selectedRating);
      });
    });
  });

  function submitReview() {
    const name = document.getElementById('review-name').value.trim();
    const text = document.getElementById('review-text').value.trim();

    const nameOk = validateField('field-review-name', name.length > 0);
    const textOk = validateField('field-review-text', text.length > 0);
    if (!(nameOk && textOk)) return;

    const rating = selectedRating > 0 ? selectedRating : 5;
    const grid = document.getElementById('reviews-grid');
    const newCard = document.createElement('div');
    newCard.className = 'review-card';
    newCard.innerHTML = `
      <div class="review-stars">${'★'.repeat(rating)}${'☆'.repeat(5 - rating)}</div>
      <div class="review-text">${text}</div>
      <div class="review-who">
        <div class="review-avatar">🐶</div>
        <div>
          <div class="review-name">${name}</div>
          <div class="review-role">ผู้เรียน Nurse ER</div>
        </div>
      </div>
    `;
    grid.prepend(newCard);

    document.getElementById('review-name').value = '';
    document.getElementById('review-text').value = '';
    selectedRating = 0;
    stars.forEach(s => s.classList.remove('active'));

    const thanks = document.getElementById('review-thanks');
    thanks.classList.remove('hidden');
    setTimeout(() => thanks.classList.add('hidden'), 3000);
  }

  // เริ่มต้นเรียกทำงาน
  updateCartUI();
</script>

</body>
</html>