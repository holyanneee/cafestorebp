<?php
@include 'config.php';
session_start();
require_once 'helpers/FormatHelper.php';

use Helpers\FormatHelper;


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
                    <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">Product Collection</h2>

                    <p class="mt-4 max-w-md text-gray-500">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Itaque praesentium cumque iure
                        dicta incidunt est ipsam, officia dolor fugit natus?
                    </p>
                </header>

                <div class="mt-8">
                    <p class="text-sm text-gray-500">Showing <span> 4 </span> of 40</p>
                </div>

                <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <li>
                        <a href="#" class="group block overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                alt=""
                                class="h-[350px] w-full object-cover transition duration-500 group-hover:scale-105 sm:h-[450px]" />

                            <div class="relative bg-white pt-3">
                                <h3 class="text-xs text-gray-700 group-hover:underline group-hover:underline-offset-4">
                                    Basic Tee
                                </h3>

                                <p class="mt-2">
                                    <span class="sr-only"> Regular Price </span>

                                    <span class="tracking-wider text-gray-900"> £24.00 GBP </span>
                                </p>
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="group block overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                alt=""
                                class="h-[350px] w-full object-cover transition duration-500 group-hover:scale-105 sm:h-[450px]" />

                            <div class="relative bg-white pt-3">
                                <h3 class="text-xs text-gray-700 group-hover:underline group-hover:underline-offset-4">
                                    Basic Tee
                                </h3>

                                <p class="mt-2">
                                    <span class="sr-only"> Regular Price </span>

                                    <span class="tracking-wider text-gray-900"> £24.00 GBP </span>
                                </p>
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="group block overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                alt=""
                                class="h-[350px] w-full object-cover transition duration-500 group-hover:scale-105 sm:h-[450px]" />

                            <div class="relative bg-white pt-3">
                                <h3 class="text-xs text-gray-700 group-hover:underline group-hover:underline-offset-4">
                                    Basic Tee
                                </h3>

                                <p class="mt-2">
                                    <span class="sr-only"> Regular Price </span>

                                    <span class="tracking-wider text-gray-900"> £24.00 GBP </span>
                                </p>
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="group block overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
                                alt=""
                                class="h-[350px] w-full object-cover transition duration-500 group-hover:scale-105 sm:h-[450px]" />

                            <div class="relative bg-white pt-3">
                                <h3 class="text-xs text-gray-700 group-hover:underline group-hover:underline-offset-4">
                                    Basic Tee
                                </h3>

                                <p class="mt-2">
                                    <span class="sr-only"> Regular Price </span>

                                    <span class="tracking-wider text-gray-900"> £24.00 GBP </span>
                                </p>
                            </div>
                        </a>
                    </li>
                </ul>

                <ol class="mt-8 flex justify-center gap-1 text-xs font-medium">
                    <li>
                        <a href="#"
                            class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-100">
                            <span class="sr-only">Prev Page</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="block size-8 rounded-sm border border-gray-100 text-center leading-8">
                            1
                        </a>
                    </li>

                    <li class="block size-8 rounded-sm border-black bg-black text-center leading-8 text-white">
                        2
                    </li>

                    <li>
                        <a href="#" class="block size-8 rounded-sm border border-gray-100 text-center leading-8">
                            3
                        </a>
                    </li>

                    <li>
                        <a href="#" class="block size-8 rounded-sm border border-gray-100 text-center leading-8">
                            4
                        </a>
                    </li>

                    <li>
                        <a href="#"
                            class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-100">
                            <span class="sr-only">Next Page</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </li>
                </ol>
            </div>
        </section>

    </main>
    <?php include 'footer.php'; ?>
    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    fetch('<?= FormatHelper::formatPath('index.php', 'services/wishlist.php') ?>', {
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

                    fetch('<?= FormatHelper::formatPath('index.php', 'services/cart.php') ?>', {
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

                    fetch('<?= FormatHelper::formatPath('index.php', 'services/cart.php') ?>', {
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