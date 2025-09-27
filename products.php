<?php
@include 'config.php';
session_start();
require_once 'helpers/FormatHelper.php';
use Helpers\FormatHelper;

// Get filters
$type = isset($_GET['type']) && in_array($_GET['type'], ['coffee', 'religious']) ? $_GET['type'] : 'coffee';
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'name_asc'; // default sort
$category = $_GET['category'] ?? '';

// Pagination settings
$perPage = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $perPage;

// Build SQL dynamically
$where = "category != :category";
$params = [':category' => 'Add-ons'];

if ($type !== '') {
    $where .= " AND type = :type";
    $params[':type'] = $type;
}

if ($search !== '') {
    $where .= " AND name LIKE :search";
    $params[':search'] = "%$search%";
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $orderBy = "price ASC";
        break;
    case 'price_desc':
        $orderBy = "price DESC";
        break;
    case 'name_desc':
        $orderBy = "name DESC";
        break;
    default:
        $orderBy = "name ASC";
}

// category 
if ($category !== '') {
    $where .= " AND category = :category_filter";
    $params[':category_filter'] = $category;
}

// Count total
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE $where");
$totalStmt->execute($params);
$totalProducts = (int) $totalStmt->fetchColumn();

// Fetch products
$sql = "SELECT * FROM products WHERE $where ORDER BY $orderBy LIMIT :offset, :perPage";
$stmt = $conn->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pages
$totalPages = (int) ceil($totalProducts / $perPage);

// Categories
$categories = $_SESSION['categories'] ?? [];

// fetch favorite products of user
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // get the ids of the products in the wishlist
    $select_wishlist = $conn->prepare("SELECT product_id FROM `wishlist` WHERE user_id = ? AND type = ?");
    $select_wishlist->execute([$user_id, $type]);
    $wishlist_product_ids = $select_wishlist->fetchAll(PDO::FETCH_COLUMN);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Kape Milagrosa
    </title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="min-h-screen font-sans text-gray-900">
    <?php include 'header.php'; ?>

    <main>

        <section class="mt-20">
            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
                <header>
                    <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">
                        Our Products
                    </h2>

                    <p class="mt-4 max-w-md text-gray-500">

                    </p>
                </header>

                <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-gray-500">
                        Showing <?= ($offset + 1); ?>–<?= min($offset + $perPage, $totalProducts); ?> of
                        <?= $totalProducts; ?>
                    </p>

                    <form method="get" class="flex flex-wrap gap-3" id="filters">
                        <!-- Type Filter -->
                        <select name="type" class="h-10 rounded border-gray-300 text-sm" id="type">
                            <option value="">All Types</option>
                            <option value="coffee" <?= $type === 'coffee' ? 'selected' : ''; ?>>Coffee</option>
                            <option value="religious" <?= $type === 'religious' ? 'selected' : ''; ?>>Religious Items
                            </option>
                        </select>

                        <!-- Sort -->
                        <select name="sort" class="h-10 rounded border-gray-300 text-sm" id="sort">
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>Name A–Z</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : ''; ?>>Name Z–A</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : ''; ?>>Price Low–High
                            </option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : ''; ?>>Price High–Low
                            </option>
                        </select>

                        <!-- Search -->
                        <input type="text" name="search" id="serach" placeholder="Search products..."
                            value="<?= htmlspecialchars($search); ?>"
                            class="h-10 rounded border-gray-300 text-sm px-3" />
                        <!-- reset btn -->
                        <a href="products.php" class="h-10 rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">Reset</a>
                    </form>
                </div>


                <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php if (empty($products)): ?>
                        <div class="mx-auto text-center col-span-4 my-20">
                            <p class="text-color text-2xl">No products found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="group relative block overflow-hidden">
                                <!-- Wishlist Button -->
                                <?php $is_in_wishlist = isset($wishlist_product_ids) && in_array($product['id'], $wishlist_product_ids); ?>
                                <button type="button"
                                    class="wishlist-btn absolute end-4 top-4 z-10 rounded-full bg-white p-1.5 text-gray-900 transition hover:text-gray-900/75 hover:shadow-lg hover:scale-105 <?= $is_in_wishlist ? 'active' : '' ?>"
                                    data-product-id="<?= $product['id'] ?>" type="button">
                                    <span class="sr-only">Wishlist</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="<?= $is_in_wishlist ? 'red' : 'none' ?>"
                                        viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="<?= $is_in_wishlist ? 'red' : 'currentColor' ?>" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>

                                <!-- Product Image -->
                                <img src="uploaded_img/<?= htmlspecialchars($product['image']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="h-64 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-72" />

                                <div class="relative border border-gray-100 bg-white p-6">
                                    <!-- Price -->
                                    <p class="text-gray-700">
                                        ₱<?= number_format($product['price'] ?? 0, 2) ?>
                                    </p>

                                    <!-- Name -->
                                    <h3 class="mt-1.5 text-lg font-medium text-gray-900">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h3>

                                    <!-- Action Buttons -->
                                    <form class="mt-4 flex gap-4">
                                        <button type="button"
                                            class="block w-full rounded-sm bg-gray-100 px-4 py-3 text-sm font-medium text-gray-900 transition hover:scale-105 cart-btn"
                                            data-product-id="<?= $product['id'] ?>">
                                            Add to Cart
                                        </button>

                                        <button type="button"
                                            class="block w-full rounded-sm bg-color px-4 py-3 text-sm font-medium text-white transition hover:scale-105 buy-btn"
                                            data-product-id="<?= $product['id'] ?>">
                                            Buy Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>


                <?php
                // Build base query string without page
                $queryString = $_GET;
                unset($queryString['page']);
                $baseQuery = http_build_query($queryString);
                ?>

                <?php if ($totalPages > 1): ?>
                    <ol class="mt-8 flex justify-center gap-1 text-xs font-medium">
                        <?php if ($page > 1): ?>
                            <li>
                                <a href="?<?= $baseQuery ?>&page=<?= $page - 1; ?>"
                                    class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-100">&laquo;</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <a href="?<?= $baseQuery ?>&page=<?= $i; ?>"
                                    class="block size-8 rounded-sm border border-gray-100 text-center leading-8 <?= $i == $page ? 'bg-black text-white' : '' ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li>
                                <a href="?<?= $baseQuery ?>&page=<?= $page + 1; ?>"
                                    class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-100">&raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ol>
                <?php endif; ?>


            </div>
        </section>

    </main>
    <?php include 'footer.php'; ?>
    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // check if the filters are changed
        document.addEventListener('DOMContentLoaded', () => {
            const filterForm = document.getElementById('filters');

            const typeSelect = document.getElementById('type');
            const sortSelect = document.getElementById('sort');
            const searchInput = document.getElementById('serach');

            const initialType = typeSelect.value;
            const initialSort = sortSelect.value;
            const initialSearch = searchInput.value;

            function checkFilters() {
                if (typeSelect.value !== initialType || sortSelect.value !== initialSort || searchInput.value !== initialSearch) {
                    // Submit the form
                    filterForm.submit();
                }
            }

            typeSelect.addEventListener('change', checkFilters);
            sortSelect.addEventListener('change', checkFilters);
            searchInput.addEventListener('input', checkFilters);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.wishlist-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.dataset.productId;

                    // Toggle filled heart locally
                    const svg = this.querySelector('svg');
                    const isActive = this.classList.toggle('active');

                    if (isActive) {
                        // turn heart red
                        svg.setAttribute('fill', 'red');
                        svg.setAttribute('stroke', 'red');
                    } else {
                        // back to outline
                        svg.setAttribute('fill', 'none');
                        svg.setAttribute('stroke', 'currentColor');
                    }

                    // Optionally send AJAX to server
                    fetch('<?= FormatHelper::formatPath('products.php', 'services/wishlist.php') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=add_to_wishlist&product_id=${productId}`
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'error') {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            } else if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                            }
                        })
                        .catch(err => console.error(err));
                });
            });
            document.querySelectorAll('.cart-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.dataset.productId;

                    fetch('<?= FormatHelper::formatPath('products.php', 'services/cart.php') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=add_to_cart&product_id=${productId}`
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'error') {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            } else if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        })
                        .catch(err => console.error(err));
                });
            });
            document.querySelectorAll('.buy-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.dataset.productId;

                    fetch('<?= FormatHelper::formatPath('products.php', 'services/cart.php') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=add_to_cart&product_id=${productId}`
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'error') {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            } else if (data.status === 'success') {
                                // Redirect to cart page
                                window.location.href = 'cart.php';
                            }
                        })
                        .catch(err => console.error(err));
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
</body>

</html>