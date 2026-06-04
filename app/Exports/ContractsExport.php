<?php

namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContractsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function title(): string
    {
        return 'Shartnomalar';
    }

    public function query()
    {
        return Contract::query()
            ->with(['apartment.block', 'client', 'manager'])
            ->when($this->filters['status']   ?? null, fn ($q) => $q->where('status', $this->filters['status']))
            ->when($this->filters['block_id'] ?? null, fn ($q) =>
                $q->whereHas('apartment', fn ($a) => $a->where('block_id', $this->filters['block_id'])))
            ->when($this->filters['pay_type'] ?? null, fn ($q) => $q->where('payment_type', $this->filters['pay_type']))
            ->when($this->filters['year']     ?? null, fn ($q) => $q->where('contract_year', $this->filters['year']))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Shartnoma №',
            'Imzolangan sana',
            'Mijoz ismi',
            'Telefon',
            'Passport',
            'PINFL',
            'Blok',
            'Xonadon №',
            'Qavat',
            'Xonalar',
            'Maydon (m²)',
            "To'lov turi",
            "Yakuniy narx ($)",
            "Boshlang'ich ($)",
            "Oylik to'lov ($)",
            'Muddat (oy)',
            "To'langan ($)",
            "Qarz ($)",
            'Holat',
            'Menejer',
        ];
    }

    public function map($contract): array
    {
        return [
            $contract->contract_number,
            $contract->signed_date?->format('d.m.Y') ?? '—',
            $contract->client->full_name,
            $contract->client->phone,
            $contract->client->passport_series ?? '—',
            $contract->client->pinfl ?? '—',
            $contract->apartment->block->name,
            $contract->apartment->number,
            $contract->apartment->floor,
            $contract->apartment->rooms,
            $contract->apartment->area_total,
            $contract->payment_type === 'full' ? "100% to'liq" : "Bo'lib to'lash",
            number_format((float) $contract->final_price, 2),
            number_format((float) $contract->initial_payment, 2),
            number_format((float) $contract->monthly_payment, 2),
            $contract->installment_months,
            number_format((float) $contract->paid_amount, 2),
            number_format((float) $contract->debt_amount, 2),
            $contract->status_label,
            $contract->manager->name,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D9E75'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
