<?php

namespace App\Http\Controllers;

use App\Models\{Block, Lead};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q');
        $view   = $request->get('view', 'kanban'); // kanban | list

        $allLeads = Lead::with(['interestedBlock'])
            ->search($search)
            ->orderBy('next_follow_up')
            ->orderByDesc('created_at')
            ->get();

        $statusDefs = Lead::statuses();

        // Kanban: har bir status uchun guruhlab chiqarish
        $kanban = collect($statusDefs)->map(fn ($info, $key) => [
            'info'  => $info,
            'leads' => $allLeads->where('status', $key)->values(),
        ]);

        $counts = $allLeads->groupBy('status')->map->count()->toArray();
        $counts['all'] = $allLeads->count();

        $todayFollowUps = $allLeads->filter(
            fn ($l) => $l->next_follow_up && $l->next_follow_up->isToday()
                       && !in_array($l->status, ['lost', 'converted'])
        )->count();

        $blocks = Block::active()->get();

        return view('leads.index', compact(
            'kanban', 'allLeads', 'counts', 'search', 'blocks', 'todayFollowUps', 'view'
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'           => ['required', 'string', 'max:120'],
            'phone'               => ['required', 'string', 'max:20'],
            'phone_extra'         => ['nullable', 'string', 'max:20'],
            'source'              => ['required', 'in:instagram,telegram,phone,referral,office,banner,other'],
            'status'              => ['required', 'in:new,contacted,consultation,considering,hot,lost,converted'],
            'interested_block_id' => ['nullable', 'integer', 'exists:blocks,id'],
            'rooms'               => ['nullable', 'integer', 'min:1', 'max:10'],
            'budget'              => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string'],
            'next_follow_up'      => ['nullable', 'date'],
            'assigned_to'         => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $data['created_by'] = auth()->id();

        $lead = Lead::create($data);
        $lead->load(['interestedBlock', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => "Lead #{$lead->id} — {$lead->full_name} qo'shildi.",
            'lead'    => $lead,
        ]);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $data = $request->validate([
            'full_name'           => ['sometimes', 'string', 'max:120'],
            'phone'               => ['sometimes', 'string', 'max:20'],
            'phone_extra'         => ['nullable', 'string', 'max:20'],
            'source'              => ['sometimes', 'in:instagram,telegram,phone,referral,office,banner,other'],
            'status'              => ['sometimes', 'in:new,contacted,consultation,considering,hot,lost,converted'],
            'interested_block_id' => ['nullable', 'integer', 'exists:blocks,id'],
            'rooms'               => ['nullable', 'integer', 'min:1', 'max:10'],
            'budget'              => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string'],
            'next_follow_up'      => ['nullable', 'date'],
            'assigned_to'         => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $lead->update($data);
        $lead->load(['interestedBlock', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => "Lead #{$lead->id} yangilandi.",
            'lead'    => $lead,
        ]);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => "Lead #{$lead->id} o'chirildi.",
        ]);
    }

    public function quickStatus(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:new,contacted,consultation,considering,hot,lost,converted'],
        ]);

        $lead->update(['status' => $request->status]);

        return response()->json([
            'success'    => true,
            'message'    => "Holat yangilandi: {$lead->status_info['label']}",
            'statusInfo' => $lead->status_info,
        ]);
    }
}
