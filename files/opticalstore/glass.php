<?php
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Product collections
$collections = [
    'General' => [
        ['name'=>'Square Glass','price'=>700,'material'=>'TR','image'=>'uploads/categories/glass1.jpg'],
        ['name'=>'Square Glasses 126225','price'=>20000,'material'=>'Other Plastic','image'=>'uploads/categories/glass2.jpg'],
        ['name'=>'Hangtime 125118','price'=>800,'material'=>'Other Plastic','image'=>'uploads/categories/glass3.jpg'],
        ['name'=>'Rectangle Glasses 125021','price'=>700,'material'=>'Other Plastic','image'=>'uploads/categories/glass4.jpg'],
        ['name'=>'Round Glasses 7801821','price'=>70,'material'=>'Mixed Materials','image'=>'uploads/categories/glass5.jpg']
    ],
    'Kids' => [
        ['name'=>'Kids Glass 1','price'=>800,'image'=>'uploads/categories/kids1.jpg'],
        ['name'=>'Kids Glass 2','price'=>900,'image'=>'uploads/categories/kids2.jpg'],
        ['name'=>'Kids Glass 3','price'=>1000,'image'=>'uploads/categories/kids3.jpg'],
        ['name'=>'Kids Glass 4','price'=>900,'image'=>'uploads/categories/kids4.jpg'],
        ['name'=>'Kids Glass 5','price'=>1000,'image'=>'uploads/categories/kids5.jpg']
    ],
    'Women' => [
        ['name'=>'Women Glass 1','price'=>2000,'image'=>'uploads/categories/womens1.jpg'],
        ['name'=>'Women Glass 2','price'=>2200,'image'=>'uploads/categories/womens2.jpg'],
        ['name'=>'Women Glass 3','price'=>2100,'image'=>'uploads/categories/womens3.jpg'],
        ['name'=>'Women Glass 4','price'=>2300,'image'=>'uploads/categories/womens4.jpg'],
          ['name'=>'Women Glass 4','price'=>2300,'image'=>'uploads/categories/womens4.jpg']
    ],
    'Men' => [
        ['name'=>'Men Glass 1','price'=>2500,'image'=>'uploads/categories/men1.jpg'],
        ['name'=>'Men Glass 2','price'=>1000,'image'=>'uploads/categories/men2.jpg'],
        ['name'=>'Men Glass 3','price'=>2100,'image'=>'uploads/categories/men3.jpg'],
        ['name'=>'Men Glass 4','price'=>2400,'image'=>'uploads/categories/men4.jpg'],
          ['name'=>'Men Glass 4','price'=>2300,'image'=>'uploads/categories/men4.jpg']
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced Glasses Collection | Optical Store</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* === Base Reset === */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Roboto', sans-serif; }
body { background:#f5f6fa; color:#333; line-height:1.6; }

/* === Header === */
header {
    background: linear-gradient(135deg, #007bff, #00d4ff);
    color:#fff;
    padding:20px 0;
    text-align:center;
    font-size:28px;
    font-weight:700;
    position:relative;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
.cart-link {
    position:absolute;
    top:20px; right:25px;
    background:#fff; color:#007bff;
    padding:10px 18px;
    border-radius:30px;
    font-weight:500;
    text-decoration:none;
    transition:0.3s;
}
.cart-link:hover { background:#007bff; color:#fff; transform:scale(1.05); }

/* === Container === */
.container { max-width:1200px; margin:40px auto; padding:0 20px; }

/* === Tabs === */
.tabs { display:flex; justify-content:center; flex-wrap:wrap; gap:15px; margin-bottom:40px; }
.tab-btn {
    padding:12px 28px;
    border-radius:30px;
    border:none;
    cursor:pointer;
    background:#e0e0e0;
    font-weight:600;
    transition:0.3s;
}
.tab-btn.active { background:#007bff; color:#fff; transform:scale(1.05); }

/* === Products Grid === */
.products {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:30px;
}
.product-card {
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,0.1);
    text-align:center;
    transition:all 0.3s ease;
    position:relative;
}
.product-card:hover { transform:translateY(-8px); box-shadow:0 15px 30px rgba(0,0,0,0.2); }
.product-image {
    width:100%;
    height:260px;
    object-fit:cover;
    transition:transform 0.3s ease;
}
.product-card:hover .product-image { transform:scale(1.05); }

/* === Badges === */
.badge {
    position:absolute;
    top:15px; left:15px;
    background:#ff4757;
    color:#fff;
    padding:5px 12px;
    border-radius:12px;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
}

/* === Product Info === */
.product-info { padding:20px; }
.product-info h3 { font-size:20px; margin-bottom:8px; font-weight:700; color:#222; }
.product-info p { font-size:14px; margin-bottom:8px; color:#555; }
.price { font-size:18px; font-weight:700; color:#007bff; margin-bottom:15px; }

/* === Buttons === */
.btn {
    display:inline-block;
    padding:12px 28px;
    background:linear-gradient(135deg,#007bff,#00c3ff);
    color:white;
    border:none;
    border-radius:30px;
    cursor:pointer;
    font-weight:600;
    transition:all 0.3s ease;
}
.btn:hover {
    background:linear-gradient(135deg,#0056b3,#009edc);
    transform:translateY(-2px);
    box-shadow:0 6px 15px rgba(0,0,0,0.15);
}

/* === Footer === */
footer { text-align:center; margin-top:50px; padding:25px; background:#f1f1f1; color:#555; font-weight:500; }

/* === Responsive === */
@media(max-width:768px) {
    .tabs { flex-direction:column; gap:10px; }
    .product-image { height:220px; }
}
</style>
</head>
<body>

<header>
  👓 Optical Store
  <a href="cart.php" class="cart-link">🛒 Cart (<?= count($_SESSION['cart']) ?>)</a>
</header>

<div class="container">
  <div class="tabs">
    <?php foreach ($collections as $label => $items): ?>
      <button class="tab-btn" data-tab="<?= strtolower($label) ?>"><?= htmlspecialchars($label) ?></button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($collections as $label => $items): ?>
    <div class="products" id="<?= strtolower($label) ?>" style="display:none;">
      <?php foreach ($items as $item): ?>
        <div class="product-card">
          <?php if($item['price']>1000): ?>
            <div class="badge">Premium</div>
          <?php endif; ?>
          <img src="<?= htmlspecialchars($item['image']) ?>" class="product-image" alt="<?= htmlspecialchars($item['name']) ?>">
          <div class="product-info">
            <h3><?= htmlspecialchars($item['name']) ?></h3>
            <?php if(isset($item['material'])): ?>
              <p>Material: <?= htmlspecialchars($item['material']) ?></p>
            <?php endif; ?>
            <div class="price">₹<?= htmlspecialchars($item['price']) ?></div>
            <form action="add_to_cart.php" method="POST">
              <input type="hidden" name="name" value="<?= htmlspecialchars($item['name']) ?>">
              <input type="hidden" name="price" value="<?= htmlspecialchars($item['price']) ?>">
              <input type="hidden" name="image" value="<?= htmlspecialchars($item['image']) ?>">
              <button type="submit" class="btn">Add to Cart</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>

<footer>© 2025 Clarity Store</footer>

<script>
const tabs = document.querySelectorAll('.tab-btn');
const sections = document.querySelectorAll('.products');
function showTab(tabKey) {
  sections.forEach(s => s.style.display = 'none');
  document.getElementById(tabKey).style.display = 'grid';
  tabs.forEach(t => t.classList.remove('active'));
  document.querySelector(`.tab-btn[data-tab="${tabKey}"]`).classList.add('active');
}
showTab('general'); // default tab
tabs.forEach(tab => tab.addEventListener('click', () => showTab(tab.dataset.tab)));
</script>

</body>
</html>
