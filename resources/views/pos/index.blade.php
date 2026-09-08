<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Terminal - SmartPOS</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden select-none">

    <!-- Top Navigation Bar -->
    <header class="h-14 bg-slate-900 text-white flex items-center justify-between px-4 sm:px-6 flex-shrink-0 shadow-md">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition group" title="Back to Dashboard">
                <i class="fa-solid fa-arrow-left text-sm group-hover:-translate-x-0.5 transition-transform"></i>
                <span class="text-xs font-semibold uppercase tracking-wider">Dashboard</span>
            </a>
            <div class="h-5 w-px bg-slate-700"></div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-500 flex items-center justify-center text-white font-black text-sm">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <span class="font-black tracking-wider text-base">Smart<span class="text-emerald-400">POS</span></span>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Active Terminal
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs">
            <div class="text-slate-400 hidden sm:block">
                <i class="fa-regular fa-clock mr-1 text-emerald-400"></i>
                <span id="posClock"></span>
            </div>
            <a href="{{ route('sales.index') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg transition flex items-center gap-1.5 font-medium">
                <i class="fa-solid fa-receipt text-emerald-400"></i>
                <span>Sales History</span>
            </a>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
        
        <!-- LEFT: Product Catalog & Search (60%) -->
        <div class="flex-1 flex flex-col overflow-hidden bg-slate-50 border-r border-slate-200">
            
            <!-- Search & Barcode Scan Header -->
            <div class="p-4 bg-white border-b border-slate-200 shadow-sm space-y-3">
                <div class="relative">
                    <i class="fa-solid fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                    <input type="text" id="barcodeSearch" placeholder="Scan barcode or type product name... (Press Enter)" 
                           autofocus
                           class="w-full pl-12 pr-10 py-3 text-base bg-slate-50 border-2 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition">
                    <button type="button" onclick="clearSearch()" id="clearSearchBtn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-bold" id="categoryTabs">
                    <button type="button" onclick="filterCategory('all')" 
                            class="cat-tab active px-3.5 py-1.5 rounded-lg bg-slate-900 text-white shadow-sm transition whitespace-nowrap" data-cat="all">
                        All Items ({{ count($products) }})
                    </button>
                    @foreach ($categories as $cat)
                        <button type="button" onclick="filterCategory('{{ $cat->id }}')" 
                                class="cat-tab px-3.5 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition whitespace-nowrap" data-cat="{{ $cat->id }}">
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 p-4 overflow-y-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3" id="productsGrid">
                    @forelse ($products as $product)
                        <div class="product-card bg-white rounded-xl border border-slate-200/90 hover:border-emerald-500 hover:shadow-md transition p-3.5 flex flex-col justify-between cursor-pointer group {{ $product->quantity <= 0 ? 'opacity-60 cursor-not-allowed' : '' }}"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->name }}"
                             data-barcode="{{ $product->barcode }}"
                             data-price="{{ $product->selling_price }}"
                             data-stock="{{ $product->quantity }}"
                             data-category="{{ $product->category_id }}"
                             onclick="addToCart({{ $product->id }})">
                            
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-600 truncate max-w-[120px]">
                                        {{ $product->category->name ?? 'General' }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400 truncate">{{ $product->barcode }}</span>
                                </div>
                                <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition line-clamp-2 leading-snug">
                                    {{ $product->name }}
                                </h4>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-slate-400 block -mb-0.5">Price</span>
                                    <span class="font-black text-slate-900 text-sm">Rs. {{ number_format($product->selling_price, 2) }}</span>
                                </div>
                                <div>
                                    @if ($product->quantity <= 0)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-700">Out</span>
                                    @else
                                        <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white font-bold text-xs transition flex items-center gap-1">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                            <span class="text-[11px]">{{ $product->quantity }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-2"></i>
                            <p class="text-sm font-medium">No products found in inventory.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT: Live Cart & Checkout (40%) -->
        <div class="w-full lg:w-[450px] xl:w-[480px] bg-white flex flex-col h-full shadow-2xl flex-shrink-0 border-l border-slate-200">
            
            <!-- Customer Selection Header -->
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-2">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Customer</label>
                    <div class="flex items-center gap-1.5">
                        <select id="customerSelect" class="flex-1 px-3 py-1.5 text-xs font-semibold bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Walk-in Customer (Guest)</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'No phone' }})</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="openQuickCustomerModal()" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg border border-emerald-200 text-xs font-bold transition" title="Quick Add Customer">
                            <i class="fa-solid fa-user-plus"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1 text-right">Cart</label>
                    <button type="button" onclick="clearCart()" class="text-xs font-bold text-rose-500 hover:text-rose-700 py-1.5 px-2 hover:bg-rose-50 rounded transition" title="Empty Cart">
                        <i class="fa-solid fa-trash-can mr-1"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Cart Items List (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2" id="cartContainer">
                <div id="emptyCartMessage" class="h-full flex flex-col items-center justify-center text-slate-400 py-16">
                    <i class="fa-solid fa-cart-shopping text-5xl text-slate-200 mb-3"></i>
                    <p class="text-sm font-semibold text-slate-500">Cart is empty</p>
                    <p class="text-xs text-slate-400 mt-0.5">Scan a barcode or click any product to add.</p>
                </div>
                <!-- Dynamic cart rows will appear here -->
            </div>

            <!-- Billing & Payment Panel -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                
                <!-- Financial Summary -->
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Items Count:</span>
                        <span class="font-bold text-slate-800" id="cartItemsCount">0 items</span>
                    </div>
                    <div class="flex items-center justify-between text-base pt-2 border-t border-slate-200 font-bold">
                        <span class="text-slate-800">Total Payable:</span>
                        <span class="text-2xl font-black text-emerald-600" id="cartTotalDisplay">Rs. 0.00</span>
                    </div>
                </div>

                <!-- Payment Method Toggle -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="setPaymentMethod('cash')" id="btnMethod_cash" 
                                class="pay-method-btn active py-2 text-xs font-bold rounded-lg border-2 border-emerald-500 bg-emerald-50 text-emerald-800 flex items-center justify-center gap-1.5 transition">
                            <i class="fa-solid fa-money-bill-wave"></i> Cash
                        </button>
                        <button type="button" onclick="setPaymentMethod('card')" id="btnMethod_card" 
                                class="pay-method-btn py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 flex items-center justify-center gap-1.5 transition">
                            <i class="fa-solid fa-credit-card"></i> Card
                        </button>
                        <button type="button" onclick="setPaymentMethod('bank_transfer')" id="btnMethod_bank_transfer" 
                                class="pay-method-btn py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 flex items-center justify-center gap-1.5 transition">
                            <i class="fa-solid fa-building-columns"></i> Transfer
                        </button>
                    </div>
                </div>

                <!-- Cash Received & Change Return Calculator -->
                <div class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Paid / Received (Rs.)</label>
                            <input type="number" step="0.01" min="0" id="paidAmountInput" oninput="calculateChange()" placeholder="0.00"
                                   class="w-full px-3 py-2 text-sm font-bold text-slate-800 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Change Return (Rs.)</label>
                            <div class="px-3 py-2 text-sm font-black text-slate-800 bg-slate-100 border border-slate-200 rounded-lg" id="changeAmountDisplay">
                                Rs. 0.00
                            </div>
                        </div>
                    </div>

                    <!-- Quick Cash Amounts Shortcuts -->
                    <div class="flex items-center gap-1.5 pt-1 text-[11px]">
                        <button type="button" onclick="setQuickCash('exact')" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-slate-600 font-bold transition">Exact</button>
                        <button type="button" onclick="addCashShortcut(500)" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-slate-600 font-bold transition">+500</button>
                        <button type="button" onclick="addCashShortcut(1000)" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-slate-600 font-bold transition">+1,000</button>
                        <button type="button" onclick="addCashShortcut(5000)" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-slate-600 font-bold transition">+5,000</button>
                    </div>
                </div>

                <!-- COMPLETE SALE BUTTON -->
                <button type="button" onclick="submitCheckout()" id="checkoutBtn" disabled
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-600/25 transition duration-150 flex items-center justify-center gap-2 group">
                    <i class="fa-solid fa-circle-check text-base group-hover:scale-110 transition-transform"></i>
                    <span>COMPLETE SALE</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt / Invoice Success Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="p-4 bg-emerald-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span class="font-bold text-sm">Sale Completed!</span>
                </div>
                <button type="button" onclick="closeReceiptModal()" class="text-white/80 hover:text-white text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Receipt Content (Thermal 80mm Style) -->
            <div class="p-6 overflow-y-auto font-mono text-xs text-slate-800 space-y-4" id="printableSlip">
                <div class="text-center space-y-1 pb-3 border-b border-dashed border-slate-300">
                    <h3 class="font-black text-base uppercase tracking-wider font-sans text-slate-900">SMARTPOS STORE</h3>
                    <p class="text-[11px] text-slate-500">Retail Point of Sale System</p>
                    <p class="text-[10px] text-slate-400">Tel: +92 300 1234567 • info@smartpos.com</p>
                </div>

                <div class="space-y-1 text-[11px] pb-2 border-b border-dashed border-slate-300">
                    <div class="flex justify-between">
                        <span>Invoice #:</span>
                        <span class="font-bold" id="receiptInvoice"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Date & Time:</span>
                        <span id="receiptDate"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Customer:</span>
                        <span class="font-bold" id="receiptCustomer"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Payment:</span>
                        <span class="font-bold uppercase" id="receiptPayment"></span>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="w-full text-left text-[11px]">
                    <thead class="border-b border-slate-300 text-slate-500">
                        <tr>
                            <th class="py-1">Item</th>
                            <th class="py-1 text-center">Qty</th>
                            <th class="py-1 text-right">Price</th>
                            <th class="py-1 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="receiptItems" class="divide-y divide-slate-100">
                        <!-- Injected via JS -->
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="space-y-1 pt-2 border-t border-dashed border-slate-300 text-[11px]">
                    <div class="flex justify-between font-black text-sm">
                        <span>Grand Total:</span>
                        <span id="receiptTotal"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Amount Paid:</span>
                        <span id="receiptPaid"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Change Given:</span>
                        <span id="receiptChange"></span>
                    </div>
                </div>

                <div class="text-center pt-4 border-t border-dashed border-slate-300 space-y-1 text-[10px] text-slate-400">
                    <p>Thank you for your business!</p>
                    <p>SmartPOS • Powered by Laravel 12</p>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                <button type="button" onclick="closeReceiptModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded-lg transition">
                    New Sale (Esc)
                </button>
                <div class="flex items-center gap-2">
                    <a id="receiptFullInvoiceLink" href="#" target="_blank" class="px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition flex items-center gap-1">
                        <i class="fa-solid fa-file-invoice"></i> A4 Invoice
                    </a>
                    <button type="button" onclick="printReceiptSlip()" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-print"></i> Print Slip
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Customer Modal -->
    <div id="quickCustomerModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full shadow-2xl overflow-hidden p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-600"></i> Quick Add Customer
                </h4>
                <button type="button" onclick="closeQuickCustomerModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="quickCustomerForm" onsubmit="saveQuickCustomer(event)" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="qc_name" required placeholder="Customer name" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Phone Number</label>
                    <input type="text" id="qc_phone" placeholder="+92 300 1234567" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                    <input type="email" id="qc_email" placeholder="customer@example.com" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeQuickCustomerModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- POS Javascript Engine -->
    <script>
        const productsCatalog = @json($products);
        let cart = [];
        let selectedPaymentMethod = 'cash';
        let latestSaleData = null;

        // Clock display
        function updateClock() {
            const now = new Date();
            document.getElementById('posClock').innerText = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Real-time Barcode & Search
        const searchInput = document.getElementById('barcodeSearch');
        const clearBtn = document.getElementById('clearSearchBtn');

        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase().trim();
            clearBtn.classList.toggle('hidden', val.length === 0);
            filterProductsGrid(val);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (!query) return;

                // Match exact barcode or single match
                const match = productsCatalog.find(p => p.barcode === query || p.name.toLowerCase() === query.toLowerCase());
                if (match) {
                    addToCart(match.id);
                    this.value = '';
                    clearBtn.classList.add('hidden');
                    filterProductsGrid('');
                }
            }
        });

        function clearSearch() {
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            filterProductsGrid('');
            searchInput.focus();
        }

        function filterProductsGrid(term) {
            const activeTab = document.querySelector('.cat-tab.active');
            const activeCatId = activeTab ? activeTab.getAttribute('data-cat') : 'all';

            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const barcode = card.getAttribute('data-barcode').toLowerCase();
                const catId = card.getAttribute('data-category');

                const matchesSearch = !term || name.includes(term) || barcode.includes(term);
                const matchesCat = activeCatId === 'all' || catId === activeCatId;

                card.style.display = (matchesSearch && matchesCat) ? 'flex' : 'none';
            });
        }

        function filterCategory(catId) {
            document.querySelectorAll('.cat-tab').forEach(btn => {
                if (btn.getAttribute('data-cat') === String(catId)) {
                    btn.classList.add('active', 'bg-slate-900', 'text-white');
                    btn.classList.remove('bg-white', 'text-slate-600');
                } else {
                    btn.classList.remove('active', 'bg-slate-900', 'text-white');
                    btn.classList.add('bg-white', 'text-slate-600');
                }
            });
            filterProductsGrid(searchInput.value.toLowerCase().trim());
        }

        // Cart Management
        function addToCart(productId) {
            const product = productsCatalog.find(p => p.id === productId);
            if (!product) return;

            if (product.quantity <= 0) {
                alert(`Cannot add '${product.name}'. Product is currently out of stock.`);
                return;
            }

            const existing = cart.find(item => item.id === productId);
            if (existing) {
                if (existing.quantity >= product.quantity) {
                    alert(`Cannot add more. Only ${product.quantity} units available in stock.`);
                    return;
                }
                existing.quantity++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    barcode: product.barcode,
                    price: parseFloat(product.selling_price),
                    stock: product.quantity,
                    quantity: 1,
                });
            }

            renderCart();
        }

        function updateCartQty(productId, newQty) {
            const item = cart.find(i => i.id === productId);
            if (!item) return;

            newQty = parseInt(newQty);
            if (isNaN(newQty) || newQty <= 0) {
                removeFromCart(productId);
                return;
            }

            if (newQty > item.stock) {
                alert(`Only ${item.stock} units available in stock.`);
                item.quantity = item.stock;
            } else {
                item.quantity = newQty;
            }

            renderCart();
        }

        function removeFromCart(productId) {
            cart = cart.filter(i => i.id !== productId);
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartContainer');
            const emptyMsg = document.getElementById('emptyCartMessage');
            const checkoutBtn = document.getElementById('checkoutBtn');

            if (cart.length === 0) {
                container.innerHTML = '';
                container.appendChild(emptyMsg);
                emptyMsg.classList.remove('hidden');
                document.getElementById('cartItemsCount').innerText = '0 items';
                document.getElementById('cartTotalDisplay').innerText = 'Rs. 0.00';
                document.getElementById('paidAmountInput').value = '';
                document.getElementById('changeAmountDisplay').innerText = 'Rs. 0.00';
                checkoutBtn.disabled = true;
                return;
            }

            emptyMsg.classList.add('hidden');
            let html = '';
            let total = 0;
            let totalQty = 0;

            cart.forEach(item => {
                const subtotal = item.quantity * item.price;
                total += subtotal;
                totalQty += item.quantity;

                html += `
                    <div class="p-2.5 bg-white border border-slate-200 rounded-xl shadow-xs flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-xs text-slate-800 truncate">${item.name}</h5>
                            <span class="text-[10px] text-slate-400 font-mono">Rs. ${item.price.toFixed(2)} each</span>
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
                            <button type="button" onclick="updateCartQty(${item.id}, ${item.quantity - 1})" class="w-5 h-5 flex items-center justify-center bg-white rounded text-slate-600 hover:text-rose-600 text-xs font-bold shadow-xs">
                                -
                            </button>
                            <input type="number" min="1" max="${item.stock}" value="${item.quantity}" onchange="updateCartQty(${item.id}, this.value)"
                                   class="w-8 text-center text-xs font-bold bg-transparent border-0 focus:outline-none p-0">
                            <button type="button" onclick="updateCartQty(${item.id}, ${item.quantity + 1})" class="w-5 h-5 flex items-center justify-center bg-white rounded text-slate-600 hover:text-emerald-600 text-xs font-bold shadow-xs">
                                +
                            </button>
                        </div>

                        <!-- Subtotal -->
                        <div class="text-right min-w-[70px]">
                            <span class="font-black text-xs text-slate-900 block">Rs. ${subtotal.toFixed(2)}</span>
                            <button type="button" onclick="removeFromCart(${item.id})" class="text-[10px] text-slate-400 hover:text-rose-600 transition">
                                Remove
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            document.getElementById('cartItemsCount').innerText = `${totalQty} units (${cart.length} unique)`;
            document.getElementById('cartTotalDisplay').innerText = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            checkoutBtn.disabled = false;

            // Update cash if exact
            if (!document.getElementById('paidAmountInput').value) {
                document.getElementById('paidAmountInput').value = total.toFixed(2);
            }
            calculateChange();
        }

        // Payment Calculation
        function setPaymentMethod(method) {
            selectedPaymentMethod = method;
            document.querySelectorAll('.pay-method-btn').forEach(btn => {
                btn.classList.remove('active', 'border-emerald-500', 'bg-emerald-50', 'text-emerald-800');
                btn.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
            });

            const activeBtn = document.getElementById(`btnMethod_${method}`);
            if (activeBtn) {
                activeBtn.classList.add('active', 'border-2', 'border-emerald-500', 'bg-emerald-50', 'text-emerald-800');
                activeBtn.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
            }
        }

        function getCartTotal() {
            return cart.reduce((sum, i) => sum + (i.quantity * i.price), 0);
        }

        function calculateChange() {
            const total = getCartTotal();
            const paid = parseFloat(document.getElementById('paidAmountInput').value) || 0;
            const change = paid - total;
            const changeDisplay = document.getElementById('changeAmountDisplay');

            if (change >= 0) {
                changeDisplay.innerText = 'Rs. ' + change.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                changeDisplay.className = 'px-3 py-2 text-sm font-black text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg';
            } else {
                changeDisplay.innerText = '- Rs. ' + Math.abs(change).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                changeDisplay.className = 'px-3 py-2 text-sm font-black text-rose-600 bg-rose-50 border border-rose-200 rounded-lg';
            }
        }

        function setQuickCash(type) {
            const total = getCartTotal();
            if (type === 'exact') {
                document.getElementById('paidAmountInput').value = total.toFixed(2);
                calculateChange();
            }
        }

        function addCashShortcut(amount) {
            const current = parseFloat(document.getElementById('paidAmountInput').value) || 0;
            document.getElementById('paidAmountInput').value = (current + amount).toFixed(2);
            calculateChange();
        }

        // Checkout Action
        async function submitCheckout() {
            if (cart.length === 0) return;

            const total = getCartTotal();
            const paid = parseFloat(document.getElementById('paidAmountInput').value) || 0;

            if (paid < total) {
                alert(`Paid amount (Rs. ${paid.toFixed(2)}) is less than total payable (Rs. ${total.toFixed(2)}).`);
                return;
            }

            const checkoutBtn = document.getElementById('checkoutBtn');
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Processing Sale...`;

            const payload = {
                customer_id: document.getElementById('customerSelect').value || null,
                payment_method: selectedPaymentMethod,
                paid_amount: paid,
                items: cart.map(i => ({ id: i.id, quantity: i.quantity })),
            };

            try {
                const res = await fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Error completing sale.');
                    alert(msg);
                    checkoutBtn.disabled = false;
                    checkoutBtn.innerHTML = `<i class="fa-solid fa-circle-check text-base"></i> <span>COMPLETE SALE</span>`;
                    return;
                }

                // Update product catalog stock in memory and DOM
                data.sale.items.forEach(sold => {
                    const found = productsCatalog.find(p => p.barcode === sold.barcode);
                    if (found) {
                        found.quantity = sold.remaining_stock;
                        const card = document.querySelector(`.product-card[data-id="${found.id}"]`);
                        if (card) {
                            card.setAttribute('data-stock', found.quantity);
                            const badge = card.querySelector('.mt-3 div:last-child');
                            if (found.quantity <= 0) {
                                card.classList.add('opacity-60', 'cursor-not-allowed');
                                badge.innerHTML = `<span class="px-2 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-700">Out</span>`;
                            } else {
                                badge.innerHTML = `
                                    <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white font-bold text-xs transition flex items-center gap-1">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                        <span class="text-[11px]">${found.quantity}</span>
                                    </span>
                                `;
                            }
                        }
                    }
                });

                // Open Receipt Modal
                latestSaleData = data;
                displayReceipt(data.sale, data.receipt_url, data.invoice_url);

                // Reset Cart
                clearCart();
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = `<i class="fa-solid fa-circle-check text-base"></i> <span>COMPLETE SALE</span>`;

            } catch (err) {
                console.error(err);
                alert('Network or server error processing sale.');
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = `<i class="fa-solid fa-circle-check text-base"></i> <span>COMPLETE SALE</span>`;
            }
        }

        // Receipt Modal
        function displayReceipt(sale, receiptUrl, invoiceUrl) {
            document.getElementById('receiptInvoice').innerText = sale.invoice_number;
            document.getElementById('receiptDate').innerText = sale.date;
            document.getElementById('receiptCustomer').innerText = sale.customer;
            document.getElementById('receiptPayment').innerText = sale.payment_method;
            document.getElementById('receiptTotal').innerText = 'Rs. ' + parseFloat(sale.total_amount).toFixed(2);
            document.getElementById('receiptPaid').innerText = 'Rs. ' + parseFloat(sale.paid_amount).toFixed(2);
            document.getElementById('receiptChange').innerText = 'Rs. ' + parseFloat(sale.change_amount).toFixed(2);
            document.getElementById('receiptFullInvoiceLink').href = invoiceUrl;

            let itemsHtml = '';
            sale.items.forEach(i => {
                itemsHtml += `
                    <tr>
                        <td class="py-1.5 font-bold">${i.name}</td>
                        <td class="py-1.5 text-center">${i.quantity}</td>
                        <td class="py-1.5 text-right">Rs. ${parseFloat(i.price).toFixed(2)}</td>
                        <td class="py-1.5 text-right font-bold">Rs. ${parseFloat(i.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            document.getElementById('receiptItems').innerHTML = itemsHtml;

            const modal = document.getElementById('receiptModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReceiptModal() {
            const modal = document.getElementById('receiptModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            searchInput.focus();
        }

        function printReceiptSlip() {
            if (latestSaleData && latestSaleData.receipt_url) {
                const win = window.open(latestSaleData.receipt_url, '_blank');
                win.focus();
            }
        }

        // Quick Customer Modal
        function openQuickCustomerModal() {
            document.getElementById('quickCustomerModal').classList.remove('hidden');
            document.getElementById('quickCustomerModal').classList.add('flex');
            document.getElementById('qc_name').focus();
        }

        function closeQuickCustomerModal() {
            document.getElementById('quickCustomerModal').classList.add('hidden');
            document.getElementById('quickCustomerModal').classList.remove('flex');
            document.getElementById('quickCustomerForm').reset();
        }

        async function saveQuickCustomer(e) {
            e.preventDefault();
            const name = document.getElementById('qc_name').value.trim();
            const phone = document.getElementById('qc_phone').value.trim();
            const email = document.getElementById('qc_email').value.trim();

            if (!name) return;

            try {
                const res = await fetch("{{ route('customers.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ name, phone, email }),
                });

                const data = await res.json();
                if (data.success && data.customer) {
                    const select = document.getElementById('customerSelect');
                    const opt = document.createElement('option');
                    opt.value = data.customer.id;
                    opt.innerText = `${data.customer.name} (${data.customer.phone || 'No phone'})`;
                    opt.selected = true;
                    select.appendChild(opt);

                    closeQuickCustomerModal();
                } else {
                    alert('Error saving customer.');
                }
            } catch (err) {
                console.error(err);
                alert('Could not save customer.');
            }
        }

        // Global hotkeys (Esc to close modals)
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptModal();
                closeQuickCustomerModal();
            }
        });
    </script>
</body>
</html>
