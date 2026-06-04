<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;

class BlockController extends Controller
{
    public function index(): View
    {
        $blocks = Block::withCount([
            'apartments',
            'apartments as free_count'     => fn ($q) => $q->where('status', 'free'),
            'apartments as reserved_count' => fn ($q) => $q->where('status', 'reserved'),
            'apartments as sold_count'     => fn ($q) => $q->where('status', 'sold'),
        ])
        ->orderBy('sort_order')
        ->get();

        return view('blocks.index', compact('blocks'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->adminOnly();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:50'],
            'code'         => ['required', 'string', 'max:10', 'unique:blocks,code'],
            'address'      => ['nullable', 'string', 'max:255'],
            'total_floors' => ['required', 'integer', 'min:1', 'max:100'],
            'color'        => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? (int) Block::max('sort_order') + 1;
        $block = Block::create($data);

        return response()->json([
            'success' => true,
            'message' => "{$block->name} qo'shildi.",
            'block'   => $block,
        ]);
    }

    public function update(Request $request, Block $block): JsonResponse
    {
        $this->adminOnly();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:50'],
            'code'         => ['required', 'string', 'max:10', 'unique:blocks,code,' . $block->id],
            'address'      => ['nullable', 'string', 'max:255'],
            'total_floors' => ['required', 'integer', 'min:1', 'max:100'],
            'color'        => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ]);

        $block->update($data);

        return response()->json([
            'success' => true,
            'message' => "{$block->name} yangilandi.",
            'block'   => $block->fresh(),
        ]);
    }

    public function toggleActive(Block $block): JsonResponse
    {
        $this->adminOnly();

        $block->update(['is_active' => !$block->is_active]);
        $state = $block->is_active ? 'faollashtirildi' : "o'chirildi";

        return response()->json([
            'success'   => true,
            'message'   => "{$block->name} {$state}.",
            'is_active' => $block->is_active,
        ]);
    }

    public function destroy(Block $block): JsonResponse
    {
        $this->adminOnly();

        if ($block->apartments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$block->name} blokida xonadonlar mavjud. Avval xonadonlarni o'chiring.",
            ], 422);
        }

        $name = $block->name;
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => "{$name} o'chirildi.",
        ]);
    }

    private function adminOnly(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Faqat admin uchun.');
        }
    }
}
