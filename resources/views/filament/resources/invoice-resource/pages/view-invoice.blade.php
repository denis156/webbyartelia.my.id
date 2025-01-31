<x-filament-panels::page>
    <style>
        /* A4 size constants */
        :root {
            --a4-width: 210mm;
            --a4-height: 297mm;
            --page-margin: 15mm;
        }

        /* Base styles */
        .print-area {
            width: var(--a4-width);
            min-height: var(--a4-height);
            margin: 0 auto;
            padding: var(--page-margin);
            box-sizing: border-box;
            background: white;
            color: black;
            overflow: hidden;
        }

        /* Screen-specific styles */
        @media screen {
            .print-area {
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                border: 1px solid rgb(243 244 246);
                margin: 2rem auto;
            }

            @media (max-width: 210mm) {
                .print-area {
                    width: 100%;
                    min-height: auto;
                    margin: 0;
                    padding: 10mm;
                }
            }
        }

        /* Print-specific styles */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                width: var(--a4-width);
                height: var(--a4-height);
                visibility: hidden;
            }

            .print-area {
                visibility: visible;
                position: absolute;
                left: 0;
                top: 0;
                width: var(--a4-width);
                height: var(--a4-height);
                padding: var(--page-margin);
                margin: 0;
                box-shadow: none;
                border: none;
                page-break-after: always;
            }

            /* Hide all non-print elements */
            nav,
            header:not(.print-area header),
            .filament-sidebar,
            .filament-topbar,
            .filament-header,
            .filament-main-topbar,
            .filament-global-search,
            .filament-footer,
            .print-hide,
            #navbar,
            .navigation,
            .nav-menu {
                display: none !important;
                visibility: hidden !important;
            }

            /* Ensure content fits within page */
            .print-area main {
                max-height: calc(var(--a4-height) - 2 * var(--page-margin) - 100mm);
                overflow: visible;
            }

            /* Ensure proper color printing */
            .print-area * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
        }

        /* Dark mode handling */
        @media (prefers-color-scheme: dark) {
            .print-area {
                background: white !important;
                color: black !important;
            }
        }

        /* Tambahkan style untuk wrapper */
        .invoice-wrapper {
            @apply p-8 rounded-xl;
            @apply bg-gray-100 dark:bg-gray-900;
            /* Background yang responsive terhadap mode */
            min-height: calc(var(--a4-height) + 4rem);
        }

        /* Style untuk pola background */
        .invoice-wrapper {
            background-image:
                radial-gradient(circle at 1px 1px, rgb(203 213 225) 1px, transparent 0),
                radial-gradient(circle at 1px 1px, rgb(203 213 225) 1px, transparent 0);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            @apply shadow-md dark:shadow-xl;
        }

        /* Dark mode untuk pola background */
        @media (prefers-color-scheme: dark) {
            .invoice-wrapper {
                background-image:
                    radial-gradient(circle at 1px 1px, rgb(107 114 128 / 0.5) 1px, transparent 0),
                    radial-gradient(circle at 1px 1px, rgb(107 114 128 / 0.5) 1px, transparent 0);
            }
        }

        /* Print style - sembunyikan wrapper saat print */
        @media print {
            .invoice-wrapper {
                background: none !important;
                padding: 0 !important;
                min-height: auto !important;
                box-shadow: none !important;
            }
        }
    </style>

    <div class="invoice-wrapper">
        <div class="print-area">
            <!-- Header -->
            <header class="border-b border-gray-100 p-6 sm:p-8 no-break" role="banner">
                <!-- Main Header Content -->
                <div class="flex items-center justify-between gap-6 sm:gap-8">
                    <div class="relative">
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-primary-600">E-FAKTUR</h1>
                        <p class="mt-2 text-lg sm:text-xl text-black/80">NO. FAKTUR: {{ $this->record->invoice_number }}
                        </p>
                    </div>

                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                        class="h-20 sm:h-24 w-20 sm:w-24 object-cover rounded-full border-2 border-gray-200 print:h-20 print:w-20">
                </div>
            </header>

            <!-- Content -->
            <main class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Project Details -->
                    <section class="space-y-4 no-break">
                        <div>
                            <h3 class="text-lg font-semibold text-black flex items-center">
                                Proyek: {{ $this->record->project->project_name }}
                            </h3>
                            <div class="mt-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div>
                                    <span class="font-medium text-black/90">Deskripsi Proyek:</span>
                                    <div class="font-medium text-sm text-black/80 mt-1 prose prose-sm max-w-none">
                                        {!! $this->record->project->description !!}
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="font-medium text-black/90 text-base">
                                        Tipe Pembayaran:
                                        @if ($this->record->payment_type === 'full')
                                            Bayar Penuh
                                        @else
                                            Bayar Bertahap
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Payment Details -->
                    <section class="no-break">
                        <h3 class="text-lg font-semibold text-black mb-3">Rincian Pembayaran:</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                            <table class="w-full">
                                <tbody>
                                    <tr class="border-b border-gray-200">
                                        <td class="p-3 text-black/80">Subtotal</td>
                                        <td class="p-3 font-medium text-right text-black">
                                            Rp {{ number_format($this->record->project->price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="p-3 text-black/80">Pajak ({{ $this->record->tax_amount }}%)</td>
                                        <td class="p-3 font-medium text-right text-black">
                                            Rp
                                            {{ number_format(($this->record->project->price * $this->record->tax_amount) / 100, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="p-3 font-semibold text-black">Total</td>
                                        <td class="p-3 font-bold text-right text-primary-600">
                                            Rp {{ number_format($this->record->total_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="p-3 text-black/80">Jumlah Dibayar</td>
                                        <td class="p-3 font-medium text-right text-success-600">
                                            Rp {{ number_format($this->record->paid_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 text-black/80">Sisa Pembayaran</td>
                                        <td class="p-3 font-medium text-right text-danger-600">
                                            Rp {{ number_format($this->record->remaining_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>

            <!-- Footer -->
            <footer class="mt-6 pt-4 border-t border-gray-200 px-4 pb-4 no-break">
                <div class="flex flex-col sm:flex-row sm:justify-between text-sm text-black/80">
                    <div class="mb-4 sm:mb-0">
                        <p class="leading-relaxed flex items-center">
                            @svg('heroicon-o-user', 'w-4 h-4 mr-2')
                            Dibuat oleh: {{ $this->record->creator->name }}
                        </p>
                        <p class="leading-relaxed flex items-center mt-2">
                            @svg('heroicon-o-calendar', 'w-4 h-4 mr-2')
                            Dibuat pada: {{ $this->record->created_at->isoFormat('D MMMM Y HH:mm') }}
                        </p>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <p class="leading-relaxed flex items-center justify-end">
                            @svg('heroicon-o-building-office', 'w-4 h-4 mr-2')
                            Dibuat di:{{ config('app.name') }}
                        </p>
                        <p class="leading-relaxed flex items-center justify-end mt-2 text-sm text-black/80">
                            @svg(
                                match ($this->record->status) {
                                    'draft' => 'fluentui-send-clock-20-o',
                                    'sent' => 'fluentui-mail-checkmark-20-o',
                                    'partially_paid' => 'fluentui-money-calculator-20-o',
                                    'paid' => 'fluentui-receipt-money-20-o',
                                    'cancelled' => 'fluentui-dismiss-circle-20-o',
                                    default => 'fluentui-send-clock-20-o',
                                },
                                'w-4 h-4 mr-2'
                            )Status:
                            {{ match ($this->record->status) {
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'paid' => 'Bayar Lunas',
                                'partially_paid' => 'Bayar Sebagian',
                                'cancelled' => 'Dibatalkan',
                                default => $this->record->status,
                            } }}
                        </p>
                    </div>
                </div>

                <!-- Thank You Message -->
                <div class="text-center mt-6">
                    <p class="text-base text-black/80 italic">
                        "Heyoo! Makasih banyak ya
                        <b>{{ $this->record->project->user?->name ?? 'Pengguna Sudah Dihapus' }}</b> sudah percaya sama
                        Artelia.DEV!
                    </p>
                    <p class="text-base text-black/80 italic mb-2">Semoga projectnya sukses dan kita bisa kolaborasi
                        lagi di project selanjutnya!"</p>
                    <p class="text-sm text-black/80 font-medium">----- Denis Djodian Ardika -----</p>
                    <p class="text-xs text-black/80">Founder Artelia.DEV & Web By Artelia</p>
                </div>

                <!-- Contact Information -->
                <div class="flex justify-between mt-4 pt-4 border-t border-gray-200 text-sm text-black/80">
                    <div class="flex items-center">
                        @svg('heroicon-o-phone', 'w-4 h-4 mr-2')
                        <span>{{ $this->record->creator->phone_number ?? '+62 821-4151-7722' }}</span>
                    </div>
                    <div class="flex items-center">
                        @svg('heroicon-o-envelope', 'w-4 h-4 mr-2')
                        <span>{{ $this->record->creator->email ?? 'denis@artelia.dev' }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

</x-filament-panels::page>
