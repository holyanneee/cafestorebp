<footer class="bg-color">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2">
            <div
                class="border-b border-gray-100 py-8 lg:order-last lg:border-s lg:border-b-0 lg:py-16 lg:ps-16 dark:border-gray-800">
                <div class="block text-teal-600 lg:hidden dark:text-teal-300">

                </div>

                <div class="mt-8 space-y-4 lg:mt-0">
                    <span class="hidden h-1 w-10 rounded-sm bg-teal-500 lg:block"></span>

                    <div>
                        <h2 class="text-2xl font-medium text-gray-900 dark:text-white">Anak ng Birhen</h2>

                        <p class="mt-4 max-w-lg text-gray-500 dark:text-gray-400">
                            <!-- description -->
                            short description
                        </p>
                        <!-- button -->
                        <div class="mt-6">
                            <a href="" class="inline-block rounded bg-white px-3 py-2 text-sm font-medium text-color transition hover:bg-gray-300
                                 focus:outline-none focus:ring">
                                View Products
                            </a>

                        </div>
                    </div>

                </div>
            </div>

            <div class="py-8 lg:py-16 lg:pe-16">
                <div class="text-white">
                    <img src="images/kape_milag.jpg" alt="Kape Milagrosa Logo"
                        class="h-20 w-20 rounded-full bg-white p-1">
                    <a href="index.php" class="text-2xl font-bold">Kape Milagrosa</a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-8 sm:grid-cols-3">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Categories</p>

                        <ul class="mt-6 space-y-4 text-sm">
                            <?php
                            $topCategoryNames = [];
                            if (isset($top_categories) && is_array($top_categories)) {
                                $topCategoryNames = array_column($top_categories, 'name');
                            }
                            if (!isset($categories)) {
                                $categories = $_SESSION['categories'];

                            }
                            ?>
                            <?php foreach ($categories as $category): ?>
                                <!-- check if the category name is on $top_categories, if true dont display it -->
                                <?php if (in_array($category['name'], $topCategoryNames, true))
                                    continue; ?>
                                <li>
                                    <a href="/category/<?= htmlspecialchars($category['name']); ?>"
                                        class="text-gray-700 transition hover:opacity-75 dark:text-gray-200">
                                        <?= htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>

                        </ul>
                    </div>

                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Company</p>

                        <ul class="mt-6 space-y-4 text-sm">
                            <li>
                                <a href="#" class="text-gray-700 transition hover:opacity-75 dark:text-gray-200">
                                    About
                                </a>
                            </li>

                            <li>
                                <a href="#" class="text-gray-700 transition hover:opacity-75 dark:text-gray-200">
                                    Meet the Team
                                </a>
                            </li>
                        </ul>

                        <p class="mt-6 font-medium text-gray-900 dark:text-white">Helpful Links</p>
                        <ul class="mt-6 space-y-4 text-sm">
                            <li>
                                <a href="https://www.facebook.com/KapeMilagrosa/reviews"
                                    class="text-gray-700 transition hover:opacity-75 dark:text-gray-200">
                                    Reviews
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Socials and
                            Contact
                        </p>

                        <ul class="mt-6 space-y-4 text-sm">
                            <li>
                                <a href="https://www.facebook.com/KapeMilagrosa" target="_blank"
                                    class="text-gray-700 transition hover:opacity-75 dark:text-gray-200 flex items-center gap-2">
                                    <span class="sr-only">Facebook</span>

                                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>
                                        Facebook
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="#"
                                    class="text-gray-700 transition hover:opacity-75 dark:text-gray-200 flex items-center gap-2">
                                    <span class="sr-only">Contact no.</span>
                                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M1.5 4.5A3 3 0 014.5 1.5h3a3 3 0 013 3v3a3 3 0 01-3 3H6l-1.5 1.5v2.25a11.25 11.25 0 0011.25 11.25h2.25L18 19.5v-2.25a3 3 0 013-3h3a3 3 0 013 3v3a3 3 0 01-3 3h-2C7.373 22.5 1.5 16.627 1.5 9V4.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>
                                        0905 877 4385
                                    </span>
                                </a>
                            </li>
                            <!-- <li>
                                <a href="#" class="text-gray-700 transition hover:opacity-75 dark:text-gray-200 flex items-center gap-2">
                                    <span class="sr-only">Email</span>
                                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path
                                            d="M1.5 8.67v8.66c0 .92.7 1.67 1.58 1.67h18.84c.88 0 1.58-.75 1.58-1.67V8.67l-11.22 6.26a3 3 0 01-3.16 0L1.5 8.67z" />
                                        <path
                                            d="M22.5 6H1.5c-.88 0-1.58.75-1.58 1.67v.01l11.22 6.26a3 3 0 003.16 0l11.22-6.26v-.01c0-.92-.7-1.67-1.58-1.67z" />
                                    </svg>
                                    <span>
                                        Email
                                    </span>
                                </a>
                            </li> -->

                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-100 pt-8 dark:border-gray-800">

                    <p class=" text-xs text-gray-500 dark:text-gray-400">
                        &copy; &copy; <?= date('Y'); ?> Kape Milagrosa | All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>