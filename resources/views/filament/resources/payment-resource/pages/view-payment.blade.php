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
            display: flex;
            flex-direction: column;
        }

        /* Style untuk gambar bukti pembayaran */
        .payment-proof-container {
            background-color: rgb(249 250 251);
            border: 1px solid rgb(229 231 235);
            border-radius: 0.5rem;
            padding: 1rem;
            max-height: calc(var(--a4-height) * 0.4);
            overflow: hidden;
        }

        .payment-proof-image {
            display: block;
            max-width: calc(var(--a4-width) - (var(--page-margin) * 4));
            max-height: calc(var(--a4-height) * 0.35);
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 0 auto;
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

                .payment-proof-image {
                    max-width: 100%;
                    max-height: 60vh;
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

            .payment-proof-container {
                break-inside: avoid;
                page-break-inside: avoid;
                background-color: white !important;
            }

            .payment-proof-image {
                max-height: calc(var(--a4-height) * 0.3) !important;
                page-break-inside: avoid;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-break {
                page-break-inside: avoid;
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
        .payment-wrapper {
            @apply p-8 rounded-xl;
            @apply bg-gray-100 dark:bg-gray-900;
            min-height: calc(var(--a4-height) + 4rem);
        }

        /* Style untuk pola background */
        .payment-wrapper {
            background-image:
                radial-gradient(circle at 1px 1px, rgb(203 213 225) 1px, transparent 0),
                radial-gradient(circle at 1px 1px, rgb(203 213 225) 1px, transparent 0);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            @apply shadow-md dark:shadow-xl;
        }

        /* Dark mode untuk pola background */
        @media (prefers-color-scheme: dark) {
            .payment-wrapper {
                background-image:
                    radial-gradient(circle at 1px 1px, rgb(107 114 128 / 0.5) 1px, transparent 0),
                    radial-gradient(circle at 1px 1px, rgb(107 114 128 / 0.5) 1px, transparent 0);
            }
        }

        /* Print style - sembunyikan wrapper saat print */
        @media print {
            .payment-wrapper {
                background: none !important;
                padding: 0 !important;
                min-height: auto !important;
                box-shadow: none !important;
            }
        }
    </style>

    <div class="payment-wrapper">
        <div class="print-area">
            <!-- Header -->
            <header class="border-b border-gray-100 p-4 sm:p-6 no-break" role="banner">
                <div class="flex items-center justify-between gap-6">
                    <div class="relative">
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-primary-600">BUKTI PEMBAYARAN</h1>
                        <p class="mt-2 text-base sm:text-lg text-black/80">NO. PEMBAYARAN:
                            {{ $this->record->payment_number }}</p>
                        <p class="mt-1 text-sm text-gray-500">UNTUK FAKTUR: {{ $this->record->invoice->invoice_number }}
                        </p>
                    </div>

                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                        class="h-16 w-16 object-cover rounded-full border-2 border-gray-200 print:h-16 print:w-16">
                </div>
            </header>

            <!-- Content -->
            <main class="p-4 flex flex-col gap-4">
                <!-- Info Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Invoice Details -->
                    <section class="no-break">
                        <h3 class="text-lg font-semibold text-black flex items-center gap-2 mb-3">
                            @svg('fluentui-document-bullet-list-20', 'w-5 h-5 text-primary-500')
                            Detail Faktur
                        </h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="mb-2">Proyek: <span
                                    class="font-medium">{{ $this->record->invoice->project->project_name }}</span></p>
                            <p class="mb-2">Klien: <span
                                    class="font-medium">{{ $this->record->invoice->project->user->name }}</span></p>
                            <p>Total Tagihan: <span class="font-medium">Rp
                                    {{ number_format($this->record->invoice->total_amount, 0, ',', '.') }}</span></p>
                        </div>
                    </section>

                    <!-- Payment Status -->
                    <section class="no-break">
                        <h3 class="text-lg font-semibold text-black flex items-center gap-2 mb-3">
                            @svg('fluentui-money-20', 'w-5 h-5 text-primary-500')
                            Status Pembayaran
                        </h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="mb-2">Status:
                                <span class="inline-flex ml-2">
                                    <x-filament::badge :color="match ($this->record->status) {
                                        'pending' => 'gray',
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }">
                                        {{ match ($this->record->status) {
                                            'pending' => 'Menunggu',
                                            'verified' => 'Terverifikasi',
                                            'rejected' => 'Ditolak',
                                            default => $this->record->status,
                                        } }}
                                    </x-filament::badge>
                                </span>
                            </p>
                            <p class="mb-2">Metode: <span
                                    class="font-medium">{{ $this->record->payment_method === 'cash' ? 'Tunai' : 'Transfer Bank' }}</span>
                            </p>
                            <p>Jumlah: <span class="font-medium text-primary-600">Rp
                                    {{ number_format($this->record->amount, 0, ',', '.') }}</span></p>
                        </div>
                    </section>
                </div>

                <!-- Payment Proof -->
                @if ($this->record->payment_proof)
                    <section class="payment-proof-section no-break">
                        <h3 class="text-lg font-semibold text-black flex items-center gap-2 mb-3">
                            @svg('fluentui-image-20', 'w-5 h-5 text-primary-500')
                            Bukti Pembayaran
                        </h3>
                        <div class="payment-proof-container">
                            <img src="{{ Storage::url($this->record->payment_proof) }}"
                                alt="Bukti Pembayaran {{ $this->record->payment_number }}" class="payment-proof-image">
                            <div class="payment-proof-caption">
                                Bukti pembayaran untuk transaksi nomor: {{ $this->record->payment_number }}
                            </div>
                        </div>
                    </section>
                @endif

                <!-- Additional Info -->
                @if ($this->record->payment_notes)
                    <section class="no-break">
                        <h3 class="text-lg font-semibold text-black flex items-center gap-2 mb-3">
                            @svg('fluentui-text-box-20', 'w-5 h-5 text-primary-500')
                            Catatan Pembayaran
                        </h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-600">{{ $this->record->payment_notes }}</p>
                        </div>
                    </section>
                @endif

                <!-- Rejection Reason -->
                @if ($this->record->status === 'rejected' && $this->record->rejection_reason)
                    <section class="no-break">
                        <h3 class="text-lg font-semibold text-danger-600 flex items-center gap-2 mb-3">
                            @svg('fluentui-warning-20', 'w-5 h-5')
                            Alasan Penolakan
                        </h3>
                        <div class="bg-danger-50 border border-danger-200 rounded-lg p-4">
                            <p class="text-danger-600">{{ $this->record->rejection_reason }}</p>
                        </div>
                    </section>
                @endif
            </main>

            <!-- Footer -->
            <footer class="mt-auto pt-4 border-t border-gray-200 px-4 pb-4 no-break">
                <div class="flex flex-col sm:flex-row sm:justify-between text-sm text-black/80">
                    <div class="mb-4 sm:mb-0">
                        @if ($this->record->status === 'verified' && $this->record->verifier)
                            <p class="leading-relaxed flex items-center">
                                @svg('heroicon-o-check-badge', 'w-4 h-4 mr-2')
                                Diverifikasi oleh: {{ $this->record->verifier->name }}
                            </p>
                            <p class="leading-relaxed flex items-center mt-2">
                                @svg('heroicon-o-calendar', 'w-4 h-4 mr-2')
                                Waktu verifikasi: {{ $this->record->verified_at?->isoFormat('D MMMM Y HH:mm') }}
                            </p>
                        @endif
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <p class="leading-relaxed flex items-center justify-end">
                            @svg('heroicon-o-building-office', 'w-4 h-4 mr-2')
                            {{ config('app.name') }}
                        </p>
                        <p class="leading-relaxed flex items-center mt-2">
                            @svg('heroicon-o-calendar', 'w-4 h-4 mr-2')
                            {{ $this->record->created_at->isoFormat('D MMMM Y HH:mm') }}
                        </p>
                    </div>
                </div>

                <!-- Contact Information -->
                @if ($this->record->status === 'verified' && $this->record->verifier)
                    <div class="flex justify-between mt-4 pt-4 border-t border-gray-200 text-sm text-black/80">
                        <div class="flex items-center">
                            @svg('heroicon-o-phone', 'w-4 h-4 mr-2')
                            <span>{{ $this->record->verifier->phone ?? '+62 819-4346-2730' }}</span>
                        </div>
                        <div class="flex items-center">
                            @svg('heroicon-o-envelope', 'w-4 h-4 mr-2')
                            <span>{{ $this->record->verifier->email ?? 'info@webbyartelia.my.id' }}</span>
                        </div>
                    </div>
                @else
                    <div class="flex justify-between mt-4 pt-4 border-t border-gray-200 text-sm text-gray-400">
                        <div class="flex items-center">
                            @svg('heroicon-o-phone', 'w-4 h-4 mr-2')
                            <span>Belum di verifikasi</span>
                        </div>
                        <div class="flex items-center">
                            @svg('heroicon-o-envelope', 'w-4 h-4 mr-2')
                            <span>Belum di verifikasi</span>
                        </div>
                    </div>
                @endif
            </footer>
        </div>
    </div>
</x-filament-panels::page>
