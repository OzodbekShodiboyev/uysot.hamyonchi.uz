<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
h1 { font-size: 17px; text-align: center; margin-bottom: 4px; font-weight: bold; }
.subtitle { text-align: center; color: #555; margin-bottom: 18px; font-size: 11px; }
.contract-no { font-family: Courier New, monospace; font-size: 16px; font-weight: bold; color: #185FA5; }
.section-title {
    font-size: 13px; font-weight: bold;
    border-bottom: 2px solid #1D9E75;
    padding-bottom: 4px; margin: 16px 0 8px;
    color: #1D9E75;
}
table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
td, th { padding: 5px 8px; border: 1px solid #ddd; font-size: 11px; }
th { background: #f5f5f5; font-weight: bold; text-align: left; }
.right { text-align: right; }
.center { text-align: center; }
.total-row { background: #f0faf5; font-weight: bold; }
.footer { margin-top: 50px; }
.sign-row { display: flex; justify-content: space-between; gap: 30px; }
.sign-box { flex: 1; text-align: center; }
.sign-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 6px; font-size: 11px; color: #444; }
.highlight { color: #1D9E75; font-weight: bold; }
</style>
</head>
<body>

<h1>SOTIB OLISH-SOTISH SHARTNOMASI</h1>
<p class="subtitle">
    Raqam: <span class="contract-no">{{ $contract->contract_number }}</span>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    Sana: {{ $contract->signed_date?->format('d.m.Y') ?? now()->format('d.m.Y') }}
</p>

{{-- Xonadon --}}
<p class="section-title">XONADON MA'LUMOTLARI</p>
<table>
<tr>
    <th width="25%">Blok</th>
    <td>{{ $contract->apartment->block->name }}</td>
    <th width="25%">Xonadon №</th>
    <td>{{ $contract->apartment->number }}-xonadon</td>
</tr>
<tr>
    <th>Qavat</th>
    <td>{{ $contract->apartment->floor }}-qavat</td>
    <th>Xonalar soni</th>
    <td>{{ $contract->apartment->rooms }} xona</td>
</tr>
<tr>
    <th>Umumiy maydon</th>
    <td>{{ $contract->apartment->area_total }} m²</td>
    <th>Ta'mir turi</th>
    <td>{{ $contract->apartment->renovation_label }}</td>
</tr>
<tr>
    <th>Manzil</th>
    <td colspan="3">{{ $contract->apartment->block->address ?? '—' }}</td>
</tr>
</table>

{{-- Xaridor --}}
<p class="section-title">XARIDOR (MIJOZ) MA'LUMOTLARI</p>
<table>
<tr>
    <th width="25%">To'liq ism (F.I.Sh.)</th>
    <td colspan="3">{{ $contract->client->full_name }}</td>
</tr>
<tr>
    <th>Telefon</th>
    <td>{{ $contract->client->phone }}</td>
    <th>Passport seriyasi</th>
    <td>{{ $contract->client->passport_series ?? '—' }}</td>
</tr>
<tr>
    <th>PINFL</th>
    <td>{{ $contract->client->pinfl ?? '—' }}</td>
    <th>Tug'ilgan sana</th>
    <td>{{ $contract->client->birth_date?->format('d.m.Y') ?? '—' }}</td>
</tr>
<tr>
    <th>Yashash manzili</th>
    <td colspan="3">{{ $contract->client->address ?? '—' }}</td>
</tr>
<tr>
    <th>Ish joyi</th>
    <td colspan="3">{{ $contract->client->workplace ?? '—' }}</td>
</tr>
</table>

{{-- Egasi --}}
@if($contract->apartment->owner)
<p class="section-title">XONADON EGASI MA'LUMOTLARI</p>
<table>
<tr>
    <th width="25%">To'liq ism</th>
    <td>{{ $contract->apartment->owner->full_name }}</td>
    <th width="25%">Telefon</th>
    <td>{{ $contract->apartment->owner->phone }}</td>
</tr>
<tr>
    <th>Passport</th>
    <td>{{ $contract->apartment->owner->passport_series ?? '—' }}</td>
    <th>PINFL</th>
    <td>{{ $contract->apartment->owner->pinfl ?? '—' }}</td>
</tr>
</table>
@endif

{{-- To'lov shartlari --}}
<p class="section-title">TO'LOV SHARTLARI</p>
<table>
<tr>
    <th width="25%">To'lov turi</th>
    <td colspan="3">{{ $contract->payment_type_label }}</td>
</tr>
<tr>
    <th>Umumiy narx</th>
    <td>{{ number_format($contract->total_price) }} so'm</td>
    @if($contract->discount_amount > 0)
    <th>Chegirma</th>
    <td>-{{ number_format($contract->discount_amount) }} so'm</td>
    @else
    <td colspan="2"></td>
    @endif
</tr>
<tr class="total-row">
    <th>Yakuniy narx</th>
    <td colspan="3" class="highlight">{{ number_format($contract->final_price) }} so'm</td>
</tr>
@if($contract->payment_type === 'installment')
<tr>
    <th>Boshlang'ich to'lov</th>
    <td>{{ number_format($contract->initial_payment) }} so'm ({{ $contract->initial_percent }}%)</td>
    <th>Oylik to'lov</th>
    <td class="highlight">{{ number_format($contract->monthly_payment) }} so'm</td>
</tr>
<tr>
    <th>Muddat</th>
    <td>{{ $contract->installment_months }} oy</td>
    <th>Boshlanish — tugash</th>
    <td>
        {{ $contract->installment_start_date?->format('d.m.Y') ?? '—' }}
        — {{ $contract->installment_end_date?->format('d.m.Y') ?? '—' }}
    </td>
</tr>
@endif
@if($contract->expected_handover_date)
<tr>
    <th>Topshirish sanasi (reja)</th>
    <td colspan="3">{{ $contract->expected_handover_date->format('d.m.Y') }}</td>
</tr>
@endif
</table>

{{-- To'lov jadvali --}}
@if($contract->payment_type === 'installment' && $contract->paymentSchedules->count())
<p class="section-title">TO'LOV JADVALI</p>
<table>
    <tr>
        <th class="center" width="8%">№</th>
        <th>To'lov sanasi</th>
        <th class="right">Summa (so'm)</th>
        <th class="right">To'langan (so'm)</th>
        <th class="center">Holat</th>
    </tr>
    @foreach($contract->paymentSchedules as $s)
    <tr>
        <td class="center">{{ $s->payment_number }}</td>
        <td>{{ $s->due_date->format('d.m.Y') }}</td>
        <td class="right">{{ number_format($s->amount) }} so'm</td>
        <td class="right">
            @if($s->paid_amount > 0) {{ number_format($s->paid_amount) }} so'm @else — @endif
        </td>
        <td class="center">{{ $s->status_label }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
        <td colspan="2" class="right">Jami:</td>
        <td class="right">{{ number_format($contract->remaining_amount) }} so'm</td>
        <td class="right">{{ number_format($contract->paid_amount) }} so'm</td>
        <td></td>
    </tr>
</table>
@endif

{{-- Imzolar --}}
<div class="footer">
    <table style="border: none;">
        <tr>
            <td style="border: none; width: 45%; text-align: center; padding: 0;">
                <p style="border-top: 1px solid #333; margin-top: 60px; padding-top: 8px; font-size: 11px; color: #444;">
                    <strong>Xaridor (Mijoz)</strong><br>
                    {{ $contract->client->full_name }}<br>
                    Imzo: _________________________
                </p>
            </td>
            <td style="border: none; width: 10%;"></td>
            <td style="border: none; width: 45%; text-align: center; padding: 0;">
                <p style="border-top: 1px solid #333; margin-top: 60px; padding-top: 8px; font-size: 11px; color: #444;">
                    <strong>Menejer</strong><br>
                    {{ $contract->manager->name }}<br>
                    Imzo: _________________________
                </p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
