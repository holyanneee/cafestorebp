<el-dialog>
    <dialog id="customize-modal-<?= $item['id'] ?>" aria-labelledby="dialog-title"
        class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
        <el-dialog-backdrop
            class="fixed inset-0 bg-gray-500/75 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>
        <div tabindex="0"
            class="flex min-h-full  items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
            <el-dialog-panel
                class="relative w-[850px] transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 data-closed:sm:translate-y-0 data-closed:sm:scale-95">
                <header>
                    <h2 class="text-lg font-medium leading-6 text-gray-900 p-4 border-b">
                        Customize: <?= $item['name'] ?>
                    </h2>
                </header>
                <form target="" method="POST" id="customize-form-<?= $item['id'] ?>">
                    <input type="hidden" name="action" value="customize_cart_item">
                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                    <div class="bg-white p-3 sm:p-4">
                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2 lg:gap-4">
                            <div>
                                <div class="flex flex-col space-x-3">
                                    <legend class="sr-only">Cup Sizes</legend>
                                    <p class="text-sm font-medium text-gray-700 mr-3">Cup Size</p>
                                    <fieldset class="flex flex-wrap gap-2">
                                        <?php foreach ($product_cup_sizes as $product_cup_size => $product_cup_price): ?>
                                            <div>
                                                <label for="<?= $product_cup_size ?>"
                                                    class="flex items-center justify-between gap-4 rounded border border-gray-300 bg-white p-3 text-sm font-medium shadow-sm transition-colors hover:bg-gray-50 has-checked:border-blue-600 has-checked:ring-1 has-checked:ring-blue-600">
                                                    <p class="text-gray-700"><?= ucfirst($product_cup_size) ?></p>

                                                    <p class="text-gray-900">₱<?= $product_cup_price ?></p>

                                                    <input type="radio" name="cup-size"
                                                        value="<?= $product_cup_size ?>"
                                                        id="<?= $product_cup_size ?>"
                                                        <?= $product_cup_size == $cup_size ? 'checked' : '' ?>
                                                        />
                                                </label>
                                            </div>
                                        <?php endforeach; ?>

                                    </fieldset>
                                </div>
                                <div class="mt-4 flex flex-col space-x-3">
                                    <legend class="sr-only">Ingredients</legend>
                                    <p class="text-sm font-medium text-gray-700 mr-3">Ingredients</p>
                                    <fieldset class="flex flex-wrap gap-2">
                                        <?php foreach ($product_ingredients as $ingredient): ?>
                                            <div class="flex flex-col p-3 text-sm font-medium">
                                                    <p class="text-gray-700"><?= ucfirst($ingredient['name']) ?></p>

                                                    <div class="flex flex-wrap gap-3 mt-3">
                                                    <?php foreach (['less', 'regular', 'extra'] as $level): ?>
                                                        <input type="radio"
                                                            name="ingredient-<?= strtolower($ingredient['name']) ?>"
                                                            value="<?= $level ?>"
                                                            id="<?= strtolower($ingredient['name']) ?>-<?= $level ?>"
                                                            <?= (isset($ingredient['level']) && ($ingredient['level']) === $level) ? 'checked' : ($level === 'Regular' ? 'checked' : '') ?>
                                                            />
                                                        <label for="<?= strtolower($ingredient['name']) ?>-<?= $level ?>">
                                                            <span class="text-gray-700"><?= ucfirst($level) ?></span>
                                                        </label>

                                                    <?php endforeach; ?>
                                                    </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </fieldset>
                                </div>

                                <div class="mt-4">
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">
                                        Quantity
                                    </label>
                                    <div class="mt-1 max-w-[80px]">
                                        <input type="number" id="quantity" name="quantity" min="1"
                                            value="<?= $item['quantity'] ?>"
                                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                    </div>
                                </div>

                            </div>
                            <div>
                                <div class="flex flex-col space-x-3">
                                    <legend class="sr-only">Add-Ons</legend>
                                    <p class="text-sm font-medium text-gray-700 mr-3">Add-Ons</p>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($add_ons as  $add_on): ?>
                                            <div>
                                                <label for="add-on-<?= $add_on['name'] ?>"
                                                    class="flex items-center justify-between gap-4 rounded border border-gray-300 bg-white p-3 text-sm font-medium shadow-sm transition-colors hover:bg-gray-50 has-checked:border-blue-600 has-checked:ring-1 has-checked:ring-blue-600">
                                                    <p class="text-gray-700"><?= ucfirst($add_on['name']) ?></p>

                                                    <p class="text-gray-900">₱<?= number_format($add_on['price'], 2) ?></p>

                                                    <input type="checkbox"
                                                        name="add-on-<?= $add_on['id'] ?>"
                                                        value="<?= $add_on['name'] ?>"
                                                        id="add-on-<?= $add_on['name'] ?>"
                                                        <?= (isset($selected_add_ons[$add_on['id']]) && $selected_add_ons[$add_on['id']]['name'] ===  $add_on['name']) ? 'checked' : '' ?>
                                                        />
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="mt-4">
                            <label for="special-instruction" class="block text-sm font-medium text-gray-700">
                                Special Instruction:
                            </label>
                            <div class="mt-1">
                                <textarea id="special-instruction" name="special-instruction" rows="4"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                    placeholder="e.g., No sugar, extra hot, etc."><?= $item['special_instruction'] ? htmlspecialchars($item['special_instruction']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" command="close" commandFor="customize-modal-<?= $item['id'] ?>"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-blue-500 sm:ml-3 sm:w-auto">
                            Save Changes
                        </button>
                        <button type="button" command="close" commandFor="customize-modal-<?= $item['id'] ?>"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs inset-ring inset-ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </form>
            </el-dialog-panel>
        </div>
    </dialog>
</el-dialog>