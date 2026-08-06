@extends('layouts.app')

@section('title', 'AI Copilot - BusinessMate')

@section('content')
@php
    $isAiOnline = $aiServiceStatus === 'online';
    $isAiFallback = $aiServiceStatus === 'fallback';

    $serviceLabel = match ($aiServiceStatus) {
        'online' => 'AI Online',
        'fallback' => 'Mode Cadangan',
        'no_products' => 'Belum Ada Produk',
        default => 'AI Offline',
    };

    $generatedLabel = $aiGeneratedAt
        ? \Carbon\Carbon::parse($aiGeneratedAt)
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->translatedFormat('d M Y, H:i') . ' WIB'
        : 'Belum tersedia';
@endphp

<div class="space-y-6">
    <!-- Header halaman -->
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="mb-1 text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
                Asisten Bisnis
            </p>
            <h1 class="text-2xl font-bold text-slate-900">
                AI Copilot
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Insight dan rekomendasi berdasarkan data bisnis Anda.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold
                {{ $isAiOnline
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : ($isAiFallback
                        ? 'border-amber-200 bg-amber-50 text-amber-700'
                        : 'border-slate-200 bg-slate-50 text-slate-600') }}">
                <span class="h-2 w-2 rounded-full
                    {{ $isAiOnline
                        ? 'bg-emerald-500'
                        : ($isAiFallback ? 'bg-amber-500' : 'bg-slate-400') }}">
                </span>
                {{ $serviceLabel }}
            </span>

            <a
                href="{{ route('ai.copilot', ['refresh' => 1]) }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
                <i class="fas fa-sync-alt"></i>
                Perbarui Insight
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Chatbot dan insight ditampilkan berdampingan pada layar desktop -->
    <div class="grid grid-cols-1 items-stretch gap-6 xl:grid-cols-5">
    <!-- Chatbot AI Copilot -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <i class="fas fa-comments"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">
                        Tanya AI tentang Bisnis
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">
                        Jawaban dibuat dari inventory, transaksi POS, dan hasil prediksi bisnis Anda.
                    </p>
                </div>
            </div>

            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Asisten Data Aktif
            </span>
        </div>

        <div
            id="ai-chat-messages"
            class="h-[350px] space-y-4 overflow-y-auto bg-slate-50/60 p-5"
            aria-live="polite"
        >
            <!-- Pesan pembuka AI -->
            <div class="flex items-start gap-3" data-chat-message="assistant">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm text-white shadow-sm">
                    <i class="fas fa-robot"></i>
                </div>

                <div class="max-w-[85%] sm:max-w-[75%]">
                    <div class="rounded-2xl rounded-tl-md border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">Halo! Saya siap membantu membaca data bisnis Anda. Coba tanyakan kondisi stok, rekomendasi restok, pendapatan, laba, atau produk terlaris.</p>
                    </div>
                    <p class="mt-1 px-1 text-[11px] text-slate-400">
                        AI Copilot
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-white p-4">
            <div
                id="ai-quick-questions"
                class="mb-3 flex flex-wrap gap-2"
            >
                <button type="button" data-question="Produk apa yang stoknya kritis?" class="ai-quick-question rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                    Stok kritis
                </button>
                <button type="button" data-question="Berikan rekomendasi restok" class="ai-quick-question rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                    Rekomendasi restok
                </button>
                <button type="button" data-question="Berapa pendapatan hari ini?" class="ai-quick-question rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                    Pendapatan hari ini
                </button>
                <button type="button" data-question="Tampilkan produk terlaris" class="ai-quick-question rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                    Produk terlaris
                </button>
            </div>

            <form id="ai-chat-form" class="flex items-end gap-2">
                <div class="min-w-0 flex-1">
                    <label for="ai-chat-input" class="sr-only">
                        Pertanyaan untuk AI Copilot
                    </label>
                    <textarea
                        id="ai-chat-input"
                        name="question"
                        rows="1"
                        minlength="2"
                        maxlength="500"
                        required
                        placeholder="Contoh: Produk apa yang perlu direstok?"
                        class="max-h-32 min-h-[46px] w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-300 focus:ring-2 focus:ring-blue-100"
                    ></textarea>
                    <p id="ai-chat-error" class="mt-1 hidden text-xs text-red-600"></p>
                </div>

                <button
                    id="ai-chat-submit"
                    type="submit"
                    class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                    aria-label="Kirim pertanyaan"
                >
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>

            <p class="mt-2 text-[11px] text-slate-400">
                Jawaban dibatasi pada data bisnis dari akun yang sedang login.
            </p>
        </div>
    </section>

        <!-- Ringkasan prediksi dan daftar insight prioritas -->
        <div class="space-y-4 xl:col-span-2">
        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm">
                        <i class="fas fa-brain text-sm"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900">
                                Ringkasan Prediksi
                            </h2>
                            <span class="rounded-full border border-blue-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                {{ $aiModeLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs leading-relaxed text-slate-600">
                            {{ $aiSummary }}
                        </p>
                    </div>
                </div>

                <span class="shrink-0 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-blue-700 shadow-sm">
                    {{ $forecastDays }} Hari
                </span>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-blue-100 pt-2 text-[11px] text-slate-400">
                <span>Diproses {{ $generatedLabel }}</span>

                @if(!$isAiOnline)
                    <span class="font-medium text-amber-600">
                        {{ $aiServiceMessage }}
                    </span>
                @endif
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">
                        Insight Prioritas
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">
                        Disusun dari prediksi permintaan, kondisi stok, dan transaksi POS.
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $suggestions->count() }} Insight
                </span>
            </div>

            <div class="max-h-[390px] space-y-3 overflow-y-auto p-4">
                @forelse($suggestions as $suggestion)
                    <article class="rounded-xl border p-3
                        {{ $suggestion['type'] === 'critical'
                            ? 'border-red-100 bg-red-50/60'
                            : ($suggestion['type'] === 'performance'
                                ? 'border-emerald-100 bg-emerald-50/60'
                                : 'border-blue-100 bg-blue-50/60') }}">
                        <div class="flex flex-col gap-3">
                            <div class="flex min-w-0 gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm
                                    {{ $suggestion['type'] === 'critical'
                                        ? 'text-red-600'
                                        : ($suggestion['type'] === 'performance'
                                            ? 'text-emerald-600'
                                            : 'text-blue-600') }}">
                                    <i class="fas
                                        {{ $suggestion['type'] === 'critical'
                                            ? 'fa-exclamation-triangle'
                                            : ($suggestion['type'] === 'performance'
                                                ? 'fa-chart-line'
                                                : 'fa-box-open') }}">
                                    </i>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold text-slate-900">
                                            {{ $suggestion['title'] }}
                                        </h3>
                                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold shadow-sm
                                            {{ $suggestion['type'] === 'critical'
                                                ? 'text-red-600'
                                                : ($suggestion['type'] === 'performance'
                                                    ? 'text-emerald-600'
                                                    : 'text-blue-600') }}">
                                            {{ $suggestion['badge'] }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-xs leading-relaxed text-slate-600">
                                        {{ $suggestion['message'] }}
                                    </p>
                                    <p class="mt-2 text-xs text-slate-400">
                                        {{ $suggestion['meta'] }}
                                    </p>

                                    @if(!is_null($suggestion['estimated_cost']))
                                        <p class="mt-2 text-xs font-semibold text-slate-600">
                                            Estimasi biaya restok:
                                            Rp {{ number_format($suggestion['estimated_cost'], 0, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <a
                                href="{{ $suggestion['action_url'] }}"
                                class="inline-flex w-fit shrink-0 items-center justify-center gap-2 self-end rounded-lg border border-white bg-white px-3 py-2 text-xs font-semibold text-blue-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50"
                            >
                                {{ $suggestion['action'] }}
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="flex min-h-[260px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <i class="fas fa-check"></i>
                        </div>
                        <h3 class="mt-4 font-bold text-slate-900">
                            Belum Ada Insight Prioritas
                        </h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-500">
                            Belum ada kondisi yang memerlukan tindakan. Insight akan muncul setelah produk dan transaksi tersedia.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
        </div>
    </div>

    <!-- Statistik bisnis dan rekomendasi utama -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="font-bold text-slate-900">
                        Statistik Cepat
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">
                        Data aktual tenant yang sedang login.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Pendapatan Hari Ini</p>
                        <p class="mt-1 text-base font-bold text-slate-900">
                            Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Transaksi Hari Ini</p>
                        <p class="mt-1 text-base font-bold text-slate-900">
                            {{ number_format($todayTransactionCount, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-red-100 bg-red-50 p-3">
                        <p class="text-xs text-red-400">Stok Kritis</p>
                        <p class="mt-1 text-base font-bold text-red-700">
                            {{ number_format($criticalStockCount, 0, ',', '.') }} Produk
                        </p>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3">
                        <p class="text-xs text-blue-400">Terlaris 30 Hari</p>
                        <p class="mt-1 truncate text-base font-bold text-blue-700">
                            {{ $bestSellingProduct->name ?? 'Belum ada data' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="h-full rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-5 text-white shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h2 class="font-bold">Rekomendasi Utama</h2>
                </div>

                @if($primarySuggestion)
                    <p class="mt-4 text-sm font-semibold">
                        {{ $primarySuggestion['title'] }}
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-blue-100">
                        {{ $primarySuggestion['message'] }}
                    </p>

                    <a
                        href="{{ $primarySuggestion['action_url'] }}"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-50"
                    >
                        {{ $primarySuggestion['action'] }}
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                @else
                    <p class="mt-4 text-sm font-semibold">
                        Kondisi bisnis belum memerlukan tindakan prioritas.
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-blue-100">
                        Tambahkan produk dan lakukan transaksi POS agar AI memiliki data untuk dianalisis.
                    </p>
                @endif
            </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatForm = document.getElementById('ai-chat-form');
    const chatInput = document.getElementById('ai-chat-input');
    const submitButton = document.getElementById('ai-chat-submit');
    const messagesContainer = document.getElementById('ai-chat-messages');
    const quickQuestionsContainer = document.getElementById(
        'ai-quick-questions'
    );
    const errorElement = document.getElementById('ai-chat-error');

    const chatUrl = @json(route('ai.copilot.chat'));
    const csrfToken = @json(csrf_token());
    let isSending = false;

    function currentTime() {
        return new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).format(new Date()) + ' WIB';
    }

    function scrollToLatestMessage() {
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }

    function createIcon(role) {
        const iconWrapper = document.createElement('div');
        iconWrapper.className = role === 'user'
            ? 'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-sm text-slate-600'
            : 'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm text-white shadow-sm';

        const icon = document.createElement('i');
        icon.className = role === 'user'
            ? 'fas fa-user'
            : 'fas fa-robot';

        iconWrapper.appendChild(icon);

        return iconWrapper;
    }

    function appendMessage(role, text, action = null) {
        const row = document.createElement('div');
        row.dataset.chatMessage = role;
        row.className = role === 'user'
            ? 'flex flex-row-reverse items-start gap-3'
            : 'flex items-start gap-3';

        row.appendChild(createIcon(role));

        const contentWrapper = document.createElement('div');
        contentWrapper.className = 'max-w-[85%] sm:max-w-[75%]';

        const bubble = document.createElement('div');
        bubble.className = role === 'user'
            ? 'rounded-2xl rounded-tr-md bg-blue-600 px-4 py-3 text-white shadow-sm'
            : 'rounded-2xl rounded-tl-md border border-slate-200 bg-white px-4 py-3 shadow-sm';

        const messageText = document.createElement('p');
        messageText.className = role === 'user'
            ? 'whitespace-pre-line text-sm leading-relaxed text-white'
            : 'whitespace-pre-line text-sm leading-relaxed text-slate-700';
        messageText.textContent = text;
        bubble.appendChild(messageText);

        if (role === 'assistant' && action?.url && action?.label) {
            const actionLink = document.createElement('a');
            actionLink.href = action.url;
            actionLink.className = 'mt-3 inline-flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-100';
            actionLink.textContent = action.label;

            const arrowIcon = document.createElement('i');
            arrowIcon.className = 'fas fa-arrow-right text-[10px]';
            actionLink.appendChild(arrowIcon);
            bubble.appendChild(actionLink);
        }

        const meta = document.createElement('p');
        meta.className = role === 'user'
            ? 'mt-1 px-1 text-right text-[11px] text-slate-400'
            : 'mt-1 px-1 text-[11px] text-slate-400';
        meta.textContent = role === 'user'
            ? 'Anda · ' + currentTime()
            : 'AI Copilot · ' + currentTime();

        contentWrapper.appendChild(bubble);
        contentWrapper.appendChild(meta);
        row.appendChild(contentWrapper);
        messagesContainer.appendChild(row);
        scrollToLatestMessage();

        return row;
    }

    function appendLoadingMessage() {
        const row = document.createElement('div');
        row.className = 'flex items-start gap-3';
        row.id = 'ai-chat-loading';
        row.appendChild(createIcon('assistant'));

        const bubble = document.createElement('div');
        bubble.className = 'flex items-center gap-1 rounded-2xl rounded-tl-md border border-slate-200 bg-white px-4 py-4 shadow-sm';

        for (let index = 0; index < 3; index++) {
            const dot = document.createElement('span');
            dot.className = 'h-2 w-2 animate-pulse rounded-full bg-slate-400';
            dot.style.animationDelay = `${index * 150}ms`;
            bubble.appendChild(dot);
        }

        row.appendChild(bubble);
        messagesContainer.appendChild(row);
        scrollToLatestMessage();
    }

    function removeLoadingMessage() {
        document.getElementById('ai-chat-loading')?.remove();
    }

    function renderQuickQuestions(suggestions) {
        if (!Array.isArray(suggestions) || suggestions.length === 0) {
            return;
        }

        quickQuestionsContainer.innerHTML = '';

        suggestions.slice(0, 4).forEach(function (question) {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.question = question;
            button.className = 'ai-quick-question rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600';
            button.textContent = question;
            quickQuestionsContainer.appendChild(button);
        });
    }

    function showError(message) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }

    function clearError() {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    }

    function setSendingState(sending) {
        isSending = sending;
        submitButton.disabled = sending;
        chatInput.disabled = sending;
        submitButton.innerHTML = sending
            ? '<i class="fas fa-spinner fa-spin"></i>'
            : '<i class="fas fa-paper-plane"></i>';
    }

    async function sendQuestion(question) {
        const cleanQuestion = question.trim();

        if (isSending || cleanQuestion.length < 2) {
            if (cleanQuestion.length < 2) {
                showError('Pertanyaan minimal terdiri dari 2 karakter.');
            }
            return;
        }

        clearError();
        appendMessage('user', cleanQuestion);
        chatInput.value = '';
        setSendingState(true);
        appendLoadingMessage();

        try {
            const response = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    question: cleanQuestion
                })
            });

            const data = await response.json();

            if (!response.ok) {
                const validationMessage = data.errors?.question?.[0];
                throw new Error(
                    validationMessage
                        || data.message
                        || 'Pertanyaan belum dapat diproses.'
                );
            }

            appendMessage('assistant', data.answer, data.action);
            renderQuickQuestions(data.suggestions);
        } catch (error) {
            appendMessage(
                'assistant',
                error.message
                    || 'Terjadi kesalahan saat menghubungi AI Copilot.'
            );
        } finally {
            removeLoadingMessage();
            setSendingState(false);
            chatInput.focus();
        }
    }

    chatForm.addEventListener('submit', function (event) {
        event.preventDefault();
        sendQuestion(chatInput.value);
    });

    chatInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            chatForm.requestSubmit();
        }
    });

    quickQuestionsContainer.addEventListener('click', function (event) {
        const button = event.target.closest('[data-question]');

        if (!button || isSending) {
            return;
        }

        sendQuestion(button.dataset.question);
    });
});
</script>
@endsection